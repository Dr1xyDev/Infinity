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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\shape;

use pocketmine\level\generator\vanilla\math\Vector2;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\DebugDrawePrimitiveShapesPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\DimensionIds;
use pocketmine\level\generator\vanilla\utils\Color;

/**
 * @see DebugDrawePrimitiveShapesPacket
 */
final class PacketShapeData
{
	public function __construct(
		private int $networkId,
		private ?PrimitiveShapeType $type,
		private ?Vector3 $location,
		private ?float $scale,
		private ?Vector3 $rotation,
		private ?float $totalTimeLeft,
		private ?float $maximumRenderDistance,
		private ?Color $color,
		private ?int $dimensionId,
		private ?int $attachedToEntityId,
		private ?PrimitiveShapePayload $payload,
	){}

	public static function remove(int $networkId, ?int $dimensionId = null) : self{
		return new self($networkId, null, null, null, null, null, null, null, $dimensionId, null, null);
	}

	public static function line(int $networkId, Vector3 $location, Vector3 $lineEndLocation, ?Color $color = null, ?int $dimensionId = null, ?int $attachedToEntityId = null) : self{
		return new self(
			networkId: $networkId,
			type: PrimitiveShapeType::LINE,
			location: $location,
			scale: null,
			rotation: null,
			totalTimeLeft: null,
			maximumRenderDistance: null,
			color: $color,
			dimensionId: $dimensionId,
			attachedToEntityId: $attachedToEntityId,
			payload: new PrimitiveShapeLinePayload($lineEndLocation)
		);
	}

	public static function box(int $networkId, Vector3 $location, Vector3 $boxBound, ?float $scale = null, ?Color $color = null, ?int $dimensionId = null, ?int $attachedToEntityId = null) : self{
		return new self(
			networkId: $networkId,
			type: PrimitiveShapeType::BOX,
			location: $location,
			scale: $scale,
			rotation: null,
			totalTimeLeft: null,
			maximumRenderDistance: null,
			color: $color,
			dimensionId: $dimensionId,
			attachedToEntityId: $attachedToEntityId,
			payload: new PrimitiveShapeBoxPayload($boxBound)
		);
	}

	public static function sphere(int $networkId, Vector3 $location, int $segments, ?float $scale = null, ?Color $color = null, ?int $dimensionId = null, ?int $attachedToEntityId = null) : self{
		return new self(
			networkId: $networkId,
			type: PrimitiveShapeType::SPHERE,
			location: $location,
			scale: $scale,
			rotation: null,
			totalTimeLeft: null,
			maximumRenderDistance: null,
			color: $color,
			dimensionId: $dimensionId,
			attachedToEntityId: $attachedToEntityId,
			payload: new PrimitiveShapeCircleOrSpherePayload($segments)
		);
	}

	public static function circle(int $networkId, Vector3 $location, int $segments, ?float $scale = null, ?Color $color = null, ?int $dimensionId = null, ?int $attachedToEntityId = null) : self{
		return new self(
			networkId: $networkId,
			type: PrimitiveShapeType::CIRCLE,
			location: $location,
			scale: $scale,
			rotation: null,
			totalTimeLeft: null,
			maximumRenderDistance: null,
			color: $color,
			dimensionId: $dimensionId,
			attachedToEntityId: $attachedToEntityId,
			payload: new PrimitiveShapeCircleOrSpherePayload($segments)
		);
	}

	public static function text(int $networkId, Vector3 $location, string $text, bool $useRotation = false, ?Color $backgroundColor = null, bool $depthTest = true, bool $showBackface = true, bool $showTextBackface = true, ?Color $color = null, ?int $dimensionId = null, ?int $attachedToEntityId = null) : self{
		return new self(
			networkId: $networkId,
			type: PrimitiveShapeType::TEXT,
			location: $location,
			scale: null,
			rotation: null,
			totalTimeLeft: null,
			maximumRenderDistance: null,
			color: $color,
			dimensionId: $dimensionId,
			attachedToEntityId: $attachedToEntityId,
			payload: new PrimitiveShapeTextPayload($text, $useRotation, $backgroundColor, $depthTest, $showBackface, $showTextBackface)
		);
	}

