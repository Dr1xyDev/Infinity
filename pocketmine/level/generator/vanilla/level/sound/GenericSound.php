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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelEventPacket;

class GenericSound extends Sound
{
	/** @var int */
	protected $id;
	/** @var float */
	protected $pitch = 0;

	public function __construct(Vector3 $pos, int $id, float $pitch = 0)
	{
		parent::__construct($pos->x, $pos->y, $pos->z);
		$this->id = $id;
		$this->pitch = $pitch * 1000;
	}

	public function getPitch() : float
	{
		return $this->pitch / 1000;
	}

	public function setPitch(float $pitch) : void
	{
		$this->pitch = $pitch * 1000;
	}

	public function encode()
	{
		$pk = new LevelEventPacket();
		$pk->evid = $this->id;
		$pk->position = $this->asVector3();
		$pk->data = (int) $this->pitch;

		return [$pk];
	}
}
