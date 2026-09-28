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

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\entity\Ageable;
use pocketmine\level\generator\vanilla\entity\behavior\AvoidMobTypeBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\FloatBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\LookAtPlayerBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\PanicBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomLookAroundBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomStrollBehavior;
use pocketmine\level\generator\vanilla\entity\Effect;
use pocketmine\level\generator\vanilla\entity\EffectInstance;
use pocketmine\level\generator\vanilla\entity\hostile\Zombie;
use pocketmine\level\generator\vanilla\entity\Mob;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\Player;

use function array_rand;
use function count;
use function mt_rand;

class Villager extends Mob implements Ageable
{
	public const NETWORK_ID = self::VILLAGER;

	public static $names = [
		Villager::PROFESSION_FARMER => [
			Villager::CAREER_FARMER => "entity.villager.farmer",
			Villager::CAREER_FISHERMAN => "entity.villager.fisherman",
			Villager::CAREER_STEPHERD => "entity.villager.shepherd",
			Villager::CAREER_FLETCHER => "entity.villager.fletcher"
		], Villager::PROFESSION_LIBRARIAN => [
			Villager::CAREER_LIBRARIAN => "entity.villager.librarian",
			Villager::CAREER_CARTOGRAPHER => "entity.villager.cartographer"
		], Villager::PROFESSION_PRIEST => [
			Villager::CAREER_CLERIC => "entity.villager.cleric"
		], Villager::PROFESSION_BLACKSMITH => [
			Villager::CAREER_ARMOR => "entity.villager.armor", Villager::CAREER_WEAPON => "entity.villager.weapon",
			Villager::CAREER_TOOL => "entity.villager.tool"
		], Villager::PROFESSION_BUTCHER => [
			Villager::CAREER_BUTCHER => "entity.villager.butcher", Villager::CAREER_LEATHER => "entity.villager.leather"
		]
	];

	public const CAREER_FARMER = 1, CAREER_LIBRARIAN = 1, CAREER_CLERIC = 1, CAREER_ARMOR = 1, CAREER_BUTCHER = 1;
	public const CAREER_FISHERMAN = 2, CAREER_CARTOGRAPHER = 2, CAREER_WEAPON = 2, CAREER_LEATHER = 2;
	public const CAREER_STEPHERD = 3, CAREER_TOOL = 3;
	public const CAREER_FLETCHER = 4;

	public const PROFESSION_FARMER = 0;
	public const PROFESSION_LIBRARIAN = 1;
	public const PROFESSION_PRIEST = 2;
	public const PROFESSION_BLACKSMITH = 3;
	public const PROFESSION_BUTCHER = 4;

	public float $width = 0.6;
	public float $height = 1.8;

	/** @var int */
	protected $career;
	/** @var int */
	protected $tradeTier;
	/** @var bool */
	protected $isWilling = true;

	protected $offers;

	public function getName() : string
	{
		return "Villager";
	}

	protected function addBehaviors() : void
	{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new PanicBehavior($this, 4.0));
		$this->behaviorPool->setBehavior(2, new RandomStrollBehavior($this, 2.0));
		$this->behaviorPool->setBehavior(3, new LookAtPlayerBehavior($this, 6.0));
		$this->behaviorPool->setBehavior(4, new RandomLookAroundBehavior($this));
		$this->behaviorPool->setBehavior(5, new AvoidMobTypeBehavior($this, Zombie::class, 6.0, 4.0, 4.2));
	}

	protected function initEntity() : void
	{
		parent::initEntity();

		/** @var int $profession */
		$profession = $this->namedtag->getInt("Profession", self::PROFESSION_FARMER);

		if ($profession > 4 || $profession < 0) {
			$profession = self::PROFESSION_FARMER;
		}

		$this->setProfession($profession);

		$this->career = $this->namedtag->getInt("Career", array_rand(self::$names[$this->getProfession()])); // custom
		$this->tradeTier = $this->namedtag->getInt("TradeTier", 0);
		$this->updateTradeItems();
	}

	public function updateTradeItems() : void
	{
		$this->offers = new CompoundTag("Offers", [
			new ListTag("Recipes", []) // TODO
		]);
	}

	public function updateTradeTier() : void
	{
		$tradeTier = $this->getTradeTier() + 1;
		try {
			$this->setTradeTier($tradeTier);
			$this->addEffect(new EffectInstance(Effect::getEffect(Effect::REGENERATION), mt_rand(2, 5) * 20));
		} catch (InvalidArgumentException $exception) {
		}
	}

	public function setTradeTier(int $tradeTier) : void
	{
		$items = []; // TODO
		if (count($items) < ($tradeTier + 1)) {
			throw new InvalidArgumentException("Trade tier $tradeTier is not available");
		}

		$this->tradeTier = $tradeTier;
		$this->updateTradeItems();
	}

	public function getTradeTier() : int
	{
		return $this->tradeTier;
	}

	public function getCareer() : int
	{
		return $this->career;
	}

	public function setCareer(int $career) : void
	{
		$pro = $this->getProfession();
		if (!isset(self::$names[$pro][$career])) {
			throw new InvalidArgumentException("$career is not found on $pro profession.");
		}

		$this->career = $career;
	}

	public function setOffers(CompoundTag $offers) : void
	{
		$this->offers = $offers;
	}

	public function getOffers() : ?CompoundTag
	{
		return $this->offers;
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		$this->namedtag->setInt("Profession", $this->getProfession());
		$this->namedtag->setInt("Career", $this->career);
		$this->namedtag->setInt("TradeTier", $this->tradeTier);
		$this->updateTradeItems();
		$this->namedtag->setTag($this->offers, true);
	}

	public function setProfession(int $profession) : void
	{
		$this->propertyManager->setInt(self::DATA_VARIANT, $profession);
	}

	public function getProfession() : int
	{
		return $this->propertyManager->getInt(self::DATA_VARIANT);
	}

	public function isBaby() : bool
	{
		return $this->getGenericFlag(self::DATA_FLAG_BABY);
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool
	{
		if (!$this->isBaby() && $this->offers instanceof CompoundTag && !$this->isImmobile() && !$this->isWilling()) {
			// TODO: open trade inventory
			return true;
		}
		return parent::onInteract($player, $clickPos);
	}

	public function getDisplayName() : string
	{
		return self::$names[$this->getProfession()][$this->getCareer()] ?? "entity.villager.name";
	}

	public function isWilling() : bool
	{
		return $this->isWilling;
	}

	public function setWilling(bool $isWilling) : void
	{
		$this->isWilling = $isWilling;
	}

	public function getTradingPlayer() : ?Player
	{
		$eid = $this->getTradingPlayerId();
		if ($eid !== null) {
			$player = $this->server->findEntity($eid);

			return $player instanceof Player ? $player : null;
		}

		return null;
	}

	public function setTradingPlayer(?Player $player) : void
	{
		if ($player === null) {
			$this->propertyManager->setLong(self::DATA_TRADING_PLAYER_EID, -1);
		} elseif ($player->closed) {
			throw new InvalidArgumentException("Supplied trading player is garbage and cannot be used");
		} else {
			$this->propertyManager->setLong(self::DATA_TRADING_PLAYER_EID, $player->getId());
		}
	}

	public function getTradingPlayerId() : ?int
	{
		return $this->propertyManager->getLong(self::DATA_TRADING_PLAYER_EID);
	}
}
