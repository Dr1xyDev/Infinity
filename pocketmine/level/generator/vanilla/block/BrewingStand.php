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

use pocketmine\level\generator\vanilla\block\utils\BrewingStandSlot;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\TieredTool;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\BrewingStand as BrewingStandTile;

use function array_key_exists;

class BrewingStand extends Transparent
{
	protected $id = self::BREWING_STAND_BLOCK;

	public const FLAG_EAST = 0x01;
	public const FLAG_SOUTHWEST = 0x02;
	public const FLAG_NORTHWEST = 0x04;

	protected $itemId = Item::BREWING_STAND;

	/**
	 * @var BrewingStandSlot[]
	 * @phpstan-var array<int, BrewingStandSlot>
	 */
	protected array $slots = [];

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;

		$this->slots = [];
		foreach ([
			self::FLAG_EAST => BrewingStandSlot::EAST(),
			self::FLAG_SOUTHWEST => BrewingStandSlot::NORTHWEST(),
			self::FLAG_NORTHWEST => BrewingStandSlot::SOUTHWEST(),
		] as $flag => $slot) {
			if (($meta & $flag) !== 0) {
				$this->slots[$slot->id()] = $slot;
			}
		}
	}

	public function getName() : string
	{
		return "Brewing Stand";
	}

	public function getHardness() : float
	{
		return 0.5;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_WOODEN;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function getLightLevel() : int
	{
		return 1;
	}

	public function getBlastResistance() : float
	{
		return 2.5;
	}

	public function hasSlot(BrewingStandSlot $slot) : bool
	{
		return array_key_exists($slot->id(), $this->slots);
	}

	public function setSlot(BrewingStandSlot $slot, bool $occupied) : self
	{
		if ($occupied) {
			$this->slots[$slot->id()] = $slot;
		} else {
			unset($this->slots[$slot->id()]);
		}
		return $this;
	}

	/**
	 * @return BrewingStandSlot[]
	 * @phpstan-return array<int, BrewingStandSlot>
	 */
	public function getSlots() : array
	{
		return $this->slots;
	}

	/** @param BrewingStandSlot[] $slots */
	public function setSlots(array $slots) : self
	{
		$this->slots = [];
		foreach ($slots as $slot) {
			$this->slots[$slot->id()] = $slot;
		}
		return $this;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$parent = parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		if (!$blockReplace->getSide(Facing::DOWN)->isTransparent()) {
			new BrewingStandTile($this->getLevel(), BrewingStandTile::createNBT($this, $face, $item, $player));
		}

		return $parent;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if ($player instanceof Player) {
			$stand = $this->getLevel()->getTile($this);
			if (!$stand instanceof BrewingStandTile) {
				$stand = new BrewingStandTile($this->getLevel(), BrewingStandTile::createNBT($this, null, $item, $player));
			}

			if ($stand instanceof BrewingStandTile && $stand->canOpenWith($item->getCustomName())) {
				$player->addWindow($stand->getInventory());
			}
		}

		return true;
	}

	public function onScheduledUpdate() : void
	{
		$level = $this->getLevel();
		$brewing = $level->getTile($this);
		if ($brewing instanceof BrewingStandTile) {
			if ($brewing->onUpdate()) {
				$level->scheduleDelayedBlockUpdate($this, 1);
			}

			$changed = false;
			foreach (BrewingStandSlot::getAll() as $slot) {
				$occupied = !$brewing->getInventory()->isSlotEmpty($slot->getSlotNumber());
				if ($occupied !== $this->hasSlot($slot)) {
					$this->setSlot($slot, $occupied);
					$changed = true;
				}
			}

			if ($changed) {
				$level->setBlock($this, $this);
			}
		}
	}
}
