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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;

use function count;

final class Experiments
{
	/**
	 * @var bool[]
	 * @phpstan-var array<string, bool>
	 */
	public array $experiments;
	public bool $hasPreviouslyUsedExperiments;

	/**
	 * @param bool[] $experiments
	 * @phpstan-param array<string, bool> $experiments
	 */
	public function __construct(array $experiments, bool $hasPreviouslyUsedExperiments)
	{
		$this->experiments = $experiments;
		$this->hasPreviouslyUsedExperiments = $hasPreviouslyUsedExperiments;
	}

	/** @return bool[] */
	public function getExperiments() : array
	{
		return $this->experiments;
	}

	public function hasPreviouslyUsedExperiments() : bool
	{
		return $this->hasPreviouslyUsedExperiments;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$experiments = [];
		for ($i = 0, $len = $in->getLInt(); $i < $len; ++$i) {
			$experimentName = $in->getString();
			$enabled = $in->getBool();
			$experiments[$experimentName] = $enabled;
		}
		$hasPreviouslyUsedExperiments = $in->getBool();
		return new self($experiments, $hasPreviouslyUsedExperiments);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putLInt(count($this->experiments));
		foreach ($this->experiments as $experimentName => $enabled) {
			$out->putString($experimentName);
			$out->putBool($enabled);
		}
		$out->putBool($this->hasPreviouslyUsedExperiments);
	}
}
