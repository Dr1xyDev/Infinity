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

namespace pocketmine\level\generator\vanilla\entity\player;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\entity\Effect;
use pocketmine\level\generator\vanilla\item\enchantment\Enchantment;
use pocketmine\level\generator\vanilla\level\particle\BlockPunchParticle;
use pocketmine\level\generator\vanilla\level\sound\BlockPunchSound;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;
use function abs;

final class SurvivalBlockBreakHandler{

	public const DEFAULT_FX_INTERVAL_TICKS = 5;

	private int $fxTicker = 0;
	private float $breakSpeed;
	private float $breakProgress = 0;

	public function __construct(
		private Player $player,
		private Vector3 $blockPos,
		private Block $block,
		private int $targetedFace,
		private int $maxPlayerDistance,
		private int $fxTickInterval = self::DEFAULT_FX_INTERVAL_TICKS
	){
		$this->breakSpeed = $this->calculateBreakProgressPerTick();
		if($this->breakSpeed > 0){
			$pk = new LevelEventPacket();
			$pk->evid = LevelEventPacket::EVENT_BLOCK_START_BREAK;
			$pk->data = (int) (65535 * $this->breakSpeed);
			$pk->position = $this->blockPos;
			$this->player->getLevel()->broadcastPacketToViewers($this->blockPos, $pk);
		}
	}

	/**
	 * Returns the calculated break speed as percentage progress per game tick.
	 */
	private function calculateBreakProgressPerTick() : float{
		if(!$this->block->isBreakable($this->player->getInventory()->getItemInHand())){
			return 0.0;
		}
		$breakTimePerTick = $this->block->getBreakTime($this->player->getInventory()->getItemInHand()) * 20;
		if(!$this->player->isOnGround() && !$this->player->isFlying()){
			$breakTimePerTick *= 5;
		}
		if($this->player->isUnderwater() && !$this->player->getArmorInventory()->getHelmet()->hasEnchantment(Enchantment::AQUA_AFFINITY)){
			$breakTimePerTick *= 5;
		}
		if($breakTimePerTick > 0){
			$progressPerTick = 1 / $breakTimePerTick;

			$haste = $this->player->getEffect(Effect::HASTE);
			if($haste !== null){
				$hasteLevel = $haste->getEffectLevel();
				$progressPerTick *= (1 + 0.2 * $hasteLevel) * (1.2 ** $hasteLevel);
			}

			$miningFatigue = $this->player->getEffect(Effect::MINING_FATIGUE);
			if($miningFatigue !== null){
				$miningFatigueLevel = $miningFatigue->getEffectLevel();
				$progressPerTick *= 0.21 ** $miningFatigueLevel;
			}

			return $progressPerTick;
		}
		return 1;
	}

	public function update() : bool{
		if($this->player->getPosition()->distanceSquared($this->blockPos->add(0.5, 0.5, 0.5)) > $this->maxPlayerDistance ** 2){
			return false;
		}

		$newBreakSpeed = $this->calculateBreakProgressPerTick();
		if(abs($newBreakSpeed - $this->breakSpeed) > 0.0001){
			$this->breakSpeed = $newBreakSpeed;
			$pk = new LevelEventPacket();
			$pk->evid = LevelEventPacket::EVENT_BLOCK_BREAK_SPEED;
			$pk->data = (int) (65535 * $this->breakSpeed);
			$pk->position = $this->blockPos;

			$this->player->getLevel()->broadcastPacketToViewers($this->blockPos, $pk);
		}

		$this->breakProgress += $this->breakSpeed;

		if(($this->fxTicker++ % $this->fxTickInterval) === 0 && $this->breakProgress < 1){
			$this->player->getLevel()->addParticle(new BlockPunchParticle($this->blockPos, $this->block, $this->targetedFace));
			$this->player->getLevel()->addSound(new BlockPunchSound($this->blockPos, $this->block));
		}

		return $this->breakProgress < 1;
	}

	public function getBlockPos() : Vector3{
		return $this->blockPos;
	}

	public function getTargetedFace() : int{
		return $this->targetedFace;
	}

	public function setTargetedFace(int $face) : void{
		Facing::validate($face);
		$this->targetedFace = $face;
	}

	public function getBreakSpeed() : float{
		return $this->breakSpeed;
	}

	public function getBreakProgress() : float{
		return $this->breakProgress;
	}

	public function __destruct(){
		if($this->player->getLevel()->isInLoadedTerrain($this->blockPos)){
			$pk = new LevelEventPacket();
			$pk->evid = LevelEventPacket::EVENT_BLOCK_STOP_BREAK;
			$pk->data = 0;
			$pk->position = $this->blockPos;

			$this->player->getLevel()->broadcastPacketToViewers($this->blockPos, $pk);
		}
	}
}
