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

namespace pocketmine\level\generator\vanilla\level\generator\structure\fortress;

use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\generator\structure\StructureStart;
use pocketmine\level\generator\vanilla\utils\Random;
use function array_splice;
use function count;

class FortressStart extends StructureStart
{
	public function __construct(int $chunkX, int $chunkZ, Random $random)
	{
		parent::__construct($chunkX, $chunkZ);

		$start = new FortressStartPiece($random, ($chunkX << Chunk::COORD_BIT_SIZE) + 2, ($chunkZ << Chunk::COORD_BIT_SIZE) + 2);
		$this->components[] = $start;
		$start->buildComponent($start, $this->components, $random);

		while (count($start->pendingChildren) > 0) {
			$piece = array_splice($start->pendingChildren, $random->nextBoundedInt(count($start->pendingChildren)), 1)[0];
			$piece->buildComponent($start, $this->components, $random);
		}

		$this->updateBoundingBox();
		$this->setRandomHeight($random, 48, 70);
	}
}
