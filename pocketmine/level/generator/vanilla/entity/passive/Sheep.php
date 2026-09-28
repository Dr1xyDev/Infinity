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

use pocketmine\level\generator\vanilla\entity\Animal;
use pocketmine\level\generator\vanilla\entity\behavior\EatBlockBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\FloatBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\FollowParentBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\LookAtPlayerBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\MateBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\PanicBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomLookAroundBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomStrollBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\TemptBehavior;
use pocketmine\level\generator\vanilla\item\Dye;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\Shears;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Color;
use pocketmine\level\generator\vanilla\utils\Random;

use function boolval;
use function intval;
use function rand;

class Sheep extends Animal
{
	public const NETWORK_ID = self::SHEEP;

	public float $width = 0.9;
	public float $height = 1.3;

	protected function addBehaviors() : void
	{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new PanicBehavior($this, 1.25));
		$this->behaviorPool->setBehavior(2, new MateBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(3, new TemptBehavior($this, [Item::WHEAT], 1.1));
		$this->behaviorPool->setBehavior(4, new FollowParentBehavior($this, 1.1));
		$this->behaviorPool->setBehavior(5, new EatBlockBehavior($this));
		$this->behaviorPool->setBehavior(6, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(7, new LookAtPlayerBehavior($this, 6.0));
		$this->behaviorPool->setBehavior(8, new RandomLookAroundBehavior($this));
	}

	protected function initEntity() : void
	{
		$this->setMaxHealth(8);
		$this->setMovementSpeed(0.25);
		$this->setFollowRange(10);
		$this->propertyManager->setByte(self::DATA_COLOR, $this->namedtag->getByte("Color", $this->getRandomColor($this->level->random)));
		$this->setSheared(boolval($this->namedtag->getByte("Sheared", 0)));

		parent::initEntity();
	}

	public function getName() : string
	{
		return "Sheep";
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool
	{
		if (!$this->isImmobile()) {
			$item = $player->getInventory()->getItemInHand();
			if ($item instanceof Shears && !$this->isSheared()) {
				$this->setSheared(true);
				if ($player->hasFiniteResources()) {
					$item->applyDamage(1);
					$player->getInventory()->setItemInHand($item);
				}

				$i = 1 + $this->level->random->nextBoundedInt(3);
				for ($a = 0; $a < $i; $a++) {
					$this->level->dropItem($this, ItemFactory::get(Item::WOOL, intval($this->propertyManager->getByte(self::DATA_COLOR)), 1));

					$this->motion->y += $this->level->random->nextFloat() * 0.05;
					$this->motion->x += ($this->level->random->nextFloat() - $this->level->random->nextFloat()) * 0.1;
					$this->motion->z += ($this->level->random->nextFloat() - $this->level->random->nextFloat()) * 0.1;
				}

				return true;
			}

			if ($item instanceof Dye) {
				if ($this->propertyManager->getByte(self::DATA_COLOR) !== Color::COLOR_SHEEP_WHITE) {
					return false;
				}

				//dye metas count the other way round than wool colours, plus the 1.14 dyes (black, brown, blue, white)
				$dyeMeta = $item->getDamage();
				$woolColor = match ($dyeMeta) {
					16 => Color::COLOR_SHEEP_BLACK,
					17 => Color::COLOR_SHEEP_BROWN,
					18 => Color::COLOR_SHEEP_BLUE,
					19 => Color::COLOR_SHEEP_WHITE,
					default => ($dyeMeta & 0x0f) ^ 0x0f
				};

				if ($player->isSurvival()) {
					$item->pop();
					$player->getInventory()->setItemInHand($item);
				}

				$this->propertyManager->setByte(self::DATA_COLOR, $woolColor);
				return true;
			}
		}
		return parent::onInteract($player, $clickPos);
	}

	public function getXpDropAmount() : int
	{
		return rand(1, ($this->isInLove() ? 7 : 3));
	}

	public function getDrops() : array
	{
		$looting = $this->getLootingLevel();
		return [
			ItemFactory::get(Item::WOOL, intval($this->propertyManager->getByte(self::DATA_COLOR)), $this->isSheared() ? 0 : 1),
			($this->isOnFire() ? ItemFactory::get(Item::COOKED_MUTTON, 0, rand(1, 3 + $looting)) : ItemFactory::get(Item::RAW_MUTTON, 0, rand(1, 3 + $looting)))
		];
	}

	public function isSheared() : bool
	{
		return $this->getGenericFlag(self::DATA_FLAG_SHEARED);
	}

	public function setSheared(bool $value = true) : void
	{
		$this->setGenericFlag(self::DATA_FLAG_SHEARED, $value);
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		$this->namedtag->setByte("Sheared", intval($this->isSheared()));
		$this->namedtag->setByte("Color", intval($this->propertyManager->getByte(self::DATA_COLOR)));
	}

	public function getRandomColor(Random $random) : int
	{
		$i = $random->nextBoundedInt(100);
		return $i < 5 ? Color::COLOR_SHEEP_BLACK : ($i < 10 ? Color::COLOR_SHEEP_GRAY : ($i < 15 ? Color::COLOR_SHEEP_LIGHT_GRAY : ($i < 18 ? Color::COLOR_SHEEP_BROWN : ($random->nextBoundedInt(500) === 0 ? Color::COLOR_SHEEP_PINK : Color::COLOR_SHEEP_WHITE))));
	}

	public function eatGrassBonus(Vector3 $pos) : void
	{
		if (!$this->isBaby()) {
			if ($this->isSheared()) {
				$this->setSheared(false);
			}
		} else {
			// TODO: enlarge baby
		}
	}
}