	public static function arrow(int $networkId, Vector3 $location, Vector3 $lineEndLocation, ?float $scale = null, ?Color $color = null, ?float $arrowHeadLength = null, ?float $arrowHeadRadius = null, ?int $segments = null, ?int $dimensionId = null, ?int $attachedToEntityId = null) : self{
		return new self(
			networkId: $networkId,
			type: PrimitiveShapeType::ARROW,
			location: $location,
			scale: $scale,
			rotation: null,
			totalTimeLeft: null,
			maximumRenderDistance: null,
			color: $color,
			dimensionId: $dimensionId,
			attachedToEntityId: $attachedToEntityId,
			payload: new PrimitiveShapeArrowPayload($lineEndLocation, $arrowHeadLength, $arrowHeadRadius, $segments)
		);
	}

	public static function cylinder(int $networkId, Vector3 $location, Vector2 $radiusX, Vector2 $radiusZ, float $height, int $segments, ?Color $color = null, ?int $dimensionId = null, ?int $attachedToEntityId = null) : self{
		return new self(
			networkId: $networkId,
			type: PrimitiveShapeType::CYLINDER,
			location: $location,
			scale: null,
			rotation: null,
			totalTimeLeft: null,
			maximumRenderDistance: null,
			color: $color,
			dimensionId: $dimensionId,
			attachedToEntityId: $attachedToEntityId,
			payload: new PrimitiveShapeCylinderPayload($radiusX, $radiusZ, $height, $segments)
		);
	}

	public static function pyramid(int $networkId, Vector3 $location, float $width, float $height, ?float $depth = null, ?Color $color = null, ?int $dimensionId = null, ?int $attachedToEntityId = null) : self{
		return new self(
			networkId: $networkId,
			type: PrimitiveShapeType::PYRAMID,
			location: $location,
			scale: null,
			rotation: null,
			totalTimeLeft: null,
			maximumRenderDistance: null,
			color: $color,
			dimensionId: $dimensionId,
			attachedToEntityId: $attachedToEntityId,
			payload: new PrimitiveShapePyramidPayload($width, $depth, $height)
		);
	}

	public static function ellipsoid(int $networkId, Vector3 $location, Vector3 $radii, int $segmentsPerAxis, ?Color $color = null, ?int $dimensionId = null, ?int $attachedToEntityId = null) : self{
		return new self(
			networkId: $networkId,
			type: PrimitiveShapeType::ELLIPSOID,
			location: $location,
			scale: null,
			rotation: null,
			totalTimeLeft: null,
			maximumRenderDistance: null,
			color: $color,
			dimensionId: $dimensionId,
			attachedToEntityId: $attachedToEntityId,
			payload: new PrimitiveShapeEllipsoidPayload($radii, $segmentsPerAxis)
		);
	}

	public static function cone(int $networkId, Vector3 $location, Vector2 $radii, float $height, int $segments, ?Color $color = null, ?int $dimensionId = null, ?int $attachedToEntityId = null) : self{
		return new self(
			networkId: $networkId,
			type: PrimitiveShapeType::CONE,
			location: $location,
			scale: null,
			rotation: null,
			totalTimeLeft: null,
			maximumRenderDistance: null,
			color: $color,
			dimensionId: $dimensionId,
			attachedToEntityId: $attachedToEntityId,
			payload: new PrimitiveShapeConePayload($radii, $height, $segments)
		);
	}

	public function getNetworkId() : int{ return $this->networkId; }

	public function getType() : ?PrimitiveShapeType{ return $this->type; }

	public function getLocation() : ?Vector3{ return $this->location; }

	public function getScale() : ?float{ return $this->scale; }

