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

use pocketmine\level\generator\vanilla\block\Air;
use pocketmine\level\generator\vanilla\block\Bedrock;
use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\object\EnderCrystal;
use pocketmine\level\generator\vanilla\level\Location;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

use function count;

class EndCrystal extends Item
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::END_CRYSTAL, $meta, "End Crystal");
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool
	{
		if ($blockClicked->getId() === self::OBSIDIAN || $blockClicked instanceof Bedrock) {
			$level = $blockClicked->getLevel();
			$entities = $level->getNearbyEntities(new AxisAlignedBB($blockClicked->getX(), $blockClicked->getY(), $blockClicked->getZ(), $blockClicked->getX() + 1, $blockClicked->getY() + 2, $blockClicked->getZ() + 1));
			if (count($entities) === 0 && $level->getBlock($blockClicked->up()) instanceof Air && $level->getBlock($blockClicked->up(2)) instanceof Air) {
				$nbt = Entity::createBaseNBT(Location::fromObject($blockClicked->add(0.5, 1.5, 0.5)));

				$crystal = new EnderCrystal($level, $nbt);
				$crystal->spawnToAll();

				$this->pop();

				return true;
			}
		}
		return false;
	}
}
