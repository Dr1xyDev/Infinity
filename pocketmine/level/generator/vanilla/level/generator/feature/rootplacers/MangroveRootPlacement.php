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

namespace pocketmine\level\generator\vanilla\level\generator\feature\rootplacers;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\BlockStateProvider;

class MangroveRootPlacement{
	/**
	 * @param Block[] $canGrowThrough
	 * @param Block[] $muddyRootsIn
	 */
	public function __construct(
		private array $canGrowThrough,
		private array $muddyRootsIn,
		private BlockStateProvider $muddyRootsProvider,
		private int $maxRootWidth,
		private int $maxRootLength,
		private float $randomSkewChance
	){}

	public function canGrowThrough() : array{
		return $this->canGrowThrough;
	}

	public function muddyRootsIn() : array{
		return $this->muddyRootsIn;
	}

	public function muddyRootsProvider() : BlockStateProvider{
		return $this->muddyRootsProvider;
	}

	public function maxRootWidth() : int{
		return $this->maxRootWidth;
	}

	public function maxRootLength() : int{
		return $this->maxRootLength;
	}

	public function randomSkewChance() : float{
		return $this->randomSkewChance;
	}
}
