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

namespace pocketmine\level\generator\vanilla\entity\object;

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\item\Fireworks;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;

class FireworksRocket extends Entity
{
	public const NETWORK_ID = self::FIREWORKS_ROCKET;

	public float $width = 0.25;
	public float $height = 0.25;

	/** @var int */
	protected $lifeTime = 0;

	public function __construct(ChunkManager $level, CompoundTag $nbt, ?Fireworks $fireworks = null)
	{
		parent::__construct($level, $nbt);

		if ($fireworks !== null && $fireworks->getNamedTagEntry("Fireworks") instanceof CompoundTag) {
			$this->propertyManager->setItem(self::DATA_MINECART_DISPLAY_BLOCK, $fireworks);
			$this->setLifeTime($fireworks->getRandomizedFlightDuration());
		}

		if ($level instanceof Level) {
			$level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_LAUNCH);
		}
	}

	protected function tryChangeMovement() : void
	{
		$this->motion->x *= 1.15;
		$this->motion->y += 0.04;
		$this->motion->z *= 1.15;
	}

	public function entityBaseTick(int $tickDiff = 1) : bool
	{
		if ($this->closed) {
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);
		if ($this->doLifeTimeTick()) {
			$hasUpdate = true;
		}

		return $hasUpdate;
	}

	public function setLifeTime(int $life) : void
	{
		$this->lifeTime = $life;
	}

	protected function doLifeTimeTick() : bool
	{
		if (!$this->isFlaggedForDespawn() && --$this->lifeTime < 0) {
			$this->doExplosionAnimation();
			$this->flagForDespawn();
			return true;
		}

		return false;
	}

	protected function doExplosionAnimation() : void
	{
		$fireworks_nbt = $this->propertyManager->getItem(self::DATA_MINECART_DISPLAY_BLOCK);
		if ($fireworks_nbt === null) {
			return;
		}

		$fireworks_nbt = $fireworks_nbt->getNamedtag()->getCompoundTag("Fireworks");
		if ($fireworks_nbt === null) {
			return;
		}

		$explosions = $fireworks_nbt->getListTag("Explosions");
		if ($explosions === null) {
			return;
		}

		/** @var CompoundTag $explosion */
		foreach ($explosions->getAllValues() as $explosion) {
			switch ($explosion->getByte("FireworkType")) {
				case Fireworks::TYPE_SMALL_SPHERE:
					$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_BLAST);
					break;
				case Fireworks::TYPE_HUGE_SPHERE:
					$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_LARGE_BLAST);
					break;
				case Fireworks::TYPE_STAR:
				case Fireworks::TYPE_BURST:
				case Fireworks::TYPE_CREEPER_HEAD:
					$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_TWINKLE);
					break;
			}
		}

		$this->broadcastEntityEvent(ActorEventPacket::FIREWORK_PARTICLES);
	}
}
