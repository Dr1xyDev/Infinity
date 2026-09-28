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

/**
 * All Block classes are in here
 */

namespace pocketmine\level\generator\vanilla\block;

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\object\FallingBlock;
use pocketmine\level\generator\vanilla\entity\projectile\Projectile;
use pocketmine\level\generator\vanilla\event\entity\EntityBlockBounceEvent;
use pocketmine\level\generator\vanilla\item\enchantment\Enchantment;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\RayTraceResult;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\Tile;

use function abs;
use function array_merge;
use function count;
use function get_class;

use const PHP_INT_MAX;

class Block extends Position implements BlockIds
{
	public const INTERNAL_METADATA_BITS = 8;
	public const INTERNAL_METADATA_MASK = ~(~0 << self::INTERNAL_METADATA_BITS);

	/**
	 * Returns a new Block instance with the specified ID, meta and position.
	 *
	 * This function redirects to {@link BlockFactory#get}.
	 */
	public static function get(int $id, int $meta = 0, ?Position $pos = null) : Block
	{
		return BlockFactory::get($id, $meta, $pos);
	}

	/**
	 * Converts horizontal direction to legacy block-facing metadata.
	 *
	 * Accepts either Facing::NORTH/SOUTH/WEST/EAST constants or player direction indices (0-3).
	 *
	 * @throws InvalidArgumentException
	 */
	public static function getMetaFace(int $direction) : int
	{
		$direction = match ($direction) {
			0 => Facing::SOUTH,
			1 => Facing::WEST,
			2 => Facing::NORTH,
			3 => Facing::EAST,
			default => $direction
		};

		return match ($direction) {
			Facing::SOUTH => Facing::WEST,
			Facing::WEST => Facing::NORTH,
			Facing::NORTH => Facing::EAST,
			Facing::EAST => Facing::SOUTH,
			default => throw new InvalidArgumentException("Invalid horizontal direction $direction")
		};
	}

	/** @var int */
	protected $id;
	/** @var int */
	protected $meta = 0;
	/** @var string|null */
	protected $fallbackName;
	/** @var int|null */
	protected $itemId;

	/** @var AxisAlignedBB|null */
	protected $boundingBox = null;

	/** @var AxisAlignedBB[]|null */
	protected $collisionBoxes = null;

	public function __construct(int $id, int $meta = 0, ?string $name = null, ?int $itemId = null)
	{
		parent::__construct();

		$this->id = $id;
		$this->meta = $meta;
		$this->fallbackName = $name;
		$this->itemId = $itemId;
	}

	public function getName() : string
	{
		return $this->fallbackName ?? "Unknown";
	}

	final public function getId() : int
	{
		return $this->id;
	}

	/**
	 * @internal
	 *
	 * Returns the full blockstate ID of this block. This is a compact way of representing a blockstate used to store
	 * blocks in chunks at runtime.
	 *
	 * This ID can be used to later obtain a copy of this block using {@link BlockFactory::get()}.
	 */
	public function getFullId() : int
	{
		return ($this->getId() << self::INTERNAL_METADATA_BITS) | $this->getDamage();
	}

	/**
	 * Returns the block as an item.
	 * State information such as facing, powered/unpowered, open/closed, etc., is discarded.
	 * Type information such as colour, wood type, etc. is preserved.
	 */
	public function asItem() : Item
	{
		return ItemFactory::get($this->getItemId(), $this->getVariant());
	}

	/**
	 * Returns the ID of the item form of the block.
	 * Used for drops for blocks (some blocks such as doors have a different item ID).
	 */
	public function getItemId() : int
	{
		return $this->itemId ?? ($this->getId() > 255 ? 255 - $this->getId() : $this->getId());
	}

	final public function getDamage() : int
	{
		return $this->meta;
	}

	final public function setDamage(int $meta) : self
	{
		if($meta < 0 || $meta >= (1 << Block::INTERNAL_METADATA_BITS)){
			throw new InvalidArgumentException("Block meta value $meta is out of bounds");
		}

		$this->meta = $meta;

		return $this;
	}

	/**
	 * Bitmask to use to remove superfluous information from block meta when getting its item form or name.
	 * This defaults to -1 (don't remove any data). Used to remove rotation data and bitflags from block drops.
	 *
	 * If your block should not have any meta value when it's dropped as an item, override this to return 0 in
	 * descendent classes.
	 */
	public function getVariantBitmask() : int
	{
		return -1;
	}

