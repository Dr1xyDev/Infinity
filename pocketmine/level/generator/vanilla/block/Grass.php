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

use pocketmine\level\generator\vanilla\block\utils\BlockEventHelper;
use pocketmine\level\generator\vanilla\item\Hoe;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\item\Shovel;
use pocketmine\level\generator\vanilla\level\generator\feature\FlowersFeature;
use pocketmine\level\generator\vanilla\level\sound\ItemUseOnBlockSound;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Random;

use function count;
use function mt_rand;

class Grass extends Solid implements Growable
{
	protected $id = self::GRASS;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Grass";
	}

	public function getHardness() : float
	{
		return 0.6;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_SHOVEL;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			ItemFactory::get(ItemIds::DIRT)
		];
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		$level = $this->getLevel();
		$lightAbove = $level->getFullLightAt($this->x, $this->y + 1, $this->z);
		if ($lightAbove < 4 && $level->getBlockAt($this->x, $this->y + 1, $this->z)->getLightFilter() >= 2) {
			//grass dies
			BlockEventHelper::spread($this, BlockFactory::get(BlockIds::DIRT), $this);
		} elseif ($lightAbove >= 9) {
			//try grass spread
			for ($i = 0; $i < 4; ++$i) {
				$x = mt_rand($this->x - 1, $this->x + 1);
				$y = mt_rand($this->y - 3, $this->y + 1);
				$z = mt_rand($this->z - 1, $this->z + 1);

				$b = $level->getBlockAt($x, $y, $z);
				if (
					!($b instanceof Dirt) ||
					$b->getDamage() === 1 ||
					$level->getFullLightAt($x, $y + 1, $z) < 4 ||
					$level->getBlockAt($x, $y + 1, $z)->getLightFilter() >= 2
				) {
					continue;
				}

				BlockEventHelper::spread($b, BlockFactory::get(BlockIds::GRASS), $this);
			}
		}
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if($this->getSide(Facing::UP)->getId() !== BlockIds::AIR){
			return false;
		}
		$level = $this->level;
		if($item instanceof Hoe){
			$item->applyDamage(1);
			$newBlock = BlockFactory::get(BlockIds::FARMLAND);
			$level->addSound(new ItemUseOnBlockSound($this->add(0.5, 0.5, 0.5), $newBlock));
			$level->setBlock($this, $newBlock);

			return true;
		}elseif($item instanceof Shovel){
			$item->applyDamage(1);
			$newBlock = BlockFactory::get(BlockIds::GRASS_PATH);
			$level->addSound(new ItemUseOnBlockSound($this->add(0.5, 0.5, 0.5), $newBlock));
			$level->setBlock($this, $newBlock);

			return true;
		}

		return false;
	}

	public function canGrow(Random $random, ?Player $player) : bool{
		return true;
	}

	public function canUseBonemeal(Random $random, ?Player $player) : bool{
		return true;
	}

	public function grow(Random $random, ?Player $player) : void{
		if (BlockEventHelper::grow($this, $this, $player)) {
			$up = $this->getSide(Facing::UP);
			for ($i = 0; $i < 128; ++$i) {
				$up2 = clone $up;
				$count = 0;
				while (true) {
					if ($count >= $i / 16) {
						if ($up2->getId() === BlockIds::AIR) {
							if ($random->nextBoundedInt(8) === 0) {
								$list = $this->level->getBiome($this->getFloorX(), $this->getFloorY())->getGenerationSettings()->getFlowerFeatures();

								$placedBlock = BlockFactory::get(mt_rand(0, 1) === 0 ? BlockIds::POPPY : BlockIds::DANDELION);
								if (count($list) !== 0) {
									$feature = $list[0];
									if ($feature instanceof FlowersFeature) {
										$placedBlock = $feature->getFlowerToPlace($random, $this);
									}
								}
							} else {
								$placedBlock = BlockFactory::get(BlockIds::TALL_GRASS, TallGrass::TYPE_TALL_GRASS);
							}

							$this->level->setBlock($up2, $placedBlock, true, true);
						}

						break;
					}

					$up2 = $this->level->getBlock($up2->add($random->nextBoundedInt(3) - 1, ($random->nextBoundedInt(3) - 1) * $random->nextBoundedInt(3) / 2, $random->nextBoundedInt(3) - 1));
					if ($up2->getSide(Facing::DOWN)->getId() !== BlockIds::GRASS || ($up2->isSolid() && $up2->isFullCube())) {
						break;
					}

					$count++;
				}
			}
		}
	}
}
