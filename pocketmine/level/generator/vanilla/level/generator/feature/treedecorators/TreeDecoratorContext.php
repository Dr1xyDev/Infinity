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

namespace pocketmine\level\generator\vanilla\level\generator\feature\treedecorators;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\generator\feature\setter\Setter;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use function usort;

class TreeDecoratorContext {

	/** @var Vector3[] */
	private array $logs;
	/** @var Vector3[] */
	private array $leaves;
	/** @var Vector3[] */
	private array $roots;

	/**
	 * @param Vector3[] $trunkSet
	 * @param Vector3[] $foliageSet
	 * @param Vector3[] $rootSet
	 */
	public function __construct(
		private ChunkManager $level,
		private Setter       $decorationSetter,
		private Random       $random,
		array                $trunkSet,
		array                $foliageSet,
		array                $rootSet
	){
		$this->roots = $rootSet;
		$this->logs = $trunkSet;
		$this->leaves = $foliageSet;

		usort($this->logs, fn(Vector3 $a, Vector3 $b) => $a->getY() <=> $b->getY());
		usort($this->leaves, fn(Vector3 $a, Vector3 $b) => $a->getY() <=> $b->getY());
		usort($this->roots, fn(Vector3 $a, Vector3 $b) => $a->getY() <=> $b->getY());
	}

	public function placeVine(Vector3 $pos, int $direction) : void {
		$this->setBlock($pos, BlockFactory::get(BlockIds::VINE, $direction));
	}

	public function setBlock(Vector3 $pos, Block $state) : void {
		$this->decorationSetter->set($pos, $state);
	}

	public function isAir(Vector3 $pos) : bool {
		return $this->level->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ())->getId() === BlockIds::AIR;
	}

	public function checkBlock(Vector3 $pos, Block $block) : bool {
		return $this->level->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ())->isSameType($block);
	}

	public function level() : ChunkManager {
		return $this->level;
	}

	public function random() : Random {
		return $this->random;
	}

	public function logs() : array {
		return $this->logs;
	}

	public function leaves() : array {
		return $this->leaves;
	}

	public function roots() : array {
		return $this->roots;
	}
}
