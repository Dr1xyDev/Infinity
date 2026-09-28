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

use pocketmine\level\generator\vanilla\block\utils\StaticSupportTrait;
use pocketmine\level\generator\vanilla\entity\projectile\Projectile;
use pocketmine\level\generator\vanilla\event\block\StructureGrowEvent;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\level\sound\ChorusFlowerDieSound;
use pocketmine\level\generator\vanilla\level\sound\ChorusFlowerGrowSound;
use pocketmine\level\generator\vanilla\math\Axis;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\RayTraceResult;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use function abs;
use function array_rand;
use function count;
use function min;
use function mt_rand;

class ChorusFlower extends Flowable
{
	use StaticSupportTrait;

	protected $id = self::CHORUS_FLOWER;

	public const MIN_AGE = 0;
	public const MAX_AGE = 5;

	private const MAX_STEM_HEIGHT = 5;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Chorus Flower";
	}

	public function getHardness() : float
	{
		return 0.4;
	}

	public function getBlastResistance() : float
	{
		return 0.4;
	}

	protected function canBeSupportedAt(Block $block) : bool{
		$level = $block->getLevel();
		$down = $level->getBlock($block->down());

		if($down->getId() === BlockIds::END_STONE || $down->getId() === BlockIds::CHORUS_PLANT){
			return true;
		}

		$plantAdjacent = false;
		foreach($block->sidesAroundAxis(Axis::Y) as $sidePosition){
			$block = $level->getBlock($sidePosition);

			if($block->getId() === BlockIds::CHORUS_PLANT){
				if($plantAdjacent){ //at most one plant may be horizontally adjacent
					return false;
				}
				$plantAdjacent = true;
			}elseif($block->getId() !== BlockIds::AIR){
				return false;
			}
		}

		return $plantAdjacent;
	}

	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void
	{
		$this->level->useBreakOn($this);
	}

	/**
	 * @phpstan-return array{int, bool}
	 */
	private function scanStem() : array{
		$level = $this->level;

		$stemHeight = 0;
		$endStoneBelow = false;
		for($yOffset = 0; $yOffset < self::MAX_STEM_HEIGHT; $yOffset++, $stemHeight++){
			$down = $level->getBlock($this->down($yOffset + 1));

			if($down->getId() !== BlockIds::CHORUS_PLANT){
				if($down->getId() === BlockIds::END_STONE){
					$endStoneBelow = true;
				}
				break;
			}
		}

		return [$stemHeight, $endStoneBelow];
	}

	private function canGrowUpwards(int $stemHeight, bool $endStoneBelow) : bool{
		$level = $this->level;

		$up = $this->up();
		if(
			//the space above must be empty and writable
			!$level->isInWorld($up->x, $up->y, $up->z) ||
			$level->getBlock($up)->getId() !== BlockIds::AIR ||
			(
				//the space above that must be empty, but doesn't need to be writable
				$level->isInWorld($up->x, $up->y + 1, $up->z) &&
				$level->getBlock($up->up())->getId() !== BlockIds::AIR
			)
		){
			return false;
		}

		if($this->getSide(Facing::DOWN)->getId() !== BlockIds::AIR){
			if($stemHeight >= self::MAX_STEM_HEIGHT){
				return false;
			}

			if($stemHeight > 1 && $stemHeight > mt_rand(0, $endStoneBelow ? 4 : 3)){ //chance decreases for each added block of chorus plant
				return false;
			}
		}

		return self::allHorizontalBlocksEmpty($level, $up, null);
	}

	private function grow(int $facing, int $ageChange) : Block{
		$position = $this->getSide($facing)->floor();
		$block = (clone $this)->setDamage(min(self::MAX_AGE, $this->meta + $ageChange));
		$block->position(Position::fromObject($position, $this->level));
		return $block;
	}

	public function ticksRandomly() : bool{ return $this->meta < self::MAX_AGE; }

	public function onRandomTick() : void{
		$level = $this->level;

		if($this->meta >= self::MAX_AGE){
			return;
		}

		$flowers = [];
		[$stemHeight, $endStoneBelow] = $this->scanStem();
		if($this->canGrowUpwards($stemHeight, $endStoneBelow)){
			$flowers[] = $this->grow(Facing::UP, 0);
		}else{
			$facingVisited = [];
			for($attempts = 0, $maxAttempts = mt_rand(0, $endStoneBelow ? 4 : 3); $attempts < $maxAttempts; $attempts++){
				$facing = Facing::HORIZONTAL[array_rand(Facing::HORIZONTAL)];
				if(isset($facingVisited[$facing])){
					continue;
				}
				$facingVisited[$facing] = true;

				$sidePosition = $this->getSide($facing);
				if(
					$level->getBlock($sidePosition)->getId() === BlockIds::AIR &&
					$level->getBlock($sidePosition->down())->getId() === BlockIds::AIR &&
					self::allHorizontalBlocksEmpty($level, $sidePosition, Facing::opposite($facing))
				){
					$flowers[] = $this->grow($facing, 1);
				}
			}
		}

		if(count($flowers) !== 0){
			$ev = new StructureGrowEvent($this, [BlockFactory::get(BlockIds::CHORUS_PLANT, 0, $this)] + $flowers, null);
			$ev->call();
			if(!$ev->isCancelled()){
				foreach ($ev->getNewState() as $newState){
					$this->level->setBlockAt($newState->x, $newState->y, $newState->z, $newState);
				}

				$level->addSound(new ChorusFlowerGrowSound($this->add(0.5, 0.5, 0.5)));
			}
		}else{
			$level->addSound(new ChorusFlowerDieSound($this->add(0.5, 0.5, 0.5)));
			$this->level->setBlock($this, $this->setDamage(self::MAX_AGE));
		}
	}

	public static function generatePlant(ChunkManager $level, Vector3 $origin, Random $random, int $maxHorizontalSpread) : void {
		$level->setBlockAt($origin->getX(), $origin->getY(), $origin->getZ(), BlockFactory::get(BlockIds::CHORUS_PLANT));
		self::growTreeRecursive($level, $origin, $random, $origin, $maxHorizontalSpread, 0);
	}

	public static function growTreeRecursive(ChunkManager $level, Vector3 $current, Random $random, Vector3 $startPos, int $maxHorizontalSpread, int $depth) : void{
		$current = $current->floor();
		$startPos = $startPos->floor();

		$chorus = BlockFactory::get(BlockIds::CHORUS_PLANT);
		$height = $random->nextBoundedInt(4) + 1;
		if ($depth == 0) {
			$height++;
		}

		for ($i = 0; $i < $height; $i++) {
			$target = $current->up($i + 1);
			if (!self::allHorizontalBlocksEmpty($level, $target, null)) {
				return;
			}

			$level->setBlockAt($target->getX(), $target->getY(), $target->getZ(), $chorus);
		}

		$placedStem = false;
		if ($depth < 4) {
			$stems = $random->nextBoundedInt(4);
			if ($depth == 0) {
				$stems++;
			}

			for ($i = 0; $i < $stems; $i++) {
				$direction = Facing::HORIZONTAL[$random->nextBoundedInt(4)];
				$target = $current->up($height)->getSide($direction);
				if (
					abs($target->getX() - $startPos->getX()) < $maxHorizontalSpread &&
					abs($target->getZ() - $startPos->getZ()) < $maxHorizontalSpread &&
					$level->getBlockAt($target->getX(), $target->getY(), $target->getZ())->getId() === BlockIds::AIR &&
					$level->getBlockAt($target->getX(), $target->getY() - 1, $target->getZ())->getId() === BlockIds::AIR &&
					self::allHorizontalBlocksEmpty($level, $target, Facing::opposite($direction))
				) {
					$placedStem = true;
					$level->setBlockAt($target->getX(), $target->getY(), $target->getZ(), $chorus);
					self::growTreeRecursive($level, $target, $random, $startPos, $maxHorizontalSpread, $depth + 1);
				}
			}
		}

		if (!$placedStem) {
			$level->setBlockAt(($flower = $current->up($height))->getX(), $flower->getY(), $flower->getZ(), BlockFactory::get(BlockIds::CHORUS_FLOWER)->setDamage(5));
		}
	}

	public static function allHorizontalBlocksEmpty(ChunkManager $level, Vector3 $position, ?int $except) : bool{
		foreach($position->sidesAroundAxis(Axis::Y) as $facing => $sidePosition){
			if($facing === $except){
				continue;
			}
			if($level->getBlockAt($sidePosition->getX(), $sidePosition->getY(), $sidePosition->getZ())->getId() !== BlockIds::AIR){
				return false;
			}
		}

		return true;
	}
}
