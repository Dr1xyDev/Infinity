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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol;

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ScorePacketEntry;

use function count;

class SetScorePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_SCORE_PACKET;

	public const TYPE_CHANGE = 0;
	public const TYPE_REMOVE = 1;

	/** @var int */
	public $type;
	/** @var ScorePacketEntry[] */
	public $entries = [];

	/** Since 1.26.40 every entry carries its own action (ordinal + name). Ordinals match ScorePacketEntry::TYPE_* */
	private const ACTION_REMOVE_V2168 = 0;
	private const ACTION_NAMES_V2168 = [
		self::ACTION_REMOVE_V2168 => "remove",
		ScorePacketEntry::TYPE_PLAYER => "changeplayer",
		ScorePacketEntry::TYPE_ENTITY => "changeentity",
		ScorePacketEntry::TYPE_FAKE_PLAYER => "changefakeplayer",
	];

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->type = self::TYPE_CHANGE;
			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
				$action = $this->getUnsignedVarInt();
				$actionName = $this->getString();
				if ((self::ACTION_NAMES_V2168[$action] ?? null) !== $actionName) {
					throw new PacketDecodeException("Unexpected score entry action $action ($actionName)");
				}
				$entry = new ScorePacketEntry();
				$entry->scoreboardId = $this->getVarLong();
				if ($action === self::ACTION_REMOVE_V2168) {
					$this->type = self::TYPE_REMOVE;
					$entry->objectiveName = $this->getOptional(fn() => $this->getString()) ?? "";
				} else {
					$entry->type = $action;
					$entry->objectiveName = $this->getString();
					$entry->score = $this->getLInt();
					if ($action === ScorePacketEntry::TYPE_FAKE_PLAYER) {
						$entry->customName = $this->getString();
					} else {
						$entry->entityUniqueId = $this->getEntityUniqueId();
					}
				}
				$this->entries[] = $entry;
			}
			return;
		}

		$this->type = $this->getByte();
		for ($i = 0, $i2 = $this->getUnsignedVarInt(); $i < $i2; ++$i) {
			$entry = new ScorePacketEntry();
			$entry->scoreboardId = $this->getVarLong();
			$entry->objectiveName = $this->getString();
			$entry->score = $this->getLInt();
			if ($this->type !== self::TYPE_REMOVE) {
				$entry->type = $this->getByte();
				switch ($entry->type) {
					case ScorePacketEntry::TYPE_PLAYER:
					case ScorePacketEntry::TYPE_ENTITY:
						$entry->entityUniqueId = $this->getEntityUniqueId();
						break;
					case ScorePacketEntry::TYPE_FAKE_PLAYER:
						$entry->customName = $this->getString();
						break;
					default:
						throw new PacketDecodeException("Unknown entry type $entry->type");
				}
			}
			$this->entries[] = $entry;
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->putUnsignedVarInt(count($this->entries));
			foreach ($this->entries as $entry) {
				$action = $this->type === self::TYPE_REMOVE ? self::ACTION_REMOVE_V2168 : $entry->type;
				if (!isset(self::ACTION_NAMES_V2168[$action])) {
					throw new InvalidArgumentException("Unknown entry type $entry->type");
				}
				$this->putUnsignedVarInt($action);
				$this->putString(self::ACTION_NAMES_V2168[$action]);
				$this->putVarLong($entry->scoreboardId);
				//1.26.40+ clients disconnect when receiving empty objective/custom names
				$objectiveName = ($entry->objectiveName ?? "") === "" ? " " : $entry->objectiveName;
				if ($action === self::ACTION_REMOVE_V2168) {
					$this->putOptional($objectiveName, fn(string $v) => $this->putString($v));
					continue;
				}
				$this->putString($objectiveName);
				$this->putLInt($entry->score);
				if ($action === ScorePacketEntry::TYPE_FAKE_PLAYER) {
					$this->putString(($entry->customName ?? "") === "" ? " " : $entry->customName);
				} else {
					$this->putEntityUniqueId($entry->entityUniqueId);
				}
			}
			return;
		}

		$this->putByte($this->type);
		$this->putUnsignedVarInt(count($this->entries));
		foreach ($this->entries as $entry) {
			$this->putVarLong($entry->scoreboardId);
			$this->putString($entry->objectiveName);
			$this->putLInt($entry->score);
			if ($this->type !== self::TYPE_REMOVE) {
				$this->putByte($entry->type);
				switch ($entry->type) {
					case ScorePacketEntry::TYPE_PLAYER:
					case ScorePacketEntry::TYPE_ENTITY:
						$this->putEntityUniqueId($entry->entityUniqueId);
						break;
					case ScorePacketEntry::TYPE_FAKE_PLAYER:
						$this->putString($entry->customName);
						break;
					default:
						throw new InvalidArgumentException("Unknown entry type $entry->type");
				}
			}
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleSetScore($this);
	}
}
