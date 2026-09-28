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

namespace pocketmine\level\generator\vanilla\level\generator\feature;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\generator\feature\getter\Getter;
use pocketmine\level\generator\vanilla\level\generator\feature\predicate\Predicate;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;

abstract class Feature {

	public static function isStone(Block $state) : bool {
		$stateLegacyId = $state->getId();
		return
			$stateLegacyId === BlockIds::STONE || //granite, diorite and andesite
			$stateLegacyId === BlockIds::TUFF ||
			$stateLegacyId === BlockIds::DEEPSLATE;
	}

	public static function isDirt(Block $state) : bool {
		$stateLegacyId = $state->getId();
		return
			$stateLegacyId === BlockIds::DIRT || //dirt and coarse
			$stateLegacyId === BlockIds::GRASS ||
			$stateLegacyId === BlockIds::PODZOL ||
			$stateLegacyId === BlockIds::MYCELIUM ||
			$stateLegacyId === BlockIds::DIRT_WITH_ROOTS ||
			$stateLegacyId === BlockIds::MOSS_BLOCK ||
			$stateLegacyId === BlockIds::PALE_MOSS_BLOCK ||
			$stateLegacyId === BlockIds::MUD ||
			$stateLegacyId === BlockIds::MUDDY_MANGROVE_ROOTS;
	}

	public static function isGrassOrDirt(ChunkManager $level, Vector3 $pos) : bool{
		$state = $level->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
		return Feature::isDirt($state);
	}

	protected function setBlock(ChunkManager $level, Vector3 $pos, Block $blockState) : void {
		$level->setBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $blockState);
	}

	public function checkNeighbors(Getter $blockGetter, Vector3 $pos, Predicate $predicate) : bool{
		foreach (Facing::ALL as $direction) {
			$neighborPos = $pos->getSide($direction);
			if ($predicate->is($blockGetter->get($neighborPos))) {
				return true;
			}
		}

		return false;
	}

	public function isAdjacentToAir(Getter $blockGetter, Vector3 $pos) : bool{
		return $this->checkNeighbors($blockGetter, $pos, new class implements Predicate {
			public function is(Block $block) : bool{
				return $block->getId() === BlockIds::AIR;
			}
		});
	}

	abstract public function place(FeaturePlaceContext $context) : bool;

}
