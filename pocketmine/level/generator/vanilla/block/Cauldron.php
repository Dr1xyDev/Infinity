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

namespace pocketmine\level\generator\vanilla\block;

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\event\entity\EntityCombustByBlockEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByBlockEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\event\player\PlayerBucketEmptyEvent;
use pocketmine\level\generator\vanilla\event\player\PlayerBucketFillEvent;
use pocketmine\level\generator\vanilla\item\Bucket;
use pocketmine\level\generator\vanilla\item\Dye;
use pocketmine\level\generator\vanilla\item\GlassBottle;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\LeatherBoots;
use pocketmine\level\generator\vanilla\item\LeatherCap;
use pocketmine\level\generator\vanilla\item\LeatherPants;
use pocketmine\level\generator\vanilla\item\LeatherTunic;
use pocketmine\level\generator\vanilla\item\Potion;
use pocketmine\level\generator\vanilla\item\SplashPotion;
use pocketmine\level\generator\vanilla\item\TieredTool;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\Cauldron as TileCauldron;
use pocketmine\level\generator\vanilla\tile\Tile;
use pocketmine\level\generator\vanilla\utils\Binary;
use pocketmine\level\generator\vanilla\utils\Color;

use function min;

class Cauldron extends Solid
{
	protected $id = self::CAULDRON_BLOCK;

