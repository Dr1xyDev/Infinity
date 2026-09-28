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

namespace pocketmine\level\generator\vanilla\level;

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\TNT;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\event\block\BlockExplodeEvent;
use pocketmine\level\generator\vanilla\event\block\BlockUpdateEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByBlockEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByEntityEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityExplodeEvent;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\particle\HugeExplodeParticle;
use pocketmine\level\generator\vanilla\level\sound\ExplodeSound;
use pocketmine\level\generator\vanilla\level\utils\SubChunkIteratorManager;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ExplodePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\tile\Chest;
use pocketmine\level\generator\vanilla\tile\Container;
use pocketmine\level\generator\vanilla\tile\LootContainer;
use pocketmine\level\generator\vanilla\tile\Tile;
use pocketmine\level\generator\vanilla\utils\Utils;
use function ceil;
use function count;
use function floor;
use function mt_rand;

class Explosion
{
	public const DEFAULT_FIRE_CHANCE = 1.0 / 3.0;

	private int $rays = 16;
	public Level $level;

	/** @var Block[] */
	public array $affectedBlocks = [];
	public float $stepLen = 0.3;
	/** @var Block[] */
	private array $fireIgnitions = [];

	private SubChunkIteratorManager $subChunkHandler;

	public function __construct(
		public Position $source,
		public float $size,
		private Entity|Block|null $what = null,
		private float $fireChance = 0.0
	) {
		if (!$source->isValid()) {
			throw new InvalidArgumentException("Position does not have a valid world");
		}
		$this->level = $source->getLevel();
		Utils::checkFloatNotInfOrNaN("fireChance", $fireChance);
		if ($fireChance < 0.0 || $fireChance > 1.0) {
			throw new InvalidArgumentException("Fire chance must be a number between 0 and 1.");
		}
		if ($size <= 0) {
			throw new InvalidArgumentException("Explosion radius must be greater than 0, got $size");
		}

		$this->subChunkHandler = new SubChunkIteratorManager($this->level, false);
	}

