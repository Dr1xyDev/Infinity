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
use pocketmine\level\generator\vanilla\level\portal\PortalShape;
use pocketmine\level\generator\vanilla\level\sound\FlintSteelSound;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\DimensionIds;
use pocketmine\Player;
use pocketmine\Server;

class FlintSteel extends Tool
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::FLINT_STEEL, $meta, "Flint and Steel");
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool
	{
		if ($blockReplace->getId() === self::AIR) {
			$fire = BlockFactory::get((($did = $blockReplace->getSide(Facing::DOWN)->getId()) == BlockIds::SOUL_SAND || $did == BlockIds::SOUL_SOIL) ? BlockIds::SOUL_FIRE : BlockIds::FIRE);
			$level = $player->getLevel();
			$level->setBlock($blockReplace, $fire, true);
			$level->addSound(new FlintSteelSound($blockReplace->add(0.5, 0.5, 0.5)));

			$this->applyDamage(1);

			//fire lit inside of an obsidian frame opens a nether portal (not in the end, like vanilla)
			if ($level->getDimension() !== DimensionIds::THE_END && Server::getInstance()->isAllowNether()) {
				$portal = PortalShape::find($level, $blockReplace->getFloorX(), $blockReplace->getFloorY(), $blockReplace->getFloorZ());
				if ($portal !== null) {
					$portal->light($level);
					$level->getPortalIndex()->add($portal);
				}
			}

			return true;
		}

		return false;
	}

	public function getMaxDurability() : int
	{
		return 65;
	}
}
