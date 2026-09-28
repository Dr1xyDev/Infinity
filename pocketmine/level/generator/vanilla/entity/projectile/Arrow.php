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
use pocketmine\level\generator\vanilla\entity\Effect;
use pocketmine\level\generator\vanilla\entity\EffectInstance;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\hostile\Enderman;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\event\entity\ProjectileHitEvent;
use pocketmine\level\generator\vanilla\event\inventory\InventoryPickupArrowEvent;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\item\Potion;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\particle\MobSpellParticle;
use pocketmine\level\generator\vanilla\level\sound\ArrowHitSound;
use pocketmine\level\generator\vanilla\math\RayTraceResult;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\ByteTag;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\TakeItemActorPacket;
use pocketmine\Player;

use pocketmine\level\generator\vanilla\utils\Binary;
use function ceil;
use function count;
use function max;
use function mt_rand;
use function sqrt;

class Arrow extends Projectile
{
	public const NETWORK_ID = self::ARROW;

	public const PICKUP_NONE = 0;
	public const PICKUP_ANY = 1;
	public const PICKUP_CREATIVE = 2;

	private const TAG_PICKUP = "pickup"; //TAG_Byte
	public const TAG_CRIT = "crit"; //TAG_Byte
	private const TAG_LIFE = "life"; //TAG_Short
	private const TAG_MOB_EFFECTS = "mobEffects"; //TAG_List<TAG_Compound>
	private const TAG_AUX_VALUE = "auxValue"; //TAG_Byte

	public float $width = 0.25;
	public float $height = 0.25;

	protected $gravity = 0.05;
	protected $drag = 0.01;

	protected float $damage = 2.0;
	protected int $pickupMode = self::PICKUP_ANY;
	protected float $punchKnockback = 0.0;
	protected int $collideTicks = 0;
	protected bool $critical = false;

	/** @var EffectInstance[] */
	protected array $mobEffects = [];

	public function __construct(ChunkManager $level, CompoundTag $nbt, ?Entity $shootingEntity = null, bool $critical = false){
		parent::__construct($level, $nbt, $shootingEntity);
		$this->setCritical($critical);
	}

	protected function initEntity() : void{
		parent::initEntity();

		$this->pickupMode = $this->namedtag->getByte(self::TAG_PICKUP, self::PICKUP_ANY);
		$this->critical = $this->namedtag->getByte(self::TAG_CRIT, 0) === 1;
		$this->collideTicks = $this->namedtag->getShort(self::TAG_LIFE, $this->collideTicks);

		if ($this->namedtag->hasTag(self::TAG_MOB_EFFECTS, ListTag::class)) {
			/** @var CompoundTag[]|ListTag|null $effectList */
			$effectList = $this->namedtag->getListTag(self::TAG_MOB_EFFECTS);
			foreach ($effectList as $e) {
				$effect = Effect::getEffect($e->getByte("Id"));
				if ($effect === null) {
					continue;
				}

				$this->mobEffects[] = new EffectInstance(
					$effect,
					$e->getInt("Duration"),
					Binary::unsignByte($e->getByte("Amplifier")),
					$e->getByte("ShowParticles", 1) !== 0,
					$e->getByte("Ambient", 0) !== 0
				);
			}
		}

		$this->setAuxValue($this->namedtag->getByte(self::TAG_AUX_VALUE, 0));
	}

	public function saveNBT() : void{
		parent::saveNBT();

		$this->namedtag->setByte(self::TAG_PICKUP, $this->pickupMode);
		$this->namedtag->setByte(self::TAG_CRIT, $this->critical ? 1 : 0);
		$this->namedtag->setShort(self::TAG_LIFE, $this->collideTicks);

		if (count($this->mobEffects) > 0) {
			$effects = [];
			foreach ($this->mobEffects as $effect) {
				$effects[] = new CompoundTag("", [
					new ByteTag("Id", $effect->getId()),
					new ByteTag("Amplifier", Binary::signByte($effect->getAmplifier())),
					new IntTag("Duration", $effect->getDuration()),
					new ByteTag("Ambient", $effect->isAmbient() ? 1 : 0),
					new ByteTag("ShowParticles", $effect->isVisible() ? 1 : 0)
				]);
			}

			$this->namedtag->setTag(new ListTag(self::TAG_MOB_EFFECTS, $effects));
		} else {
			$this->namedtag->removeTag(self::TAG_MOB_EFFECTS);
		}

		$this->namedtag->setByte(self::TAG_AUX_VALUE, $this->getAuxValue());
	}

