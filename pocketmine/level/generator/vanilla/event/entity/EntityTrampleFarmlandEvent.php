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
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\event\Cancellable;

/**
 * @phpstan-extends EntityEvent<Living>
 */
class EntityTrampleFarmlandEvent extends EntityEvent implements Cancellable{

	public function __construct(
		Living $entity,
		private Block $block
	){
		$this->entity = $entity;
	}

	public function getBlock() : Block{
		return $this->block;
	}
}
