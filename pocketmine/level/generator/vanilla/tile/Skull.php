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
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\Player;

use function floor;

class Skull extends Spawnable
{
	public const int TYPE_SKELETON = 0;
	public const int TYPE_WITHER_SKELETON = 1;
	public const int TYPE_ZOMBIE = 2;
	public const int TYPE_PLAYER = 3;
	public const int TYPE_CREEPER = 4;
	public const int TYPE_DRAGON = 5;
	public const int TYPE_PIGLIN = 6;

	public const string TAG_SKULL_TYPE = "SkullType"; //TAG_Byte
	public const string TAG_ROT = "Rot"; //TAG_Byte
	public const string TAG_MOUTH_MOVING = "MouthMoving"; //TAG_Byte
	public const string TAG_MOUTH_TICK_COUNT = "MouthTickCount"; //TAG_Int

	private int $skullType;
	private int $skullRotation;

	protected function readSaveData(CompoundTag $nbt) : void
	{
		$this->skullType = $nbt->getByte(self::TAG_SKULL_TYPE, self::TYPE_SKELETON, true);
		$this->skullRotation = $nbt->getByte(self::TAG_ROT, 0, true);
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setByte(self::TAG_SKULL_TYPE, $this->skullType);
		$nbt->setByte(self::TAG_ROT, $this->skullRotation);
	}

	public function setType(int $type) : void
	{
		$this->skullType = $type;
		$this->onChanged();
		$this->resendBlock();
	}

	/**
	 * Since 1.21.40 every head type is its own block, so the block itself has to be resent when the type changes
	 */
	public function resendBlock() : void
	{
		$level = $this->getLevel();
		if ($level === null || $this->isClosed()) {
			return;
		}
		$chunkX = $this->getFloorX() >> 4;
		$chunkZ = $this->getFloorZ() >> 4;
		$level->clearChunkCache($chunkX, $chunkZ);
		$level->sendBlocks($level->getChunkPlayers($chunkX, $chunkZ), [$this->asVector3()]);
	}

	public function getType() : int
	{
		return $this->skullType;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void
	{
		$nbt->setByte(self::TAG_SKULL_TYPE, $this->skullType);
		$nbt->setByte(self::TAG_ROT, $this->skullRotation);
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void
	{
		$nbt->setByte(self::TAG_SKULL_TYPE, $item !== null ? $item->getDamage() : self::TYPE_SKELETON);

		$rot = 0;
		if ($face === Facing::UP && $player !== null) {
			$rot = floor(($player->yaw * 16 / 360) + 0.5) & 0x0F;
		}
		$nbt->setByte(self::TAG_ROT, $rot);
	}
}
