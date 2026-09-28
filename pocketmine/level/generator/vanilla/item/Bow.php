<?php

/*
 *
 *   _____       _                          _
 *  / ____|     | |                        (_)
 * | (___  _   _| |__  _ __ ___   __ _ _ __ _ _ __   ___
 *  \___ \| | | | '_ \| '_ ` _ \ / _` | '__| | '_ \ / _ \
 *  ____) | |_| | |_) | | | | | | (_| | |  | | | | |  __/
 * |_____/ \__,_|_.__/|_| |_| |_|\__,_|_|  |_|_| |_|\___|
 *
 * This program is private software. No license required.
 * Publication of this program is forbidden and will be punished.
 *
 * @author SEMENNEJO
 * @link vk.com/vk.snikers && t.me/semennejo
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\level\generator\vanilla\item;

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\projectile\Arrow as ArrowEntity;
use pocketmine\level\generator\vanilla\entity\projectile\Projectile;
use pocketmine\level\generator\vanilla\event\entity\EntityShootBowEvent;
use pocketmine\level\generator\vanilla\event\entity\ProjectileLaunchEvent;
use pocketmine\level\generator\vanilla\inventory\Inventory;
use pocketmine\level\generator\vanilla\item\enchantment\Enchantment;
use pocketmine\level\generator\vanilla\level\sound\BowShootSound;
use pocketmine\Player;

use function intdiv;
use function max;
use function min;

class Bow extends Tool implements Releasable
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::BOW, $meta, "Bow");
	}

	public function getFuelTime() : int
	{
		return 200;
	}

	public function getMaxDurability() : int
	{
		return 385;
	}

	public function getEnchantAbility() : int{
		return 1;
	}

	/**
	 * @return mixed[]|null
	 * @phpstan-return array{Arrow, Inventory}
	 */
	private function findArrow(Player $player) : ?array{
		$offHandInv = $player->getOffHandInventory();
		foreach ($offHandInv->getContents() as $item) {
			if ($item->getId() === ItemIds::ARROW) {
				return [$item, $offHandInv];
			}
		}

		$inv = $player->getInventory();
		foreach ($inv->getContents() as $item) {
			if ($item->getId() === ItemIds::ARROW) {
				return [$item, $inv];
			}
		}

		return null;
	}

	public function onReleaseUsing(Player $player) : bool{
		[$arrow, $inventory] = $this->findArrow($player);
		if($player->hasFiniteResources() && $inventory === null){
			return false;
		}

		if ($arrow === null) {
			$arrow = ItemFactory::get(ItemIds::ARROW);
		}

		$arrow = (clone $arrow)->setCount(1);

		$diff = $player->getItemUseDuration();
		$p = $diff / 20;
		$baseForce = min((($p ** 2) + $p * 2) / 3, 1);

		$entity = Entity::createEntity("Arrow", $player->getLevel(), Entity::createBaseNBT(
			$player->add(0, $player->getEyeHeight(), 0),
			$player->getDirectionVector(),
			($player->yaw > 180 ? 360 : 0) - $player->yaw,
			-$player->pitch
		), $player, $baseForce >= 1);
		if (!$entity instanceof ArrowEntity) {
			return false;
		}

		$infinity = $this->hasEnchantment(Enchantment::INFINITY);
		if($infinity){
			$entity->setPickupMode(ArrowEntity::PICKUP_CREATIVE);
		}
		if(($punchLevel = $this->getEnchantmentLevel(Enchantment::PUNCH)) > 0){
			$entity->setPunchKnockback($punchLevel);
		}
		if(($powerLevel = $this->getEnchantmentLevel(Enchantment::POWER)) > 0){
			$entity->setBaseDamage($entity->getBaseDamage() + (($powerLevel + 1) / 2));
		}
		if($this->hasEnchantment(Enchantment::FLAME)){
			$entity->setOnFire(intdiv($entity->getFireTicks(), 20) + 100);
		}

		$entity->setAuxValue($arrow->getDamage());
		if($arrow->getDamage() !== 0){
			foreach (Potion::getPotionEffectsById($arrow->getDamage() - 1) as $effect) {
				$entity->addMobEffect((clone $effect)->setDuration(max(1, (int) ($effect->getDuration() / 8))));
			}
		}

		$ev = new EntityShootBowEvent($player, $this, $entity, $baseForce * 3);

		if($baseForce < 0.1 || $diff < 5 || $player->isSpectator()){
			$ev->setCancelled();
		}

		$ev->call();

		$entity = $ev->getProjectile(); //This might have been changed by plugins

		if($ev->isCancelled()){
			$entity->flagForDespawn();
			return false;
		}

		$entity->setMotion($entity->getMotion()->multiply($ev->getForce()));

		if($entity instanceof Projectile){
			$projectileEv = new ProjectileLaunchEvent($entity);
			$projectileEv->call();
			if($projectileEv->isCancelled()){
				$ev->getProjectile()->flagForDespawn();
				return false;
			}

			$ev->getProjectile()->spawnToAll();
			$player->getLevel()->addSound(new BowShootSound($player));
		}else{
			$entity->spawnToAll();
		}

		if($player->hasFiniteResources()){
			if(!$infinity){ //TODO: tipped arrows are still consumed when Infinity is applied
				$inventory?->removeItem($arrow);
			}
			$this->applyDamage(1);
		}

		return true;
	}

	public function canStartUsingItem(Player $player) : bool{
		if (!$player->hasFiniteResources()) {
			return true;
		}

		return $this->findArrow($player) !== null;
	}
}