	/**
	 * Returns the block meta, stripped of non-variant flags.
	 */
	public function getVariant() : int
	{
		return $this->meta & $this->getVariantBitmask();
	}

	/**
	 * Returns a type ID that identifies this type of block. This does not include information like facing, open/closed,
	 * powered/unpowered, etc.
	 */
	public function getTypeId() : int
	{
		return ($this->getId() << Block::INTERNAL_METADATA_BITS) | $this->getVariant();
	}

	/**
	 * Returns whether the given block has an equivalent type to this one. This compares the type IDs.
	 *
	 * Note: This ignores additional IDs used to represent additional states. This means that, for example, a lit
	 * furnace and unlit furnace are considered the same type.
	 */
	public function isSameType(Block $other) : bool
	{
		return $this->getTypeId() === $other->getTypeId();
	}

	/**
	 * Returns whether the given block has the same type and properties as this block.
	 */
	public function isSameState(Block $other) : bool
	{
		return $this->getFullId() === $other->getFullId();
	}

	/**
	 * AKA: Block->isPlaceable
	 */
	public function canBePlaced() : bool
	{
		return true;
	}

	public function canBeReplaced() : bool
	{
		return false;
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool
	{
		return $blockReplace->canBeReplaced();
	}

	/**
	 * Places the Block, using block space and block target, and side. Returns if the block has been placed.
	 */
	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$this->getLevel()->setBlock($this, $this, true, true);
		return true;
	}

	/**
	 * Returns if the block can be broken with an specific Item
	 */
	public function isBreakable(Item $item) : bool
	{
		return true;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_NONE;
	}

	/**
	 * Returns the level of tool required to harvest this block (for normal blocks). When the tool type matches the
	 * block's required tool type, the tool must have a harvest level greater than or equal to this value to be able to
	 * successfully harvest the block.
	 *
	 * If the block requires a specific minimum tier of tiered tool, the minimum tier required should be returned.
	 * Otherwise, 1 should be returned if a tool is required, 0 if not.
	 *
	 * @see Item::getBlockToolHarvestLevel()
	 */
	public function getToolHarvestLevel() : int
	{
		return 0;
	}

	/**
	 * Returns whether the specified item is the proper tool to use for breaking this block. This checks tool type and
	 * harvest level requirement.
	 *
	 * In most cases this is also used to determine whether block drops should be created or not, except in some
	 * special cases such as vines.
	 */
	public function isCompatibleWithTool(Item $tool) : bool
	{
		if ($this->getHardness() < 0) {
			return false;
		}

		$toolType = $this->getToolType();
		$harvestLevel = $this->getToolHarvestLevel();

		return $toolType === BlockToolType::TYPE_NONE || $harvestLevel === 0 || (
			($toolType & $tool->getBlockToolType()) !== 0 && $tool->getBlockToolHarvestLevel() >= $harvestLevel
		);
	}

