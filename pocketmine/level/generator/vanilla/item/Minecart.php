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
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use function in_array;

class Minecart extends Item
{
	private const RAILS = [Block::RAIL, Block::POWERED_RAIL, Block::DETECTOR_RAIL, Block::ACTIVATOR_RAIL];

	public function __construct(int $meta = 0)
	{
		parent::__construct(self::MINECART, $meta, "Minecart");
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	/**
	 * Save name of the entity placed by this item
	 */
	protected function getEntityName() : string
	{
		return "Minecart";
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool
	{
		if (!in_array($blockClicked->getId(), self::RAILS, true)) {
			return false;
		}

		$nbt = Entity::createBaseNBT($blockClicked->add(0.5, 0, 0.5));
		if ($this->hasCustomName()) {
			$nbt->setString("CustomName", $this->getCustomName());
		}
		$entity = Entity::createEntity($this->getEntityName(), $player->level, $nbt);
		if ($entity === null) {
			return false;
		}
		$entity->spawnToAll();

		if ($player->hasFiniteResources()) {
			$this->pop();
		}

		return true;
	}
}
