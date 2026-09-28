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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\DimensionData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\DimensionNameIds;

use function count;

/**
 * Sets properties of different dimensions of the world, such as its Y axis bounds and generator used
 */
class DimensionDataPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::DIMENSION_DATA_PACKET;

	/**
	 * @var DimensionData[]
	 * @phpstan-var array<DimensionNameIds::*, DimensionData>
	 */
	private array $definitions;

	/**
	 * @generate-create-func
	 * @param DimensionData[] $definitions
	 * @phpstan-param array<DimensionNameIds::*, DimensionData> $definitions
	 */
	public static function create(array $definitions) : self
	{
		$result = new self();
		$result->definitions = $definitions;
		return $result;
	}

	/**
	 * @return DimensionData[]
	 * @phpstan-return array<DimensionNameIds::*, DimensionData>
	 */
	public function getDefinitions() : array
	{
		return $this->definitions;
	}

	protected function decodePayload() : void
	{
		$this->definitions = [];

		for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; $i++) {
			$dimensionNameId = $this->getString();
			$dimensionData = DimensionData::read($this);

			if (isset($this->definitions[$dimensionNameId])) {
				throw new PacketDecodeException("Repeated dimension data for key \"$dimensionNameId\"");
			}
			if ($this->protocol < ProtocolInfo::PROTOCOL_2168 && $dimensionNameId !== DimensionNameIds::OVERWORLD && $dimensionNameId !== DimensionNameIds::NETHER && $dimensionNameId !== DimensionNameIds::THE_END) {
				throw new PacketDecodeException("Invalid dimension name ID \"$dimensionNameId\"");
			}
			$this->definitions[$dimensionNameId] = $dimensionData;
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt(count($this->definitions));

		foreach ($this->definitions as $dimensionNameId => $definition) {
			$this->putString((string) $dimensionNameId);
			$definition->write($this);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleDimensionData($this);
	}
}
