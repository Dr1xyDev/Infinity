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

namespace pocketmine\level\generator\vanilla\entity;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\Grass;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorEventPacket;
use pocketmine\Player;

use function max;

abstract class Animal extends Mob implements Ageable
{
	protected $inLove = 0;
	protected $spawnableBlock = Block::GRASS;

	public function getBlockPathWeight(Vector3 $pos) : float
	{
		return $this->level->getBlock($pos->down()) instanceof Grass ? 10 : max($this->level->getRealBlockSkyLightAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ()), $this->level->getBlockLightAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ())) - 0.5;
	}

	public function canSpawnHere() : bool
	{
		return $this->level->getBlock($this->down())->getId() === $this->spawnableBlock && $this->level->getRealBlockSkyLightAt($this->getFloorX(), $this->getFloorY(), $this->getFloorZ()) > 8 && parent::canSpawnHere();
	}

	public function isBreedingItem(Item $item) : bool // TODO: Apply this to all animals
	{
		return $item->getId() === Item::WHEAT;
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool
	{
		$item = $player->getInventory()->getItemInHand();
		if ($this->isBreedingItem($item) && !$this->isImmobile()) {
			if (!$this->isBaby() && !$this->isInLove()) {
				$this->setInLove(true);

				if ($player->isSurvival()) {
					$item->pop();
					$player->getInventory()->setItemInHand($item);
				}
				return true;
			} elseif ($this->isBaby()) {
				if ($player->isSurvival()) {
					$item->pop();
					$player->getInventory()->setItemInHand($item);
				}
				return true;
			}
		}
		return parent::onInteract($player, $clickPos);
	}

	public function entityBaseTick(int $diff = 1) : bool
	{
		if ($this->isInLove()) {
			if ($this->inLove-- > 0 && $this->inLove % 10 === 0) {
				$this->broadcastEntityEvent(ActorEventPacket::LOVE_PARTICLES);
			}
		}
		return parent::entityBaseTick($diff);
	}

	public function setInLove(bool $value) : void
	{
		parent::setInLove($value);
		if ($value) {
			$this->inLove = 10;
		}
	}

	public function eatItem(Item $item) : void
	{
		$this->broadcastEntityEvent(ActorEventPacket::EATING_ITEM, $item->getId());
	}

	public function eatGrassBonus(Vector3 $pos) : void
	{
		// for sheep
	}

	public function allowLeashing() : bool
	{
		return !$this->isLeashed() && !$this->isImmobile();
	}

	protected function initEntity() : void
	{
		parent::initEntity();

		$this->inLove = $this->namedtag->getInt("InLove", 0);
	}

	public function canDespawn() : bool
	{
		return false;
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		$this->namedtag->setInt("InLove", $this->inLove);
	}

	public function getVariant() : int
	{
		return $this->propertyManager->getInt(self::DATA_VARIANT);
	}

	public function setVariant(int $variant) : void
	{
		$this->propertyManager->setInt(self::DATA_VARIANT, $variant);
	}

	public function getMarkVariant() : int
	{
		return $this->propertyManager->getInt(self::DATA_MARK_VARIANT);
	}

	public function setMarkVariant(int $markVariant) : void
	{
		$this->propertyManager->setInt(self::DATA_MARK_VARIANT, $markVariant);
	}
}
