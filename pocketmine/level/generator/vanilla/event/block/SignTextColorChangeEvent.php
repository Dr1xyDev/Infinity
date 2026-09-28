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
use pocketmine\level\generator\vanilla\utils\Color;

class SignTextColorChangeEvent extends BlockEvent implements Cancellable
{
	private Color $color;

	public function __construct(Block $block, Color $color)
	{
		parent::__construct($block);

		$this->color = $color;
	}

	public function getColor() : Color
	{
		return $this->color;
	}

	public function setColor(Color $color) : void
	{
		$this->color = $color;
	}
}
