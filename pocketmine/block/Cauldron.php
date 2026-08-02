<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\block;

use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerGlassBottleEvent;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\item\Potion;
use pocketmine\level\sound\ExplodeSound;
use pocketmine\level\sound\GraySplashSound;
use pocketmine\level\sound\SpellSound;
use pocketmine\level\sound\SplashSound;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\tile\Cauldron as TileCauldron;
use pocketmine\tile\Tile;
use pocketmine\utils\Color;

class Cauldron extends Solid{

	protected $id = self::CAULDRON_BLOCK;

	public function __construct($meta = 0){
		$this->meta = (int) $meta;
	}

	public function getHardness(){
		return 2;
	}

	public function getName() : string{
		return "Cauldron";
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$nbt = new CompoundTag("", [
			new StringTag("id", Tile::CAULDRON),
			new IntTag("x", $block->x),
			new IntTag("y", $block->y),
			new IntTag("z", $block->z),
			new ShortTag("PotionId", 0xffff),
			new ByteTag("SplashPotion", 0),
			new ListTag("Items", [])
		]);
		
		if($item->hasCustomBlockData()){
			foreach($item->getCustomBlockData() as $key => $v){
				$nbt->{$key} = $v;
			}
		}
		
		$chunk = $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4);
		$tile = Tile::createTile("Cauldron", $chunk, $nbt);
		$this->getLevel()->setBlock($block, $this, true, true);
		return true;
	}

	public function onBreak(Item $item){
		$this->getLevel()->setBlock($this, new Air(), true);
		return true;
	}

	public function getDrops(Item $item) : array{
		if($item->isPickaxe() >= 1){
			return [
				[Item::CAULDRON, 0, 1]
			];
		}
		return [];
	}

	public function update(){
		$this->getLevel()->setBlock($this, Block::get($this->id, $this->meta + 1), true);
		$this->getLevel()->setBlock($this, $this, true);
	}

	public function isEmpty(){
		return $this->meta === 0x00;
	}

	public function isFull(){
		return $this->meta === 0x06;
	}

	public function onActivate(Item $item, Player $player = null){
		$tile = $this->getLevel()->getTile($this);
		if(!($tile instanceof TileCauldron)){
			return false;
		}
		switch($item->getId()){
			case Item::BUCKET:
				if($item->getDamage() === 0){
					if(!$this->isFull() or $tile->isCustomColor() or $tile->hasPotion()){
						break;
					}
					$bucket = clone $item;
					$bucket->setDamage(8);
					Server::getInstance()->getPluginManager()->callEvent($ev = new PlayerBucketFillEvent($player, $this, 0, $item, $bucket));
					if(!$ev->isCancelled()){
						if($player->isSurvival()){
							$player->getInventory()->setItemInHand($ev->getItem());
						}
						$this->meta = 0;
						$this->getLevel()->setBlock($this, $this, true);
						$tile->clearCustomColor();
						$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
					}
				}elseif($item->getDamage() === 8){
					if($this->isFull() and !$tile->isCustomColor() and !$tile->hasPotion()){
						break;
					}
					$bucket = clone $item;
					$bucket->setDamage(0);
					Server::getInstance()->getPluginManager()->callEvent($ev = new PlayerBucketEmptyEvent($player, $this, 0, $item, $bucket));
					if(!$ev->isCancelled()){
						if($player->isSurvival()){
							$player->getInventory()->setItemInHand($ev->getItem());
						}
						if($tile->hasPotion()){
							$this->meta = 0;
							$tile->setPotionId(0xffff);
							$tile->setSplashPotion(false);
							$tile->clearCustomColor();
							$this->getLevel()->setBlock($this, $this, true);
							$this->getLevel()->addSound(new ExplodeSound($this->add(0.5, 0, 0.5)));
						}else{
							$this->meta = 6;
							$tile->clearCustomColor();
							$this->getLevel()->setBlock($this, $this, true);
							$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
						}
						$this->update();
					}
				}
				break;
			case Item::DYE:
				if($tile->hasPotion()) break;
				$color = Color::getDyeColor($item->getDamage());
				if($tile->isCustomColor()){
					$color = Color::averageColor($color, $tile->getCustomColor());
				}
				if($player->isSurvival()){
					$item->setCount($item->getCount() - 1);
					
				}
				$tile->setCustomColor($color);
				$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
				$this->update();
				break;
			case Item::LEATHER_CAP:
			case Item::LEATHER_TUNIC:
			case Item::LEATHER_PANTS:
			case Item::LEATHER_BOOTS:
				if($this->isEmpty()) break;
				if($tile->isCustomColor()){
					--$this->meta;
					$this->getLevel()->setBlock($this, $this, true);
					$newItem = clone $item;
					
					$newItem->setCustomColor($tile->getCustomColor());
					$player->getInventory()->setItemInHand($newItem);
					$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
					if($this->isEmpty()){
						$tile->clearCustomColor();
					}
				}else{
					--$this->meta;
					$this->getLevel()->setBlock($this, $this, true);
					$newItem = clone $item;
					
					$newItem->clearCustomColor();
					$player->getInventory()->setItemInHand($newItem);
					$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
				}
				break;
			case Item::POTION:
			case Item::SPLASH_POTION:
				if(!$this->isEmpty() and (($tile->getPotionId() !== $item->getDamage() and $item->getDamage() !== Potion::WATER_BOTTLE) or
						($item->getId() === Item::POTION and $tile->getSplashPotion()) or
						($item->getId() === Item::SPLASH_POTION and !$tile->getSplashPotion()) and $item->getDamage() !== 0 or
						($item->getDamage() === Potion::WATER_BOTTLE and $tile->hasPotion()))
				){
					$this->meta = 0x00;
					$this->getLevel()->setBlock($this, $this, true);
					$tile->setPotionId(0xffff);
					$tile->setSplashPotion(false);
					$tile->clearCustomColor();
					if($player->isSurvival()){
						$player->getInventory()->setItemInHand(Item::get(Item::GLASS_BOTTLE));
					}
					$this->getLevel()->addSound(new ExplodeSound($this->add(0.5, 0, 0.5)));
				}elseif($item->getDamage() === Potion::WATER_BOTTLE){
					$this->meta += 2;
					if($this->meta > 0x06) $this->meta = 0x06;
					$this->getLevel()->setBlock($this, $this, true);
					if($player->isSurvival()){
						$player->getInventory()->setItemInHand(Item::get(Item::GLASS_BOTTLE));
					}
					$tile->setPotionId(0xffff);
					$tile->setSplashPotion(false);
					$tile->clearCustomColor();
					$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
				}elseif(!$this->isFull()){
					$this->meta += 2;
					if($this->meta > 0x06) $this->meta = 0x06;
					$tile->setPotionId($item->getDamage());
					$tile->setSplashPotion($item->getId() === Item::SPLASH_POTION);
					$tile->clearCustomColor();
					$this->getLevel()->setBlock($this, $this, true);
					if($player->isSurvival()){
						$player->getInventory()->setItemInHand(Item::get(Item::GLASS_BOTTLE));
					}
					$color = Potion::getColor($item->getDamage());
					$this->getLevel()->addSound(new SpellSound($this->add(0.5, 1, 0.5), $color[0], $color[1], $color[2]));
				}
				break;
			case Item::GLASS_BOTTLE:
				$player->getServer()->getPluginManager()->callEvent($ev = new PlayerGlassBottleEvent($player, $this, $item));
				if($ev->isCancelled()){
					return false;
				}
				if($this->meta < 2) {
					break;
				}
				if($tile->hasPotion()){
					$this->meta -= 2;
					if($tile->getSplashPotion() === true){
						$result = Item::get(Item::SPLASH_POTION, $tile->getPotionId());
					}else{
						$result = Item::get(Item::POTION, $tile->getPotionId());
					}
					if($this->isEmpty()){
						$tile->setPotionId(0xffff);
						$tile->setSplashPotion(false);
						$tile->clearCustomColor();
					}
					$this->getLevel()->setBlock($this, $this, true);
					$this->addItem($item, $player, $result);
					$color = Potion::getColor($result->getDamage());
					$this->getLevel()->addSound(new SpellSound($this->add(0.5, 1, 0.5), $color[0], $color[1], $color[2]));
				}else{
					$this->meta -= 2;
					$this->getLevel()->setBlock($this, $this, true);
					if($player->isSurvival()){
						$result = Item::get(Item::POTION, Potion::WATER_BOTTLE);
						$this->addItem($item, $player, $result);
					}
					$this->getLevel()->addSound(new GraySplashSound($this->add(0.5, 1, 0.5)));
				}
				break;
		}
		return true;
	}

	public function addItem(Item $item, Player $player, Item $result){
		if($item->getCount() <= 1){
			$player->getInventory()->setItemInHand($result);
		}else{
			$item->setCount($item->getCount() - 1);
			if($player->getInventory()->canAddItem($result) === true){
				$player->getInventory()->addItem($result);
			}else{
				$motion = $player->getDirectionVector()->multiply(0.4);
				$position = clone $player->getPosition();
				$player->getLevel()->dropItem($position->add(0 , 0.5, 0), $result , $motion, 40);
			}
		}
	}
}