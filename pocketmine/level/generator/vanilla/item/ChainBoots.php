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

use pocketmine\level\generator\vanilla\level\sound\ArmorEquipChainSound;
use pocketmine\level\generator\vanilla\level\sound\Sound;
use pocketmine\level\generator\vanilla\math\Vector3;

class ChainBoots extends Armor
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::CHAIN_BOOTS, $meta, "Chainmail Boots");
	}

	public function getDefensePoints() : int
	{
		return 1;
	}

	public function getMaxDurability() : int
	{
		return 196;
	}

	public function getEnchantAbility() : int{
		return 12;
	}

	public function getArmorSlot() : int
	{
		return ArmorSlot::SLOT_BOOTS;
	}

	public function getEquipSound(Vector3 $vector3) : ?Sound
	{
		return new ArmorEquipChainSound($vector3);
	}
}
