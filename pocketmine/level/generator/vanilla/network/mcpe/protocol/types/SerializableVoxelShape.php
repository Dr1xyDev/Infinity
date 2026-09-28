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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use function count;

final class SerializableVoxelShape{

	/**
	 * @param list<SerializableVoxelCells> $cells
	 * @param list<float>                  $xCoordinates
	 * @param list<float>                  $yCoordinates
	 * @param list<float>                  $zCoordinates
	 */
	public function __construct(
		private array $cells,
		private array $xCoordinates,
		private array $yCoordinates,
		private array $zCoordinates
	){}

	/**
	 * @return list<SerializableVoxelCells>
	 */
	public function getCells() : array{ return $this->cells; }

	/**
	 * @return list<float>
	 */
	public function getXCoordinates() : array{ return $this->xCoordinates; }

	/**
	 * @return list<float>
	 */
	public function getYCoordinates() : array{ return $this->yCoordinates; }

	/**
	 * @return list<float>
	 */
	public function getZCoordinates() : array{ return $this->zCoordinates; }

	public static function read(NetworkBinaryStream $in) : self{
		$cells = [];
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_944) {
			//since 1.26.10 every shape has exactly one cell storage
			$cells[] = SerializableVoxelCells::read($in);
		} else {
			for($i = 0, $cellsCount = $in->getUnsignedVarInt(); $i < $cellsCount; ++$i){
				$cells[] = SerializableVoxelCells::read($in);
			}
		}

		$xCoordinates = [];
		for($i = 0, $xCoordinatesCount = $in->getUnsignedVarInt(); $i < $xCoordinatesCount; ++$i){
			$xCoordinates[] = $in->getLFloat();
		}

		$yCoordinates = [];
		for($i = 0, $yCoordinatesCount = $in->getUnsignedVarInt(); $i < $yCoordinatesCount; ++$i){
			$yCoordinates[] = $in->getLFloat();
		}

		$zCoordinates = [];
		for($i = 0, $zCoordinatesCount = $in->getUnsignedVarInt(); $i < $zCoordinatesCount; ++$i){
			$zCoordinates[] = $in->getLFloat();
		}

		return new self(
			$cells,
			$xCoordinates,
			$yCoordinates,
			$zCoordinates
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_944) {
			($this->cells[0] ?? new SerializableVoxelCells(0, 0, 0, []))->write($out);
		} else {
			$out->putUnsignedVarInt(count($this->cells));
			foreach($this->cells as $cell){
				$cell->write($out);
			}
		}

		$out->putUnsignedVarInt(count($this->xCoordinates));
		foreach($this->xCoordinates as $value){
			$out->putLFloat($value);
		}

		$out->putUnsignedVarInt(count($this->yCoordinates));
		foreach($this->yCoordinates as $value){
			$out->putLFloat($value);
		}

		$out->putUnsignedVarInt(count($this->zCoordinates));
		foreach($this->zCoordinates as $value){
			$out->putLFloat($value);
		}
	}
}
