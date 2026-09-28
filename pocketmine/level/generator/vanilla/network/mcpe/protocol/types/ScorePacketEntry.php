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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types;

class ScorePacketEntry
{
	public const TYPE_PLAYER = 1;
	public const TYPE_ENTITY = 2;
	public const TYPE_FAKE_PLAYER = 3;

	/** @var int */
	public $scoreboardId;
	/** @var string */
	public $objectiveName;
	/** @var int */
	public $score;

	/** @var int */
	public $type;

	/** @var int|null (if type entity or player) */
	public $entityUniqueId;
	/** @var string|null (if type fake player) */
	public $customName;
}
