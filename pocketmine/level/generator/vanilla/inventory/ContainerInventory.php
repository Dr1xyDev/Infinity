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

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\Player;

abstract class ContainerInventory extends BaseInventory
{
	/** @var Vector3 */
	protected $holder;

	public function __construct(Vector3 $holder, array $items = [], ?int $size = null, ?string $title = null)
	{
		$this->holder = $holder;
		parent::__construct($items, $size, $title);
	}

	public function onOpen(Player $who) : void
	{
		parent::onOpen($who);
		$pk = new ContainerOpenPacket();
		$pk->windowId = $who->getWindowId($this);
		$pk->type = $this->getNetworkType();
		$holder = $this->getHolder();

		$pk->x = $pk->y = $pk->z = 0;
		$pk->entityUniqueId = -1;

		if ($holder instanceof Entity) {
			$pk->entityUniqueId = $holder->getId();
		} elseif ($holder instanceof Vector3) {
			$pk->x = $holder->getFloorX();
			$pk->y = $holder->getFloorY();
			$pk->z = $holder->getFloorZ();
		}

		$who->setCurrentWindowType($pk->type);

		$who->dataPacket($pk);

		$this->sendContents($who);
	}

	public function onClose(Player $who) : void
	{
		$pk = new ContainerClosePacket();
		$pk->windowId = $who->getWindowId($this);
		$pk->windowType = $who->getCurrentWindowType();
		$pk->server = $who->getClosingWindowId() !== $pk->windowId;
		$who->dataPacket($pk);
		parent::onClose($who);
	}

	/**
	 * Returns the Minecraft PE inventory type used to show the inventory window to clients.
	 */
	abstract public function getNetworkType() : int;

	/**
	 * @return Vector3
	 */
	public function getHolder()
	{
		return $this->holder;
	}
}
