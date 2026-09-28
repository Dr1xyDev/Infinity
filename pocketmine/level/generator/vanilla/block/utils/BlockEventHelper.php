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

namespace pocketmine\level\generator\vanilla\block\utils;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\event\block\BlockDeathEvent;
use pocketmine\level\generator\vanilla\event\block\BlockFormEvent;
use pocketmine\level\generator\vanilla\event\block\BlockGrowEvent;
use pocketmine\level\generator\vanilla\event\block\BlockMeltEvent;
use pocketmine\level\generator\vanilla\event\block\BlockSpreadEvent;
use pocketmine\Player;

/**
 * Helper class to call block changing events and apply the results to the world.
 * TODO: try to further reduce the amount of code duplication here - while this is much better than before, it's still
 * very repetitive.
 */
final class BlockEventHelper{

	public static function grow(Block $oldState, Block $newState, ?Player $causingPlayer) : bool{
		$ev = new BlockGrowEvent($oldState, $newState, $causingPlayer);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		$newState = $ev->getNewState();

		$position = $oldState->asPosition();
		$position->level->setBlock($position, $newState);
		return true;
	}

	public static function spread(Block $oldState, Block $newState, Block $source) : bool{
		$ev = new BlockSpreadEvent($oldState, $source, $newState);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		$newState = $ev->getNewState();

		$position = $oldState->asPosition();
		$position->level->setBlock($position, $newState);
		return true;
	}

	public static function form(Block $oldState, Block $newState, Block $cause) : bool{
		$ev = new BlockFormEvent($oldState, $newState, $cause);
		$ev->call();
		if ($ev->isCancelled()) {
			return false;
		}
		$newState = $ev->getNewState();

		$position = $oldState->asPosition();
		$position->level->setBlock($position, $newState);
		return true;
	}

	public static function melt(Block $oldState, Block $newState) : bool{
		$ev = new BlockMeltEvent($oldState, $newState);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		$newState = $ev->getNewState();

		$position = $oldState->asPosition();
		$position->level->setBlock($position, $newState);
		return true;
	}

	public static function die(Block $oldState, Block $newState) : bool{
		$ev = new BlockDeathEvent($oldState, $newState);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		$newState = $ev->getNewState();

		$position = $oldState->asPosition();
		$position->level->setBlock($position, $newState);
		return true;
	}
}
