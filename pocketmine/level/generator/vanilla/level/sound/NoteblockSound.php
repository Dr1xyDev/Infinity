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

namespace pocketmine\level\generator\vanilla\level\sound;

use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\BlockEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;

class NoteblockSound extends Sound
{
	public const INSTRUMENT_PIANO = 0;
	public const INSTRUMENT_BASS_DRUM = 1;
	public const INSTRUMENT_CLICK = 2;
	public const INSTRUMENT_TABOUR = 3;
	public const INSTRUMENT_BASS = 4;
	public const INSTRUMENT_GLOCKENSPIEL = 5;
	public const INSTRUMENT_FLUTE = 6;
	public const INSTRUMENT_CHIME = 7;
	public const INSTRUMENT_GUITAR = 8;
	public const INSTRUMENT_XYLOPHONE = 9;
	public const INSTRUMENT_VIBRAPHONE = 10;
	public const INSTRUMENT_COW_BELL = 11;
	public const INSTRUMENT_DIDGERIDOO = 12;
	public const INSTRUMENT_SQUARE_WAVE = 13;
	public const INSTRUMENT_BANJO = 14;
	public const INSTRUMENT_ELECTRIC_PIANO = 15;

	protected $instrument = self::INSTRUMENT_PIANO;
	protected $note = 0;

	/**
	 * NoteBlockSound constructor.
	 */
	public function __construct(Vector3 $pos, int $instrument = self::INSTRUMENT_PIANO, int $note = 0)
	{
		parent::__construct($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());

		$this->instrument = $instrument;
		$this->note = $note;
	}

	public function encode()
	{
		$pk = BlockEventPacket::create($this->x, $this->y, $this->z, $this->instrument, $this->note);

		$pk2 = new LevelSoundEventPacket();
		$pk2->sound = LevelSoundEventPacket::SOUND_NOTE;
		$pk2->position = $this;
		$pk2->extraData = $this->instrument << 8 | $this->note;

		return [$pk, $pk2];
	}
}
