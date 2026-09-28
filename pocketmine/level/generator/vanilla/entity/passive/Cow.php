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
use pocketmine\level\generator\vanilla\item\Bucket;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

use function rand;

class Cow extends Animal
{
	public const NETWORK_ID = self::COW;

	public float $width = 0.9;
	public float $height = 1.3;

	/** @var int */
	private $milkingTicks = 0;

	public const MILKING_COOLDOWN = 20 * 60 * 5; // 5 minutes

	protected function addBehaviors() : void
	{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new PanicBehavior($this, 2.0));
		$this->behaviorPool->setBehavior(2, new MateBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(3, new TemptBehavior($this, [Item::WHEAT], 1.25));
		$this->behaviorPool->setBehavior(4, new FollowParentBehavior($this, 1.25));
		$this->behaviorPool->setBehavior(5, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(6, new LookAtPlayerBehavior($this, 6.0));
		$this->behaviorPool->setBehavior(7, new RandomLookAroundBehavior($this));
	}

	protected function initEntity() : void
	{
		$this->setMaxHealth(10);
		$this->setMovementSpeed(0.2);
		$this->setFollowRange(10);
		$this->milkingTicks = $this->namedtag->getInt("MilkingCooldown", 0);

		parent::initEntity();
	}

	public function getName() : string
	{
		return "Cow";
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool
	{
		if (!$this->isImmobile()) {
			$item = $player->getInventory()->getItemInHand();
			if ($item instanceof Bucket && $item->getDamage() === 0) {
				if ($this->milkingTicks > 0) {
					return false;
				}

				if ($player->hasFiniteResources()) {
					$item->pop();
					$player->getInventory()->setItemInHand($item);
				}
				$player->getInventory()->addItem(ItemFactory::get(Item::BUCKET, 1));
				$this->milkingTicks = self::MILKING_COOLDOWN;
				return true;
			}
		}

		return parent::onInteract($player, $clickPos);
	}

	public function entityBaseTick(int $diff = 1) : bool
	{
		if ($this->milkingTicks > 0) {
			$this->milkingTicks--;
		}

		return parent::entityBaseTick($diff);
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		$this->namedtag->setInt("MilkingCooldown", $this->milkingTicks);
	}

	public function getXpDropAmount() : int
	{
		return rand(1, ($this->isInLove() ? 7 : 3));
	}

	public function getDrops() : array
	{
		$looting = $this->getLootingLevel();
		return [
			ItemFactory::get(Item::LEATHER, 0, rand(0, 2 + $looting)),
			($this->isOnFire() ? ItemFactory::get(Item::STEAK, 0, rand(1, 3 + $looting)) : ItemFactory::get(Item::RAW_BEEF, 0, rand(1, 3 + $looting)))
		];
	}
}
