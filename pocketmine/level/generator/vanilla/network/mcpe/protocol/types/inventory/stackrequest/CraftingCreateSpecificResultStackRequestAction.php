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
 * I have no clear idea what this does. It seems to be the client hinting to the server "hey, put a secondary output in
 * X crafting grid slot". This is used for things like buckets.
 */
final class CraftingCreateSpecificResultStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::CRAFTING_CREATE_SPECIFIC_RESULT;

	public function __construct(
		private int $resultIndex
	) {
	}

	public function getResultIndex() : int
	{
		return $this->resultIndex;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$slot = $in->getByte();
		return new self($slot);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putByte($this->resultIndex);
	}
}
