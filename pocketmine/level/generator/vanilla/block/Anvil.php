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

use pocketmine\level\generator\vanilla\entity\object\FallingBlock;
use pocketmine\level\generator\vanilla\inventory\AnvilInventory;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\TieredTool;
use pocketmine\level\generator\vanilla\level\sound\AnvilFallSound;
use pocketmine\level\generator\vanilla\level\sound\Sound;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Utils;
use function round;

class Anvil extends Fallable
{
	public const TYPE_NORMAL = 0;
	public const TYPE_SLIGHTLY_DAMAGED = 4;
	public const TYPE_VERY_DAMAGED = 8;

	protected $id = self::ANVIL;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function isTransparent() : bool
	{
		return true;
	}

	public function getHardness() : float
	{
		return 5;
	}

	public function getBlastResistance() : float
	{
		return 6000;
	}

	public function getVariantBitmask() : int
	{
		return 0x0c;
	}

	public function getName() : string
	{
		static $names = [
			self::TYPE_NORMAL => "Anvil",
			self::TYPE_SLIGHTLY_DAMAGED => "Slightly Damaged Anvil",
			self::TYPE_VERY_DAMAGED => "Very Damaged Anvil"
		];
		return $names[$this->getVariant()] ?? "Anvil";
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_WOODEN;
	}

	public function recalculateBoundingBox() : ?AxisAlignedBB
	{
		$inset = 0.125;

		if ($this->meta & 0x01) { //east/west
			return new AxisAlignedBB(
				$this->x,
				$this->y,
				$this->z + $inset,
				$this->x + 1,
				$this->y + 1,
				$this->z + 1 - $inset
			);
		} else {
			return new AxisAlignedBB(
				$this->x + $inset,
				$this->y,
				$this->z,
				$this->x + 1 - $inset,
				$this->y + 1,
				$this->z + 1
			);
		}
	}

	public function onActivate(Item $item, ?Player $player = null) : bool{
		if ($player instanceof Player) {
			$player->addWindow(new AnvilInventory($this));
		}

		return true;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$direction = ($player !== null ? $player->getDirection() : 0) & 0x03;
		$this->meta = $direction | $this->getVariant();

		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onHitGround(FallingBlock $blockEntity, float $fallDistance) : bool{
		if(Utils::getRandomFloat() < 0.05 + (round($fallDistance) - 1) * 0.05){
			if($this->getVariant() !== self::TYPE_VERY_DAMAGED){
				$this->meta = $this->getFacing() | ((($this->getVariant() >> 2) + 1) << 2);
			}else{
				return false;
			}
		}
		return true;
	}

	public function getFallDamagePerBlock() : float{
		return 2.0;
	}

	public function getMaxFallDamage() : float{
		return 40.0;
	}

	public function getLandSound(Vector3 $position) : ?Sound{
		return new AnvilFallSound($position);
	}

	public function getFacing() : int {
		return $this->meta & 0x03;
	}

	public function setFacing(int $facing) : void{
		$this->meta = $facing | $this->getVariant();
	}
}
