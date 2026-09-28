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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;

final class SystemDiagnosticTimingInfo
{

	public function __construct(
		private string $displayName,
		private int    $systemIndex,
		private int    $timeInNS,
		private int    $percentOfTotal,
	)
	{
	}

	public function getDisplayName() : string
	{
		return $this->displayName;
	}

	public function getSystemIndex() : int
	{
		return $this->systemIndex;
	}

	public function getTimeInNS() : int
	{
		return $this->timeInNS;
	}

	public function getPercentOfTotal() : int
	{
		return $this->percentOfTotal;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$displayName = $in->getString();
		$systemIndex = $in->getLLong();
		$timeInNS = $in->getLLong();
		$percentOfTotal = $in->getByte();

		return new self(
			$displayName,
			$systemIndex,
			$timeInNS,
			$percentOfTotal
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putString($this->displayName);
		$out->putLLong($this->systemIndex);
		$out->putLLong($this->timeInNS);
		$out->putByte($this->timeInNS);
	}
}
