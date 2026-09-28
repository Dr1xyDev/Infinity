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
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\object\ArmorStand as EntityArmorStand;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

use function round;

class ArmorStand extends Item
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::ARMOR_STAND, $meta, "Armor Stand");
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool
	{
		$entity = Entity::createEntity("ArmorStand", $player->level, Entity::createBaseNBT($blockReplace->asVector3()->add(0.5, 0, 0.5), null, $this->getDirection($player->getYaw())));

		if ($entity instanceof EntityArmorStand) {
			if ($player->isSurvival()) {
				$this->pop();
			}

			$entity->spawnToAll();
			$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_PLACE);
			return true;
		}

		return false;
	}

	public function getDirection(float $yaw)
	{
		return (round($yaw / 22.5 / 2) * 45) - 180;
	}

	public function getItemProtocol(int $playerProtocol) : ?TranslatedItemData
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_407) {
			return new TranslatedItemData(ItemIds::PLANKS, $this->getDamage(), $this->getName());
		}

		return parent::getItemProtocol($playerProtocol);
	}
}
