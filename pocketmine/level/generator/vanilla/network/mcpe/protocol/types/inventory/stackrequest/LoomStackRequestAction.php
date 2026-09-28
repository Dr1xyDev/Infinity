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
 * Apply a pattern to a banner using a loom.
 */
final class LoomStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::CRAFTING_LOOM;

	public function __construct(
		private string $patternId
	) {
	}

	public function getPatternId() : string
	{
		return $this->patternId;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		return new self($in->getString());
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putString($this->patternId);
	}
}
