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

use pocketmine\level\generator\vanilla\level\generator\feature\configurations\MultipleRandomFeatureConfiguration;

class MultipleWithChanceRandomFeature extends Feature {

	public function __construct(
		public MultipleRandomFeatureConfiguration $config
	){}

	public function place(FeaturePlaceContext $context) : bool{
		$level = $context->level();
		$origin = $context->origin();
		$random = $context->random();
		$config = $this->config;

		foreach ($config->features as $randomFeatureList) {
			if ($random->nextFloat() < $randomFeatureList->chance) {
				return $randomFeatureList->feature->place(new FeaturePlaceContext($level, $random, $origin));
			}
		}

		return $config->defaultFeature->place(new FeaturePlaceContext($level, $random, $origin));
	}
}
