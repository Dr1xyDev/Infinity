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
use pocketmine\level\generator\vanilla\block\BlockToolType;
use pocketmine\level\generator\vanilla\block\CopperDoor;
use pocketmine\level\generator\vanilla\block\utils\CopperMaterial;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\level\sound\CopperWaxRemoveSound;
use pocketmine\level\generator\vanilla\level\sound\ScrapeSound;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

class Axe extends TieredTool
{
	public function getBlockToolType() : int
	{
		return BlockToolType::TYPE_AXE;
	}

	public function getBlockToolHarvestLevel() : int
	{
		return $this->tier;
	}

	public function getAttackPoints() : int
	{
		return self::getBaseDamageFromTier($this->tier) - 1;
	}

	public function onDestroyBlock(Block $block) : bool
	{
		if ($block->getHardness() > 0) {
			return $this->applyDamage(1);
		}
		return false;
	}

	public function onAttackEntity(Entity $victim) : bool
	{
		return $this->applyDamage(2);
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool{
		if ($blockClicked instanceof CopperMaterial){
			$level = $blockClicked->getLevel();
			if($blockClicked->isWaxed()){
				$level->setBlock($blockClicked, BlockFactory::get($blockClicked->getNonWaxedId(), $blockClicked->getDamage()), true);
				if ($blockClicked instanceof CopperDoor) {
					$other = $blockClicked->getSide((($blockClicked->getDamage() & 0x08) !== 0) ? Facing::DOWN : Facing::UP);
					if ($other instanceof CopperDoor) {
						$level->setBlock($other, BlockFactory::get($blockClicked->getNonWaxedId(), $other->getDamage()), true);
					}
				}

				//TODO: white particles are supposed to appear when removing wax
				$level->addSound(new CopperWaxRemoveSound($blockClicked));
				$this->applyDamage(1);
				return true;
			}

			$previousOxidationId = $blockClicked->getPreviousOxidationId();
			if($previousOxidationId !== null){
				$level->setBlock($blockClicked, BlockFactory::get($previousOxidationId, $blockClicked->getDamage()), true);
				if ($blockClicked instanceof CopperDoor) {
					$other = $blockClicked->getSide((($blockClicked->getDamage() & 0x08) !== 0) ? Facing::DOWN : Facing::UP);
					if ($other instanceof CopperDoor) {
						$level->setBlock($other, BlockFactory::get($previousOxidationId, $other->getDamage()), true);
					}
				}

				//TODO: turquoise particles are supposed to appear when removing oxidation
				$level->addSound(new ScrapeSound($blockClicked));
				$this->applyDamage(1);
				return true;
			}
		}

		return parent::onActivate($player, $blockReplace, $blockClicked, $face, $clickVector);
	}

	public function getItemProtocol(int $playerProtocol) : ?TranslatedItemData
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_407) {
			if ($this->getId() === ItemIds::NETHERITE_AXE) {
				return new TranslatedItemData(ItemIds::DIAMOND_AXE, $this->getDamage(), $this->getName());
			}
		}
		return parent::getItemProtocol($playerProtocol);
	}
}
