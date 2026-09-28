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

use pocketmine\level\generator\vanilla\block\utils\StaticSupportTrait;
use pocketmine\level\generator\vanilla\entity\EffectInstance;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\item\FoodSource;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemBlock;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class Cake extends Transparent implements FoodSource
{
	use StaticSupportTrait;

	protected $id = self::CAKE_BLOCK;

	protected $itemId = Item::CAKE;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getHardness() : float
	{
		return 0.5;
	}

	public function getName() : string
	{
		return "Cake";
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{

		$f = $this->getDamage() * 0.125; //1 slice width

		return new AxisAlignedBB(
			$this->x + 0.0625 + $f,
			$this->y,
			$this->z + 0.0625,
			$this->x + 1 - 0.0625,
			$this->y + 0.5,
			$this->z + 1 - 0.0625
		);
	}

	protected function canBeSupportedAt(Block $block) : bool{
		return $block->getSide(Facing::DOWN)->getId() !== BlockIds::AIR;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return false;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if ($this->meta === 0 && $item instanceof ItemBlock) {
			$block = $item->getBlock();
			if ($block instanceof Candle) {
				$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_CAKE_ADD_CANDLE);
				$this->level->setBlock($this, BlockFactory::get($block->getCandleCakeId()));
				$item->pop();
				return true;
			}
		}

		if ($player !== null) {
			$player->consumeObject($this);
			return true;
		}

		return false;
	}

	public function getFoodRestore() : int
	{
		return 2;
	}

	public function getSaturationRestore() : float
	{
		return 0.4;
	}

	public function requiresHunger() : bool
	{
		return true;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	/**
	 * @return Block
	 */
	public function getResidue()
	{
		$clone = clone $this;
		$clone->meta++;
		if ($clone->meta > 0x06) {
			$clone = BlockFactory::get(Block::AIR);
		}
		return $clone;
	}

	/**
	 * @return EffectInstance[]
	 */
	public function getAdditionalEffects() : array
	{
		return [];
	}

	public function onConsume(Living $consumer) : void
	{
		$this->level->setBlock($this, $this->getResidue());
	}

	public function canStartUsingItem(Player $player) : bool
	{
		return false;
	}
}
