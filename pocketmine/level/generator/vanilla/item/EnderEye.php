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
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\EndPortalFrame;
use pocketmine\level\generator\vanilla\level\particle\SmokeParticle;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\utils\Utils;

class EnderEye extends Item
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::ENDER_EYE, $meta, "Eye of Ender");
	}

	public function getMaxStackSize() : int
	{
		return 64;
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool{
		if(!Server::getInstance()->isAllowTheEnd()){
			return false;
		}

		if($blockClicked->getId() !== BlockIds::END_PORTAL_FRAME || ($blockClicked->getDamage() & EndPortalFrame::META_EYE) !== 0){
			return false;
		}

		$newMeta = $blockClicked->getDamage() | EndPortalFrame::META_EYE;
		$blockClicked->getLevel()->setBlock($blockClicked, Block::get(BlockIds::END_PORTAL_FRAME, $newMeta));
		$this->pop();

		for ($i = 0; $i < 16; ++$i)  {
			$player->getLevel()->addParticle(new SmokeParticle(new Vector3(
				$blockClicked->getX() + (5.0 + Utils::getRandomFloat() * 6.0) / 16.0,
				$blockClicked->getY() + 0.8125,
				$blockClicked->getZ() + (5.0 + Utils::getRandomFloat() * 6.0) / 16.0
			)));
		}

		$player->getLevel()->broadcastLevelSoundEvent($blockClicked->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_BLOCK_END_PORTAL_FRAME_FILL);

		$patternHelper = EndPortalFrame::getOrCreatePortalShape()->match($player->getLevel(), $blockClicked);
		if($patternHelper !== null){
			for($palmOffset = 1; $palmOffset <= 3; ++$palmOffset){
				for($thumbOffset = 1; $thumbOffset <= 3; ++$thumbOffset){
					$player->getLevel()->setBlock($patternHelper->translateOffsetPosition($palmOffset, $thumbOffset, 0), BlockFactory::get(BlockIds::END_PORTAL));
				}
			}

			$player->getLevel()->broadcastLevelSoundEvent($patternHelper->translateOffsetPosition(2, 2, 0), LevelSoundEventPacket::SOUND_BLOCK_END_PORTAL_SPAWN);
		}

		return true;
	}
}
