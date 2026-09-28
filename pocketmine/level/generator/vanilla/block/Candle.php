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

use pocketmine\level\generator\vanilla\entity\projectile\Projectile;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\level\sound\FireExtinguishSound;
use pocketmine\level\generator\vanilla\level\sound\FlintSteelSound;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\RayTraceResult;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class Candle extends Transparent
{
	public const int CANDLES_BIT = 0x03;
	public const int LIT_BIT = 0x04;

	protected int $candleCakeId = BlockIds::CANDLE_CAKE;

	public function __construct(int $id, int $meta = 0, ?string $name = null, ?int $itemId = null, int $candleCakeId = BlockIds::CANDLE_CAKE)
	{
		parent::__construct($id, $meta, $name, $itemId);
		$this->candleCakeId = $candleCakeId;
	}

	public function getCandleCakeId() : int
	{
		return $this->candleCakeId;
	}

	public function getBlastResistance() : float
	{
		return 0.1;
	}

	public function getHardness() : float
	{
		return 0.1;
	}

	public function getLightLevel() : int
	{
		return ($this->meta >= self::LIT_BIT) ? (($this->meta & self::CANDLES_BIT) * 3) : 0;
	}

	protected function getCandleIfCompatibleType(Block $block) : ?Candle
	{
		return ($block instanceof Candle) && $block->getId() === $this->getId() ? $block : null;
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool
	{
		$candle = $this->getCandleIfCompatibleType($blockReplace) ? $blockReplace : null;
		return $candle !== null ? ($candle->getDamage() & self::CANDLES_BIT) < 3 : parent::canBePlacedAt($blockReplace, $clickVector, $face, $isClickedBlock);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($this->getSide(Facing::DOWN)->isTransparent()) {
			return false;
		}

		$existing = $this->getCandleIfCompatibleType($blockReplace);
		if ($existing !== null) {
			if (($blockReplace->getDamage() & self::CANDLES_BIT) < 3) {
				$blockReplace->setDamage($blockReplace->getDamage() + 1);
				$this->level->setBlock($blockReplace, $blockReplace, true, true);
				return true;
			}

			return false;
		}

		$this->level->setBlock($this, $this, true, true);
		return true;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if ($item->getId() != ItemIds::FLINT_AND_STEEL && !$item->isNull()) {
			return false;
		}

		if (($this->meta >= self::LIT_BIT) && $item->getId() != ItemIds::FLINT_AND_STEEL) {
			$this->meta -= self::LIT_BIT;
			$this->level->addSound(new FireExtinguishSound($this));
			$this->level->setBlock($this, $this, true, true);
			return true;
		} elseif ($this->meta < self::LIT_BIT && $item->getId() == ItemIds::FLINT_AND_STEEL) {
			$this->meta += self::LIT_BIT;
			$this->level->addSound(new FlintSteelSound($this));
			$this->level->setBlock($this, $this, true, true);
			return true;
		}

		return false;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	protected function recalculateCollisionBoxes() : array
	{
		return [];
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [$this->asItem()->setCount(($this->meta & self::CANDLES_BIT))];
	}

	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void
	{
		if ($this->meta < self::LIT_BIT && $projectile->isOnFire()) {
			$this->meta += self::LIT_BIT;
			$this->level->setBlock($this, $this);
		}
	}
}
