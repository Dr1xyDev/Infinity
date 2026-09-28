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

namespace pocketmine\level\generator\vanilla\network\mcpe\convert;

use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo1001;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo113;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo2168;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo2193;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo407;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo419;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo422;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo428;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo431;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo440;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo448;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo465;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo471;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo486;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo503;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo527;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo534;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo544;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo554;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo560;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo567;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo575;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo582;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo594;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo618;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo630;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo649;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo662;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo671;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo685;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo686;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo712;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo729;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo748;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo766;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo776;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo786;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo800;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo818;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo844;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo859;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo897;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo924;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo944;
use pocketmine\level\generator\vanilla\network\mcpe\convert\protocol\ProtocolInfo975;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\utils\SingletonTrait;
use ReflectionClass;

class PacketIdTranslator
{
	use SingletonTrait;

	/** @var int[][] */
	private array $coreToNetMapping = [];
	/** @var int[][] */
	private array $netToCoreMapping = [];

	private static function make() : self
	{
		return new self([
			ProtocolInfo::PROTOCOL_2193 => ProtocolInfo2193::class,
			ProtocolInfo::PROTOCOL_2168 => ProtocolInfo2168::class,
			ProtocolInfo::PROTOCOL_1001 => ProtocolInfo1001::class,
			ProtocolInfo::PROTOCOL_975 => ProtocolInfo975::class,
			ProtocolInfo::PROTOCOL_944 => ProtocolInfo944::class,
			ProtocolInfo::PROTOCOL_924 => ProtocolInfo924::class,
			ProtocolInfo::PROTOCOL_897 => ProtocolInfo897::class,
			ProtocolInfo::PROTOCOL_859 => ProtocolInfo859::class,
			ProtocolInfo::PROTOCOL_844 => ProtocolInfo844::class,
			ProtocolInfo::PROTOCOL_818 => ProtocolInfo818::class,
			ProtocolInfo::PROTOCOL_800 => ProtocolInfo800::class,
			ProtocolInfo::PROTOCOL_786 => ProtocolInfo786::class,
			ProtocolInfo::PROTOCOL_776 => ProtocolInfo776::class,
			ProtocolInfo::PROTOCOL_766 => ProtocolInfo766::class,
			ProtocolInfo::PROTOCOL_748 => ProtocolInfo748::class,
			ProtocolInfo::PROTOCOL_729 => ProtocolInfo729::class,
			ProtocolInfo::PROTOCOL_712 => ProtocolInfo712::class,
			ProtocolInfo::PROTOCOL_686 => ProtocolInfo686::class,
			ProtocolInfo::PROTOCOL_685 => ProtocolInfo685::class,
			ProtocolInfo::PROTOCOL_671 => ProtocolInfo671::class,
			ProtocolInfo::PROTOCOL_662 => ProtocolInfo662::class,
			ProtocolInfo::PROTOCOL_649 => ProtocolInfo649::class,
			ProtocolInfo::PROTOCOL_630 => ProtocolInfo630::class,
			ProtocolInfo::PROTOCOL_618 => ProtocolInfo618::class,
			ProtocolInfo::PROTOCOL_594 => ProtocolInfo594::class,
			ProtocolInfo::PROTOCOL_582 => ProtocolInfo582::class,
			ProtocolInfo::PROTOCOL_575 => ProtocolInfo575::class,
			ProtocolInfo::PROTOCOL_567 => ProtocolInfo567::class,
			ProtocolInfo::PROTOCOL_560 => ProtocolInfo560::class,
			ProtocolInfo::PROTOCOL_554 => ProtocolInfo554::class,
			ProtocolInfo::PROTOCOL_544 => ProtocolInfo544::class,
			ProtocolInfo::PROTOCOL_534 => ProtocolInfo534::class,
			ProtocolInfo::PROTOCOL_527 => ProtocolInfo527::class,
			ProtocolInfo::PROTOCOL_503 => ProtocolInfo503::class,
			ProtocolInfo::PROTOCOL_486 => ProtocolInfo486::class,
			ProtocolInfo::PROTOCOL_471 => ProtocolInfo471::class,
			ProtocolInfo::PROTOCOL_465 => ProtocolInfo465::class,
			ProtocolInfo::PROTOCOL_448 => ProtocolInfo448::class,
			ProtocolInfo::PROTOCOL_440 => ProtocolInfo440::class,
			ProtocolInfo::PROTOCOL_431 => ProtocolInfo431::class,
			ProtocolInfo::PROTOCOL_428 => ProtocolInfo428::class,
			ProtocolInfo::PROTOCOL_422 => ProtocolInfo422::class,
			ProtocolInfo::PROTOCOL_419 => ProtocolInfo419::class,
			ProtocolInfo::PROTOCOL_407 => ProtocolInfo407::class,
			ProtocolInfo::PROTOCOL_113 => ProtocolInfo113::class,
		]);
	}

	public function __construct(array $packetIds)
	{
		$corePacketIds = (new ReflectionClass(ProtocolInfo::class))->getConstants();

		foreach ($packetIds as $protocolVersion => $netClassPacketIds) {
			$netPacketIds = (new ReflectionClass($netClassPacketIds))->getConstants();

			foreach ($corePacketIds as $name => $value) {
				if (isset($netPacketIds[$name])) {
					$this->coreToNetMapping[$protocolVersion][$value] = $netPacketIds[$name];
				}
			}

			foreach ($netPacketIds as $name => $value) {
				if (isset($corePacketIds[$name])) {
					$this->netToCoreMapping[$protocolVersion][$value] = $corePacketIds[$name];
				}
			}
		}
	}

	public function fromNetworkId(int $playerProtocol, int $packetId) : int
	{
		foreach ($this->netToCoreMapping as $protocol => $ids) {
			if ($playerProtocol >= $protocol) {
				return $ids[$packetId] ?? throw new PacketDecodeException("The ID of the $packetId package was not found");
			}
		}

		throw new PacketDecodeException("The ID of the $packetId package was not found");
	}

	public function toNetworkId(int $playerProtocol, int $packetId) : ?int
	{
		foreach ($this->coreToNetMapping as $protocol => $ids) {
			if ($playerProtocol >= $protocol) {
				return $ids[$packetId] ?? null;
			}
		}

		return null;
	}
}