	protected $itemId = Item::CAULDRON;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Cauldron";
	}

	protected function recalculateCollisionBoxes() : array
	{
		return [
			new AxisAlignedBB($this->x, $this->y, $this->z, $this->x + 1, $this->y + 0.3125, $this->z + 1),
			new AxisAlignedBB($this->x, $this->y, $this->z, $this->x + 0.125, $this->y + 1, $this->z + 1),
			new AxisAlignedBB($this->x, $this->y, $this->z, $this->x + 1, $this->y + 1, $this->z + 0.125),
			new AxisAlignedBB($this->x + 0.875, $this->y, $this->z, $this->x + 1, $this->y + 1, $this->z + 1),
			new AxisAlignedBB($this->x, $this->y, $this->z + 0.875, $this->x + 1, $this->y + 1, $this->z + 1),
		];
	}

	public function getHardness() : float
	{
		return 2;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function isFull() : bool
	{
		return $this->getDamage() === 6 || $this->getDamage() === 15;
	}

	public function isEmpty() : bool
	{
		return $this->getDamage() === 0;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_WOODEN;
	}

	public function getVariantBitmask() : int
	{
		return 15;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$this->getLevel()->setBlock($blockReplace, $this, true, true);

		Tile::createTile(Tile::CAULDRON, $this->getLevel(), TileCauldron::createNBT($this, $face, $item, $player));

		return true;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if ($player !== null) {
			$tile = $this->level->getTile($this);

			if ($tile instanceof TileCauldron) {
				if ($item instanceof Bucket) {
					if ($item->getDamage() === Block::AIR) {
						if (!$tile->hasPotion() && $tile->getCustomColor() === null && $this->isFull()) {
							$stack = clone $item;

							$stack->pop();
							$resultItem = ItemFactory::get(Item::BUCKET, $this->getDamage() < 9 ? Block::FLOWING_WATER : Block::FLOWING_LAVA);
							$ev = new PlayerBucketFillEvent($player, $this, 0, $item, $resultItem);
							$ev->call();
							if (!$ev->isCancelled()) {
								$this->setDamage(0);
								$this->level->setBlock($this, $this, true, true);

								$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_TAKE_WATER);
								if ($player->isSurvival()) {
									if ($stack->getCount() === 0) {
										$player->getInventory()->setItemInHand($ev->getItem());
									} else {
										$player->getInventory()->setItemInHand($stack);
										$player->getInventory()->addItem($ev->getItem());
									}
								} else {
									$player->getInventory()->addItem($ev->getItem());
								}
							} else {
								$player->getInventory()->sendContents($player);
							}
						}
					} elseif ($item->getDamage() === Block::FLOWING_WATER) {
						$ev = new PlayerBucketEmptyEvent($player, $this, 0, $item, ItemFactory::get(Item::BUCKET));
						$ev->call();
						if (!$ev->isCancelled()) {
							if ($tile->hasPotion() || $tile->getCustomColor() !== null || $this->getDamage() >= 9) {
								$this->explodeCauldron($tile);
							} else {
								$this->setDamage(6);
								$tile->setCustomColor(null);
								$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_FILL_WATER);
							}

							$player->getLevel()->setBlock($this, $this, true, true);

							if ($player->isSurvival()) {
								$player->getInventory()->setItemInHand($ev->getItem());
							}
						} else {
							$player->getInventory()->sendContents($player);
						}
					} elseif ($item->getDamage() === Block::FLOWING_LAVA) {
						$ev = new PlayerBucketEmptyEvent($player, $this, 0, $item, ItemFactory::get(Item::BUCKET));
						$ev->call();
						if (!$ev->isCancelled()) {
							if ($tile->hasPotion() || $tile->getCustomColor() !== null || ($this->getDamage() > 0 && $this->getDamage() <= 6)) {
								$this->explodeCauldron($tile);
							} else {
								$this->setDamage(15);
								$tile->setCustomColor(null);
								$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_BUCKET_FILL_LAVA);
							}

							$this->level->setBlock($this, $this, true, true);

							if ($player->isSurvival()) {
								$player->getInventory()->setItemInHand($ev->getItem());
							}
						} else {
							$player->getInventory()->sendContents($player);
						}
					}
				} elseif ($item instanceof Dye) {
					if ($this->getDamage() <= 6 && $this->getDamage() > 0) { // only water
						$color = Color::getDyeColor($item->getDamage());

						if ($tile->getCustomColor() !== null) {
							$color = Color::mix($color, $tile->getCustomColor());
						}

						if ($player->isSurvival()) {
							$item->pop();
							$player->getInventory()->setItemInHand($item);
						}

						$tile->setCustomColor($color);

						$this->updateLiquid();

						// TODO: fix updating of color

						$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_ADD_DYE, Binary::signInt($color->toABGR()));
					}
				} elseif ($item instanceof Potion || $item instanceof SplashPotion) {
					if ((
						!$this->isEmpty() && (($tile->getPotionId() !== $item->getDamage() && $item->getDamage() !== Potion::WATER) ||
								($item->getId() === Item::POTION && $tile->isSplashPotion()) ||
								($item->getId() === Item::SPLASH_POTION && !$tile->isSplashPotion()) && $item->getDamage() !== 0 ||
								($item->getDamage() === Potion::WATER && $tile->hasPotion()))
					) || $this->getDamage() >= 9) {
						$this->explodeCauldron($tile);

						$this->level->setBlock($this, $this, true, true);

						if ($player->isSurvival()) {
							$player->getInventory()->setItemInHand(ItemFactory::get(Item::GLASS_BOTTLE));
						}
					} elseif ($item->getDamage() === Potion::WATER) {
						$this->setDamage(min(6, $this->getDamage() + 2));

						$this->level->setBlock($this, $this, true, true);

						if ($player->isSurvival()) {
							$player->getInventory()->setItemInHand(ItemFactory::get(Item::GLASS_BOTTLE));
						}

						$tile->setPotionId(-1);
						$tile->setSplashPotion(false);
						$tile->setCustomColor(null);

						$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_FILL_WATER);
					} elseif (!$this->isFull()) {
						$this->setDamage(min(6, $this->getDamage() + 2));

						$tile->setPotionId($item->getDamage());
						$tile->setSplashPotion($item instanceof SplashPotion);
						$tile->setCustomColor(null);

						$this->level->setBlock($this, $this, true, true);

						if ($player->isSurvival()) {
							$player->getInventory()->setItemInHand(ItemFactory::get(Item::GLASS_BOTTLE));
						}

						$this->updateLiquid();

						$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_FILL_POTION, $tile->getPotionId());
					}
				} elseif ($item instanceof GlassBottle) {
					if ($this->getDamage() >= 2 && $this->getDamage() < 9) {
						if ($tile->hasPotion()) {
							$this->setDamage($this->getDamage() - 2);

							if ($tile->isSplashPotion()) {
								$result = ItemFactory::get(Item::SPLASH_POTION, $tile->getPotionId());
							} else {
								$result = ItemFactory::get(Item::POTION, $tile->getPotionId());
							}

							if ($this->isEmpty()) {
								$tile->setPotionId(-1);
								$tile->setSplashPotion(false);
								$tile->setCustomColor(null);
							}

							$this->getLevel()->setBlock($this, $this, true);

							if ($player->isSurvival()) {
								$item->pop();
								$player->getInventory()->setItemInHand($item);
							}

							if ($player->getInventory()->canAddItem($result)) {
								$player->getInventory()->addItem($result);
							} else {
								$player->dropItem($result);
							}

							$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_TAKE_POTION);
						} else {
							$this->setDamage($this->getDamage() - 2);
							$this->getLevel()->setBlock($this, $this, true);

							if ($player->isSurvival()) {
								$item->pop();
								$player->getInventory()->setItemInHand($item);
							}

							$result = ItemFactory::get(Item::POTION, Potion::WATER);

							if ($player->getInventory()->canAddItem($result)) {
								$player->getInventory()->addItem($result);
							} else {
								$player->dropItem($result);
							}

							$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_TAKE_WATER);
						}
					}
				} elseif ($item instanceof LeatherBoots || $item instanceof LeatherCap || $item instanceof LeatherPants || $item instanceof LeatherTunic) {
					if (!$this->isEmpty() && $this->getDamage() < 9) {
						if ($tile->getCustomColor() !== null) {
							$this->setDamage($this->getDamage() - 1);
							$this->getLevel()->setBlock($this, $this, true);

							$newItem = clone $item;
							$newItem->setCustomColor($tile->getCustomColor());
							$player->getInventory()->setItemInHand($newItem);

							$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_DYE_ARMOR);

							if ($this->isEmpty()) {
								$tile->setCustomColor(null);
							}
						} else {
							$this->setDamage($this->getDamage() - 1);
							$this->getLevel()->setBlock($this, $this, true);

							$newItem = clone $item;
							$newItem->clearCustomColor();
							$player->getInventory()->setItemInHand($newItem);

							$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_CLEAN_ARMOR);
						}
					}
				}

				return true;
			}
		}

		return false;
	}

	public function explodeCauldron(TileCauldron $cauldron) : void
	{
		$cauldron->setCustomColor(null);
		$cauldron->setPotionId(-1);
		$cauldron->setSplashPotion(false);

		$this->setDamage(0);

		$this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_CAULDRON_EXPLODE);
	}

	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function onEntityCollide(Entity $entity) : void
	{
		$i = $this->getDamage() % 8;
		$f = $this->y + (6 + (6 * $i)) / 16;
		if ($entity->getBoundingBox()->minY <= $f) {
			if ($this->getDamage() >= 9) { // inside of lava
				$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_LAVA, 4);
				$entity->attack($ev);

				$ev = new EntityCombustByBlockEvent($this, $entity, 15);
				$ev->call();
				if (!$ev->isCancelled()) {
					$entity->setOnFire($ev->getDuration());
				}
			} elseif ($i > 0 && $entity->isOnFire()) {
				$this->setDamage($this->getDamage() - 1);

				$entity->extinguish();
			}
		}
	}

	private function updateLiquid() : void
	{
		$pk = new UpdateBlockPacket();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->blockId = $this->getId();
		$pk->blockMeta = $this->getDamage();
		$pk->dataLayerId = UpdateBlockPacket::DATA_LAYER_LIQUID;
		$pk->flags = UpdateBlockPacket::FLAG_ALL_PRIORITY;

		$this->level->broadcastPacketToViewers($this, $pk);
	}

	public function getLightLevel() : int
	{
		// TODO: Fix light problem in api 4.0
		return $this->getDamage() >= 9 ? 15 : 0;
	}

	public function getLightFilter() : int
	{
		return 0;
	}
}
