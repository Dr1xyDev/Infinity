<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\generator\normal\object;

use pocketmine\block\Block;
use pocketmine\block\RedMushroomBlock;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class HugeRedMushroom{

	/**
	 * Block IDs used for mushroom generation.
	 */
	const CAP_BLOCK = Block::RED_MUSHROOM_BLOCK; // 100

	/**
	 * Metadata constants for mushroom block faces.
	 * MCPE (Bedrock) metadata values for huge mushroom blocks:
	 *   14 = cap texture on all 6 faces
	 *   10 = stem texture on all 6 faces
	 *   1  = cap on top+bottom, pores on 4 horizontal sides
	 *   0  = pores on all 6 faces
	 *
	 * Edge metadata values encode which horizontal faces show cap texture:
	 *   3  = cap on top+bottom+north (pores on south, east, west)
	 *   4  = cap on top+bottom+south (pores on north, east, west)
	 *   5  = cap on top+bottom+north+south (pores on east, west)
	 *   6  = cap on top+bottom+west (pores on north, south, east)
	 *   7  = cap on top+bottom+west+north (pores on south, east)
	 *   8  = cap on top+bottom+west+south (pores on north, east)
	 *   9  = cap on top+bottom+west+north+south (pores on east)
	 *   (east-only and combinations use approximate fallback)
	 */
	const META_STEM        = RedMushroomBlock::STEM; // 10
	const META_CAP_FULL    = RedMushroomBlock::RED;  // 14
	const META_CAP_CENTER  = 1;  // cap on top+bottom, pores on sides
	const META_PORES       = 0;  // all faces pores

	/**
	 * Generates a huge red mushroom at the given position.
	 *
	 * @param ChunkManager $level     The world/level
	 * @param Random        $rand     Random number generator
	 * @param Vector3       $position Base position (where the small mushroom was)
	 * @return bool Whether the mushroom was successfully generated
	 */
	public function generate(ChunkManager $level, Random $rand, Vector3 $position): bool{
		// Height: 4-6 (normal), with 1/12 chance doubled to 8-12
		$height = $rand->nextBoundedInt(3) + 4;
		if($rand->nextBoundedInt(12) === 0){
			$height *= 2;
		}

		$baseX = $position->getFloorX();
		$baseY = $position->getFloorY();
		$baseZ = $position->getFloorZ();

		// Y bounds check: must fit within world height
		if($baseY < 1 || $baseY + $height + 1 > 127){
			return false;
		}

		// Ground check: block below must be dirt, grass, or mycelium
		$groundBlock = $level->getBlockIdAt($baseX, $baseY - 1, $baseZ);
		if($groundBlock !== Block::DIRT && $groundBlock !== Block::GRASS && $groundBlock !== Block::MYCELIUM){
			return false;
		}

		// Space check: verify all required positions are clear (air, leaves, or snow)
		for($y = 0; $y < $height; ++$y){
			$checkRadius = 0;
			if($y < $height - 3){
				// Stem area: check wider footprint for cap overlap
				$checkRadius = 2;
			}elseif($y === $height){
				// Top of cap: check 3x3 area
				$checkRadius = 1;
			}

			for($dx = -$checkRadius; $dx <= $checkRadius; ++$dx){
				for($dz = -$checkRadius; $dz <= $checkRadius; ++$dz){
					$blockId = $level->getBlockIdAt($baseX + $dx, $baseY + $y, $baseZ + $dz);
					if($blockId !== Block::AIR && $blockId !== Block::LEAVES && $blockId !== Block::LEAVES2
						&& $blockId !== Block::SNOW_LAYER && $blockId !== Block::TALL_GRASS
						&& $blockId !== Block::SAPLING && $blockId !== Block::VINE){
						return false;
					}
				}
			}
		}

		// Build the stem: 1x1 column of mushroom stem blocks
		for($y = 0; $y < $height; ++$y){
			$existingBlock = $level->getBlockIdAt($baseX, $baseY + $y, $baseZ);
			// Only replace non-solid blocks (air, leaves, etc.)
			if(!Block::$solid[$existingBlock]){
				$level->setBlockIdAt($baseX, $baseY + $y, $baseZ, self::CAP_BLOCK);
				$level->setBlockDataAt($baseX, $baseY + $y, $baseZ, self::META_STEM);
			}
		}

		// Build the cap
		// Cap radius: 1 for normal height (4-6), 2 for doubled height (8-12)
		$capRadius = ($height > 7) ? 2 : 1;

		// Cap layers: from (height - 3) to (height), 4 layers total
		for($layer = $height - 3; $layer <= $height; ++$layer){
			// Top layer uses smaller radius; other layers use full cap radius
			$layerRadius = $capRadius;
			if($layer === $height){
				// Top layer: narrower cap (one less than full radius)
				$layerRadius = max(0, $capRadius - 1);
			}

			// For capRadius=1: top layer radius=0, other layers radius=1
			// For capRadius=2: top layer radius=1, other layers radius=2

			for($dx = -$layerRadius; $dx <= $layerRadius; ++$dx){
				for($dz = -$layerRadius; $dz <= $layerRadius; ++$dz){
					// Determine if this position should get a cap block
					$atEdgeX = abs($dx) === $layerRadius && $layerRadius > 0;
					$atEdgeZ = abs($dz) === $layerRadius && $layerRadius > 0;
					$isCenter = $dx === 0 && $dz === 0;

					// Placement condition (vanilla logic):
					// - Top layer (layer == height): all positions get cap blocks
					// - Other layers: only corners (atEdgeX && atEdgeZ) and center
					// This creates the flat disc shape with open edges
					$shouldPlace = ($layer === $height) || ($atEdgeX && $atEdgeZ) || $isCenter;

					if(!$shouldPlace){
						continue;
					}

					$bx = $baseX + $dx;
					$by = $baseY + $layer;
					$bz = $baseZ + $dz;

					// Don't replace solid blocks (stone, etc.)
					$existingBlock = $level->getBlockIdAt($bx, $by, $bz);
					if(Block::$solid[$existingBlock]){
						continue;
					}

					// Compute metadata based on position relative to center
					// In Java edition, HugeMushroomBlock.WEST/EAST/NORTH/SOUTH states
					// are set per-block. WEST=true when dx<0 (west face shows cap texture
					// because it faces outward), EAST=true when dx>0, etc.
					// For center (dx=0, dz=0): all horizontal faces are pores.
					// UP is always cap texture (default true in Java). DOWN is always cap
					// texture (default true in Java).
					$meta = $this->computeCapMeta($dx < 0, $dx > 0, $dz < 0, $dz > 0);

					$level->setBlockIdAt($bx, $by, $bz, self::CAP_BLOCK);
					$level->setBlockDataAt($bx, $by, $bz, $meta);
				}
			}
		}

		// Replace the stem top block with cap center block
		// (The stem goes through the center of the cap; at the top of the mushroom
		// the center position should be a cap block, not stem)
		$topY = $baseY + $height;
		$level->setBlockDataAt($baseX, $topY, $baseZ, self::META_CAP_CENTER);

		return true;
	}

	/**
	 * Computes the metadata value for a red mushroom cap block based on
	 * which horizontal faces should show cap texture vs pores.
	 *
	 * The Java edition uses HugeMushroomBlock.WEST/EAST/NORTH/SOUTH boolean
	 * block states. MCPE encodes these as metadata values 0-15.
	 *
	 * Mapping (all cap blocks have UP=true and DOWN=true in Java, meaning
	 * cap texture on top and bottom faces):
	 *   - Center (no horizontal faces): meta 1 (cap on top/bottom, pores on sides)
	 *   - Full (all horizontal faces): meta 14 (cap on all 6 sides)
	 *   - Partial: specific metadata values per face combination
	 *
	 * @param bool $west  Whether the west face should show cap texture
	 * @param bool $east  Whether the east face should show cap texture
	 * @param bool $north Whether the north face should show cap texture
	 * @param bool $south Whether the south face should show cap texture
	 * @return int Metadata value for Block::RED_MUSHROOM_BLOCK
	 */
	private function computeCapMeta(bool $west, bool $east, bool $north, bool $south): int{
		// Center column: no horizontal faces are cap (all pores on sides)
		// UP=true, DOWN=true → cap on top and bottom
		if(!$west && !$east && !$north && !$south){
			return self::META_CAP_CENTER; // 1
		}

		// All 4 horizontal faces are cap → full cap block
		if($west && $east && $north && $south){
			return self::META_CAP_FULL; // 14
		}

		// Specific face combination mappings for MCPE metadata:
		// These encode which HORIZONTAL faces show cap texture (outward-facing),
		// while UP and DOWN always show cap texture for cap blocks.
		//
		// MCPE metadata values for RED_MUSHROOM_BLOCK (partial face combinations):
		//   3  = cap on north side (plus top/bottom) → pores on east, west, south
		//   4  = cap on south side (plus top/bottom) → pores on east, west, north
		//   5  = cap on north+south sides → pores on east, west
		//   6  = cap on west side (plus top/bottom) → pores on east, north, south
		//   7  = cap on west+north sides → pores on east, south
		//   8  = cap on west+south sides → pores on east, north
		//   9  = cap on west+north+south sides → pores on east
		//   (east-only combinations use fallback to 14)

		// Single face:
		if($west && !$east && !$north && !$south){
			return 6; // west only
		}
		if(!$west && $east && !$north && !$south){
			// East-only: no exact MCPE metadata, approximate with full cap
			// (the east face shows cap, and the visible outward faces are cap)
			return self::META_CAP_FULL; // 14 fallback
		}
		if(!$west && !$east && $north && !$south){
			return 3; // north only
		}
		if(!$west && !$east && !$north && $south){
			return 4; // south only
		}

		// Two faces:
		if(!$west && !$east && $north && $south){
			return 5; // north+south
		}
		if($west && !$east && $north && !$south){
			return 7; // west+north
		}
		if($west && !$east && !$north && $south){
			return 8; // west+south
		}
		if(!$west && $east && $north && !$south){
			// east+north: no exact MCPE metadata, fallback
			return self::META_CAP_FULL;
		}
		if(!$west && $east && !$north && $south){
			// east+south: no exact MCPE metadata, fallback
			return self::META_CAP_FULL;
		}
		if($west && $east && !$north && !$south){
			// west+east: no exact MCPE metadata, fallback
			return self::META_CAP_FULL;
		}

		// Three faces:
		if($west && !$east && $north && $south){
			return 9; // west+north+south (pores on east)
		}
		if(!$west && $east && $north && $south){
			// east+north+south: no exact MCPE metadata, fallback
			return self::META_CAP_FULL;
		}
		if($west && $east && $north && !$south){
			// west+east+north: fallback
			return self::META_CAP_FULL;
		}
		if($west && $east && !$north && $south){
			// west+east+south: fallback
			return self::META_CAP_FULL;
		}

		// Fallback for any unmapped combination: full cap
		return self::META_CAP_FULL;
	}
}
