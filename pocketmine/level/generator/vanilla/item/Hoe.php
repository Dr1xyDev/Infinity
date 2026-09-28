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

namespace pocketmine\level\generator\vanilla\item;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockToolType;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

class Hoe extends TieredTool
{
	public function getBlockToolType() : int
	{
		return BlockToolType::TYPE_HOE;
	}

	public function onAttackEntity(Entity $victim) : bool
	{
		return $this->applyDamage(1);
	}

	public function onDestroyBlock(Block $block) : bool
	{
		if ($block->getHardness() > 0) {
			return $this->applyDamage(1);
		}
		return false;
	}

	public function getItemProtocol(int $playerProtocol) : ?TranslatedItemData
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_407) {
			if ($this->getId() === ItemIds::NETHERITE_HOE) {
				return new TranslatedItemData(ItemIds::DIAMOND_HOE, $this->getDamage(), $this->getName());
			}
		}

		return parent::getItemProtocol($playerProtocol);
	}
}
