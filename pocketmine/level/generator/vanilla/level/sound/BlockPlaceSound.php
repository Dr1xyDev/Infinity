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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\BlockProtocolConvertor;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\RuntimeBlockMapping;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

final class BlockPlaceSound extends Sound
{
	public function __construct(
		Vector3 $pos,
		private Block $block
	) {
		parent::__construct($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
	}

	public function encode()
	{
		$block = BlockProtocolConvertor::getInstance()->get($this->block, $this->protocol) ?? $this->block;
		return [LevelSoundEventPacket::nonActorSound(
			LevelSoundEventPacket::SOUND_PLACE,
			$this,
			false,
			($this->protocol >= ProtocolInfo::PROTOCOL_407 ?
				RuntimeBlockMapping::getInstance($this->protocol)->toRuntimeId($block->getFullId()) :
				$block->getId())
		)];
	}

	public function isUseProtocol() : bool
	{
		return true;
	}
}
