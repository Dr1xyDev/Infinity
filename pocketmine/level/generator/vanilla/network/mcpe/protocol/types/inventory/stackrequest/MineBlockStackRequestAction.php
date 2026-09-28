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

final class MineBlockStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::MINE_BLOCK;

	public function __construct(
		private int $hotbarSlot,
		private int $predictedDurability,
		private int $stackId
	) {
	}

	public function getHotbarSlot() : int
	{
		return $this->hotbarSlot;
	}

	public function getPredictedDurability() : int
	{
		return $this->predictedDurability;
	}

	public function getStackId() : int
	{
		return $this->stackId;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$hotbarSlot = $in->getVarInt();
		$predictedDurability = $in->getVarInt();
		$stackId = $in->readItemStackNetIdVariant();
		return new self($hotbarSlot, $predictedDurability, $stackId);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putVarInt($this->hotbarSlot);
		$out->putVarInt($this->predictedDurability);
		$out->writeItemStackNetIdVariant($this->stackId);
	}
}
