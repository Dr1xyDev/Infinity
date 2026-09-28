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

namespace pocketmine\level\generator\vanilla\level\particle;

use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelEventPacket;

class MobSpawnParticle extends Particle
{
	protected int $width;
	protected int $height;

	public function __construct(Vector3 $pos, int $width = 0, int $height = 0)
	{
		parent::__construct($pos->x, $pos->y, $pos->z);
		$this->width = $width;
		$this->height = $height;
	}

	public function encode()
	{
		$pk = new LevelEventPacket();
		$pk->evid = LevelEventPacket::EVENT_PARTICLE_SPAWN;
		$pk->position = $this->asVector3();
		$pk->data = ($this->width & 0xff) + (($this->height & 0xff) << 8);

		return $pk;
	}
}