	/**
	 * Calculates which blocks will be destroyed by this explosion. If explodeB() is called without calling this, no blocks
	 * will be destroyed.
	 */
	public function explodeA() : bool
	{
		if ($this->size < 0.1) {
			return false;
		}

		$vector = new Vector3(0, 0, 0);
		$vBlock = new Position(0, 0, 0, $this->level);

		$mRays = (int) ($this->rays - 1);
		$incendiary = $this->fireChance > 0;
		for ($i = 0; $i < $this->rays; ++$i) {
			for ($j = 0; $j < $this->rays; ++$j) {
				for ($k = 0; $k < $this->rays; ++$k) {
					if ($i === 0 || $i === $mRays || $j === 0 || $j === $mRays || $k === 0 || $k === $mRays) {
						$vector = new Vector3($i / $mRays * 2 - 1, $j / $mRays * 2 - 1, $k / $mRays * 2 - 1);
						$vector = new Vector3(($vector->x / ($len = $vector->length())) * $this->stepLen, ($vector->y / $len) * $this->stepLen, ($vector->z / $len) * $this->stepLen);
						$pointerX = $this->source->x;
						$pointerY = $this->source->y;
						$pointerZ = $this->source->z;

						for ($blastForce = $this->size * (mt_rand(700, 1300) / 1000); $blastForce > 0; $blastForce -= $this->stepLen * 0.75) {
							$x = (int) $pointerX;
							$y = (int) $pointerY;
							$z = (int) $pointerZ;
							$vBlock->x = $pointerX >= $x ? $x : $x - 1;
							$vBlock->y = $pointerY >= $y ? $y : $y - 1;
							$vBlock->z = $pointerZ >= $z ? $z : $z - 1;

							$pointerX += $vector->x;
							$pointerY += $vector->y;
							$pointerZ += $vector->z;

							if (!$this->subChunkHandler->moveTo($vBlock->x, $vBlock->y, $vBlock->z)) {
								continue;
							}

							$fullId = $this->subChunkHandler->currentSubChunk->getFullBlock($vBlock->x & 0x0f, $vBlock->y & 0x0f, $vBlock->z & 0x0f);

							if ($fullId !== 0) {
								$blastForce -= (BlockFactory::$blastResistance[$fullId] / 5 + 0.3) * $this->stepLen;
								if ($blastForce > 0) {
									if (!isset($this->affectedBlocks[$index = Level::blockHash($vBlock->x, $vBlock->y, $vBlock->z)])) {
										$block = BlockFactory::fromFullBlock($fullId, $vBlock);
										$this->affectedBlocks[$index] = $block;

										if ($incendiary && Utils::getRandomFloat() <= $this->fireChance) {
											$this->fireIgnitions[$index] = $block;
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Executes the explosion's effects on the world. This includes destroying blocks (if any), harming and knocking back entities,
	 * and creating sounds and particles.
	 */
	public function explodeB() : bool
	{
		$send = [];
		$updateBlocks = [];

		$source = (new Vector3($this->source->x, $this->source->y, $this->source->z))->floor();
		$yield = (1 / $this->size) * 100;

		if ($this->what instanceof Entity) {
			$ev = new EntityExplodeEvent($this->what, $this->source, $this->affectedBlocks, $yield, $this->fireIgnitions);

			$ev->call();
			if ($ev->isCancelled()) {
				return false;
			}

			$yield = $ev->getYield();
			$this->affectedBlocks = $ev->getBlockList();
			$this->fireIgnitions = $ev->getIgnitions();
		} elseif ($this->what instanceof Block) {
			$ev = new BlockExplodeEvent(
				$this->what,
				$this->source,
				$this->affectedBlocks,
				$yield,
				$this->fireIgnitions,
			);

			$ev->call();
			if ($ev->isCancelled()) {
				return false;
			}

			$yield = $ev->getYield();
			$this->affectedBlocks = $ev->getAffectedBlocks();
			$this->fireIgnitions = $ev->getIgnitions();
		}

		$explosionSize = $this->size * 2;
		$minX = (int) floor($this->source->x - $explosionSize - 1);
		$maxX = (int) ceil($this->source->x + $explosionSize + 1);
		$minY = (int) floor($this->source->y - $explosionSize - 1);
		$maxY = (int) ceil($this->source->y + $explosionSize + 1);
		$minZ = (int) floor($this->source->z - $explosionSize - 1);
		$maxZ = (int) ceil($this->source->z + $explosionSize + 1);

		$explosionBB = new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);

		$list = $this->level->getNearbyEntities($explosionBB, $this->what instanceof Entity ? $this->what : null);
		foreach ($list as $entity) {
			$distance = $entity->distance($this->source) / $explosionSize;

			if ($distance <= 1) {
				$motion = $entity->subtractVector($this->source)->normalize();

				$impact = (1 - $distance) * ($exposure = 1);

				$damage = (int) ((($impact * $impact + $impact) / 2) * 8 * $explosionSize + 1);

				if ($this->what instanceof Entity) {
					$ev = new EntityDamageByEntityEvent($this->what, $entity, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, $damage);
				} elseif ($this->what instanceof Block) {
					$ev = new EntityDamageByBlockEvent($this->what, $entity, EntityDamageEvent::CAUSE_BLOCK_EXPLOSION, $damage);
				} else {
					$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_BLOCK_EXPLOSION, $damage);
				}

				$entity->attack($ev);
				$entity->setMotion($motion->multiply($impact));
			}
		}

		$air = ItemFactory::get(ItemIds::AIR);
		$fireBlock = BlockFactory::get(BlockIds::FIRE);
		$airBlock = BlockFactory::get(BlockIds::AIR);

		foreach ($this->affectedBlocks as $index => $block) {
			$yieldDrops = false;

			if ($block instanceof TNT) {
				$block->ignite(mt_rand(10, 30));
			} elseif ($yieldDrops = (mt_rand(0, 100) < $yield)) {
				foreach ($block->getDrops($air) as $drop) {
					$this->level->dropItem($block->add(0.5, 0.5, 0.5), $drop);
				}
			}

			$targetBlock =
				isset($this->fireIgnitions[$index]) &&
				$block->getSide(Facing::DOWN)->isSolid() ?
					$fireBlock :
					$airBlock;
			$this->level->setBlockAt($block->x, $block->y, $block->z, $targetBlock);

			$t = $this->level->getTileAt($block->x, $block->y, $block->z);
			if ($t instanceof Tile) {
				if ($t instanceof Chest) {
					$t->unpair();
				}
				if ($t instanceof LootContainer) {
					$t->unpackLootTable();
				}
				if ($t instanceof Container) {
					$t->getInventory()->dropContents($this->level, $t->add(0.5, 0.5, 0.5));
				}

				$t->close();
			}

			$pos = new Vector3($block->x, $block->y, $block->z);

			for ($side = 0; $side <= 5; $side++) {
				$sideBlock = $pos->getSide($side);
				if (!$this->level->isInWorld($sideBlock->x, $sideBlock->y, $sideBlock->z)) {
					continue;
				}
				if (!isset($this->affectedBlocks[$index = Level::blockHash($sideBlock->x, $sideBlock->y, $sideBlock->z)]) && !isset($updateBlocks[$index])) {
					$ev = new BlockUpdateEvent($this->level->getBlockAt($sideBlock->x, $sideBlock->y, $sideBlock->z));
					$ev->call();
					if (!$ev->isCancelled()) {
						foreach ($this->level->getNearbyEntities(new AxisAlignedBB($sideBlock->x - 1, $sideBlock->y - 1, $sideBlock->z - 1, $sideBlock->x + 2, $sideBlock->y + 2, $sideBlock->z + 2)) as $entity) {
							$entity->onNearbyBlockChange();
						}
						$ev->getBlock()->onNearbyBlockChange();
					}
					$updateBlocks[$index] = true;
				}
			}

			$send[] = new Vector3($block->x - $source->x, $block->y - $source->y, $block->z - $source->z);
		}

		$pk = new ExplodePacket();
		$pk->position = $this->source->asVector3();
		$pk->radius = $this->size;
		$pk->records = $send;

		$viewers = $this->level->getChunkPlayers($this->source->getFloorX() >> Chunk::COORD_BIT_SIZE, $this->source->getFloorZ() >> Chunk::COORD_BIT_SIZE);
		if (count($viewers) > 0) {
			foreach ($viewers as $player) {
				if ($player->getProtocolVersion() < ProtocolInfo::PROTOCOL_407) {
					$player->sendDataPacket($pk);
				}
			}
		}

		$this->level->addParticle(new HugeExplodeParticle($source));
		$this->level->addSound(new ExplodeSound($source));

		return true;
	}

	/**
	 * Sets a chance between 0 and 1 of creating a fire.
	 * For example, if the chance is 1/3, then that amount of affected blocks will be ignited.
	 *
	 * @param float $fireChance 0 ... 1
	 */
	public function setFireChance(float $fireChance) : void
	{
		Utils::checkFloatNotInfOrNaN("fireChance", $fireChance);
		if ($fireChance < 0.0 || $fireChance > 1.0) {
			throw new InvalidArgumentException("Fire chance must be a number between 0 and 1.");
		}
		$this->fireChance = $fireChance;
	}
}
