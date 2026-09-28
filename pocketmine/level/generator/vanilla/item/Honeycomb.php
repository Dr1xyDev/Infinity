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
use pocketmine\level\generator\vanilla\block\CopperDoor;
use pocketmine\level\generator\vanilla\block\utils\CopperMaterial;
use pocketmine\level\generator\vanilla\level\sound\CopperWaxApplySound;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

class Honeycomb extends Item
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::HONEYCOMB, $meta, "Honeycomb");
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool{
		if ($blockClicked instanceof CopperMaterial) {
			if(!$blockClicked->isWaxed()){
				$level = $blockClicked->getLevel();
				$level->setBlock($blockClicked, BlockFactory::get($blockClicked->getWaxedId(), $blockClicked->getDamage()), true);
				if ($blockClicked instanceof CopperDoor) {
					$other = $blockClicked->getSide((($blockClicked->getDamage() & 0x08) !== 0) ? Facing::DOWN : Facing::UP);
					if ($other instanceof CopperDoor) {
						$level->setBlock($other, BlockFactory::get($blockClicked->getWaxedId(), $other->getDamage()), true);
					}
				}

				//TODO: orange particles are supposed to appear when applying wax
				$level->addSound(new CopperWaxApplySound($blockClicked));
				$this->pop();
				return true;
			}
		}

		return parent::onActivate($player, $blockReplace, $blockClicked, $face, $clickVector);
	}

	public function getItemProtocol(int $playerProtocol) : ?TranslatedItemData
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_407) {
			return new TranslatedItemData(ItemIds::BLAZE_POWDER, $this->getDamage(), $this->getName());
		}

		return parent::getItemProtocol($playerProtocol);
	}
}
