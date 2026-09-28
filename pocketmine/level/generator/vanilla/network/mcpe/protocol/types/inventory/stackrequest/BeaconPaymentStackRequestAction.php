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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

/**
 * Completes a transaction involving a beacon consuming input to produce effects.
 */
final class BeaconPaymentStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::BEACON_PAYMENT;

	public function __construct(
		private int $primaryEffectId,
		private int $secondaryEffectId
	) {
	}

	public function getPrimaryEffectId() : int
	{
		return $this->primaryEffectId;
	}

	public function getSecondaryEffectId() : int
	{
		return $this->secondaryEffectId;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$primary = $in->getVarInt();
		$secondary = $in->getVarInt();
		return new self($primary, $secondary);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putVarInt($this->primaryEffectId);
		$out->putVarInt($this->secondaryEffectId);
	}
}
