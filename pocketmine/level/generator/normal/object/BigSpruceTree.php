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

use pocketmine\level\generator\normal\populator\helper\PopulatorHelpers;
use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\object\SpruceTree;
use pocketmine\utils\Random;

class BigSpruceTree extends SpruceTree
{
                /** @var int */
                private $leafStartHeightMultiplier;
                private $baseLeafRadius;

                public function __construct($leafStartHeightMultiplier, $baseLeafRadius)
                {
                                parent::__construct();

                                $this->leafStartHeightMultiplier = $leafStartHeightMultiplier;
                                $this->baseLeafRadius = $baseLeafRadius;
                }

                public function placeObject(ChunkManager $level, $x, $y, $z, Random $random)
                {
                                $this->treeHeight = $random->nextBoundedInt(15) + 20;

                                // Place the 2x2 trunk for the full height (clean trunk, no leaves along it)
                                $this->placeTrunk($level, $x, $y, $z, $random, $this->treeHeight);

                                // Place 3 rings of crown at the tip only (Taiga-like conical shape)
                                // Crown is concentrated at the top, trunk stays clean below
                                $topY = $y + $this->treeHeight;

                                // Ring 1 (top tip): radius 1 — small cone tip, similar to Taiga tree tip
                                $this->placeLeafRing($level, $x, $z, $topY, 1);

                                // Ring 2 (middle): radius 2 — wider cone layer, 2 blocks below tip
                                $this->placeLeafRing($level, $x, $z, $topY - 2, 2);

                                // Ring 3 (base of crown): radius 2 — widest base layer, 4 blocks below tip
                                $this->placeLeafRing($level, $x, $z, $topY - 4, 2);
                }

                protected function placeTrunk(ChunkManager $level, $x, $y, $z, Random $random, $trunkHeight)
                {
                                // The base dirt block
                                $level->setBlockIdAt($x, $y - 1, $z, Block::DIRT);
                                $level->setBlockIdAt($x + 1, $y - 1, $z, Block::DIRT);
                                $level->setBlockIdAt($x, $y - 1, $z + 1, Block::DIRT);
                                $level->setBlockIdAt($x + 1, $y - 1, $z + 1, Block::DIRT);

                                $trunkWidth = 2;

                                for ($yy = 0; $yy < $trunkHeight; ++$yy) {
                                                for ($xx = 0; $xx < $trunkWidth; $xx++) {
                                                                for ($zz = 0; $zz < $trunkWidth; $zz++) {
                                                                                $block = $level->getBlockIdAt($x + $xx, $y + $yy, $z + $zz);
                                                                                if ($this->canOverride(Block::get($block))) {
                                                                                                $level->setBlockIdAt($x + $xx, $y + $yy, $z + $zz, $this->trunkBlock);
                                                                                                $level->setBlockDataAt($x + $xx, $y + $yy, $z + $zz, $this->type);
                                                                                }
                                                                }
                                                }
                                }
                }

                public function canOverride(Block $block): bool
                {
                                return isset($this->overridable[$block->getId()]);
                }

                /**
                 * Place a single horizontal ring of leaves at a given Y level.
                 * The ring extends $radius blocks beyond the 2x2 trunk on each side,
                 * creating a Taiga-like conical shape. Corner blocks at the maximum
                 * distance from the trunk are skipped (same pattern as SpruceTree).
                 *
                 * @param ChunkManager $level  The world/level
                 * @param int          $x      Trunk base X coordinate
                 * @param int          $z      Trunk base Z coordinate
                 * @param int          $ringY  The Y level for this leaf ring
                 * @param int          $radius How many blocks beyond the 2x2 trunk to extend
                 */
                private function placeLeafRing(ChunkManager $level, $x, $z, $ringY, $radius)
                {
                                for ($xx = $x - $radius; $xx <= $x + 1 + $radius; ++$xx) {
                                                // Distance beyond the trunk edge in X axis
                                                if ($xx < $x) {
                                                                $dx = $x - $xx;
                                                } elseif ($xx > $x + 1) {
                                                                $dx = $xx - ($x + 1);
                                                } else {
                                                                $dx = 0;
                                                }

                                                for ($zz = $z - $radius; $zz <= $z + 1 + $radius; ++$zz) {
                                                                // Distance beyond the trunk edge in Z axis
                                                                if ($zz < $z) {
                                                                                $dz = $z - $zz;
                                                                } elseif ($zz > $z + 1) {
                                                                                $dz = $zz - ($z + 1);
                                                                } else {
                                                                                $dz = 0;
                                                                }

                                                                // Skip corner blocks at maximum distance (Taiga/Spruce pattern)
                                                                if ($dx >= $radius && $dz >= $radius && $radius > 0) {
                                                                                continue;
                                                                }

                                                                if (PopulatorHelpers::isNonSolid($level->getBlockIdAt($xx, $ringY, $zz))) {
                                                                                $level->setBlockIdAt($xx, $ringY, $zz, $this->leafBlock);
                                                                                $level->setBlockDataAt($xx, $ringY, $zz, $this->type);
                                                                }
                                                }
                                }
                }
}
