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

namespace pocketmine\level\generator\vanilla\entity\vehicle;

use pocketmine\level\generator\vanilla\entity\Vehicle;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByEntityEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\GameRules;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class Minecart extends Vehicle
{
	public const NETWORK_ID = self::MINECART;

	public float $height = 0.7;
	public float $width = 0.98;

	protected $gravity = 0.5;
	protected $drag = 0.1;

	protected function initEntity() : void
	{
		$this->setHealth(6);

		parent::initEntity();
	}

	public function getRiderSeatPosition(int $seatNumber = 0) : Vector3
	{
		return new Vector3($seatNumber * 0.8, 0, 0);
	}

	/**
	 * @return Item[]
	 */
	public function getDrops() : array
	{
		return [
			ItemFactory::get(Item::MINECART)
		];
	}

	public function attack(EntityDamageEvent $source) : void
	{
		if ($this->isKilled) {
			//already broken, don't drop twice
			return;
		}

		parent::attack($source);

		if ($source->isCancelled() || !($source instanceof EntityDamageByEntityEvent)) {
			return;
		}

		$this->setHurtTime(10);
		$this->setHurtDirection(-$this->getHurtDirection());

		$damager = $source->getDamager();
		$creative = $damager instanceof Player && $damager->isCreative();
		if ($creative || $this->getHealth() <= 0) {
			$this->kill();
			if (!$creative && $this->level->getGameRules()->getBool(GameRules::RULE_DO_ENTITY_DROPS)) {
				foreach ($this->getDrops() as $drop) {
					$this->level->dropItem($this, $drop);
				}
			}
		}
	}
}
