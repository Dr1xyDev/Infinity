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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\SerializableVoxelShape;
use function count;

class VoxelShapesPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::VOXEL_SHAPES_PACKET;

	/**
	 * @var SerializableVoxelShape[]
	 * @phpstan-var list<SerializableVoxelShape>
	 */
	private array $shapes = [];
	/**
	 * @var int[]
	 * @phpstan-var array<string, int>
	 */
	private array $nameMap;
	public int $customShapeCount;

	/**
	 * @generate-create-func
	 * @param SerializableVoxelShape[] $shapes
	 * @param int[]                    $nameMap
	 * @phpstan-param list<SerializableVoxelShape> $shapes
	 * @phpstan-param array<string, int>           $nameMap
	 */
	public static function create(array $shapes, array $nameMap, int $customShapeCount) : self{
		$result = new self();
		$result->shapes = $shapes;
		$result->nameMap = $nameMap;
		$result->customShapeCount = $customShapeCount;
		return $result;
	}

	/**
	 * @return SerializableVoxelShape[]
	 * @phpstan-return list<SerializableVoxelShape>
	 */
	public function getShapes() : array{ return $this->shapes; }

	/**
	 * @return int[]
	 * @phpstan-return array<string, int>
	 */
	public function getNameMap() : array{ return $this->nameMap; }

	public function getCustomShapeCount() : int{ return $this->customShapeCount; }

	protected function decodePayload() : void{
		$this->shapes = [];
		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$this->shapes[] = SerializableVoxelShape::read($this);
		}

		$this->nameMap = [];
		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$name = $this->getString();
			$id = $this->getLShort();
			$this->nameMap[$name] = $id;
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_944) {
			$this->customShapeCount = $this->getLShort();
		}
	}

	protected function encodePayload() : void{
		$this->putUnsignedVarInt(count($this->shapes));
		foreach($this->shapes as $shape){
			$shape->write($this);
		}

		$this->putUnsignedVarInt(count($this->nameMap));
		foreach($this->nameMap as $name => $id){
			$this->putString($name);
			$this->putLShort($id);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_944) {
			$this->putLShort($this->customShapeCount);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleVoxelShapes($this);
	}
}
