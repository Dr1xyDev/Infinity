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

namespace pocketmine\level\generator\vanilla\event\block;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\utils\Utils;

/**
 * Called when a block explodes, after explosion impact has been calculated.
 *
 * @see BlockPreExplodeEvent
 */
class BlockExplodeEvent extends BlockEvent implements Cancellable
{
	/**
	 * @param Block[] $blocks
	 * @param Block[] $ignitions
	 */
	public function __construct(
		Block $block,
		private Position $position,
		private array $blocks,
		private float $yield,
		private array $ignitions
	) {
		parent::__construct($block);

		Utils::checkFloatNotInfOrNaN("yield", $yield);
		if ($yield < 0.0 || $yield > 100.0) {
			throw new \InvalidArgumentException("Yield must be in range 0.0 - 100.0");
		}
	}

	public function getPosition() : Position
	{
		return $this->position;
	}

	/**
	 * Returns the percentage chance of drops from each block destroyed by the explosion.
	 *
	 * @return float 0-100
	 */
	public function getYield() : float
	{
		return $this->yield;
	}

	/**
	 * Sets the percentage chance of drops from each block destroyed by the explosion.
	 *
	 * @param float $yield 0-100
	 */
	public function setYield(float $yield) : void
	{
		Utils::checkFloatNotInfOrNaN("yield", $yield);
		if ($yield < 0.0 || $yield > 100.0) {
			throw new \InvalidArgumentException("Yield must be in range 0.0 - 100.0");
		}
		$this->yield = $yield;
	}

	/**
	 * Returns a list of blocks destroyed by the explosion.
	 *
	 * @return Block[]
	 */
	public function getAffectedBlocks() : array
	{
		return $this->blocks;
	}

	/**
	 * Sets the blocks destroyed by the explosion.
	 *
	 * @param Block[] $blocks
	 */
	public function setAffectedBlocks(array $blocks) : void
	{
		Utils::validateArrayValueType($blocks, fn (Block $block) => null);
		$this->blocks = $blocks;
	}

	/**
	 * Returns a list of affected blocks that will be replaced by fire.
	 *
	 * @return Block[]
	 */
	public function getIgnitions() : array
	{
		return $this->ignitions;
	}

	/**
	 * Set the list of blocks that will be replaced by fire.
	 *
	 * @param Block[] $ignitions
	 */
	public function setIgnitions(array $ignitions) : void
	{
		Utils::validateArrayValueType($ignitions, fn (Block $block) => null);
		$this->ignitions = $ignitions;
	}
}
