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

use pocketmine\level\generator\vanilla\block\utils\FortuneDropHelper;
use pocketmine\level\generator\vanilla\event\block\LeavesDecayEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

use SplQueue;
use function mt_rand;
use function spl_object_hash;

class Leaves extends Transparent
{
	public const int OAK = 0;
	public const int SPRUCE = 1;
	public const int BIRCH = 2;
	public const int JUNGLE = 3;
	public const int ACACIA = 0;
	public const int DARK_OAK = 1;

	protected $id = self::LEAVES;
	protected int $woodType = self::LOG;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getHardness() : float
	{
		return 0.2;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_SHEARS;
	}

	public function getName() : string
	{
		static $names = [
			self::OAK => "Oak Leaves",
			self::SPRUCE => "Spruce Leaves",
			self::BIRCH => "Birch Leaves",
			self::JUNGLE => "Jungle Leaves"
		];
		return $names[$this->getVariant()];
	}

	public function diffusesSkyLight() : bool
	{
		return true;
	}

	protected function findLog() : bool {
		$visited = [];
		$queue = new SplQueue();
		$distance = [];

		$queue->enqueue($this);
		$visited[spl_object_hash($this)] = true;
		$distance[spl_object_hash($this)] = 0;

		while (!$queue->isEmpty()) {
			/** @var Block $currentBlock */
			$currentBlock = $queue->dequeue();
			$currentDistance = $distance[spl_object_hash($currentBlock)];

			if ($currentDistance > 4) {
				return false;
			}

			foreach (Facing::ALL as $face) {
				$nextBlock = $currentBlock->getSide($face); // If side chunk not loaded, do not load or decay
				if ($nextBlock instanceof Log) {
					return true;
				}

				if ($nextBlock instanceof Leaves && !isset($visited[spl_object_hash($nextBlock)])) {
					$queue->enqueue($nextBlock);
					$visited[spl_object_hash($nextBlock)] = true;
					$distance[spl_object_hash($nextBlock)] = $currentDistance + 1;
				}
			}
		}

		return false;
	}

	public function onNearbyBlockChange() : void
	{
		if (!$this->isPersistent() && !$this->isCheckDecay()) {
			$this->setCheckDecay(true);
			$this->getLevel()->setBlock($this, $this, true, false);
		}
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		if (!$this->isPersistent() && $this->isCheckDecay()) {
			$ev = new LeavesDecayEvent($this);
			$ev->call();
			if ($ev->isCancelled() || $this->findLog()) {
				$this->setCheckDecay(false);
				$this->getLevel()->setBlock($this, $this, false, false);
			} else {
				$this->getLevel()->useBreakOn($this);
			}
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$this->setPersistent(true);
		$this->getLevel()->setBlock($this, $this, true);
		return true;
	}

	public function getVariantBitmask() : int
	{
		return 0x03;
	}

	public function getDrops(Item $item) : array
	{
		if (($item->getBlockToolType() & BlockToolType::TYPE_SHEARS) !== 0) {
			return $this->getDropsForCompatibleTool($item);
		}

		$drops = [];
		if (FortuneDropHelper::bonusChanceDivisor($item, 20, 4)) { //Saplings
			$drops[] = $this->getSaplingItem();
		}
		if (
			($this->canDropApples()) &&
			FortuneDropHelper::bonusChanceDivisor($item, 200, 20)
		) { //Apples
			$drops[] = ItemFactory::get(Item::APPLE);
		}
		if (FortuneDropHelper::bonusChanceDivisor($item, 50, 5)) {
			$drops[] = ItemFactory::get(Item::STICK)->setCount(mt_rand(1, 2));
		}

		return $drops;
	}

	public function getSaplingItem() : Item
	{
		return ItemFactory::get(ItemIds::SAPLING, $this->getVariant());
	}

	public function canDropApples() : bool
	{
		return $this->getVariant() === self::OAK;
	}

	public function getCheckDecayBitmask() : int {
		return 0x08;
	}

	public function isCheckDecay() : bool {
		return ($this->meta & $this->getCheckDecayBitmask()) !== 0;
	}

	public function setCheckDecay(bool $value) : void{
		$this->meta = ($this->meta & ~$this->getCheckDecayBitmask()) | ($value ? $this->getCheckDecayBitmask() : 0);
	}

	public function getPersistentBitmask() : int {
		return 0x04;
	}

	public function isPersistent() : bool {
		return ($this->meta & $this->getPersistentBitmask()) !== 0;
	}

	public function setPersistent(bool $value) : void{
		$this->meta = ($this->meta & ~$this->getPersistentBitmask()) | ($value ? $this->getPersistentBitmask() : 0);
	}

	public function getFlameEncouragement() : int
	{
		return 30;
	}

	public function getFlammability() : int
	{
		return 60;
	}
}
