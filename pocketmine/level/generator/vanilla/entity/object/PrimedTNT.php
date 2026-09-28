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
use pocketmine\level\generator\vanilla\entity\Explosive;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\event\entity\ExplosionPrimeEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\Explosion;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\nbt\tag\ShortTag;

class PrimedTNT extends Entity implements Explosive
{
	public const NETWORK_ID = self::TNT;

	public float $width = 0.98;
	public float $height = 0.98;

	protected float $baseOffset = 0.49;

	protected $gravity = 0.04;
	protected $drag = 0.02;

	protected $fuse;

	public bool $canCollide = false;

	public function attack(EntityDamageEvent $source) : void
	{
		if ($source->getCause() === EntityDamageEvent::CAUSE_VOID) {
			parent::attack($source);
		}
	}

	protected function initEntity() : void
	{
		parent::initEntity();

		if ($this->namedtag->hasTag("Fuse", ShortTag::class)) {
			$this->fuse = $this->namedtag->getShort("Fuse");
		} else {
			$this->fuse = 80;
		}

		$this->setGenericFlag(self::DATA_FLAG_IGNITED, true);
		$this->propertyManager->setInt(self::DATA_FUSE_LENGTH, $this->fuse);
	}

	public function canCollideWith(Entity $entity) : bool
	{
		return false;
	}

	public function saveNBT() : void
	{
		parent::saveNBT();
		$this->namedtag->setShort("Fuse", $this->fuse, true); //older versions incorrectly saved this as a byte
	}

	public function entityBaseTick(int $tickDiff = 1) : bool
	{
		if ($this->closed) {
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if ($this->fuse % 5 === 0) { //don't spam it every tick, it's not necessary
			$this->propertyManager->setInt(self::DATA_FUSE_LENGTH, $this->fuse);
		}

		if (!$this->isFlaggedForDespawn()) {
			$this->fuse -= $tickDiff;

			if ($this->fuse <= 0) {
				$this->flagForDespawn();
				$this->explode();
			}
		}

		return $hasUpdate || $this->fuse >= 0;
	}

	public function explode() : void{
		$ev = new ExplosionPrimeEvent($this, 4);
		$ev->call();
		if (!$ev->isCancelled()) {
			$explosion = new Explosion(Position::fromObject($this->add(0, $this->height / 2, 0), $this->level), $ev->getForce(), $this);
			if ($ev->isBlockBreaking()) {
				$explosion->explodeA();
			}
			$explosion->explodeB();
		}
	}

	public function getPickedItem() : ?Item
	{
		return ItemFactory::get(Item::TNT);
	}
}
