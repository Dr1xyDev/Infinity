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

namespace pocketmine\level\generator\vanilla\level\generator\feature;

use pocketmine\level\generator\vanilla\level\generator\feature\configurations\SphereReplaceConfiguration;

abstract class AbstractSphereReplaceFeature extends Feature {

	public function __construct(
		public SphereReplaceConfiguration $config
	){}

	public function place(FeaturePlaceContext $context) : bool{
		$level = $context->level();
		$origin = $context->origin();
		$random = $context->random();
		$config = $this->config;

		$flag = false;
		$i = $config->radius->getCount($random);

		for ($j = $origin->getX() - $i; $j <= $origin->getX() + $i; ++$j) {
			for ($k = $origin->getZ() - $i; $k <= $origin->getZ() + $i; ++$k) {
				$l = $j - $origin->getX();
				$i1 = $k - $origin->getZ();
				if ($l * $l + $i1 * $i1 <= $i * $i) {
					for ($j1 = $origin->getY() - $config->halfHeight; $j1 <= $origin->getY() + $config->halfHeight; ++$j1) {
						$block = $level->getBlockAt($j, $j1, $k);

						foreach ($config->targets as $blockState) {
							if ($blockState->isSameType($block)) {
								$level->setBlockAt($j, $j1, $k, $config->state);
								$flag = true;
								break;
							}
						}
					}
				}
			}
		}

		return $flag;
	}
}
