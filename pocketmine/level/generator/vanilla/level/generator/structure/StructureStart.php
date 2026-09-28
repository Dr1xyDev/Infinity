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

namespace pocketmine\level\generator\vanilla\level\generator\structure;

use pocketmine\level\generator\vanilla\utils\Random;

/**
 * All pieces of one structure (one mineshaft, one fortress...)
 */
class StructureStart
{
	/** @var StructurePiece[] */
	protected array $components = [];
	protected StructureBoundingBox $boundingBox;

	public function __construct(protected int $chunkX, protected int $chunkZ)
	{
		$this->boundingBox = StructureBoundingBox::empty();
	}

	public function getBoundingBox() : StructureBoundingBox
	{
		return $this->boundingBox;
	}

	/**
	 * @return StructurePiece[]
	 */
	public function getComponents() : array
	{
		return $this->components;
	}

	public function isValid() : bool
	{
		return $this->components !== [];
	}

	/**
	 * Places the parts of the structure which are inside of $box
	 */
	public function generateStructure(StructureWorld $world, Random $random, StructureBoundingBox $box) : void
	{
		foreach ($this->components as $component) {
			if ($component->getBoundingBox()->intersectsWith($box)) {
				$component->addComponentParts($world, $random, $box);
			}
		}
	}

	protected function updateBoundingBox() : void
	{
		$this->boundingBox = StructureBoundingBox::empty();
		foreach ($this->components as $component) {
			$this->boundingBox->expandTo($component->getBoundingBox());
		}
	}

	protected function moveVertically(int $dy) : void
	{
		$this->boundingBox->offset(0, $dy, 0);
		foreach ($this->components as $component) {
			$component->offset(0, $dy, 0);
		}
	}

	/**
	 * Moves the structure below the given sea level (vanilla markAvailableHeight)
	 */
	protected function markAvailableHeight(int $seaLevel, Random $random, int $offset) : void
	{
		$max = $seaLevel - $offset;
		$height = $this->boundingBox->getYSize() + 1;
		if ($height < $max) {
			$height += $random->nextBoundedInt($max - $height);
		}
		$this->moveVertically($height - $this->boundingBox->maxY);
	}

	/**
	 * Moves the structure to a random height between $min and $max
	 */
	protected function setRandomHeight(Random $random, int $min, int $max) : void
	{
		$range = $max - $min + 1 - $this->boundingBox->getYSize();
		$y = $range > 1 ? $min + $random->nextBoundedInt($range) : $min;
		$this->moveVertically($y - $this->boundingBox->minY);
	}
}
