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

namespace pocketmine\level\generator\vanilla\entity\hostile;

use pocketmine\level\generator\vanilla\entity\Effect;
use pocketmine\level\generator\vanilla\entity\EffectInstance;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;

class Stray extends Skeleton
{
	public const NETWORK_ID = self::STRAY;

	public function getName() : string
	{
		return "Stray";
	}

	public function getDrops() : array
	{
		$looting = $this->getLootingLevel();
		$drops = parent::getDrops();
		$drops[] = ItemFactory::get(Item::ARROW, 18, 1 + $looting);
		return $drops;
	}

	public function onCollideWithEntity(Entity $entity) : void
	{
		parent::onCollideWithEntity($entity);

		if ($this->getTargetEntityId() === $entity->getId() && $entity instanceof Living) {
			$entity->addEffect(new EffectInstance(Effect::getEffect(Effect::WITHER), 7 * 20, 1));
		}
	}
}
