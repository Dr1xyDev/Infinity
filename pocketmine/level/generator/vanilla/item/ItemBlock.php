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
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\BlockProtocolConvertor;

/**
 * Class used for Items that can be Blocks
 */
class ItemBlock extends Item
{
	private int $blockFullId;

	/**
	 * @param int $meta usually 0-15 (placed blocks may only have meta values 0-15)
	 */
	public function __construct(int $id, int $meta, Block $block)
	{
		parent::__construct($id, $meta, $block->getName());
		$this->blockFullId = $block->getFullId();
	}

	public function getBlock() : Block
	{
		return BlockFactory::fromFullBlock($this->blockFullId);
	}

	public function getVanillaName() : string
	{
		return $this->getBlock()->getName();
	}

	public function getFuelTime() : int
	{
		return $this->getBlock()->getFuelTime();
	}

	public function getItemProtocol(int $playerProtocol) : ?TranslatedItemData
	{
		$blockProtocol = BlockProtocolConvertor::getInstance()->get($this->getBlock(), $playerProtocol);
		if ($blockProtocol === null) {
			return null;
		}

		$item = $blockProtocol->asItem();
		return new TranslatedItemData($item->getId(), $item->getDamage(), $this->getName());
	}
}
