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
use pocketmine\level\generator\vanilla\entity\projectile\Projectile;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\TieredTool;
use pocketmine\level\generator\vanilla\level\particle\DestroyBlockParticle;
use pocketmine\level\generator\vanilla\level\sound\AmethystBlockChimeSound;
use pocketmine\level\generator\vanilla\level\sound\BlockPunchSound;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\RayTraceResult;
use pocketmine\Player;

use function array_rand;
use function mt_rand;

class BuddingAmethyst extends Solid
{
	protected $id = self::BUDDING_AMETHYST;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Budding Amethyst";
	}

	public function getHardness() : float
	{
		return 1.5;
	}

	public function getBlastResistance() : float
	{
		return 1.5;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_IRON;
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		if (mt_rand(1, 5) === 1) {
			$face = Facing::ALL[array_rand(Facing::ALL)];

			$adjacent = $this->getSide($face);
			//TODO: amethyst buds can spawn in water - we need waterlogging support for this

			$targetBlock = null;
			if ($adjacent->getId() === BlockIds::AIR || (($adjacent->getId() == BlockIds::WATER || $adjacent->getId() == BlockIds::STILL_WATER) && $adjacent->getDamage() == 8)) {
				$targetBlock = BlockFactory::get(BlockIds::SMALL_AMETHYST_BUD);
			} elseif ($adjacent->getId() == BlockIds::SMALL_AMETHYST_BUD && $adjacent->getDamage() == $face) {
				$targetBlock = BlockFactory::get(BlockIds::MEDIUM_AMETHYST_BUD);
			} elseif ($adjacent->getId() == BlockIds::MEDIUM_AMETHYST_BUD && $adjacent->getDamage() == $face) {
				$targetBlock = BlockFactory::get(BlockIds::LARGE_AMETHYST_BUD);
			} elseif ($adjacent->getId() == BlockIds::LARGE_AMETHYST_BUD && $adjacent->getDamage() == $face) {
				$targetBlock = BlockFactory::get(BlockIds::AMETHYST_CLUSTER);
			}

			if ($targetBlock != null) {
				$targetBlock->setDamage($face);
				BlockEventHelper::spread($adjacent, $targetBlock, $this);
			}
		}
	}

	public function onBreak(Item $item, ?Player $player = null) : bool
	{
		foreach (Facing::ALL as $face) {
			$side = $this->getSide($face);
			if ($side instanceof AmethystBud && $side->getDamage() == $face) {
				$this->level->setBlock($side, BlockFactory::get(BlockIds::AIR), true, true);
				$this->level->addParticle(new DestroyBlockParticle($side->add(0.5, 0, 0), $side));
			}
		}

		return parent::onBreak($item, $player);
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [];
	}

	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void
	{
		$this->level->addSound(new AmethystBlockChimeSound($this));
		$this->level->addSound(new BlockPunchSound($this, $this));
	}
}
