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

use pocketmine\level\generator\vanilla\entity\Mob;
use pocketmine\Player;

use function in_array;
use function sqrt;

class TemptBehavior extends Behavior
{
	protected float $speedMultiplier;
	/** @var int[] */
	protected array $temptItems;
	protected int $delayTemptCounter = 0;
	protected ?Player $temptingPlayer = null;
	protected bool $scaredByPlayerMovement = false;

	public function __construct(Mob $mob, array $temptItemIds, float $speedMultiplier, bool $scaredByPlayerMovement = false)
	{
		parent::__construct($mob);

		$this->temptItems = $temptItemIds;
		$this->speedMultiplier = $speedMultiplier;
		$this->scaredByPlayerMovement = $scaredByPlayerMovement;

		$this->mutexBits = 3;
	}

	public function canStart() : bool
	{
		if ($this->delayTemptCounter > 0) {
			$this->delayTemptCounter--;
			return false;
		}

		$player = $this->mob->level->getNearestEntity($this->mob, sqrt(10), Player::class);

		if ($player instanceof Player) {
			if (in_array($player->getInventory()->getItemInHand()->getId(), $this->temptItems, true)) {
				$this->temptingPlayer = $player;

				return true;
			}
		}

		return false;
	}

	public function canContinue() : bool
	{
		if ($this->scaredByPlayerMovement) {
			if ($this->temptingPlayer->hasMovementUpdate()) {
				return false;
			}
		}

		if ($this->temptingPlayer === null || !$this->temptingPlayer->isAlive() || $this->temptingPlayer->isClosed()) {
			return false;
		}
		if ($this->temptingPlayer->distanceSquared($this->mob) > 100) {
			return false;
		}
		return in_array($this->temptingPlayer->getInventory()->getItemInHand()->getId(), $this->temptItems, true);
	}

	public function onTick() : void
	{
		$this->mob->getLookHelper()->setLookPositionWithEntity($this->temptingPlayer, 30, $this->mob->getVerticalFaceSpeed());

		if ($this->temptingPlayer->distanceSquared($this->mob) < 6.25) {
			$this->mob->getNavigator()->clearPath();
		} else {
			$this->mob->getNavigator()->tryMoveTo($this->temptingPlayer, $this->speedMultiplier);
		}
	}

	public function onEnd() : void
	{
		$this->delayTemptCounter = 100;
		$this->temptingPlayer = null;
		$this->mob->pitch = 0;
		$this->mob->getNavigator()->clearPath();
	}
}
