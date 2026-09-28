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

namespace pocketmine\level\generator\vanilla\level\generator;

use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\scheduler\AsyncTask;

class GeneratorUnregisterTask extends AsyncTask
{
	public int $levelId;

	public function __construct(Level $level)
	{
		$this->levelId = $level->getId();
	}

	public function onRun() : void{
		$this->removeFromThreadStore("generation.level{$this->levelId}.manager");
		$this->removeFromThreadStore("generation.level{$this->levelId}.generator");
	}
}
