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

class CaveSpider extends Spider
{
	public const NETWORK_ID = self::CAVE_SPIDER;

	public float $width = 0.7;
	public float $height = 0.5;

	protected function initEntity() : void
	{
		parent::initEntity();

		$this->setMaxHealth(12);
		$this->setHealth(12);
	}

	public function getName() : string
	{
		return "CaveSpider";
	}

	public function onCollideWithEntity(Entity $entity) : void
	{
		parent::onCollideWithEntity($entity);

		if ($entity instanceof Living) {
			if ($this->getTargetEntity() === $entity) {
				$entity->addEffect(new EffectInstance(Effect::getEffect(Effect::POISON), 7 * 20, 1));
			}
		}
	}
}
