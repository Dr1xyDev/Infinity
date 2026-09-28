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
use pocketmine\Player;

/**
 * Called when plants or crops grow.
 */
class StructureGrowEvent extends BlockEvent implements Cancellable
{
	/** @var Block[] */
	private $newStates;

	/** @var ?Player */
	private $player;

	public function __construct(Block $block, array $newStates, ?Player $player)
	{
		parent::__construct($block);
		$this->newStates = $newStates;
		$this->player = $player;
	}

	public function getNewState() : array
	{
		return $this->newStates;
	}

	public function setNewState(array $blocks) : void
	{
		$this->newStates = $blocks;
	}

	public function getPlayer() : ?Player
	{
		return $this->player;
	}
}