	public function getRotation() : ?Vector3{ return $this->rotation; }

	public function getTotalTimeLeft() : ?float{ return $this->totalTimeLeft; }

	public function getMaximumRenderDistance() : ?float{ return $this->maximumRenderDistance; }

	public function getColor() : ?Color{ return $this->color; }

	public function getDimensionId() : ?int{ return $this->dimensionId; }

	public function getAttachedToEntityId() : ?int{ return $this->attachedToEntityId; }

	public function getPayload() : ?PrimitiveShapePayload{ return $this->payload; }

	public static function read(NetworkBinaryStream $in) : self
	{
		$networkId = $in->getUnsignedVarLong();
		$shapeType = $in->getOptional(fn () => PrimitiveShapeType::fromPacket($in->getByte()));
		$location = $in->getOptional($in->getVector3(...));
		$scale = $in->getOptional($in->getLFloat(...));
		$rotation = $in->getOptional($in->getVector3(...));
		$totalTimeLeft = $in->getOptional($in->getLFloat(...));
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$maximumRenderDistance = $in->getOptional($in->getLFloat(...));
		}

		$color = $in->getOptional(fn () => Color::fromARGB($in->getLInt()));

		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_859) {
			if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_924) {
				$dimensionId = $in->getOptional($in->getVarInt(...));
				$attachedToEntityId = $in->getOptional($in->getEntityRuntimeId(...));
			} else {
				$dimensionId = $in->getVarInt();
			}

			$payloadType = $in->getUnsignedVarInt();
			//WTF IS THIS HORROR SHOW
			if(
				($shapeType !== null && $payloadType !== $shapeType->getPayloadType() && $payloadType !== PrimitiveShapeType::PAYLOAD_TYPE_NONE) ||
				($shapeType === null && $payloadType !== PrimitiveShapeType::PAYLOAD_TYPE_NONE)
			){
				throw new PacketDecodeException("Unexpected payload type $payloadType for provided shape type " . ($shapeType->name ?? "(not set)"));
			}

			$payload = match($payloadType){
				PrimitiveShapeType::PAYLOAD_TYPE_NONE => null,
				PrimitiveShapeType::PAYLOAD_TYPE_ARROW => PrimitiveShapeArrowPayload::read($in),
				PrimitiveShapeType::PAYLOAD_TYPE_TEXT => PrimitiveShapeTextPayload::read($in),
				PrimitiveShapeType::PAYLOAD_TYPE_BOX => PrimitiveShapeBoxPayload::read($in),
				PrimitiveShapeType::PAYLOAD_TYPE_LINE => PrimitiveShapeLinePayload::read($in),
				PrimitiveShapeType::PAYLOAD_TYPE_CIRCLE_OR_SPHERE => PrimitiveShapeCircleOrSpherePayload::read($in),
				PrimitiveShapeType::PAYLOAD_TYPE_CYLINDER => PrimitiveShapeCylinderPayload::read($in),
				PrimitiveShapeType::PAYLOAD_TYPE_PYRAMID => PrimitiveShapePyramidPayload::read($in),
				PrimitiveShapeType::PAYLOAD_TYPE_ELLIPSOID => PrimitiveShapeEllipsoidPayload::read($in),
				PrimitiveShapeType::PAYLOAD_TYPE_CONE => PrimitiveShapeConePayload::read($in),
				default => throw new PacketDecodeException("Unknown payload type $payloadType")
			};
		} else {
			$text = $in->getOptional($in->getString(...));
			$boxBound = $in->getOptional($in->getVector3(...));
			$lineEndLocation = $in->getOptional($in->getVector3(...));
			$arrowHeadLength = $in->getOptional($in->getLFloat(...));
			$arrowHeadRadius = $in->getOptional($in->getLFloat(...));
			$segments = $in->getOptional($in->getByte(...));
			if ($text !== null) {
				$payload = new PrimitiveShapeTextPayload($text, false, null, false, false, false);
			} elseif ($boxBound !== null) {
				$payload = new PrimitiveShapeBoxPayload($boxBound);
			} elseif ($lineEndLocation !== null && ($arrowHeadLength === null || $arrowHeadRadius === null || $segments === null)) {
				$payload = new PrimitiveShapeLinePayload($lineEndLocation);
			} elseif ($segments !== null && ($lineEndLocation === null || $arrowHeadLength === null || $arrowHeadRadius === null)) {
				$payload = new PrimitiveShapeCircleOrSpherePayload($segments);
			} elseif ($lineEndLocation !== null && $arrowHeadLength !== null && $arrowHeadRadius !== null && $segments !== null) {
				$payload = new PrimitiveShapeArrowPayload($lineEndLocation, $arrowHeadLength, $arrowHeadRadius, $segments);
			} else {
				$payload = null;
			}
		}

		return new self(
			$networkId,
			$shapeType,
			$location,
			$scale,
			$rotation,
			$totalTimeLeft,
			$maximumRenderDistance ?? null,
			$color,
			$dimensionId ?? null,
			$attachedToEntityId ?? null,
			$payload
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putUnsignedVarLong($this->networkId);
		$out->putOptional($this->type, fn(PrimitiveShapeType $type) => $out->putByte($type->value));
		$out->putOptional($this->location, $out->putVector3(...));
		$out->putOptional($this->scale, $out->putLFloat(...));
		$out->putOptional($this->rotation, $out->putVector3(...));
		$out->putOptional($this->totalTimeLeft, $out->putLFloat(...));
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$out->putOptional($this->maximumRenderDistance, $out->putLFloat(...));
		}
		$out->putOptional($this->color, fn(Color $color) => $out->putLInt($color->toARGB()));

		$payload = $this->payload;
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_859) {
			if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_924) {
				$out->putOptional($this->dimensionId, $out->putVarInt(...));
				$out->putOptional($this->attachedToEntityId, $out->putEntityRuntimeId(...));
			} else {
				$out->putVarInt($this->dimensionId ?? DimensionIds::OVERWORLD);
			}

			//A godawful hack for a godawful packet
			if ($this->type === null) {
				$out->putUnsignedVarInt(PrimitiveShapeType::PAYLOAD_TYPE_NONE);
			} else {
				$out->putUnsignedVarInt($payload?->getTypeId() ?? PrimitiveShapeType::PAYLOAD_TYPE_NONE);
				$payload?->write($out);
			}
		} else {
			$text = null;
			$boxBound = null;
			$lineEndLocation = null;
			$arrowHeadLength = null;
			$arrowHeadRadius = null;
			$segments = null;
			if ($payload instanceof PrimitiveShapeTextPayload) {
				$text = $payload->getText();
			} elseif ($payload instanceof PrimitiveShapeBoxPayload) {
				$boxBound = $payload->getBoxBound();
			} elseif ($payload instanceof PrimitiveShapeLinePayload) {
				$lineEndLocation = $payload->getLineEndLocation();
			} elseif ($payload instanceof PrimitiveShapeCircleOrSpherePayload) {
				$segments = $payload->getSegments();
			} elseif ($payload instanceof PrimitiveShapeArrowPayload) {
				$lineEndLocation = $payload->getLineEndLocation();
				$arrowHeadLength = $payload->getArrowHeadLength();
				$arrowHeadRadius = $payload->getArrowHeadRadius();
				$segments = $payload->getSegments();
			}

			$out->putOptional($text, $out->putString(...));
			$out->putOptional($boxBound, $out->putVector3(...));
			$out->putOptional($lineEndLocation, $out->putVector3(...));
			$out->putOptional($arrowHeadLength, $out->putLFloat(...));
			$out->putOptional($arrowHeadRadius, $out->putLFloat(...));
			$out->putOptional($segments, $out->putByte(...));
		}
	}
}
