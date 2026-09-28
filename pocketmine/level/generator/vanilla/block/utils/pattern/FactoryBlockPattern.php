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

use function count;
use function implode;
use function strlen;

class FactoryBlockPattern{

	/** @var array<int, string[]> */
	private array $depth = [];

	/** @var array<string, callable(Block): bool|null> */
	private array $symbolMap = [];

	private int $aisleHeight = 0;
	private int $rowWidth = 0;

	private function __construct(){
		$this->symbolMap[' '] = static function(Block $block) : bool{
			return true;
		};
	}

	public static function start() : self{
		return new self();
	}

	public function aisle(string ...$aisle) : self{
		if(count($aisle) === 0 || strlen($aisle[0]) === 0){
			throw new \InvalidArgumentException("Empty pattern for aisle");
		}

		if(count($this->depth) === 0){
			$this->aisleHeight = count($aisle);
			$this->rowWidth = strlen($aisle[0]);
		}

		if(count($aisle) !== $this->aisleHeight){
			throw new \InvalidArgumentException(
				"Expected aisle with height of " . $this->aisleHeight
				. ", but was given one with a height of " . count($aisle) . ")"
			);
		}

		foreach($aisle as $row){
			if(strlen($row) !== $this->rowWidth){
				throw new \InvalidArgumentException(
					"Not all rows in the given aisle are the correct width (expected "
					. $this->rowWidth . ", found one with " . strlen($row) . ")"
				);
			}

			for($i = 0, $len = strlen($row); $i < $len; ++$i){
				$ch = $row[$i];
				if(!isset($this->symbolMap[$ch])){
					$this->symbolMap[$ch] = null;
				}
			}
		}

		$this->depth[] = $aisle;
		return $this;
	}

	/**
	 * @param callable(Block): bool $blockMatcher
	 */
	public function where(string $symbol, callable $blockMatcher) : self{
		$this->symbolMap[$symbol] = $blockMatcher;
		return $this;
	}

	public function build() : BlockPattern{
		return new BlockPattern($this->makePredicateArray());
	}

	/**
	 * @return array<int, array<int, array<int, callable(Block): bool>>>
	 */
	private function makePredicateArray() : array{
		$this->checkMissingPredicates();

		$result = [];
		for($i = 0, $depthCount = count($this->depth); $i < $depthCount; ++$i){
			$result[$i] = [];
			for($j = 0; $j < $this->aisleHeight; ++$j){
				$result[$i][$j] = [];
				$row = $this->depth[$i][$j];
				for($k = 0; $k < $this->rowWidth; ++$k){
					$result[$i][$j][$k] = $this->symbolMap[$row[$k]];
				}
			}
		}
		return $result;
	}

	private function checkMissingPredicates() : void{
		$missing = [];
		foreach($this->symbolMap as $symbol => $predicate){
			if($predicate === null){
				$missing[] = $symbol;
			}
		}

		if(count($missing) > 0){
			throw new \RuntimeException(
				"Predicates for character(s) " . implode(",", $missing) . " are missing"
			);
		}
	}
}
