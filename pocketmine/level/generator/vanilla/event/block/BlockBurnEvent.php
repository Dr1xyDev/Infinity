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

/**
 * Called when a block is burned away by fire.
 */
class BlockBurnEvent extends BlockEvent implements Cancellable
{
	/** @var Block */
	private $causingBlock;

	public function __construct(Block $block, Block $causingBlock)
	{
		parent::__construct($block);
		$this->causingBlock = $causingBlock;
	}

	/**
	 * Returns the block (usually Fire) which caused the target block to be burned away.
	 */
	public function getCausingBlock() : Block
	{
		return $this->causingBlock;
	}
}
