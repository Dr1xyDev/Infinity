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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

/**
 * Repair and/or remove enchantments from an item in a grindstone.
 */
final class GrindstoneStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::CRAFTING_GRINDSTONE;

	public function __construct(
		private int $recipeId,
		private int $repairCost,
		private int $repetitions
	) {
	}

	public function getRecipeId() : int
	{
		return $this->recipeId;
	}

	/** WARNING: This may be negative */
	public function getRepairCost() : int
	{
		return $this->repairCost;
	}

	public function getRepetitions() : int
	{
		return $this->repetitions;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			//since 1.26.40: LE uint recipe ID, then repetitions, then repair cost
			$recipeId = $in->getLInt() & 0xffffffff;
			$repetitions = $in->getByte();
			$repairCost = $in->getVarInt();
			return new self($recipeId, $repairCost, $repetitions);
		}

		$recipeId = $in->readRecipeNetId();
		$repairCost = $in->getVarInt(); //WHY!!!!
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			$repetitions = $in->getByte();
		}

		return new self($recipeId, $repairCost, $repetitions ?? 1);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$out->putLInt($this->recipeId);
			$out->putByte($this->repetitions);
			$out->putVarInt($this->repairCost);
			return;
		}

		$out->writeRecipeNetId($this->recipeId);
		$out->putVarInt($this->repairCost);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			$out->putByte($this->repetitions);
		}
	}
}
