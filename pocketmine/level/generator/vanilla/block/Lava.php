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

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\event\entity\EntityCombustByBlockEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByBlockEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class Lava extends Liquid
{
	protected $id = self::FLOWING_LAVA;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getLightLevel() : int
	{
		return 15;
	}

	public function getName() : string
	{
		return "Lava";
	}

	public function getStillForm() : Block
	{
		return BlockFactory::get(Block::STILL_LAVA, $this->meta);
	}

	public function getFlowingForm() : Block
	{
		return BlockFactory::get(Block::FLOWING_LAVA, $this->meta);
	}

	public function getBucketFillSound() : int
	{
		return LevelSoundEventPacket::SOUND_BUCKET_FILL_LAVA;
	}

	public function getBucketEmptySound() : int
	{
		return LevelSoundEventPacket::SOUND_BUCKET_EMPTY_LAVA;
	}

	public function tickRate() : int
	{
		return 30;
	}

	public function getFlowDecayPerBlock() : int
	{
		return 2; //TODO: this is 1 in the nether
	}

	protected function checkForHarden()
	{
		$colliding = null;
		for ($side = 1; $side <= 5; ++$side) { //don't check downwards side
			$blockSide = $this->getSide($side);
			if ($blockSide instanceof Water) {
				$colliding = $blockSide;
				break;
			}
		}

		if ($colliding !== null) {
			if ($this->getDamage() === 0) {
				$this->liquidCollide($colliding, BlockFactory::get(Block::OBSIDIAN));
			} elseif ($this->getDamage() <= 4) {
				$this->liquidCollide($colliding, BlockFactory::get(Block::COBBLESTONE));
			}
		}
	}

	protected function flowIntoBlock(Block $block, int $newFlowDecay) : void
	{
		if ($block instanceof Water) {
			$block->liquidCollide($this, BlockFactory::get(Block::STONE));
		} else {
			parent::flowIntoBlock($block, $newFlowDecay);
		}
	}

	public function onEntityCollide(Entity $entity) : void
	{
		$entity->fallDistance *= 0.5;

		$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_LAVA, 4);
		$entity->attack($ev);

		$ev = new EntityCombustByBlockEvent($this, $entity, 15);
		$ev->call();
		if (!$ev->isCancelled()) {
			$entity->setOnFire($ev->getDuration());
		}

		$entity->resetFallDistance();
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$this->getLevel()->setBlock($this, $this);
		$this->getLevel()->scheduleDelayedBlockUpdate($this, $this->tickRate());

		return true;
	}
}
