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

namespace pocketmine\level\generator\vanilla\block;

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\TieredTool;

class Cobweb extends Flowable
{
	protected $id = self::COBWEB;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function getName() : string
	{
		return "Cobweb";
	}

	public function getHardness() : float
	{
		return 4;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_SWORD | BlockToolType::TYPE_SHEARS;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_WOODEN;
	}

	public function onEntityCollide(Entity $entity) : void
	{
		$entity->resetFallDistance();
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			ItemFactory::get(Item::STRING)
		];
	}

	public function diffusesSkyLight() : bool
	{
		return true;
	}
}
