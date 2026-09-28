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

namespace pocketmine\level\generator\vanilla\inventory;

use pocketmine\level\generator\vanilla\entity\passive\AbstractHorse;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\Saddle;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;

abstract class AbstractHorseInventory extends BaseInventory
{
	/** @var AbstractHorse */
	protected $holder;

	public function __construct(AbstractHorse $holder, array $items = [], ?int $size = null, ?string $title = null)
	{
		$this->holder = $holder;
		parent::__construct($items, $size, $title);
	}

	public function setSaddle(Item $saddle) : void
	{
		$this->setItem(0, $saddle);

		$this->holder->setSaddled($saddle instanceof Saddle);
	}

	public function onSlotChange(int $index, Item $before, bool $send) : void
	{
		parent::onSlotChange($index, $before, $send);

		if ($index === 0) {
			$this->holder->setSaddled($this->getSaddle() instanceof Saddle);

			$this->holder->level->broadcastLevelSoundEvent($this->holder, LevelSoundEventPacket::SOUND_SADDLE);
		}
	}

	public function getSaddle() : Item
	{
		return $this->getItem(0);
	}

	/**
	 * @return AbstractHorse
	 */
	public function getHolder()
	{
		return $this->holder;
	}
}
