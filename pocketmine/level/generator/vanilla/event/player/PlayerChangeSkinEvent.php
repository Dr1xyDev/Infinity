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

namespace pocketmine\level\generator\vanilla\event\player;

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\entity\Skin;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\Player;

/**
 * Called when a player changes their skin in-game.
 */
class PlayerChangeSkinEvent extends PlayerEvent implements Cancellable
{
	/** @var Skin */
	private $oldSkin;
	/** @var Skin */
	private $newSkin;

	public function __construct(Player $player, Skin $oldSkin, Skin $newSkin)
	{
		$this->player = $player;
		$this->oldSkin = $oldSkin;
		$this->newSkin = $newSkin;
	}

	public function getOldSkin() : Skin
	{
		return $this->oldSkin;
	}

	public function getNewSkin() : Skin
	{
		return $this->newSkin;
	}

	/**
	 * @throws InvalidArgumentException if the specified skin is not valid
	 */
	public function setNewSkin(Skin $skin) : void
	{
		$skin->validate();
		$this->newSkin = $skin;
	}
}
