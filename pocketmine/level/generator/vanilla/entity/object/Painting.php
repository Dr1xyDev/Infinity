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

namespace pocketmine\level\generator\vanilla\entity\object;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByEntityEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\level\particle\DestroyBlockParticle;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\ByteTag;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AddPaintingPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

use function ceil;

class Painting extends Entity
{
	public const NETWORK_ID = self::PAINTING;

	/** @var float */
	protected $gravity = 0.0;
	/** @var float */
	protected $drag = 1.0;

	//these aren't accurate, but it doesn't matter since they aren't used (vanilla PC does something similar)
	public float $height = 0.5;
	public float $width = 0.5;

	/** @var Vector3 */
	protected $blockIn;
	/** @var int */
	protected $direction = 0;
	/** @var string */
	protected $motive;

	public function __construct(ChunkManager $level, CompoundTag $nbt)
	{
		$this->motive = $nbt->getString("Motive");
		$this->blockIn = new Vector3($nbt->getInt("TileX"), $nbt->getInt("TileY"), $nbt->getInt("TileZ"));
		if ($nbt->hasTag("Direction", ByteTag::class)) {
			$this->direction = $nbt->getByte("Direction");
		} elseif ($nbt->hasTag("Facing", ByteTag::class)) {
			$this->direction = $nbt->getByte("Facing");
		}
		parent::__construct($level, $nbt);
	}

	protected function initEntity() : void
	{
		$this->setMaxHealth(1);
		$this->setHealth(1);
		parent::initEntity();
	}

	public function saveNBT() : void
	{
		parent::saveNBT();
		$this->namedtag->setInt("TileX", (int) $this->blockIn->x);
		$this->namedtag->setInt("TileY", (int) $this->blockIn->y);
		$this->namedtag->setInt("TileZ", (int) $this->blockIn->z);

		$this->namedtag->setByte("Facing", (int) $this->direction);
		$this->namedtag->setByte("Direction", (int) $this->direction); //Save both for full compatibility

		$this->namedtag->setString("Motive", $this->motive);
	}

	public function kill() : void
	{
		if (!$this->isAlive()) {
			return;
		}
		parent::kill();

		$drops = true;

		if ($this->lastDamageCause instanceof EntityDamageByEntityEvent) {
			$killer = $this->lastDamageCause->getDamager();
			if ($killer instanceof Player && $killer->isCreative()) {
				$drops = false;
			}
		}

		if ($drops) {
			//non-living entities don't have a way to create drops generically yet
			$this->level->dropItem($this, ItemFactory::get(Item::PAINTING));
		}
		$this->level->addParticle(new DestroyBlockParticle($this->add(0.5, 0.5, 0.5), BlockFactory::get(Block::PLANKS)));
	}

	protected function recalculateBoundingBox() : void
	{
		static $directions = [
			0 => Facing::SOUTH,
			1 => Facing::WEST,
			2 => Facing::NORTH,
			3 => Facing::EAST
		];

		$facing = $directions[$this->direction];

		$this->boundingBox = self::getPaintingBB($this->blockIn->getSide($facing), $facing, $this->getMotive());
	}

	public function onNearbyBlockChange() : void
	{
		parent::onNearbyBlockChange();

		static $directions = [
			0 => Facing::SOUTH,
			1 => Facing::WEST,
			2 => Facing::NORTH,
			3 => Facing::EAST
		];

		$face = $directions[$this->direction];
		if (!self::canFit($this->level, $this->blockIn->getSide($face), $face, false, $this->getMotive())) {
			$this->kill();
		}
	}

	public function hasMovementUpdate() : bool
	{
		return false;
	}

	protected function updateMovement(bool $teleport = false) : void
	{

	}

	public function canBeCollidedWith() : bool
	{
		return false;
	}

	protected function sendSpawnPacket(Player $player) : void
	{
		if ($player->getProtocolVersion() < ProtocolInfo::PROTOCOL_407) {
			[$x, $y, $z] = [
				$this->blockIn->x,
				$this->blockIn->y,
				$this->blockIn->z
			];
		} else {
			[$x, $y, $z] = [
				(($boundingBox = $this->boundingBox)->minX + $boundingBox->maxX) / 2,
				($boundingBox->minY + $boundingBox->maxY) / 2,
				($boundingBox->minZ + $boundingBox->maxZ) / 2
			];
		}

		$player->sendDataPacket(AddPaintingPacket::create(
			$this->getId(),
			$this->getId(),
			(int) $x,
			(int) $y,
			(int) $z,
			$this->direction,
			$this->motive
		));
	}

