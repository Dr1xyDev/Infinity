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

namespace pocketmine\level\generator\vanilla\block;

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\level\sound\RedstonePowerOffSound;
use pocketmine\level\generator\vanilla\level\sound\RedstonePowerOnSound;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

abstract class Button extends Flowable
{
	public function canBeFlowedInto() : bool
	{
		return false;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($this->canBeSupportedAt($blockReplace->getSide(Facing::opposite($face)))) {
			$this->meta = $face;
			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}
		return false;
	}

	abstract protected function getActivationTime() : int;

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if (!$this->isActivated()) {
			$this->meta ^= 0x08;
			$this->level->setBlock($this, $this);
			$this->level->scheduleDelayedBlockUpdate($this, $this->getActivationTime());
			$this->level->addSound(new RedstonePowerOnSound($this->add(0.5, 0.5, 0.5)));
		}
		return true;
	}

	public function onScheduledUpdate() : void
	{
		if ($this->isActivated()) {
			$this->meta ^= 0x08;
			$this->level->setBlock($this, $this);
			$this->level->addSound(new RedstonePowerOffSound($this->add(0.5, 0.5, 0.5)));
		}
	}

	public function onNearbyBlockChange() : void
	{
		$side = $this->getDamage();
		if ($this->isActivated()) {
			$side ^= 0x08;
		}
		if (!$this->canBeSupportedAt($this->getSide(Facing::opposite($side)))) {
			$this->level->useBreakOn($this);
		}
	}

	protected function canBeSupportedAt(Block $block) : bool
	{
		if (!$block->isTransparent()) {
			return true;
		}
		if (($this->meta & 0x07) == Facing::UP) {
			return $this->canStayOnFullSolid($block);
		}
		return $this->canSupportToFullSolid($block);
	}

	public function isActivated() : bool
	{
		return (($this->meta & 0x08) === 0x08);
	}
}
