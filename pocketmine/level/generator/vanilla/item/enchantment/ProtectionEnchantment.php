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

namespace pocketmine\level\generator\vanilla\item\enchantment;

use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;

use function array_flip;
use function floor;

class ProtectionEnchantment extends Enchantment
{
	/** @var float */
	protected $typeModifier;
	/** @var int[]|null */
	protected $applicableDamageTypes = null;

	/**
	 * ProtectionEnchantment constructor.
	 *
	 * @param int[]|null $applicableDamageTypes EntityDamageEvent::CAUSE_* constants which this enchantment type applies to, or null if it applies to all types of damage.
	 */
	public function __construct(int $id, string $name, int $rarity, int $primaryItemFlags, int $secondaryItemFlags, int $maxLevel, float $typeModifier, ?array $applicableDamageTypes)
	{
		parent::__construct($id, $name, $rarity, $primaryItemFlags, $secondaryItemFlags, $maxLevel);

		$this->typeModifier = $typeModifier;
		if ($applicableDamageTypes !== null) {
			$this->applicableDamageTypes = array_flip($applicableDamageTypes);
		}
	}

	/**
	 * Returns the multiplier by which this enchantment type's EPF increases with each enchantment level.
	 */
	public function getTypeModifier() : float
	{
		return $this->typeModifier;
	}

	/**
	 * Returns the base EPF this enchantment type offers for the given enchantment level.
	 */
	public function getProtectionFactor(int $level) : int
	{
		return (int) floor((6 + $level ** 2) * $this->typeModifier / 3);
	}

	/**
	 * Returns whether this enchantment type offers protection from the specified damage source's cause.
	 */
	public function isApplicable(EntityDamageEvent $event) : bool
	{
		return $this->applicableDamageTypes === null || isset($this->applicableDamageTypes[$event->getCause()]);
	}
}
