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
use pocketmine\level\generator\vanilla\event\entity\EntityPreExplodeEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\level\Explosion;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;

class EnderCrystal extends Entity implements Explosive
{
	public const NETWORK_ID = self::ENDER_CRYSTAL;

	public const TAG_SHOWBASE = "ShowBottom"; //TAG_Byte

	public const TAG_BLOCKTARGET_X = "BlockTargetX"; //TAG_Int
	public const TAG_BLOCKTARGET_Y = "BlockTargetY"; //TAG_Int
	public const TAG_BLOCKTARGET_Z = "BlockTargetZ"; //TAG_Int

	public float $height = 2.0;
	public float $width = 2.0;

	protected $gravity = 0;
	protected $drag = 1.0;

	private bool $primed = false;

	protected function initEntity() : void{
		parent::initEntity();

		$this->setMaxHealth(1);
		$this->setHealth(1);

		$this->setShowBase($this->namedtag->getByte(self::TAG_SHOWBASE, 0) === 1);

		if(
			($beamXTag = $this->namedtag->getTag(self::TAG_BLOCKTARGET_X)) instanceof IntTag &&
			($beamYTag = $this->namedtag->getTag(self::TAG_BLOCKTARGET_Y)) instanceof IntTag &&
			($beamZTag = $this->namedtag->getTag(self::TAG_BLOCKTARGET_Z)) instanceof IntTag
		){
			$this->setBeamTarget(new Vector3($beamXTag->getValue(), $beamYTag->getValue(), $beamZTag->getValue()));
		}
	}

	public function saveNBT() : void{
		parent::saveNBT();

		$this->namedtag->setByte(self::TAG_SHOWBASE, $this->isShowBase() ? 1 : 0);

		$beamTarget = $this->getBeamTarget();
		if($beamTarget !== null){
			$this->namedtag->setInt(self::TAG_BLOCKTARGET_X, $beamTarget->getFloorX());
			$this->namedtag->setInt(self::TAG_BLOCKTARGET_Y, $beamTarget->getFloorY());
			$this->namedtag->setInt(self::TAG_BLOCKTARGET_Z, $beamTarget->getFloorZ());
		}
	}

	public function isShowBase() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_SHOWBASE);
	}

	public function setShowBase(bool $value) : void{
		$this->setGenericFlag(self::DATA_FLAG_SHOWBASE, $value);
	}

	public function getBeamTarget() : ?Vector3 {
		return $this->getDataPropertyManager()->getVector3(self::DATA_BLOCK_TARGET);
	}

	public function setBeamTarget(?Vector3 $target) : void{
		$this->getDataPropertyManager()->setVector3(self::DATA_BLOCK_TARGET, $target);
	}

	public function attack(EntityDamageEvent $source) : void{
		parent::attack($source);

		if(
			$source->getCause() !== EntityDamageEvent::CAUSE_VOID &&
			!$source->isCancelled()
		){
			$this->primed = true;
		}
	}

	protected function onDeathUpdate(int $tickDiff) : bool{
		if($this->primed){
			$this->explode();
		}
		return true;
	}

	public function explode() : void{
		$ev = new EntityPreExplodeEvent($this, 6);
		$ev->call();
		if(!$ev->isCancelled()){
			$explosion = new Explosion($this->getPosition(), $ev->getRadius(), $this, $ev->getFireChance());
			if($ev->isBlockBreaking()){
				$explosion->explodeA();
			}
			$explosion->explodeB();
		}
	}

	public function isFireProof() : bool{
		return true;
	}

	public function getPickedItem() : ?Item{
		return ItemFactory::get(ItemIds::END_CRYSTAL);
	}
}
