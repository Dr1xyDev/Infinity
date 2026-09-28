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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera\CameraSplineDefinition;
use function count;

class CameraSplinePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CAMERA_SPLINE_PACKET;

	/**
	 * @var CameraSplineDefinition[]
	 * @phpstan-var list<CameraSplineDefinition>
	 */
	private array $splines = [];

	/**
	 * @generate-create-func
	 * @param CameraSplineDefinition[] $splines
	 * @phpstan-param list<CameraSplineDefinition> $splines
	 */
	public static function create(array $splines) : self{
		$result = new self();
		$result->splines = $splines;
		return $result;
	}

	/**
	 * @return CameraSplineDefinition[]
	 * @phpstan-return list<CameraSplineDefinition>
	 */
	public function getSplines() : array{ return $this->splines; }

	protected function decodePayload() : void{
		$this->splines = [];
		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$this->splines[] = CameraSplineDefinition::read($this);
		}
	}

	protected function encodePayload() : void{
		$this->putUnsignedVarInt(count($this->splines));
		foreach($this->splines as $spline){
			$spline->write($this);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleCameraSpline($this);
	}
}
