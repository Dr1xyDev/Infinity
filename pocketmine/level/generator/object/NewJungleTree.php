<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\generator\object;

use pocketmine\level\generator\normal\math\FacingHelper;
use pocketmine\block\Block;
use pocketmine\block\CocoaBlock;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class NewJungleTree extends CustomTree
{

                /**
                 * The minimum height of a generated tree.
                 */
                protected $minTreeHeight;

                protected $maxTreeHeight;

                /**
                 * The metadata value of the wood to use in tree generation.
                 */
                protected $metaWood = \pocketmine\block\Wood::JUNGLE;

                /**
                 * The metadata value of the leaves to use in tree generation.
                 */
                protected $metaLeaves = \pocketmine\block\Wood::JUNGLE;

                public function __construct($minTreeHeight, $maxTreeHeight)
                {
                                $this->minTreeHeight = $minTreeHeight;
                                $this->maxTreeHeight = $maxTreeHeight;
                }

                public function generate(ChunkManager $worldIn, Random $rand, Vector3 $vectorPosition): bool
                {
                                $position = new Vector3($vectorPosition->getFloorX(), $vectorPosition->getFloorY(), $vectorPosition->getFloorZ());

                                $i = $rand->nextBoundedInt($this->maxTreeHeight) + $this->minTreeHeight;
                                $flag = true;

                                if ($position->getY() >= 1 && $position->getY() + $i + 1 <= 256) {
                                                for ($j = $position->getY(); $j <= $position->getY() + 1 + $i; ++$j) {
                                                                $k = 1;

                                                                if ($j === $position->getY()) {
                                                                                $k = 0;
                                                                }

                                                                if ($j >= $position->getY() + 1 + $i - 2) {
                                                                                $k = 2;
                                                                }

                                                                $pos2 = new Vector3();

                                                                for ($l = $position->getX() - $k; $l <= $position->getX() + $k && $flag; ++$l) {
                                                                                for ($i1 = $position->getZ() - $k; $i1 <= $position->getZ() + $k && $flag; ++$i1) {
                                                                                                if ($j >= 0 && $j < 256) {
                                                                                                                $pos2->setComponents($l, $j, $i1);
                                                                                                                if (!$this->canOverride(Block::get($worldIn->getBlockIdAt($pos2->getFloorX(), $pos2->getFloorY(), $pos2->getFloorZ())))) {
                                                                                                                                $flag = false;
                                                                                                                }
                                                                                                } else {
                                                                                                                $flag = false;
                                                                                                }
                                                                                }
                                                                }
                                                }

                                                if (!$flag) {
                                                                return false;
                                                } else {
                                                                $down = $position->getSide(Vector3::SIDE_DOWN);
                                                                $block = $worldIn->getBlockIdAt($down->getFloorX(), $down->getFloorY(), $down->getFloorZ());

                                                                if (($block === Block::GRASS || $block === Block::DIRT || $block === Block::FARMLAND) && $position->getY() < 256 - $i - 1) {
                                                                                $worldIn->setBlockIdAt($down->getFloorX(), $down->getFloorY(), $down->getFloorZ(), Block::DIRT);
                                                                                $k2 = 3;
                                                                                $l2 = 0;

                                                                                for ($i3 = $position->getY() - 3 + $i; $i3 <= $position->getY() + $i; ++$i3) {
                                                                                                $i4 = $i3 - ($position->getY() + $i);
                                                                                                $j1 = 1 - $i4 / 2;

                                                                                                for ($k1 = $position->getX() - $j1; $k1 <= $position->getX() + $j1; ++$k1) {
                                                                                                                $l1 = $k1 - $position->getX();

                                                                                                                for ($i2 = $position->getZ() - $j1; $i2 <= $position->getZ() + $j1; ++$i2) {
                                                                                                                                $j2 = $i2 - $position->getZ();

                                                                                                                                if (abs($l1) !== $j1 || abs($j2) !== $j1 || $rand->nextBoundedInt(2) !== 0 && $i4 !== 0) {
                                                                                                                                                $blockpos = new Vector3($k1, $i3, $i2);
                                                                                                                                                $id = $worldIn->getBlockIdAt($blockpos->getFloorX(), $blockpos->getFloorY(), $blockpos->getFloorZ());

                                                                                                                                                if ($id === Block::AIR || $id === Block::LEAVES || $id === Block::VINE || $id === Block::SAPLING || $id === Block::SNOW_LAYER) {
                                                                                                                                                                $this->setBlockAndNotifyAdequately($worldIn, $blockpos, Block::get(Block::LEAVES, $this->metaLeaves));
                                                                                                                                                }
                                                                                                                                }
                                                                                                                }
                                                                                                }
                                                                                }

                                                                                for ($j3 = 0; $j3 < $i; ++$j3) {
                                                                                                $up = $position->getSide(Vector3::SIDE_UP, $j3);
                                                                                                $id = $worldIn->getBlockIdAt($up->getFloorX(), $up->getFloorY(), $up->getFloorZ());

                                                                                                if ($id === Block::AIR || $id === Block::LEAVES || $id === Block::VINE || $id === Block::SAPLING || $id === Block::SNOW_LAYER) {
                                                                                                                $this->setBlockAndNotifyAdequately($worldIn, $up, Block::get(Block::LOG, $this->metaWood));

                                                                                                                if ($j3 > 0) {
                                                                                                                                if ($rand->nextBoundedInt(3) > 0 && $this->isAirBlock($worldIn, $position->add(-1, $j3, 0))) {
                                                                                                                                                $this->addVine($worldIn, $position->add(-1, $j3, 0), 8);
                                                                                                                                }

                                                                                                                                if ($rand->nextBoundedInt(3) > 0 && $this->isAirBlock($worldIn, $position->add(1, $j3, 0))) {
                                                                                                                                                $this->addVine($worldIn, $position->add(1, $j3, 0), 2);
                                                                                                                                }

                                                                                                                                if ($rand->nextBoundedInt(3) > 0 && $this->isAirBlock($worldIn, $position->add(0, $j3, -1))) {
                                                                                                                                                $this->addVine($worldIn, $position->add(0, $j3, -1), 1);
                                                                                                                                }

                                                                                                                                if ($rand->nextBoundedInt(3) > 0 && $this->isAirBlock($worldIn, $position->add(0, $j3, 1))) {
                                                                                                                                                $this->addVine($worldIn, $position->add(0, $j3, 1), 4);
                                                                                                                                }
                                                                                                                }
                                                                                                }
                                                                                }

                                                                                for ($k3 = $position->getY() - 3 + $i; $k3 <= $position->getY() + $i; ++$k3) {
                                                                                                $j4 = $k3 - ($position->getY() + $i);
                                                                                                $k4 = 2 - $j4 / 2;
                                                                                                $pos2 = new Vector3();

                                                                                                for ($l4 = $position->getX() - $k4; $l4 <= $position->getX() + $k4; ++$l4) {
                                                                                                                for ($i5 = $position->getZ() - $k4; $i5 <= $position->getZ() + $k4; ++$i5) {
                                                                                                                                $pos2->setComponents($l4, $k3, $i5);

                                                                                                                                if ($worldIn->getBlockIdAt($pos2->getFloorX(), $pos2->getFloorY(), $pos2->getFloorZ()) === Block::LEAVES) {
                                                                                                                                                $blockpos2 = $pos2->getSide(Vector3::SIDE_WEST);
                                                                                                                                                $blockpos3 = $pos2->getSide(Vector3::SIDE_EAST);
                                                                                                                                                $blockpos4 = $pos2->getSide(Vector3::SIDE_NORTH);
                                                                                                                                                $blockpos1 = $pos2->getSide(Vector3::SIDE_SOUTH);

                                                                                                                                                if ($rand->nextBoundedInt(4) === 0 && $worldIn->getBlockIdAt($blockpos2->getFloorX(), $blockpos2->getFloorY(), $blockpos2->getFloorZ()) === Block::AIR) {
                                                                                                                                                                $this->addHangingVine($worldIn, $blockpos2, 8);
                                                                                                                                                }

                                                                                                                                                if ($rand->nextBoundedInt(4) === 0 && $worldIn->getBlockIdAt($blockpos3->getFloorX(), $blockpos3->getFloorY(), $blockpos3->getFloorZ()) === Block::AIR) {
                                                                                                                                                                $this->addHangingVine($worldIn, $blockpos3, 2);
                                                                                                                                                }

                                                                                                                                                if ($rand->nextBoundedInt(4) === 0 && $worldIn->getBlockIdAt($blockpos4->getFloorX(), $blockpos4->getFloorY(), $blockpos4->getFloorZ()) === Block::AIR) {
                                                                                                                                                                $this->addHangingVine($worldIn, $blockpos4, 1);
                                                                                                                                                }

                                                                                                                                                if ($rand->nextBoundedInt(4) === 0 && $worldIn->getBlockIdAt($blockpos1->getFloorX(), $blockpos1->getFloorY(), $blockpos1->getFloorZ()) === Block::AIR) {
                                                                                                                                                                $this->addHangingVine($worldIn, $blockpos1, 4);
                                                                                                                                                }
                                                                                                                                }
                                                                                                                }
                                                                                                }
                                                                                }

                                                                                if ($rand->nextBoundedInt(5) === 0 && $i > 5) {
                                                                                                for ($l3 = 0; $l3 < 2; ++$l3) {
                                                                                                                foreach (FacingHelper::HORIZONTAL as $face) {
                                                                                                                                if ($rand->nextBoundedInt(4 - $l3) === 0) {
                                                                                                                                                $enumfacing1 = FacingHelper::opposite($face);
                                                                                                                                                $this->placeCocoa($worldIn, $rand->nextBoundedInt(3), $position->add(FacingHelper::xOffset($enumfacing1), $i - 5 + $l3, FacingHelper::zOffset($enumfacing1)), $face);
                                                                                                                                }
                                                                                                                }
                                                                                                }
                                                                                }

                                                                                return true;
                                                                } else {
                                                                                return false;
                                                                }
                                                }
                                } else {
                                                return false;
                                }
                }

                public function setBlockAndNotifyAdequately(ChunkManager $level, Vector3 $pos, Block $block)
                {
                                $level->setBlockIdAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $block->getId());
                                $level->setBlockDataAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $block->getDamage());
                }

                private function isAirBlock(ChunkManager $level, Vector3 $v): bool
                {
                                return $level->getBlockIdAt($v->getFloorX(), $v->getFloorY(), $v->getFloorZ()) === Block::AIR;
                }

                private function addVine(ChunkManager $worldIn, Vector3 $pos, $meta)
                {
                                $this->setBlockAndNotifyAdequately($worldIn, $pos, Block::get(Block::VINE, $meta));
                }

                private function addHangingVine(ChunkManager $worldIn, Vector3 $pos, $meta)
                {
                                $this->addVine($worldIn, $pos, $meta);
                                $i = 4;

                                for ($pos = $pos->getSide(Vector3::SIDE_DOWN); $i > 0 && $worldIn->getBlockIdAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ()) === Block::AIR; --$i) {
                                                $this->addVine($worldIn, $pos, $meta);
                                                $pos = $pos->getSide(Vector3::SIDE_DOWN);
                                }
                }

                private function placeCocoa(ChunkManager $worldIn, $age, Vector3 $pos, $side)
                {
                                $meta = $this->getCocoaMeta($age, $side);

                                $this->setBlockAndNotifyAdequately($worldIn, $pos, Block::get(Block::COCOA_BLOCK, $meta));
                }

                private function getCocoaMeta($age, $side): int
                {
                                $meta = 0;

                                $meta *= $age;

                                //3 4 2 5
                                switch ($side) {
                                                case 4:
                                                                $meta++;
                                                                break;
                                                case 2:
                                                                $meta += 2;
                                                                break;
                                                case 5:
                                                                $meta += 3;
                                                                break;
                                }

                                return $meta;
                }

}