	public function setThrowableMotion(Vector3 $motion, float $velocity, float $inaccuracy) : bool{
		return $this->setMotion($motion->add(
			$this->random->nextFloat() * ($this->random->nextBoolean() ? 1 : -1) * 0.0075 * $inaccuracy,
			$this->random->nextFloat() * ($this->random->nextBoolean() ? 1 : -1) * 0.0075 * $inaccuracy,
			$this->random->nextFloat() * ($this->random->nextBoolean() ? 1 : -1) * 0.0075 * $inaccuracy
		)
			->multiply($velocity));
	}

	public function isCritical() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_CRITICAL);
	}

	public function setCritical(bool $value = true) : void{
		$this->setGenericFlag(self::DATA_FLAG_CRITICAL, $value);
	}

	public function getResultDamage() : int{
		$base = (int) ceil($this->motion->length() * parent::getResultDamage());
		if($this->isCritical()){
			return ($base + mt_rand(0, (int) ($base / 2) + 1));
		}else{
			return $base;
		}
	}

	public function getMobEffects() : array{
		return $this->mobEffects;
	}

	/**
	 * @param EffectInstance[] $mobEffects
	 */
	public function setMobEffects(array $mobEffects) : void{
		$this->mobEffects = $mobEffects;
	}

	public function addMobEffect(EffectInstance $effect) : void{
		$this->mobEffects[] = $effect;
	}

	public function getPunchKnockback() : float{
		return $this->punchKnockback;
	}

	public function setPunchKnockback(float $punchKnockback) : void{
		$this->punchKnockback = $punchKnockback;
	}

	public function getAuxValue() : int{
		return $this->getDataPropertyManager()->getShort(self::DATA_POTION_AUX_VALUE);
	}

	public function setAuxValue(int $auxValue) : void{
		$this->getDataPropertyManager()->setShort(self::DATA_POTION_AUX_VALUE, $auxValue);
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if ($this->closed) {
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		$auxValue = $this->getAuxValue();
		if ($auxValue > 0 || count($this->mobEffects) !== 0) {
			if (!$this->onGround) {
				for ($i = 0; $i < 2; $i++) {
					$px = $this->x + ($this->motion->x * ($i / 2.0));
					$py = $this->y + ($this->motion->y * ($i / 2.0));
					$pz = $this->z + ($this->motion->z * ($i / 2.0));
					$particle = $this->getParticle(new Vector3($px, $py, $pz));
					if ($particle !== null) {
						$this->level->addParticle($particle);
					}
				}
			} elseif ((count($this->mobEffects) !== 0 || $this->getAuxValue() > 0) && $this->collideTicks % 6 === 0) {
				$px = $this->x + ($this->random->nextFloat() * 2.0 - 1.0) * 0.2;
				$py = $this->y + ($this->random->nextFloat() * 2.0 - 1.0) * 0.2;
				$pz = $this->z + ($this->random->nextFloat() * 2.0 - 1.0) * 0.2;
				$particle = $this->getParticle(new Vector3($px, $py, $pz));
				if ($particle !== null) {
					$this->level->addParticle($particle);
				}
			}
		}

		if (!$this->onGround && $this->isCritical()) {
			for ($i = 0; $i < 4; $i++) {
				$px = $this->x + ($this->motion->x * ($i / 4.0));
				$py = $this->y + ($this->motion->y * ($i / 4.0));
				$pz = $this->z + ($this->motion->z * ($i / 4.0));
				$particle = $this->getParticle(new Vector3($px, $py, $pz));
				if ($particle !== null) {
					$this->level->addParticle($particle);
				}
			}
		}

		if ($this->blockHit !== null) {
			$this->collideTicks += $tickDiff;
			if ($this->collideTicks > 1200) {
				$this->flagForDespawn();
				$hasUpdate = true;
			}
		} else {
			$this->collideTicks = 0;
		}

		return $hasUpdate;
	}

	public function getParticle(Vector3 $pos) : ?MobSpellParticle {
		if (count($this->mobEffects) === 0) {
			$auxValue = $this->getAuxValue();
			if ($auxValue === 0) {
				return null;
			}

			foreach (Potion::getPotionEffectsById($auxValue - 1) as $effect) {
				$this->addMobEffect((clone $effect)->setDuration(max(1, (int) ($effect->getDuration() / 8))));
			}

			if (count($this->mobEffects) === 0) {
				return null;
			}
		}

		$color = $this->mobEffects[0]->getColor();
		return new MobSpellParticle($pos, $color->getR(), $color->getG(), $color->getB(), $color->getA());
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		$this->setCritical(false);
		$this->broadcastSound(new ArrowHitSound($this));
	}

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		parent::onHitBlock($blockHit, $hitResult);
		$this->broadcastEntityEvent(ActorEventPacket::ARROW_SHAKE, 7); //7 ticks

		$this->motion->x = (float) ($hitResult->hitVector->x - $this->x);
		$this->motion->y = (float) ($hitResult->hitVector->y - $this->y);
		$this->motion->z = (float) ($hitResult->hitVector->z - $this->z);

		$distance = sqrt($this->motion->x ** 2 + $this->motion->y ** 2 + $this->motion->z ** 2);
		if ($distance > 0.0) {
			$this->x -= $this->motion->x / $distance * 0.05;
			$this->y -= $this->motion->y / $distance * 0.05;
			$this->z -= $this->motion->z / $distance * 0.05;
		}
	}

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : bool{
		if (($hit = parent::onHitEntity($entityHit, $hitResult))) {
			if ($this->isOnFire() && !($entityHit instanceof Enderman)) {
				$entityHit->setOnFire(5);
			}

			if($this->punchKnockback > 0){
				$horizontalSpeed = sqrt($this->motion->x ** 2 + $this->motion->z ** 2);
				if($horizontalSpeed > 0){
					$multiplier = $this->punchKnockback * 0.6 / $horizontalSpeed;
					$entityHit->setMotion($entityHit->getMotion()->add($this->motion->x * $multiplier, 0.1, $this->motion->z * $multiplier));
				}
			}

			if ($entityHit instanceof Living) {
				foreach ($this->mobEffects as $effect) {
					$entityHit->addEffect($effect);
				}
			}

			if ($entityHit instanceof Enderman) {
				$this->flagForDespawn();
			}
		} else {
			$this->motion->x *= -0.1;
			$this->motion->y *= -0.1;
			$this->motion->z *= -0.1;
			$this->yaw += 180.0;
		}

		return $hit;
	}

	public function getPickupMode() : int{
		return $this->pickupMode;
	}

	public function setPickupMode(int $pickupMode) : void{
		$this->pickupMode = $pickupMode;
	}

	public function onCollideWithPlayer(Player $player) : void{
		if ($this->blockHit === null) {
			return;
		}

		$item = ItemFactory::get(ItemIds::ARROW, $this->getAuxValue());
		if ($player->isSurvival()) {
			if ($player->getOffHandInventory()->getItem(0)->canStackWith($item)) {
				$playerInventory = $player->getOffHandInventory();
			} elseif ($player->getInventory()->canAddItem($item)) {
				$playerInventory = $player->getInventory();
			} else {
				return;
			}
		} else {
			$playerInventory = $player->getInventory();
		}

		$ev = new InventoryPickupArrowEvent($playerInventory, $this);
		if ($this->pickupMode === self::PICKUP_NONE || ($this->pickupMode === self::PICKUP_CREATIVE && !$player->isCreative())) {
			$ev->setCancelled();
		}

		$ev->call();
		if ($ev->isCancelled()) {
			return;
		}

		$pk = new TakeItemActorPacket();
		$pk->eid = $player->getId();
		$pk->target = $this->getId();
		$this->server->broadcastPacket($this->getViewers(), $pk);

		$playerInventory->addItem(clone $item);
		$this->flagForDespawn();
	}
}
