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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\BlockProtocolConvertor;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\RuntimeBlockMapping;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

class DestroyBlockParticle extends Particle
{
	protected Block $block;

	public function __construct(Vector3 $pos, Block $block)
	{
		parent::__construct($pos->x, $pos->y, $pos->z);
		$this->block = $block;
	}

	public function encode()
	{
		$pk = new LevelEventPacket();
		$pk->evid = LevelEventPacket::EVENT_PARTICLE_DESTROY;
		$pk->position = $this->asVector3();

		$block = BlockProtocolConvertor::getInstance()->get($this->block, $this->protocol) ?? $this->block;
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$pk->data = RuntimeBlockMapping::getInstance($this->protocol)->toRuntimeId($block->getFullId());
		} else {
			$pk->data = $block->getId() + ($block->getDamage() << 8);
		}

		return $pk;
	}

	public function isUseProtocol() : bool
	{
		return true;
	}
}
