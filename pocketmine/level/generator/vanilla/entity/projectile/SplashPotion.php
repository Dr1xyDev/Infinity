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

namespace pocketmine\level\generator\vanilla\entity\projectile;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\entity\EffectInstance;
use pocketmine\level\generator\vanilla\entity\hostile\Witch;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\entity\Monster;
use pocketmine\level\generator\vanilla\event\entity\ProjectileHitBlockEvent;
use pocketmine\level\generator\vanilla\event\entity\ProjectileHitEntityEvent;
use pocketmine\level\generator\vanilla\event\entity\ProjectileHitEvent;
use pocketmine\level\generator\vanilla\item\Potion;
use pocketmine\level\generator\vanilla\level\sound\PotionSplashSound;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelEventPacket;
use pocketmine\level\generator\vanilla\utils\Color;

use function round;
use function sqrt;

class SplashPotion extends Throwable
{
	public const NETWORK_ID = self::SPLASH_POTION;

	protected $gravity = 0.05;
	protected $drag = 0.01;

	protected function initEntity() : void
	{
		parent::initEntity();

		$this->setPotionId($this->namedtag->getShort("PotionId", 0));
	}

	public function saveNBT() : void
	{
		parent::saveNBT();
		$this->namedtag->setShort("PotionId", $this->getPotionId());
	}

	public function getResultDamage() : int
	{
		return -1; //no damage
	}

	protected function onHit(ProjectileHitEvent $event) : void
	{
		$effects = $this->getPotionEffects();
		$hasEffects = true;

		if (empty($effects)) {
			$colors = [
				new Color(0x38, 0x5d, 0xc6) //Default colour for splash water bottle and similar with no effects.
			];
			$hasEffects = false;
		} else {
			$colors = [];
			foreach ($effects as $effect) {
				$level = $effect->getEffectLevel();
				for ($j = 0; $j < $level; ++$j) {
					$colors[] = $effect->getColor();
				}
			}
		}

		$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_PARTICLE_SPLASH, Color::mix(...$colors)->toARGB());
		$this->broadcastSound(new PotionSplashSound($this));

		if ($hasEffects) {
			if (!$this->willLinger()) {
				foreach ($this->level->getNearbyEntities($this->boundingBox->expandedCopy(4.125, 2.125, 4.125), $this) as $entity) {
					if ($entity instanceof Living && $entity->isAlive() && !($this->getOwningEntity() instanceof Witch && $entity instanceof Monster)) {
						$distanceSquared = $entity->add(0, $entity->getEyeHeight(), 0)->distanceSquared($this);
						if ($distanceSquared > 16) { //4 blocks
							continue;
						}

						$distanceMultiplier = 1 - (sqrt($distanceSquared) / 4);
						if ($event instanceof ProjectileHitEntityEvent && $entity === $event->getEntityHit()) {
							$distanceMultiplier = 1.0;
						}

						foreach ($this->getPotionEffects() as $effect) {
							//getPotionEffects() is used to get COPIES to avoid accidentally modifying the same effect instance already applied to another entity

							if (!$effect->getType()->isInstantEffect()) {
								$newDuration = (int) round($effect->getDuration() * 0.75 * $distanceMultiplier);
								if ($newDuration < 20) {
									continue;
								}
								$effect->setDuration($newDuration);
								$entity->addEffect($effect);
							} else {
								$effect->getType()->applyEffect($entity, $effect, $distanceMultiplier, $this, $this->getOwningEntity());
							}
						}
					}
				}
			} else {
				//TODO: lingering potions
			}
		} elseif ($event instanceof ProjectileHitBlockEvent && $this->getPotionId() === Potion::WATER) {
			$blockIn = $event->getBlockHit()->getSide($event->getRayTraceResult()->getHitFace());

			if ($blockIn->getId() === Block::FIRE) {
				$this->level->setBlock($blockIn, BlockFactory::get(Block::AIR));
			}
			foreach ($blockIn->getHorizontalSides() as $horizontalSide) {
				if ($horizontalSide->getId() === Block::FIRE) {
					$this->level->setBlock($horizontalSide, BlockFactory::get(Block::AIR));
				}
			}
		}
	}

	/**
	 * Returns the meta value of the potion item that this splash potion corresponds to. This decides what effects will be applied to the entity when it collides with its target.
	 */
	public function getPotionId() : int
	{
		return $this->propertyManager->getShort(self::DATA_POTION_AUX_VALUE) ?? 0;
	}

	public function setPotionId(int $id) : void
	{
		$this->propertyManager->setShort(self::DATA_POTION_AUX_VALUE, $id);
	}

	/**
	 * Returns whether this splash potion will create an area-effect cloud when it lands.
	 */
	public function willLinger() : bool
	{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_LINGER);
	}

	/**
	 * Sets whether this splash potion will create an area-effect-cloud when it lands.
	 */
	public function setLinger(bool $value = true) : void
	{
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_LINGER, $value);
	}

	/**
	 * @return EffectInstance[]
	 */
	public function getPotionEffects() : array
	{
		return Potion::getPotionEffectsById($this->getPotionId());
	}
}
