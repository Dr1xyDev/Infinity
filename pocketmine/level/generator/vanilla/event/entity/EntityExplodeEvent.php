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

namespace pocketmine\level\generator\vanilla\event\entity;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\utils\Utils;

/**
 * Called when a entity explodes
 * @phpstan-extends EntityEvent<Entity>
 */
class EntityExplodeEvent extends EntityEvent implements Cancellable
{
	/** @var Position */
	protected $position;

	/** @var Block[] */
	protected $blocks;

	/** @var float */
	protected $yield;

	/** @var Block[] */
	private $ignitions;

	/**
	 * @param Block[] $blocks
	 * @param Block[] $ignitions
	 */
	public function __construct(Entity $entity, Position $position, array $blocks, float $yield, array $ignitions = [])
	{
		$this->entity = $entity;
		$this->position = $position;
		$this->blocks = $blocks;
		$this->yield = $yield;
		$this->ignitions = $ignitions;

		if ($yield < 0.0 || $yield > 100.0) {
			throw new \InvalidArgumentException("Yield must be in range 0.0 - 100.0");
		}
	}

	public function getPosition() : Position
	{
		return $this->position;
	}

	/**
	 * @return Block[]
	 */
	public function getBlockList() : array
	{
		return $this->blocks;
	}

	/**
	 * @param Block[] $blocks
	 */
	public function setBlockList(array $blocks) : void
	{
		Utils::validateArrayValueType($blocks, function (Block $_) : void { });
		$this->blocks = $blocks;
	}

	public function getYield() : float
	{
		return $this->yield;
	}

	public function setYield(float $yield) : void
	{
		if ($yield < 0.0 || $yield > 100.0) {
			throw new \InvalidArgumentException("Yield must be in range 0.0 - 100.0");
		}

		$this->yield = $yield;
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

	/**
	 * Returns a list of affected blocks that will be replaced by fire.
	 *
	 * @return Block[]
	 */
	public function getIgnitions() : array
	{
		return $this->ignitions;
	}
}
