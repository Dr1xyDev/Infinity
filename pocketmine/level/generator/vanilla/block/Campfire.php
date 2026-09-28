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

namespace pocketmine\level\generator\vanilla\block;

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\entity\projectile\Projectile;
use pocketmine\level\generator\vanilla\entity\projectile\SplashPotion;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByBlockEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\inventory\FurnaceType;
use pocketmine\level\generator\vanilla\item\FlintSteel;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\item\LiquidBucket;
use pocketmine\level\generator\vanilla\item\Potion;
use pocketmine\level\generator\vanilla\item\Shovel;
use pocketmine\level\generator\vanilla\level\sound\CampfireSound;
use pocketmine\level\generator\vanilla\level\sound\FireExtinguishSound;
use pocketmine\level\generator\vanilla\level\sound\FlintSteelSound;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\RayTraceResult;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\Campfire as TileCampfire;
use pocketmine\level\generator\vanilla\tile\Tile;

use function mt_rand;

class Campfire extends Transparent
{
	protected $id = self::CAMPFIRE;
	protected $itemId = ItemIds::CAMPFIRE;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Campfire";
	}

	public function getHardness() : float
	{
		return 2.0;
	}

	public function getBlastResistance() : float
	{
		return 2.0;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_AXE;
	}

	public function getLightLevel() : int
	{
		return $this->isExtinguished() ? 0 : 15;
	}

	public function isExtinguished() : bool
	{
		return ($this->meta & 4) !== 0;
	}

	public function setExtinguished(bool $value) : void
	{
		if ($value) {
			$this->meta |= 4;
		} else {
			$this->meta &= ~4;
		}
	}

	public function getFacingDirection() : int
	{
		return $this->meta & 3;
	}

	public function setFacingDirection(int $direction) : void
	{
		$this->meta = ($this->meta & ~3) | ($direction & 3);
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return new AxisAlignedBB(
			$this->x,
			$this->y,
			$this->z,
			$this->x,
			$this->y + 0.4375,
			$this->z
		);
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool
	{
		return parent::canBePlacedAt($blockReplace, $clickVector, $face, $isClickedBlock) && $blockReplace->getSide(Facing::DOWN)->isSolid();
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if ($player instanceof Player) {
			$this->meta = ((int) $player->getDirection() + 3) % 4;
		}

		$this->getLevel()->setBlock($blockReplace, $this, true, true);

		Tile::createTile(Tile::CAMPFIRE, $this->getLevel(), TileCampfire::createNBT($this));

		return true;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get(ItemIds::COAL, 1, 2)
		];
	}

	public function getFurnaceType() : FurnaceType {
		return FurnaceType::CAMPFIRE;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		$tile = $this->getLevel()->getTile($this);
		if (!($tile instanceof TileCampfire)) {
			$tile = Tile::createTile(Tile::CAMPFIRE, $this->getLevel(), TileCampfire::createNBT($this));
			if (!($tile instanceof TileCampfire)) {
				return false;
			}
		}

		$isWaterItem = ($item instanceof LiquidBucket && !$item->getLiquid() instanceof Lava) || ($item->getId() === ItemIds::POTION && $item->getDamage() === 0);
		if ($isWaterItem) {
			if (!$this->isExtinguished()) {
				$this->extinguish();

				if ($player !== null && $player->hasFiniteResources()) {
					if ($item instanceof LiquidBucket) {
						$player->getInventory()->setItemInHand(ItemFactory::get(ItemIds::BUCKET));
					} else {
						$player->getInventory()->setItemInHand(ItemFactory::get(ItemIds::GLASS_BOTTLE));
					}
				}
				return true;
			}
		} elseif ($item instanceof Shovel) {
			if (!$this->isExtinguished()) {
				$this->extinguish();

				$item->applyDamage(1);
				if ($player !== null) {
					$player->getInventory()->setItemInHand($item);
				}
				return true;
			}
		} elseif ($item instanceof FlintSteel || $item->getId() === ItemIds::FIRE_CHARGE) {
			if ($this->isExtinguished()) {
				$this->ignite();

				if ($item instanceof FlintSteel) {
					$item->applyDamage(1);
				} else {
					$item->pop();
				}
				if ($player !== null) {
					$player->getInventory()->setItemInHand($item);
				}
				return true;
			}
		}

		$recipe = $this->level->getServer()->getCraftingManager()->matchFurnaceRecipe($item, $this->getFurnaceType());

		if ($recipe !== null) {
			$slot = -1;
			for ($i = 0; $i < 4; ++$i) {
				if ($tile->getInventory()->getItem($i)->isNull()) {
					$slot = $i;
					break;
				}
			}

			if ($slot !== -1) {
				$singleItem = clone $item;
				$singleItem->setCount(1);
				$tile->addItem($slot, $singleItem);

				if ($player !== null && !$player->isCreative()) {
					$item->pop();
					$player->getInventory()->setItemInHand($item);
				}

				if (!$this->isExtinguished()) {
					$this->level->scheduleDelayedBlockUpdate($this, 10);
				}
				return true;
			}
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->isExtinguished() && $this->getSide(Facing::UP)->getId() === BlockIds::WATER){
			$this->extinguish();
			//TODO: Waterlogging
		}
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function onEntityCollide(Entity $entity) : void{
		if($this->isExtinguished()){
			if($entity->isOnFire()){
				$this->ignite();
			}
		}elseif($entity instanceof Living){
			$entity->attack(new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_FIRE, $this->getEntityCollisionDamage()));
		}
	}

	protected function getEntityCollisionDamage() : int{
		return 1;
	}

	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void{
		if(!$this->isExtinguished() && $projectile instanceof SplashPotion && $projectile->getPotionId() === Potion::WATER){
			$this->extinguish();
		}
	}

	private function extinguish() : void{
		$this->level->addSound(new FireExtinguishSound($this));
		$this->setExtinguished(true);
		$this->level->setBlock($this, $this);
	}

	private function ignite() : void{
		$this->level->addSound(new FlintSteelSound($this));
		$this->setExtinguished(false);
		$this->level->setBlock($this, $this);
		$this->level->scheduleDelayedBlockUpdate($this, 10);
	}

	public function onScheduledUpdate() : void
	{
		$level = $this->getLevel();
		$tile = $level->getTile($this);
		if ($tile instanceof TileCampfire) {
			$cooking = $tile->onUpdate();
			if (!$this->isExtinguished()) {
				if (mt_rand(1, 40) === 1) {
					$level->addSound(new CampfireSound($this));
				}

				if ($cooking) {
					$level->scheduleDelayedBlockUpdate($this, 10);
				}
			}
		}
	}
}
