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
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class Water extends Liquid
{
	protected $id = self::FLOWING_WATER;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Water";
	}

	public function getLightFilter() : int
	{
		return 2;
	}

	public function getStillForm() : Block
	{
		return BlockFactory::get(Block::STILL_WATER, $this->meta);
	}

	public function getFlowingForm() : Block
	{
		return BlockFactory::get(Block::FLOWING_WATER, $this->meta);
	}

	public function getBucketFillSound() : int
	{
		return LevelSoundEventPacket::SOUND_BUCKET_FILL_WATER;
	}

	public function getBucketEmptySound() : int
	{
		return LevelSoundEventPacket::SOUND_BUCKET_EMPTY_WATER;
	}

	public function tickRate() : int
	{
		return 5;
	}

	public function onEntityCollide(Entity $entity) : void
	{
		$entity->resetFallDistance();
		if ($entity->isOnFire()) {
			$entity->extinguish();
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$this->getLevel()->setBlock($this, $this);
		$this->getLevel()->scheduleDelayedBlockUpdate($this, $this->tickRate());

		return true;
	}
}
