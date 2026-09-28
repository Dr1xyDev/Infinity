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
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\DimensionIds;
use pocketmine\Player;
use pocketmine\Server;

class EndPortal extends Transparent
{
	protected $id = self::END_PORTAL;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getLightLevel() : int
	{
		return 1;
	}

	public function getName() : string
	{
		return "End Portal";
	}

	public function getHardness() : float
	{
		return -1;
	}

	public function getBlastResistance() : float
	{
		return 18000000;
	}

	public function isBreakable(Item $item) : bool
	{
		return false;
	}

	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function onEntityCollide(Entity $entity) : void
	{
		$server = Server::getInstance();
		if (!$server->isAllowTheEnd() || !$this->getBoundingBox()->isVectorInside($entity)) {
			return;
		}

		$currentLevel = $entity->getLevel();
		if ($currentLevel === null || $currentLevel->isClosed() || $entity->getPortalTravel() !== null) {
			return;
		}
		if ($entity->hasPortalCooldown()) {
			$entity->resetPortalCooldown();
			return;
		}

		//the exit portal of the end leads back to the spawn point, every other end portal into the end
		$entity->travelToDimension($currentLevel->getDimension() === DimensionIds::THE_END ? DimensionIds::OVERWORLD : DimensionIds::THE_END);
	}

	public function onBreak(Item $item, ?Player $player = null) : bool
	{
		$result = parent::onBreak($item, $player);

		foreach ($this->getHorizontalSides() as $side) {
			if ($side instanceof EndPortal) {
				$side->onBreak($item, $player);
			}
		}

		return $result;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return new AxisAlignedBB(
			$this->x,
			$this->y,
			$this->z,
			$this->x + 1,
			$this->y + 0.75,
			$this->z + 1
		);
	}
}
