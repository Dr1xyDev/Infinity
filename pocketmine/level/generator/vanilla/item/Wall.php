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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

class Wall extends ItemBlock
{
	protected int $olderMetaBlockId;

	public function __construct(int $id, int $meta, Block $name, int $oldMetaBlockId){
		parent::__construct($id, $meta, $name);
		$this->olderMetaBlockId = $oldMetaBlockId;
	}

	public function getItemProtocol(int $playerProtocol) : ?TranslatedItemData
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_407) {
			return new TranslatedItemData(ItemIds::COBBLESTONE_WALL, ($this->olderMetaBlockId % 2), $this->getName());
		}elseif ($playerProtocol < ProtocolInfo::PROTOCOL_729) {
			return new TranslatedItemData(ItemIds::COBBLESTONE_WALL, $this->olderMetaBlockId, $this->getName());
		}

		return null;
	}
}
