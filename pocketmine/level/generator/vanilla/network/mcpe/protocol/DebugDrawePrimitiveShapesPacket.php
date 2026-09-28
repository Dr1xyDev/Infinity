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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\shape\PacketShapeData;
use function count;

class DebugDrawePrimitiveShapesPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PRIMITIVE_SHAPES_PACKET;

	/**
	 * @var PacketShapeData[]
	 * @phpstan-var list<PacketShapeData>
	 */
	private array $shapes = [];

	/**
	 * @generate-create-func
	 * @param PacketShapeData[] $shapes
	 * @phpstan-param list<PacketShapeData> $shapes
	 */
	public static function create(array $shapes) : self
	{
		$result = new self();
		$result->shapes = $shapes;
		return $result;
	}

	/**
	 * @return PacketShapeData[]
	 * @phpstan-return list<PacketShapeData>
	 */
	public function getShapes() : array
	{
		return $this->shapes;
	}

	protected function decodePayload() : void
	{
		$this->shapes = [];
		for ($i = 0, $len = $this->getUnsignedVarInt(); $i < $len; ++$i) {
			$this->shapes[] = PacketShapeData::read($this);
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt(count($this->shapes));
		foreach ($this->shapes as $shape) {
			$shape->write($this);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleDebugDrawer($this);
	}
}