	public function getPickedItem() : ?Item
	{
		return ItemFactory::get(Item::PAINTING);
	}

	/**
	 * Returns the painting motive (which image is displayed on the painting)
	 */
	public function getMotive() : PaintingMotive
	{
		return PaintingMotive::getMotiveByName($this->motive);
	}

	public function getDirection() : ?int
	{
		return $this->direction;
	}

	/**
	 * Returns the bounding-box a painting with the specified motive would have at the given position and direction.
	 */
	private static function getPaintingBB(Vector3 $blockIn, int $facing, PaintingMotive $motive) : AxisAlignedBB
	{
		$width = $motive->getWidth();
		$height = $motive->getHeight();

		$horizontalStart = (int) (ceil($width / 2) - 1);
		$verticalStart = (int) (ceil($height / 2) - 1);

		$thickness = 1 / 16;

		$minX = $maxX = 0;
		$minZ = $maxZ = 0;

		$minY = -$verticalStart;
		$maxY = $minY + $height;

		switch ($facing) {
			case Facing::NORTH:
				$minZ = 1 - $thickness;
				$maxZ = 1;
				$maxX = $horizontalStart + 1;
				$minX = $maxX - $width;
				break;
			case Facing::SOUTH:
				$minZ = 0;
				$maxZ = $thickness;
				$minX = -$horizontalStart;
				$maxX = $minX + $width;
				break;
			case Facing::WEST:
				$minX = 1 - $thickness;
				$maxX = 1;
				$minZ = -$horizontalStart;
				$maxZ = $minZ + $width;
				break;
			case Facing::EAST:
				$minX = 0;
				$maxX = $thickness;
				$maxZ = $horizontalStart + 1;
				$minZ = $maxZ - $width;
				break;
		}

		return new AxisAlignedBB(
			$blockIn->x + $minX,
			$blockIn->y + $minY,
			$blockIn->z + $minZ,
			$blockIn->x + $maxX,
			$blockIn->y + $maxY,
			$blockIn->z + $maxZ
		);
	}

	/**
	 * Returns whether a painting with the specified motive can be placed at the given position.
	 */
	public static function canFit(Level $level, Vector3 $blockIn, int $facing, bool $checkOverlap, PaintingMotive $motive) : bool
	{
		$width = $motive->getWidth();
		$height = $motive->getHeight();

		$horizontalStart = (int) (ceil($width / 2) - 1);
		$verticalStart = (int) (ceil($height / 2) - 1);

		switch ($facing) {
			case Facing::NORTH:
				$rotatedFace = Facing::WEST;
				break;
			case Facing::WEST:
				$rotatedFace = Facing::SOUTH;
				break;
			case Facing::SOUTH:
				$rotatedFace = Facing::EAST;
				break;
			case Facing::EAST:
				$rotatedFace = Facing::NORTH;
				break;
			default:
				return false;
		}

		$oppositeSide = Facing::opposite($facing);

		$startPos = $blockIn->asVector3()->getSide(Facing::opposite($rotatedFace), $horizontalStart)->getSide(Facing::DOWN, $verticalStart);

		for ($w = 0; $w < $width; ++$w) {
			for ($h = 0; $h < $height; ++$h) {
				$pos = $startPos->getSide($rotatedFace, $w)->getSide(Facing::UP, $h);

				$block = $level->getBlockAt($pos->x, $pos->y, $pos->z);
				if ($block->isSolid() || !$block->getSide($oppositeSide)->isSolid()) {
					return false;
				}
			}
		}

		if ($checkOverlap) {
			$bb = self::getPaintingBB($blockIn, $facing, $motive);

			foreach ($level->getNearbyEntities($bb) as $entity) {
				if ($entity instanceof self) {
					return false;
				}
			}
		}

		return true;
	}
}
