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

namespace pocketmine\level\generator\vanilla\level\generator\structure\mineshaft;

use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\generator\structure\StructureStart;
use pocketmine\level\generator\vanilla\utils\Random;
use function intdiv;

class MineshaftStart extends StructureStart
{
	public function __construct(int $chunkX, int $chunkZ, Random $random, int $type, int $seaLevel)
	{
		parent::__construct($chunkX, $chunkZ);

		$room = new MineshaftRoom(0, $random, ($chunkX << Chunk::COORD_BIT_SIZE) + 2, ($chunkZ << Chunk::COORD_BIT_SIZE) + 2, $type);
		$this->components[] = $room;
		$room->buildComponent($room, $this->components, $random);
		$this->updateBoundingBox();

		if ($type === MineshaftPiece::TYPE_MESA) {
			$this->moveVertically($seaLevel - $this->boundingBox->maxY + intdiv($this->boundingBox->getYSize(), 2) + 5);
		} else {
			$this->markAvailableHeight($seaLevel, $random, 10);
		}
	}
}
