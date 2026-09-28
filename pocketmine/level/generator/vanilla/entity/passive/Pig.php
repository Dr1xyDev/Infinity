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

namespace pocketmine\level\generator\vanilla\entity\passive;

use pocketmine\level\generator\vanilla\entity\Animal;
use pocketmine\level\generator\vanilla\entity\behavior\FloatBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\FollowParentBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\LookAtPlayerBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\MateBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\PanicBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomLookAroundBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomStrollBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\TemptBehavior;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

use function boolval;
use function intval;
use function rand;

class Pig extends Animal
{
	public const NETWORK_ID = self::PIG;

	public float $width = 0.9;
	public float $height = 0.9;

	protected function addBehaviors() : void
	{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new PanicBehavior($this, 1.25));
		$this->behaviorPool->setBehavior(2, new MateBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(3, new TemptBehavior($this, [Item::CARROT], 1.2));
		// TODO: Add ControlledByPlayerBehavior
		$this->behaviorPool->setBehavior(4, new FollowParentBehavior($this, 1.1));
		$this->behaviorPool->setBehavior(5, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(6, new LookAtPlayerBehavior($this, 6.0));
		$this->behaviorPool->setBehavior(7, new RandomLookAroundBehavior($this));
	}

	protected function initEntity() : void
	{
		$this->setMaxHealth(10);
		$this->setMovementSpeed(0.25);
		$this->setFollowRange(10);
		$this->setSaddled(boolval($this->namedtag->getByte("Saddle", 0)));

		parent::initEntity();
	}

	public function getName() : string
	{
		return "Pig";
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool
	{
		if (parent::onInteract($player, $clickPos)) {
			return true;
		}

		if (!$this->isImmobile()) {
			if ($this->isSaddled() && $this->getRiddenByEntity() === null) {
				$player->mountEntity($this);
				return true;
			}
		}
		return false;
	}

	public function getXpDropAmount() : int
	{
		return rand(1, ($this->isInLove() ? 7 : 3));
	}

	public function getDrops() : array
	{
		$looting = $this->getLootingLevel();
		$drops = [
			($this->isOnFire() ? ItemFactory::get(Item::COOKED_PORKCHOP, 0, rand(1, 3 + $looting)) : ItemFactory::get(Item::RAW_PORKCHOP, 0, rand(1, 3 + $looting)))
		];

		if ($this->isSaddled()) {
			$drops[] = ItemFactory::get(Item::SADDLE, 0, 1);
		}

		return $drops;
	}

	public function isSaddled() : bool
	{
		return $this->getGenericFlag(self::DATA_FLAG_SADDLED);
	}

	public function setSaddled(bool $value = true) : void
	{
		$this->setGenericFlag(self::DATA_FLAG_SADDLED, $value);
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		$this->namedtag->setByte("Saddle", intval($this->isSaddled()));
	}

	public function getRiderSeatPosition(int $seatNumber = 0) : Vector3
	{
		return new Vector3(0, 0.63, 0);
	}

	public function isBreedingItem(Item $item) : bool
	{
		return $item->getId() === Item::CARROT;
	}
}
