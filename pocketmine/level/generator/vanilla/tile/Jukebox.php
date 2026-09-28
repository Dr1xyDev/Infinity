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

namespace pocketmine\level\generator\vanilla\tile;

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\Record;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\TextPacket;
use pocketmine\Player;

use function str_ireplace;
use function ucwords;

class Jukebox extends Spawnable
{
	public const TAG_RECORD_ITEM = "RecordItem";

	protected ?Record $recordItem = null;

	public function setRecordItem(?Record $item) : void
	{
		$this->recordItem = $item;
		$this->onChanged();
	}

	public function getRecordItem() : ?Record
	{
		return $this->recordItem;
	}

	public function playDisc(?Player $player = null) : void
	{
		if ($this->getRecordItem() instanceof Record) {
			$this->level->broadcastLevelSoundEvent($this, $this->getRecordItem()->getSoundId());

			if ($player instanceof Player && $player->getProtocolVersion() >= ProtocolInfo::PROTOCOL_407) {
				$pk = new TextPacket();
				$pk->type = TextPacket::TYPE_JUKEBOX_POPUP;
				$pk->needsTranslation = true;
				$pk->message = "record.nowPlaying";
				$pk->parameters = [
					ucwords(str_ireplace([
						"record", "."
					], [
						"", ""
					], (string) $this->getRecordItem()->getSoundId()))
				];
				$player->sendDataPacket($pk);
			}
		}
	}

	public function stopDisc() : void
	{
		if ($this->getRecordItem() instanceof Record) {
			$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_STOP_RECORD);
		}
	}

	public function dropDisc() : void
	{
		if ($this->getRecordItem() instanceof Record) {
			$this->stopDisc();
			$this->level->dropItem($this->add(0.5, 1, 0.5), $this->getRecordItem());
			$this->setRecordItem(null);
		}
	}

	public function hasRecordItem() : bool
	{
		return $this->recordItem instanceof Record;
	}

	public function getDefaultName() : string
	{
		return "Jukebox";
	}

	protected function readSaveData(CompoundTag $nbt) : void
	{
		if ($nbt->hasTag(self::TAG_RECORD_ITEM)) {
			$item = Item::nbtDeserialize($nbt->getCompoundTag(self::TAG_RECORD_ITEM));
			if ($item instanceof Record) {
				$this->recordItem = $item;
			}
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		if ($this->recordItem !== null) {
			$nbt->setTag($this->recordItem->nbtSerialize(-1, self::TAG_RECORD_ITEM));
		}
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void
	{
		$this->writeSaveData($nbt);
	}

	public function spawnTo(Player $player) : bool
	{
		if ($this->hasRecordItem()) {
			$pk = new LevelSoundEventPacket();
			$pk->sound = $this->getRecordItem()->getSoundId();
			$pk->position = $this;

			$player->sendDataPacket($pk);
		}
		return parent::spawnTo($player);
	}
}
