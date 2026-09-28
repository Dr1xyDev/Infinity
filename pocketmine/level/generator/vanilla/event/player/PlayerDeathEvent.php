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

namespace pocketmine\level\generator\vanilla\event\player;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByBlockEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByEntityEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDeathEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\lang\TextContainer;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\Player;

class PlayerDeathEvent extends EntityDeathEvent
{
	/** @var Player */
	protected $player;

	/** @var TextContainer|string */
	private $deathMessage;
	/** @var bool */
	private $keepInventory = false;
	private $keepExperience = false;

	/**
	 * @param Item[]                    $drops
	 * @param string|TextContainer|null $deathMessage Null will cause the default vanilla message to be used
	 */
	public function __construct(Player $entity, array $drops, $deathMessage = null, int $xp = 0)
	{
		parent::__construct($entity, $drops, $xp);
		$this->player = $entity;
		$this->deathMessage = $deathMessage ?? self::deriveMessage($entity->getDisplayName(), $entity->getLastDamageCause());
	}

	/**
	 * @return Player
	 */
	public function getEntity()
	{
		return $this->player;
	}

	public function getPlayer() : Player
	{
		return $this->player;
	}

	/**
	 * @return TextContainer|string
	 */
	public function getDeathMessage()
	{
		return $this->deathMessage;
	}

	/**
	 * @param TextContainer|string $deathMessage
	 */
	public function setDeathMessage($deathMessage) : void
	{
		$this->deathMessage = $deathMessage;
	}

	public function getKeepInventory() : bool
	{
		return $this->keepInventory;
	}

	public function setKeepInventory(bool $keepInventory) : void
	{
		$this->keepInventory = $keepInventory;
	}

	public function getKeepExperience() : bool
	{
		return $this->keepExperience;
	}

	public function setKeepExperience(bool $keepExperience) : void
	{
		$this->keepExperience = $keepExperience;
	}

	/**
	 * Returns the vanilla death message for the given death cause.
	 */
	public static function deriveMessage(string $name, ?EntityDamageEvent $deathCause) : TranslationContainer
	{
		$message = "death.attack.generic";
		$params = [$name];

		switch ($deathCause === null ? EntityDamageEvent::CAUSE_CUSTOM : $deathCause->getCause()) {
			case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
				if ($deathCause instanceof EntityDamageByEntityEvent) {
					$e = $deathCause->getDamager();
					if ($e instanceof Player) {
						$message = "death.attack.player";
						$params[] = $e->getDisplayName();
						break;
					} elseif ($e instanceof Living) {
						$message = "death.attack.mob";
						$params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
						break;
					} else {
						$params[] = "Unknown";
					}
				}
				break;
			case EntityDamageEvent::CAUSE_PROJECTILE:
				if ($deathCause instanceof EntityDamageByEntityEvent) {
					$e = $deathCause->getDamager();
					if ($e instanceof Player) {
						$message = "death.attack.arrow";
						$params[] = $e->getDisplayName();
					} elseif ($e instanceof Living) {
						$message = "death.attack.arrow";
						$params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
						break;
					} else {
						$params[] = "Unknown";
					}
				}
				break;
			case EntityDamageEvent::CAUSE_SUICIDE:
				$message = "death.attack.generic";
				break;
			case EntityDamageEvent::CAUSE_VOID:
				$message = "death.attack.outOfWorld";
				break;
			case EntityDamageEvent::CAUSE_FALL:
				if ($deathCause instanceof EntityDamageEvent) {
					if ($deathCause->getFinalDamage() > 2) {
						$message = "death.fell.accident.generic";
						break;
					}
				}
				$message = "death.attack.fall";
				break;

			case EntityDamageEvent::CAUSE_SUFFOCATION:
				$message = "death.attack.inWall";
				break;

			case EntityDamageEvent::CAUSE_LAVA:
				$message = "death.attack.lava";
				break;

			case EntityDamageEvent::CAUSE_FIRE:
				$message = "death.attack.onFire";
				break;

			case EntityDamageEvent::CAUSE_FIRE_TICK:
				$message = "death.attack.inFire";
				break;

			case EntityDamageEvent::CAUSE_DROWNING:
				$message = "death.attack.drown";
				break;

			case EntityDamageEvent::CAUSE_CONTACT:
				if ($deathCause instanceof EntityDamageByBlockEvent) {
					if ($deathCause->getDamager()->getId() === Block::CACTUS) {
						$message = "death.attack.cactus";
					}
				}
				break;

			case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
			case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
				if ($deathCause instanceof EntityDamageByEntityEvent) {
					$e = $deathCause->getDamager();
					if ($e instanceof Player) {
						$message = "death.attack.explosion.player";
						$params[] = $e->getDisplayName();
					} elseif ($e instanceof Living) {
						$message = "death.attack.explosion.player";
						$params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
						break;
					}
				} else {
					$message = "death.attack.explosion";
				}
				break;

			case EntityDamageEvent::CAUSE_MAGIC:
				$message = "death.attack.magic";
				break;

			case EntityDamageEvent::CAUSE_CUSTOM:
				break;

			default:
				break;
		}

		return new TranslationContainer($message, $params);
	}
}
