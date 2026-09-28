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

namespace pocketmine\level\generator\vanilla\utils\valueproviders;

use pocketmine\level\generator\vanilla\utils\Random;

class ConstantInt extends IntProvider {

	public function __construct(
		private int $value
	){}

	public static function of(int $value) : ConstantInt {
		return new ConstantInt($value);
	}

	public function getValue() : int {
		return $this->value;
	}

	public function sample(Random $random) : int {
		return $this->value;
	}

	public function getMinValue() : int {
		return $this->value;
	}

	public function getMaxValue() : int {
		return $this->value;
	}

	public function getType() : IntProviderType {
		return IntProviderType::CONSTANT;
	}
}
