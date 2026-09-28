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

namespace pocketmine\level\generator\vanilla\entity\utils;

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\EntityIds;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AddActorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\BossEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\Player;

use function max;
use function min;
use function spl_object_id;

/*
 * This is a Helper class to create a simple Bossbar
 * Note: This is not an entity
 */

class Bossbar extends Vector3
{
	/** @var int */
	protected $entityId;
	/** @var string */
	protected $title;
	/** @var float */
	protected $healthPercent;
	/** @var Player[] */
	protected $viewers = [];

	public function __construct(string $title = "", float $hp = 1.0)
	{
		parent::__construct(0, 0, 0);

		$this->entityId = Entity::$entityCount++;
		$this->title = $title;
		$this->setHealthPercent($hp, false);
	}

	public function setTitle(string $title, bool $update = true)
	{
		$this->title = $title;

		if ($update) {
			$this->updateForAll();
		}
	}

	public function getTitle() : string
	{
		return $this->title;
	}

	/**
	 * @param float $hp This should be in 0.0-1.0 range
	 */
	public function setHealthPercent(float $hp, bool $update = true)
	{
		$this->healthPercent = max(0, min(1.0, $hp));

		if ($update) {
			$this->updateForAll();
		}
	}

	public function getHealthPercent() : float
	{
		return $this->healthPercent;
	}

	public function showTo(Player $player, bool $isViewer = true)
	{
		$player->sendDataPacket(AddActorPacket::create(
			$this->entityId,
			$this->entityId,
			EntityIds::SLIME,
			$this->asVector3(),
			null,
			0,
			0,
			0,
			0,
			[],
			[
				Entity::DATA_FLAGS => [
					Entity::DATA_TYPE_LONG,
					((1 << Entity::DATA_FLAG_INVISIBLE) | (1 << Entity::DATA_FLAG_IMMOBILE))
				],
				Entity::DATA_NAMETAG => [
					Entity::DATA_TYPE_STRING,
					$this->title
				]
			],
			new PropertySyncData([], []),
			[]
		));

		$this->sendBossEventPacket($player, BossEventPacket::TYPE_SHOW);

		if ($isViewer) {
			$this->viewers[spl_object_id($player)] = $player;
		}
	}

	public function hideFrom(Player $player)
	{
		$this->sendBossEventPacket($player, BossEventPacket::TYPE_HIDE);

		$pk2 = new RemoveActorPacket();
		$pk2->entityUniqueId = $this->entityId;

		$player->sendDataPacket($pk2);

		if (isset($this->viewers[spl_object_id($player)])) {
			unset($this->viewers[spl_object_id($player)]);
		}
	}

	public function updateFor(Player $player)
	{
		$this->sendBossEventPacket($player, BossEventPacket::TYPE_HEALTH_PERCENT);
		$this->sendBossEventPacket($player, BossEventPacket::TYPE_TITLE);
	}

	public function updateForAll() : void
	{
		foreach ($this->viewers as $player) {
			$this->updateFor($player);
		}
	}

	protected function sendBossEventPacket(Player $player, int $eventType) : void
	{
		$pk = new BossEventPacket();
		$pk->bossEid = $this->entityId;
		$pk->eventType = $eventType;

		switch ($eventType) {
			case BossEventPacket::TYPE_SHOW:
				$pk->title = $this->title;
				$pk->healthPercent = $this->healthPercent;
				$pk->color = 0;
				$pk->overlay = 0;
				$pk->darkenScreen = false;
				break;
			case BossEventPacket::TYPE_REGISTER_PLAYER:
			case BossEventPacket::TYPE_UNREGISTER_PLAYER:
				$pk->playerEid = $player->getId();
				break;
			case BossEventPacket::TYPE_TITLE:
				$pk->title = $this->title;
				break;
			case BossEventPacket::TYPE_HEALTH_PERCENT:
				$pk->healthPercent = $this->healthPercent;
				break;
		}

		$player->sendDataPacket($pk);
	}

	public function getViewers() : array
	{
		return $this->viewers;
	}

	public function getEntityId() : int
	{
		return $this->entityId;
	}
}