	/**
	 * Do the actions needed so the block is broken with the Item
	 */
	public function onBreak(Item $item, ?Player $player = null) : bool
	{
		$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true, true);
		return true;
	}

	/**
	 * Returns the seconds that this block takes to be broken using an specific Item
	 *
	 * @throws InvalidArgumentException if the item efficiency is not a positive number
	 */
	public function getBreakTime(Item $item) : float
	{
		$base = $this->getHardness();
		if ($this->isCompatibleWithTool($item)) {
			$base *= 1.5;
		} else {
			$base *= 5;
		}

		$efficiency = $item->getMiningEfficiency($this);
		if ($efficiency <= 0) {
			throw new InvalidArgumentException(get_class($item) . " has invalid mining efficiency: expected >= 0, got $efficiency");
		}

		$base /= $efficiency;

		return $base;
	}

	/**
	 * Called when FallingBlock hits the ground.
	 * Returns whether the block should be placed.
	 */
	public function onHitGround(FallingBlock $blockEntity, float $fallDistance) : bool
	{
		return false;
	}

	/**
	 * Called when this block or a block immediately adjacent to it changes state.
	 */
	public function onNearbyBlockChange() : void
	{

	}

	/**
	 * Returns whether random block updates will be done on this block.
	 */
	public function ticksRandomly() : bool
	{
		return false;
	}

	/**
	 * Called when this block is randomly updated due to chunk ticking.
	 * WARNING: This will not be called if ticksRandomly() does not return true!
	 */
	public function onRandomTick() : void
	{

	}

	/**
	 * Called when this block is updated by the delayed blockupdate scheduler in the level.
	 */
	public function onScheduledUpdate() : void
	{

	}

	/**
	 * Do actions when activated by Item. Returns if it has done anything
	 */
	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		return false;
	}

	/**
	 * Called when this block is attacked (left-clicked) by a player attempting to start breaking it in survival.
	 *
	 * @return bool if an action took place, prevents starting to break the block if true.
	 */
	public function onAttack(Item $item, int $face, ?Player $player = null) : bool
	{
		return false;
	}

	/**
	 * Returns a base value used to compute block break times.
	 */
	public function getHardness() : float
	{
		return 10;
	}

	/**
	 * Returns the block's resistance to explosions. Usually 5x hardness.
	 */
	public function getBlastResistance() : float
	{
		return $this->getHardness() * 5;
	}

	public function getFrictionFactor() : float
	{
		return 0.6;
	}

	/**
	 * @return int 0-15
	 */
	public function getLightLevel() : int
	{
		return 0;
	}

	/**
	 * Returns the amount of light this block will filter out when light passes through this block.
	 * This value is used in light spread calculation.
	 *
	 * @return int 0-15
	 */
	public function getLightFilter() : int
	{
		return 15;
	}

	/**
	 * Returns whether this block will diffuse sky light passing through it vertically.
	 * Diffusion means that full-strength sky light passing through this block will not be reduced, but will start being filtered below the block.
	 * Examples of this behaviour include leaves and cobwebs.
	 *
	 * Light-diffusing blocks are included by the heightmap.
	 */
	public function diffusesSkyLight() : bool
	{
		return false;
	}

	public function isTransparent() : bool
	{
		return false;
	}

	public function isSolid() : bool
	{
		return true;
	}

	/**
	 * AKA: Block->isFlowable
	 */
	public function canBeFlowedInto() : bool
	{
		return false;
	}

	public function hasEntityCollision() : bool
	{
		return false;
	}

	public function canPassThrough() : bool
	{
		return false;
	}

	/**
	 * Returns whether entities can climb up this block.
	 */
	public function canClimb() : bool
	{
		return false;
	}

	/**
	 * Returns whether entity can pass this block
	 */
	public function isPassable() : bool
	{
		return !$this->isSolid();
	}

	public function addVelocityToEntity(Entity $entity, Vector3 $vector) : void
	{

	}

	/**
	 * Sets the block position to a new Position object
	 */
	final public function position(Position $v) : void
	{
		$this->x = (int) $v->x;
		$this->y = (int) $v->y;
		$this->z = (int) $v->z;
		$this->level = $v->level;
		$this->boundingBox = null;
	}

	/**
	 * Returns an array of Item objects to be dropped
	 *
	 * @return Item[]
	 */
	public function getDrops(Item $item) : array
	{
		if ($this->isCompatibleWithTool($item)) {
			if ($this->isAffectedBySilkTouch() && $item->hasEnchantment(Enchantment::SILK_TOUCH)) {
				return $this->getSilkTouchDrops($item);
			}
			return $this->getDropsForCompatibleTool($item);
		}

		return $this->getDropsForIncompatibleTool($item);
	}

	/**
	 * Returns an array of Items to be dropped when the block is broken using the correct tool type.
	 *
	 * @return Item[]
	 */
	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [$this->asItem()];
	}

	/**
	 * Returns the items dropped by this block when broken with an incorrect tool type (or tool with a too-low tier).
	 *
	 * @return Item[]
	 */
	public function getDropsForIncompatibleTool(Item $item) : array
	{
		return [];
	}

	/**
	 * Returns an array of Items to be dropped when the block is broken using a compatible Silk Touch-enchanted tool.
	 *
	 * @return Item[]
	 */
	public function getSilkTouchDrops(Item $item) : array
	{
		return [$this->asItem()];
	}

	/**
	 * Returns how much XP will be dropped by breaking this block with the given item.
	 */
	public function getXpDropForTool(Item $item) : int
	{
		if ($item->hasEnchantment(Enchantment::SILK_TOUCH) || !$this->isCompatibleWithTool($item)) {
			return 0;
		}

		return $this->getXpDropAmount();
	}

	/**
	 * Returns how much XP this block will drop when broken with an appropriate tool.
	 */
	protected function getXpDropAmount() : int
	{
		return 0;
	}

	/**
	 * Returns whether Silk Touch enchanted tools will cause this block to drop as itself. Since most blocks drop
	 * themselves anyway, this is implicitly true.
	 */
	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}

	/**
	 * Returns the item that players will equip when middle-clicking on this block.
	 */
	public function getPickedItem(bool $addUserData = false) : Item
	{
		$item = $this->asItem();
		if ($addUserData) {
			$tile = $this->level->getTile($this);
			if ($tile instanceof Tile) {
				$nbt = $tile->getCleanedNBT();
				if ($nbt instanceof CompoundTag) {
					$item->setCustomBlockData($nbt);
					$item->setLore(["+(DATA)"]);
				}
			}
		}
		return $item;
	}

	/**
	 * Returns the time in ticks which the block will fuel a furnace for.
	 */
	public function getFuelTime() : int
	{
		return 0;
	}

	/**
	 * Returns the chance that the block will catch fire from nearby fire sources. Higher values lead to faster catching
	 * fire.
	 */
	public function getFlameEncouragement() : int
	{
		return 0;
	}

	/**
	 * Returns the base flammability of this block. Higher values lead to the block burning away more quickly.
	 */
	public function getFlammability() : int
	{
		return 0;
	}

	/**
	 * Returns whether fire lit on this block will burn indefinitely.
	 */
	public function burnsForever() : bool
	{
		return false;
	}

	/**
	 * Returns whether this block can catch fire.
	 */
	public function isFlammable() : bool
	{
		return $this->getFlammability() > 0;
	}

	/**
	 * Called when this block is burned away by being on fire.
	 */
	public function onIncinerate() : void
	{

	}

	/**
	 * Returns the Block on the side $side, works like Vector3::getSide()
	 *
	 * @return Block
	 */
	public function getSide(int $side, int $step = 1)
	{
		if ($this->isValid()) {
			[$dx, $dy, $dz] = Facing::OFFSET[$side] ?? [0, 0, 0];
			return $this->getLevel()->getBlockAt(
				(int) ($this->x + $dx * $step),
				(int) ($this->y + $dy * $step),
				(int) ($this->z + $dz * $step)
			);
		}

		throw new \LogicException("Block does not have a valid world");
	}
	/**
	 * Returns the 4 blocks on the horizontal axes around the block (north, south, east, west)
	 *
	 * @return Block[]
	 */
	public function getHorizontalSides() : array
	{
		return [
			$this->getSide(Facing::NORTH),
			$this->getSide(Facing::SOUTH),
			$this->getSide(Facing::WEST),
			$this->getSide(Facing::EAST)
		];
	}

	/**
	 * Returns the six blocks around this block.
	 *
	 * @return Block[]
	 */
	public function getAllSides() : array
	{
		return array_merge(
			[
				$this->getSide(Facing::DOWN),
				$this->getSide(Facing::UP)
			],
			$this->getHorizontalSides()
		);
	}

	/**
	 * Returns a list of blocks that this block is part of. In most cases, only contains the block itself, but in cases
	 * such as double plants, beds and doors, will contain both halves.
	 *
	 * @return Block[]
	 */
	public function getAffectedBlocks() : array
	{
		return [$this];
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return "Block[" . $this->getName() . "] (" . $this->getId() . ":" . $this->getDamage() . ")";
	}

	/**
	 * Checks for collision against an AxisAlignedBB
	 */
	public function collidesWithBB(AxisAlignedBB $bb) : bool
	{
		foreach ($this->getCollisionBoxes() as $bb2) {
			if ($bb->intersectsWith($bb2)) {
				return true;
			}
		}

		return false;
	}

	public function onEntityCollide(Entity $entity) : void
	{

	}

	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void
	{

	}

	public function onEntityFallenUpon(Entity $entity, float $fallDistance) : void
	{
		$ev = new EntityBlockBounceEvent($entity, $this, $this->getBounceMotionMultiplier(), $this->getBounceFallDistanceMultiplier());
		if ($entity->isSneaking()) {
			$ev->setCancelled();
		}

		$ev->call();

		if ($ev->isCancelled()) {
			return;
		}

		//TODO
	}

	public function onEntityCollideUpon(Entity $entity) : void
	{

	}

	public function getBounceMotionMultiplier() : float
	{
		return 0.0;
	}

	public function getBounceFallDistanceMultiplier() : float
	{
		return 1.0;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	public function getCollisionBoxes() : array
	{
		if ($this->collisionBoxes === null) {
			$this->collisionBoxes = $this->recalculateCollisionBoxes();
		}

		return $this->collisionBoxes;
	}
	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		if (($bb = $this->recalculateBoundingBox()) !== null) {
			return [$bb];
		}

		return [];
	}

	public function getBoundingBox() : ?AxisAlignedBB
	{
		if ($this->boundingBox === null) {
			$this->boundingBox = $this->recalculateBoundingBox();
		}
		return $this->boundingBox;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{
		return new AxisAlignedBB(
			$this->x,
			$this->y,
			$this->z,
			$this->x + 1,
			$this->y + 1,
			$this->z + 1
		);
	}

	/**
	 * Clears any cached precomputed objects, such as bounding boxes. This is called on block neighbour update and when
	 * the block is set into the world to remove any outdated precomputed things such as AABBs and force recalculation.
	 */
	public function clearCaches() : void
	{
		$this->boundingBox = null;
		$this->collisionBoxes = null;
	}

	public function calculateIntercept(Vector3 $pos1, Vector3 $pos2) : ?RayTraceResult
	{
		$bbs = $this->getCollisionBoxes();
		if (count($bbs) === 0) {
			return null;
		}

		/** @var RayTraceResult|null $currentHit */
		$currentHit = null;
		/** @var int|float $currentDistance */
		$currentDistance = PHP_INT_MAX;

		foreach ($bbs as $bb) {
			$nextHit = $bb->calculateIntercept($pos1, $pos2);
			if ($nextHit === null) {
				continue;
			}

			$nextDistance = $nextHit->hitVector->distanceSquared($pos1);
			if ($nextDistance < $currentDistance) {
				$currentHit = $nextHit;
				$currentDistance = $nextDistance;
			}
		}

		return $currentHit;
	}

	public function isFullCube() : bool
	{
		$bb = $this->getCollisionBoxes();

		return count($bb) === 1 && $bb[0]->getAverageEdgeLength() >= 1 && $this->isCube($bb[0]);
	}

	private function isCube(AxisAlignedBB $bb, float $epsilon = 0.000001) : bool
	{
		[$xLen, $yLen, $zLen] = [$bb->maxX - $bb->minX, $bb->maxY - $bb->minY, $bb->maxZ - $bb->minZ];
		return abs($xLen - $yLen) < $epsilon && abs($yLen - $zLen) < $epsilon;
	}

	public function canSupportToFullSolid(Block $block) : bool
	{
		if ($block->isTransparent()) {
			return match ($block->getId()) {
				BlockIds::BEACON, BlockIds::ICE, BlockIds::GLASS, BlockIds::STAINED_GLASS, BlockIds::HARD_GLASS, BlockIds::HARD_STAINED_GLASS, BlockIds::BARRIER, BlockIds::GLOWSTONE, BlockIds::SEA_LANTERN, BlockIds::MANGROVE_ROOTS, BlockIds::MUDDY_MANGROVE_ROOTS, BlockIds::MONSTER_SPAWNER => true,
				default => false,
			};
		}
		return true;
	}

	protected function canStayOnFullSolid(Block $block) : bool
	{
		if ($this->canSupportToFullSolid($block)) {
			return true;
		}
		switch ($block->getId()) {
			case BlockIds::SCAFFOLDING:
			case BlockIds::HOPPER_BLOCK:
				return true;
		}
		if ($block instanceof Slab) {
			return $block->isTop();
		}
		return $block instanceof Trapdoor && $block->isTop() && !$block->isOpen();
	}

	protected function isNarrowSurface() : bool
	{
		return $this instanceof GlassPane ||
			$this instanceof Fence ||
			$this instanceof IronBars ||
			$this instanceof Wall; //TODO: Chain
	}
}
