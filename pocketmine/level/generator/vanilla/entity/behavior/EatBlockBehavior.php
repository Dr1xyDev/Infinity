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

namespace pocketmine\level\generator\vanilla\entity\behavior;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\Grass;
use pocketmine\level\generator\vanilla\block\TallGrass;
use pocketmine\level\generator\vanilla\entity\Animal;
use pocketmine\level\generator\vanilla\level\GameRules;
use pocketmine\level\generator\vanilla\level\particle\DestroyBlockParticle;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorEventPacket;

use function max;

class EatBlockBehavior extends Behavior
{
	/** @var int */
	protected $duration;
	protected $mutexBits = 7;

	public function canStart() : bool
	{
		if ($this->random->nextBoundedInt(1000) != 0) {
			return false;
		}

		$direction = $this->mob->getDirectionVector()->normalize();
		$coordinates = $this->mob->add($direction->x, 0, $direction->z);

		return $this->mob->level->getBlock($coordinates->down()) instanceof Grass || $this->mob->level->getBlock($coordinates) instanceof TallGrass;
	}

	public function onStart() : void
	{
		$this->mob->broadcastEntityEvent(ActorEventPacket::EAT_GRASS_ANIMATION);
		$this->duration = 40;
		$this->mob->getNavigator()->clearPath();
	}

	public function canContinue() : bool
	{
		return $this->duration > 0;
	}

	public function onTick() : void
	{
		$this->duration = max(0, $this->duration - 1);

		if ($this->duration === 4) {
			$pos = $this->mob->down();

			if ($this->mob->level->getBlock($pos) instanceof Grass) {
				if ($this->mob->level->getGameRules()->getBool(GameRules::RULE_MOB_GRIEFING)) {
					$this->mob->level->addParticle(new DestroyBlockParticle($this->mob->floor(), Block::get(Block::GRASS)));
					$this->mob->level->setBlock($pos, Block::get(Block::DIRT));
				}

				if ($this->mob instanceof Animal) {
					$this->mob->eatGrassBonus($pos);
				}
			}
		}
	}

	public function onEnd() : void
	{
		$this->duration = 0;
	}
}
