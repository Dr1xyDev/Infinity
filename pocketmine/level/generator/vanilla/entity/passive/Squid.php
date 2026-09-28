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

namespace pocketmine\level\generator\vanilla\entity\passive;

use pocketmine\level\generator\vanilla\entity\WaterAnimal;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByEntityEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorEventPacket;

use function atan2;
use function mt_rand;
use function sqrt;

use const M_PI;

class Squid extends WaterAnimal
{
	public const NETWORK_ID = self::SQUID;

	public float $width = 0.95;
	public float $height = 0.95;

	/** @var Vector3|null */
	public $swimDirection = null;
	/** @var float */
	public $swimSpeed = 0.1;

	/** @var int */
	private $switchDirectionTicker = 0;

	public function initEntity() : void
	{
		$this->setMaxHealth(10);
		parent::initEntity();
	}

	public function getName() : string
	{
		return "Squid";
	}

	public function attack(EntityDamageEvent $source) : void
	{
		parent::attack($source);
		if ($source->isCancelled()) {
			return;
		}

		if ($source instanceof EntityDamageByEntityEvent) {
			$this->swimSpeed = mt_rand(150, 350) / 2000;
			$e = $source->getDamager();
			if ($e !== null) {
				$this->swimDirection = (new Vector3($this->x - $e->x, $this->y - $e->y, $this->z - $e->z))->normalize();
			}

			$this->broadcastEntityEvent(ActorEventPacket::SQUID_INK_CLOUD);
		}
	}

	private function generateRandomDirection() : Vector3
	{
		return new Vector3(mt_rand(-1000, 1000) / 1000, mt_rand(-500, 500) / 1000, mt_rand(-1000, 1000) / 1000);
	}

	public function entityBaseTick(int $tickDiff = 1) : bool
	{
		if ($this->closed) {
			return false;
		}

		if (++$this->switchDirectionTicker === 100) {
			$this->switchDirectionTicker = 0;
			if (mt_rand(0, 100) < 50) {
				$this->swimDirection = null;
			}
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if ($this->isAlive()) {

			if ($this->y > 62 && $this->swimDirection !== null) {
				$this->swimDirection->y = -0.5;
			}

			$inWater = $this->isUnderwater();
			if (!$inWater) {
				$this->swimDirection = null;
			} elseif ($this->swimDirection !== null) {
				if ($this->motion->lengthSquared() <= $this->swimDirection->lengthSquared()) {
					$this->motion = $this->swimDirection->multiply($this->swimSpeed);
				}
			} else {
				$this->swimDirection = $this->generateRandomDirection();
				$this->swimSpeed = mt_rand(50, 100) / 2000;
			}

			$f = sqrt(($this->motion->x ** 2) + ($this->motion->z ** 2));
			$this->yaw = (-atan2($this->motion->x, $this->motion->z) * 180 / M_PI);
			$this->pitch = (-atan2($f, $this->motion->y) * 180 / M_PI);
		}

		return $hasUpdate;
	}

	public function getDrops() : array
	{
		$looting = $this->getLootingLevel();
		return [
			ItemFactory::get(Item::DYE, 0, mt_rand(1, 3 + $looting))
		];
	}

	public function canSpawnHere() : bool
	{
		return parent::canSpawnHere() && $this->y > 45 && $this->y < 63;
	}

	protected function applyGravity() : void
	{
		if (!$this->isUnderwater()) {
			parent::applyGravity();
		}
	}
}
