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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\level\sound\NoteblockSound;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;

use function boolval;
use function intval;
use function max;
use function min;

class NoteBlock extends Spawnable
{
	public const TAG_NOTE = "note";
	public const TAG_POWERED = "powered";

	protected int $note = 0;
	protected bool $powered = false;

	protected function readSaveData(CompoundTag $nbt) : void
	{
		$this->note = max(0, min(24, $nbt->getByte(self::TAG_NOTE, 0, true)));
		$this->powered = boolval($nbt->getByte(self::TAG_POWERED, 0));
	}

	public function setNote(int $note) : void
	{
		$this->note = $note;
		$this->onChanged();
	}

	public function getNote() : int
	{
		return $this->note;
	}

	public function changePitch() : void
	{
		$this->note = ($this->note + 1) % 25;
		$this->onChanged();
	}

	public function triggerNote() : bool
	{
		$up = $this->level->getBlock($this->getSide(Facing::UP));
		if ($up->getId() === Block::AIR) {
			$below = $this->level->getBlock($this->getSide(Facing::DOWN));
			$instrument = NoteblockSound::INSTRUMENT_PIANO;

			switch ($below->getId()) { // TODO: implement block materials
				case Block::LOG:
				case Block::WOOD2:
				case Block::PLANKS:
				case Block::WOODEN_SLAB:
				case Block::DOUBLE_WOODEN_SLAB:
				case Block::OAK_STAIRS:
				case Block::SPRUCE_STAIRS:
				case Block::BIRCH_STAIRS:
				case Block::JUNGLE_STAIRS:
				case Block::ACACIA_STAIRS:
				case Block::DARK_OAK_STAIRS:
				case Block::FENCE:
				case Block::FENCE_GATE:
				case Block::SPRUCE_FENCE_GATE:
				case Block::BIRCH_FENCE_GATE:
				case Block::JUNGLE_FENCE_GATE:
				case Block::DARK_OAK_FENCE_GATE:
				case Block::ACACIA_FENCE_GATE:
				case Block::BOOKSHELF:
				case Block::CHEST:
				case Block::CRAFTING_TABLE:
				case Block::SIGN_POST:
				case Block::WALL_SIGN:
				case Block::OAK_DOOR_BLOCK:
				case Block::SPRUCE_DOOR_BLOCK:
				case Block::BIRCH_DOOR_BLOCK:
				case Block::JUNGLE_DOOR_BLOCK:
				case Block::ACACIA_DOOR_BLOCK:
				case Block::DARK_OAK_DOOR_BLOCK:
				case Block::NOTEBLOCK:
					$instrument = NoteblockSound::INSTRUMENT_BASS;
					break;
				case Block::SAND:
				case Block::SOUL_SAND:
					$instrument = NoteblockSound::INSTRUMENT_TABOUR;
					break;
				case Block::GLASS:
				case Block::GLASS_PANE:
					$instrument = NoteblockSound::INSTRUMENT_CLICK;
					break;
				case Block::STONE:
				case Block::COBBLESTONE:
				case Block::SANDSTONE:
				case Block::MOSS_STONE:
				case Block::BRICK_BLOCK:
				case Block::STONE_BRICK:
				case Block::NETHER_BRICK_BLOCK:
				case Block::QUARTZ_BLOCK:
				case Block::STONE_SLAB:
				case Block::COBBLESTONE_STAIRS:
				case Block::BRICK_STAIRS:
				case Block::STONE_BRICK_STAIRS:
				case Block::NETHER_BRICK_STAIRS:
				case Block::SANDSTONE_STAIRS:
				case Block::QUARTZ_STAIRS:
				case Block::COBBLESTONE_WALL:
				case Block::NETHER_BRICK_FENCE:
				case Block::BEDROCK:
				case Block::GOLD_ORE:
				case Block::IRON_ORE:
				case Block::COAL_ORE:
				case Block::LAPIS_ORE:
				case Block::DIAMOND_ORE:
				case Block::REDSTONE_ORE:
				case Block::GLOWING_REDSTONE_ORE:
				case Block::EMERALD_ORE:
				case Block::FURNACE:
				case Block::BURNING_FURNACE:
				case Block::OBSIDIAN:
				case Block::MONSTER_SPAWNER:
				case Block::NETHERRACK:
				case Block::ENCHANTING_TABLE:
				case Block::END_STONE:
				case Block::TERRACOTTA:
				case Block::COAL_BLOCK:
					$instrument = NoteblockSound::INSTRUMENT_BASS_DRUM;
					break;
			}

			$this->level->addSound(new NoteblockSound($this, $instrument, $this->note));

			return true;
		}
		return false;
	}

	public function setPowered(bool $value) : void
	{
		$this->powered = $value;
		$this->onChanged();
	}

	public function isPowered() : bool
	{
		return $this->powered;
	}

	public function getDefaultName() : string
	{
		return "NoteBlock";
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setByte(self::TAG_NOTE, $this->note, true);
		$nbt->setByte(self::TAG_POWERED, intval($this->powered));
	}

	public function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void
	{

	}
}
