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
use pocketmine\level\generator\vanilla\entity\object\Painting;
use pocketmine\level\generator\vanilla\entity\object\PaintingMotive;
use pocketmine\level\generator\vanilla\level\sound\PaintingPlaceSound;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

use function array_rand;
use function count;

class PaintingItem extends Item
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::PAINTING, $meta, "Painting");
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool
	{
		if ($face === Facing::DOWN || $face === Facing::UP) {
			return false;
		}

		$motives = [];

		$totalDimension = 0;
		foreach (PaintingMotive::getAll() as $motive) {
			$currentTotalDimension = $motive->getHeight() + $motive->getWidth();
			if ($currentTotalDimension < $totalDimension) {
				continue;
			}

			if (Painting::canFit($player->level, $blockReplace, $face, true, $motive)) {
				if ($currentTotalDimension > $totalDimension) {
					$totalDimension = $currentTotalDimension;
					/*
					 * This drops all motive possibilities smaller than this
					 * We use the total of height + width to allow equal chance of horizontal/vertical paintings
					 * when there is an L-shape of space available.
					 */
					$motives = [];
				}

				$motives[] = $motive;
			}
		}

		if (count($motives) === 0) { //No space available
			return false;
		}

		/** @var PaintingMotive $motive */
		$motive = $motives[array_rand($motives)];

		static $directions = [
			Facing::SOUTH => 0,
			Facing::WEST => 1,
			Facing::NORTH => 2,
			Facing::EAST => 3
		];

		$direction = $directions[$face] ?? -1;
		if ($direction === -1) {
			return false;
		}

		$nbt = Entity::createBaseNBT($blockReplace, null, $direction * 90, 0);
		$nbt->setByte("Direction", $direction);
		$nbt->setString("Motive", $motive->getName());
		$nbt->setInt("TileX", $blockClicked->getFloorX());
		$nbt->setInt("TileY", $blockClicked->getFloorY());
		$nbt->setInt("TileZ", $blockClicked->getFloorZ());

		$entity = Entity::createEntity("Painting", $blockReplace->getLevel(), $nbt);

		if ($entity instanceof Entity) {
			$this->pop();
			$entity->spawnToAll();

			$player->level->addSound(new PaintingPlaceSound($blockReplace->add(0.5, 0.5, 0.5)));
			return true;
		}

		return false;
	}
}
