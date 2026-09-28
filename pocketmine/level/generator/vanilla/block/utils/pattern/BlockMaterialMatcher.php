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

namespace pocketmine\level\generator\vanilla\block\utils\pattern;

use pocketmine\level\generator\vanilla\block\Block;
use function array_flip;

class BlockMaterialMatcher{

	/** @var callable(Block): bool */
	private $predicate;

	/**
	 * @param callable(Block): bool $predicate
	 */
	private function __construct(callable $predicate){
		$this->predicate = $predicate;
	}

	/**
	 * @param callable(Block): bool $predicate
	 */
	public static function forPredicate(callable $predicate) : self{
		return new self($predicate);
	}

	/**
	 * @param int[] $blockIds
	 */
	public static function forBlockIds(array $blockIds) : self{
		$ids = array_flip($blockIds);
		return new self(static function(Block $block) use ($ids) : bool{
			return isset($ids[$block->getId()]);
		});
	}

	public static function forFlammable() : self{
		return new self(static function(Block $block) : bool{
			return $block->isFlammable();
		});
	}

	public static function forSolid() : self{
		return new self(static function(Block $block) : bool{
			return $block->isSolid();
		});
	}

	public function apply(Block $block) : bool{
		return ($this->predicate)($block);
	}
}
