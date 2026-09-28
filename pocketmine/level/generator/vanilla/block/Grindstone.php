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

use pocketmine\level\generator\vanilla\inventory\GrindstoneInventory;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\TieredTool;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

/**
 * Meta: bits 0-1 direction (0 south, 1 west, 2 north, 3 east), bits 2-3 attachment
 */
class Grindstone extends Transparent
{
	public const ATTACHMENT_STANDING = 0;
	public const ATTACHMENT_HANGING = 1;
	public const ATTACHMENT_SIDE = 2;
	public const ATTACHMENT_MULTIPLE = 3;

	protected $id = self::GRINDSTONE;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Grindstone";
	}

	public function getHardness() : float
	{
		return 2;
	}

	public function getBlastResistance() : float
	{
		return 6;
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

	public function getAttachment() : int
	{
		return ($this->meta >> 2) & 0x03;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($face === Facing::UP) {
			$attachment = self::ATTACHMENT_STANDING;
			$direction = ($player !== null ? $player->getDirection() : 0) & 0x03;
		} elseif ($face === Facing::DOWN) {
			$attachment = self::ATTACHMENT_HANGING;
			$direction = ($player !== null ? $player->getDirection() : 0) & 0x03;
		} else {
			$attachment = self::ATTACHMENT_SIDE;
			$direction = match ($face) {
				Facing::SOUTH => 0,
				Facing::WEST => 1,
				Facing::NORTH => 2,
				default => 3
			};
		}
		$this->meta = $direction | ($attachment << 2);

		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if ($player instanceof Player && $player->getProtocolVersion() >= ProtocolInfo::PROTOCOL_407) {
			$player->addWindow(new GrindstoneInventory($this));
		}

		return true;
	}
}
