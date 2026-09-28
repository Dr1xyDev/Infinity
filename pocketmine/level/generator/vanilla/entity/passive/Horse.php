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

use pocketmine\level\generator\vanilla\entity\Attribute;
use pocketmine\level\generator\vanilla\entity\behavior\FloatBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\FollowParentBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\LookAtPlayerBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\MateBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\MountPathingBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\PanicBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomLookAroundBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomStrollBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\TemptBehavior;
use pocketmine\level\generator\vanilla\inventory\HorseInventory;
use pocketmine\level\generator\vanilla\inventory\InventoryHolder;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\Player;

use function mt_rand;

class Horse extends AbstractHorse implements InventoryHolder
{
	public const NETWORK_ID = self::HORSE;

	public const HORSE_VARIANT_WHITE = 0;
	public const HORSE_VARIANT_CREAMY = 1;
	public const HORSE_VARIANT_CHESTNUT = 2;
	public const HORSE_VARIANT_BROWN = 3;
	public const HORSE_VARIANT_BLACK = 4;
	public const HORSE_VARIANT_GRAY = 5;
	public const HORSE_VARIANT_DARK_BROWN = 6;

	public const HORSE_MARK_VARIANT_NONE = 0;
	public const HORSE_MARK_VARIANT_WHITE = 1;
	public const HORSE_MARK_VARIANT_WHITE_FIELD = 2;
	public const HORSE_MARK_VARIANT_WHITE_DOTS = 3;
	public const HORSE_MARK_VARIANT_BLACK_DOTS = 4;

	public float $width = 1.4;
	public float $height = 1.6;

	/** @var HorseInventory */
	protected $inventory;

	public function getName() : string
	{
		return "Horse";
	}

	public function getInventory() : HorseInventory
	{
		return $this->inventory;
	}

	protected function addBehaviors() : void
	{
		$this->behaviorPool->setBehavior(0, new MountPathingBehavior($this));
		$this->behaviorPool->setBehavior(1, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(2, new PanicBehavior($this, 1.25));
		$this->behaviorPool->setBehavior(3, new MateBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(4, new TemptBehavior($this, [Item::WHEAT, Item::APPLE, Item::WHEAT_BLOCK, Item::GOLDEN_APPLE, Item::ENCHANTED_GOLDEN_APPLE, Item::GOLDEN_CARROT, Item::SUGAR], 1.2));
		$this->behaviorPool->setBehavior(5, new FollowParentBehavior($this, 1.1));
		$this->behaviorPool->setBehavior(6, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(7, new LookAtPlayerBehavior($this, 6.0));
		$this->behaviorPool->setBehavior(8, new RandomLookAroundBehavior($this));
	}

	protected function initEntity() : void
	{
		$this->setMaxHealth($this->getModifiedMaxHealth());
		$this->setMovementSpeed($this->getModifiedMovementSpeed());
		$this->setJumpStrength($this->getModifiedJumpStrength());
		$this->setFollowRange(35);

		if ($this->namedtag->hasTag("Variant", IntTag::class) && $this->namedtag->hasTag("MarkVariant", IntTag::class)) {
			$this->setVariant($this->namedtag->getInt("Variant"));
			$this->setMarkVariant($this->namedtag->getInt("MarkVariant"));
		} else {
			$this->setVariant(mt_rand(0, 7));
			$this->setMarkVariant(mt_rand(0, 5));
		}

		$this->inventory = new HorseInventory($this);

		if ($this->namedtag->hasTag("ArmorItem", CompoundTag::class)) {
			$this->inventory->setArmor(Item::nbtDeserialize($this->namedtag->getCompoundTag("ArmorItem")));
		}

		if ($this->namedtag->hasTag("SaddleItem", CompoundTag::class)) {
			$this->inventory->setSaddle(Item::nbtDeserialize($this->namedtag->getCompoundTag("SaddleItem")));
		}

		parent::initEntity();
	}

	public function addAttributes() : void
	{
		parent::addAttributes();

		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::HORSE_JUMP_STRENGTH));
	}

	public function getRiderSeatPosition(int $seatNumber = 0) : Vector3
	{
		return new Vector3(0, 1.1, -0.2);
	}

	public function setSaddled(bool $value = true) : void
	{
		parent::setSaddled($value);

		$this->setGenericFlag(self::DATA_FLAG_CAN_POWER_JUMP, $value);
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool
	{
		if (!$this->isImmobile()) {
			// TODO: feeding

			if ($player->isSneaking()) {
				if ($this->isTamed()) {
					$player->addWindow($this->inventory);
				} else {
					$this->rearUp();
				}

				return true;
			}
		}
		return parent::onInteract($player, $clickPos);
	}

	public function sendSpawnPacket(Player $player) : void
	{
		parent::sendSpawnPacket($player);

		$this->inventory->sendArmor($player);
	}

	public function doHitAnimation() : void
	{
		parent::doHitAnimation();

		foreach ($this->getViewers() as $player) { // WTF
			$this->inventory->sendArmor($player);
		}
	}

	public function onDeath() : void
	{
		parent::onDeath();

		$this->inventory->dropContents($this->level, $this);
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		if ($this->inventory !== null) {
			$this->namedtag->setTag($this->inventory->getSaddle()->nbtSerialize(-1, "SaddleItem"));
			$this->namedtag->setTag($this->inventory->getArmor()->nbtSerialize(-1, "ArmorItem"));
		}
	}
}
