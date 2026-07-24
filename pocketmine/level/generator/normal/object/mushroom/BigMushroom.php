<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\generator\normal\object\mushroom;

use pocketmine\level\generator\normal\object\BasicGenerator;
use pocketmine\block\Block;
use pocketmine\block\BrownMushroomBlock;
use pocketmine\block\RedMushroomBlock;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class BigMushroom extends BasicGenerator
{

		const NORTH_WEST = 1;
		const NORTH = 2;
		const NORTH_EAST = 3;
		const WEST = 4;
		const CENTER = 5;
		const EAST = 6;
		const SOUTH_WEST = 7;
		const SOUTH = 8;
		const SOUTH_EAST = 9;
		const STEM = 10;
		const ALL_INSIDE = 0;
		const ALL_OUTSIDE = 14;
		const ALL_STEM = 15;

		const BROWN = 0;
		const RED = 1;

		private int $mushroomType;

		public function __construct(int $mushroomType = -1)
		{
				$this->mushroomType = $mushroomType;
		}

		public function generate(ChunkManager $level, Random $rand, Vector3 $position): bool
		{
				$block = $this->mushroomType;
				if ($block < 0) {
						$block = $rand->nextBoolean() ? self::RED : self::BROWN;
				}

				$mushroom = $block === self::BROWN ? new BrownMushroomBlock() : new RedMushroomBlock();

				$i = $rand->nextBoundedInt(3) + 4;

				if ($rand->nextBoundedInt(12) === 0) {
						$i *= 2;
				}

				$flag = true;

				if ($position->getY() >= 1 && $position->getY() + $i + 1 < 256) {
						for ($j = $position->y; $j <= $position->getY() + 1 + $i; ++$j) {
								$k = 3;

								if ($j <= $position->getY() + 3) {
										$k = 0;
								}

								$pos = new Vector3();

								for ($l = $position->x - $k; $l <= $position->x + $k && $flag; ++$l) {
										for ($i1 = $position->z - $k; $i1 <= $position->z + $k && $flag; ++$i1) {
												if ($j >= 0 && $j < 256) {
														$pos->setComponents($l, $j, $i1);
														$material = $level->getBlockIdAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());

														if ($material !== Block::AIR && $material !== Block::LEAVES) {
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
								$pos2 = $position->getSide(Vector3::SIDE_DOWN);
								$block1 = $level->getBlockIdAt($pos2->getFloorX(), $pos2->getFloorY(), $pos2->getFloorZ());

								if ($block1 !== Block::DIRT && $block1 !== Block::GRASS && $block1 !== Block::MYCELIUM) {
										return false;
								} else {
										$k2 = $position->y + $i;

										if ($block === self::RED) {
												$k2 = $position->getFloorY() + $i - 3;
										}

										for ($l2 = $k2; $l2 <= $position->getY() + $i; ++$l2) {
												$j3 = 1;

												if ($l2 < $position->getY() + $i) {
														++$j3;
												}

												if ($block === self::BROWN) {
														$j3 = 3;
												}

												$k3 = $position->x - $j3;
												$l3 = $position->x + $j3;
												$j1 = $position->z - $j3;
												$k1 = $position->z + $j3;

												for ($l1 = $k3; $l1 <= $l3; ++$l1) {
														for ($i2 = $j1; $i2 <= $k1; ++$i2) {
																$j2 = 5;

																if ($l1 === $k3) {
																		--$j2;
																} elseif ($l1 === $l3) {
																		++$j2;
																}

																if ($i2 === $j1) {
																		$j2 -= 3;
																} elseif ($i2 === $k1) {
																		$j2 += 3;
																}

																$meta = $j2;

																if ($block === self::BROWN || $l2 < $position->getY() + $i) {
																		if (($l1 == $k3 || $l1 === $l3) && ($i2 === $j1 || $i2 === $k1)) {
																				continue;
																		}

																		if ($l1 === $position->getX() - ($j3 - 1) && $i2 === $j1) {
																				$meta = self::NORTH_WEST;
																		}

																		if ($l1 === $k3 && $i2 === $position->getZ() - ($j3 - 1)) {
																				$meta = self::NORTH_WEST;
																		}

																		if ($l1 === $position->getX() + ($j3 - 1) && $i2 === $j1) {
																				$meta = self::NORTH_EAST;
																		}

																		if ($l1 === $l3 && $i2 === $position->getZ() - ($j3 - 1)) {
																				$meta = self::NORTH_EAST;
																		}

																		if ($l1 === $position->getX() - ($j3 - 1) && $i2 === $k1) {
																				$meta = self::SOUTH_WEST;
																		}

																		if ($l1 === $k3 && $i2 === $position->getZ() + ($j3 - 1)) {
																				$meta = self::SOUTH_WEST;
																		}

																		if ($l1 === $position->getX() + ($j3 - 1) && $i2 === $k1) {
																				$meta = self::SOUTH_EAST;
																		}

																		if ($l1 === $l3 && $i2 === $position->getZ() + ($j3 - 1)) {
																				$meta = self::SOUTH_EAST;
																		}
																}

																if ($meta === self::CENTER && $l2 < $position->getY() + $i) {
																		$meta = self::ALL_INSIDE;
																}

																if ($position->getY() >= $position->getY() + $i - 1 || $meta !== self::ALL_INSIDE) {
																		$blockPos = new Vector3($l1, $l2, $i2);

																		if (!(Block::get($level->getBlockIdAt($blockPos->getFloorX(), $blockPos->getFloorY(), $blockPos->getFloorZ()))->isSolid())) {
																				$this->setBlockAndNotifyAdequately($level, $blockPos, Block::get($mushroom->getId(), $meta));
																		}
																}
														}
												}
										}

										for ($i3 = 0; $i3 < $i; ++$i3) {
												$pos = $position->getSide(Vector3::SIDE_UP, $i3);
												$id = $level->getBlockIdAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());

												if (!(Block::get($id)->isSolid())) {
														$this->setBlockAndNotifyAdequately($level, $pos, Block::get($mushroom->getId(), self::STEM));
												}
										}

										return true;
								}
						}
				} else {
						return false;
				}
		}
}
