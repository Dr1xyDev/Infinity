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

namespace pocketmine\level\generator\vanilla\network\mcpe;

use pocketmine\level\generator\vanilla\entity\Attribute;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Skin;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\math\Vector2;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\LittleEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\NetworkLittleEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\NamedTag;
use pocketmine\level\generator\vanilla\network\mcpe\convert\EntityMetadataTranslator;
use pocketmine\level\generator\vanilla\network\mcpe\convert\GlobalItemTypeDictionary;
use pocketmine\level\generator\vanilla\network\mcpe\convert\ItemTranslator;
use pocketmine\level\generator\vanilla\network\mcpe\convert\TypeConverter;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOriginData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\OriginDataType;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity\AttributeModifier;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\EntityLink;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GameRuleType;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\ComplexAliasItemDescriptor;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\IntIdMetaItemDescriptor;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\ItemDescriptor;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\ItemDescriptorType;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\MolangItemDescriptor;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\RecipeIngredient;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\StringIdMetaItemDescriptor;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\TagItemDescriptor;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\skin\PersonaPieceTintColor;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\skin\PersonaSkinPiece;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\skin\PersonaSkinPieceType;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\skin\SerializedSkin;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\skin\SkinAnimation;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\skin\SkinImage;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\StructureEditorData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\StructureSettings;
use pocketmine\level\generator\vanilla\utils\BinaryDataException;
use pocketmine\level\generator\vanilla\utils\BinaryStream;
use pocketmine\level\generator\vanilla\utils\Color;
use pocketmine\level\generator\vanilla\utils\UUID;
use SplFixedArray;
use UnexpectedValueException;

use function assert;
use function chr;
use function count;
use function ctype_xdigit;
use function hexdec;
use function ord;
use function preg_match;
use function sprintf;
use function str_replace;
use function strlen;
use function trim;

class NetworkBinaryStream extends BinaryStream
{
	/** SkinArmSizeType ordinals, used since 1.26.40 */
	private const ARM_SIZE_SLIM_ORDINAL = 0;
	private const ARM_SIZE_WIDE_ORDINAL = 1;
	/** Since 1.26.40, persona piece tints always carry exactly this many ARGB colors */
	private const PERSONA_TINT_COLOR_COUNT = 4;
	private const TRUSTED_SKIN_TRUE = "true";
	private const TRUSTED_SKIN_FALSE = "false";

	/** @var int[] */
	public static array $shieldItemRuntimeIds = [];

	protected int $protocol = ProtocolInfo::CURRENT_PROTOCOL;

	public function setProtocol(int $protocol) : void{
		$this->protocol = $protocol;
	}

	public function getProtocol() : int{
		return $this->protocol;
	}

	public function getString() : string
	{
		return $this->get($this->getUnsignedVarInt());
	}

	public function putString(string $v) : void
	{
		$this->putUnsignedVarInt(strlen($v));
		$this->put($v);
	}

	public function getUUID() : UUID
	{
		//This is actually two little-endian longs: UUID Most followed by UUID Least
		$part1 = $this->getLInt();
		$part0 = $this->getLInt();
		$part3 = $this->getLInt();
		$part2 = $this->getLInt();

		return new UUID($part0, $part1, $part2, $part3);
	}

	public function putUUID(UUID $uuid) : void
	{
		$this->putLInt($uuid->getPart(1));
		$this->putLInt($uuid->getPart(0));
		$this->putLInt($uuid->getPart(3));
		$this->putLInt($uuid->getPart(2));
	}

	public function getSkin() : Skin{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			return $this->getSkinV2168();
		}

		$skinId = $this->getString();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_428) {
			$skinPlayFabId = $this->getString();
		}
		$skinResourcePatch = $this->getString();
		$skinData = $this->getSkinImage();
		$animationCount = $this->getLInt();
		if ($animationCount > 128) {
			throw new UnexpectedValueException("Too many skin animations: $animationCount");
		}
		$animations = [];
		for ($i = 0; $i < $animationCount; ++$i) {
			$animations[] = new SkinAnimation(
				$this->getSkinImage(),
				$this->getLInt(),
				$this->getLFloat(),
				($this->protocol >= ProtocolInfo::PROTOCOL_419 ? $this->getLInt() : 0)
			);
		}
		$capeData = $this->getSkinImage();
		$geometryData = $this->getString();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_465) {
			$geometryDataVersion = $this->getString();
		}
		$animationData = $this->getString();
		if ($this->protocol < ProtocolInfo::PROTOCOL_465) {
			$premium = $this->getBool();
			$persona = $this->getBool();
			$capeOnClassic = $this->getBool();
		}
		$capeId = $this->getString();
		$fullSkinId = $this->getString();

		$armSize = $this->getString();
		$skinColor = Color::fromHexString($this->getString());
		$personaPieceCount = $this->getLInt();
		if ($personaPieceCount > 128) {
			throw new UnexpectedValueException("Too many persona pieces: $personaPieceCount");
		}
		$personaPieces = [];
		for ($i = 0; $i < $personaPieceCount; ++$i) {
			$personaPieces[] = new PersonaSkinPiece(
				$this->getString(),
				$this->getString(),
				$this->getString(),
				$this->getBool(),
				$this->getString()
			);
		}
		$pieceTintColorCount = $this->getLInt();
		if ($pieceTintColorCount > 128) {
			throw new UnexpectedValueException("Too many piece tint colors: $pieceTintColorCount");
		}
		$pieceTintColors = [];
		for ($i = 0; $i < $pieceTintColorCount; ++$i) {
			$pieceType = $this->getString();
			$colorCount = $this->getLInt();
			$colors = [];
			for ($j = 0; $j < $colorCount; ++$j) {
				$colors[] = $this->getString();
			}
			$pieceTintColors[] = new PersonaPieceTintColor(
				$pieceType,
				$colors
			);
		}
		$personaPieces = SplFixedArray::fromArray($personaPieces);
		$pieceTintColors = SplFixedArray::fromArray($pieceTintColors);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_465) {
			$premium = $this->getBool();
			$persona = $this->getBool();
			$capeOnClassic = $this->getBool();
			$isPrimaryUser = $this->getBool();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_568) {
				$override = $this->getBool();
			}
		}

		return (new SerializedSkin($skinId, $skinPlayFabId ?? "", $skinData, $capeId, $capeData, $skinResourcePatch, $geometryData, $geometryDataVersion ?? "", $animationData, $animations, $premium ?? false, $persona ?? false, $capeOnClassic ?? false, $fullSkinId, $armSize, $skinColor, $personaPieces, $pieceTintColors, $isPrimaryUser ?? true, $override ?? true))->toSkin();
	}

	public function putSkin(Skin $skin) : void{
		$skin = $skin->getSerializedSkin();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->putSkinV2168($skin);
			return;
		}

		$this->putString($skin->getSkinId());
		if ($this->protocol >= ProtocolInfo::PROTOCOL_428) {
			$this->putString($skin->getPlayFabId());
		}
		$this->putString($skin->getResourcePatch());
		$this->putSkinImage($skin->getSkinImage());
		$this->putLInt(count($skin->getAnimationFrames()));
		foreach ($skin->getAnimationFrames() as $animation) {
			$this->putSkinImage($animation->getImage());
			$this->putLInt($animation->getType());
			$this->putLFloat($animation->getFrames());
			if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
				$this->putLInt($animation->getExpressionType());
			}
		}
		$this->putSkinImage($skin->getCapeImage());
		$this->putString($skin->getGeometryData());
		if ($this->protocol >= ProtocolInfo::PROTOCOL_465) {
			$this->putString($skin->getGeometryDataEngineVersion());
		}
		$this->putString($skin->getAnimationData());
		if ($this->protocol < ProtocolInfo::PROTOCOL_465) {
			$this->putBool($skin->isPremiumSkin());
			$this->putBool($skin->isPersonaSkin());
			$this->putBool($skin->isCapeOnClassicSkin());
		}
		$this->putString($skin->getCapeId());
		$this->putString($skin->getFullSkinId());
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putString($skin->getArmSize());
			$this->putString($skin->getSkinColor()->toHexString());
			$this->putLInt(count($skin->getPersonaPieces()));
			foreach ($skin->getPersonaPieces() as $piece) {
				$this->putString($piece->getPieceId());
				$this->putString($piece->getPieceType());
				$this->putString($piece->getPackId());
				$this->putBool($piece->isDefaultPiece());
				$this->putString($piece->getProductId());
			}
			$this->putLInt(count($skin->getPieceTintColors()));
			foreach ($skin->getPieceTintColors() as $tint) {
				$this->putString($tint->getPieceType());
				$this->putLInt(count($tint->getColors()));
				foreach ($tint->getColors() as $color) {
					$this->putString($color);
				}
			}
			if ($this->protocol >= ProtocolInfo::PROTOCOL_465) {
				$this->putBool($skin->isPremiumSkin());
				$this->putBool($skin->isPersonaSkin());
				$this->putBool($skin->isCapeOnClassicSkin());
				$this->putBool($skin->isPrimaryUser());
				if ($this->protocol >= ProtocolInfo::PROTOCOL_568) {
					$this->putBool($skin->isOverride());
				}
			}
		}
	}

	/**
	 * 1.26.40+ skin format: lists are varint-prefixed, arm size and persona piece types are ordinals, colors are ARGB
	 * integers, and the skin carries a trusted flag and profile hash.
	 */
	private function getSkinV2168() : Skin
	{
		$skinId = $this->getString();
		$skinPlayFabId = $this->getString();
		$skinResourcePatch = $this->getString();
		$skinData = $this->getSkinImage();
		$animationCount = $this->getUnsignedVarInt();
		if ($animationCount > 128) {
			throw new UnexpectedValueException("Too many skin animations: $animationCount");
		}
		$animations = [];
		for ($i = 0; $i < $animationCount; ++$i) {
			$image = $this->getSkinImage();
			$type = $this->getUnsignedVarInt();
			$frames = $this->getLFloat();
			$expressionType = $this->getUnsignedVarInt();
			$animations[] = new SkinAnimation($image, $type, $frames, $expressionType);
		}
		$capeData = $this->getSkinImage();
		$geometryData = $this->getString();
		$geometryDataVersion = $this->getString();
		$animationData = $this->getString();
		$capeId = $this->getString();
		$fullSkinId = $this->getString();
		$armSize = $this->getByte() === self::ARM_SIZE_SLIM_ORDINAL ? SerializedSkin::ARM_SIZE_SLIM : SerializedSkin::ARM_SIZE_WIDE;
		$skinColor = Color::fromARGB($this->getLInt());

		$personaPieceCount = $this->getUnsignedVarInt();
		if ($personaPieceCount > 128) {
			throw new UnexpectedValueException("Too many persona pieces: $personaPieceCount");
		}
		$personaPieces = [];
		for ($i = 0; $i < $personaPieceCount; ++$i) {
			$pieceId = $this->getString();
			$pieceType = PersonaSkinPieceType::ordinalToJson($this->getLInt());
			$packId = $this->getUUID()->toString();
			$isDefault = $this->getBool();
			$productId = $this->getString();
			$personaPieces[] = new PersonaSkinPiece($pieceId, $pieceType, $packId, $isDefault, $productId);
		}

		$pieceTintColorCount = $this->getUnsignedVarInt();
		if ($pieceTintColorCount > 128) {
			throw new UnexpectedValueException("Too many piece tint colors: $pieceTintColorCount");
		}
		$pieceTintColors = [];
		for ($i = 0; $i < $pieceTintColorCount; ++$i) {
			$pieceType = PersonaSkinPieceType::networkNameToJson($this->getString());
			$colors = [];
			for ($j = 0; $j < self::PERSONA_TINT_COLOR_COUNT; ++$j) {
				$colors[] = self::argbToHexString($this->getLInt());
			}
			$pieceTintColors[] = new PersonaPieceTintColor($pieceType, $colors);
		}

		$premium = $this->getBool();
		$persona = $this->getBool();
		$capeOnClassic = $this->getBool();
		$isPrimaryUser = $this->getBool();
		$override = $this->getBool();
		$trustedSkinFlag = $this->getString();
		$profileHash = $this->getString();

		$serializedSkin = new SerializedSkin($skinId, $skinPlayFabId, $skinData, $capeId, $capeData, $skinResourcePatch, $geometryData, $geometryDataVersion, $animationData, $animations, $premium, $persona, $capeOnClassic, $fullSkinId, $armSize, $skinColor, SplFixedArray::fromArray($personaPieces), SplFixedArray::fromArray($pieceTintColors), $isPrimaryUser, $override);
		$serializedSkin->setIsTrustedSkin($trustedSkinFlag !== self::TRUSTED_SKIN_FALSE);
		$serializedSkin->setProfileHash($profileHash);

		return $serializedSkin->toSkin();
	}

	private function putSkinV2168(SerializedSkin $skin) : void
	{
		$this->putString($skin->getSkinId());
		$this->putString($skin->getPlayFabId());
		$this->putString($skin->getResourcePatch());
		$this->putSkinImage($skin->getSkinImage());
		$this->putUnsignedVarInt(count($skin->getAnimationFrames()));
		foreach ($skin->getAnimationFrames() as $animation) {
			$this->putSkinImage($animation->getImage());
			$this->putUnsignedVarInt($animation->getType());
			$this->putLFloat($animation->getFrames());
			$this->putUnsignedVarInt($animation->getExpressionType());
		}
		$this->putSkinImage($skin->getCapeImage());
		//1.26.40+ clients disconnect when the geometry isn't valid JSON
		$this->putString($skin->getGeometryData() === "" ? "{}" : $skin->getGeometryData());
		$this->putString($skin->getGeometryDataEngineVersion());
		$this->putString($skin->getAnimationData());
		$this->putString($skin->getCapeId());
		$this->putString($skin->getFullSkinId());
		$this->putByte($skin->getArmSize() === SerializedSkin::ARM_SIZE_SLIM ? self::ARM_SIZE_SLIM_ORDINAL : self::ARM_SIZE_WIDE_ORDINAL);
		$this->putLInt($skin->getSkinColor()->toARGB());

		$supportsUnsupportedPieceType = $this->protocol >= ProtocolInfo::PROTOCOL_2169;
		$this->putUnsignedVarInt(count($skin->getPersonaPieces()));
		foreach ($skin->getPersonaPieces() as $piece) {
			/** @var PersonaSkinPiece $piece */
			$this->putString($piece->getPieceId());
			$this->putLInt(PersonaSkinPieceType::clampOrdinal(PersonaSkinPieceType::jsonToOrdinal($piece->getPieceType()), $supportsUnsupportedPieceType));
			$this->putUUID(self::uuidFromStringSafe($piece->getPackId()));
			$this->putBool($piece->isDefaultPiece());
			$this->putString($piece->getProductId());
		}

		$this->putUnsignedVarInt(count($skin->getPieceTintColors()));
		foreach ($skin->getPieceTintColors() as $tint) {
			/** @var PersonaPieceTintColor $tint */
			$this->putString(PersonaSkinPieceType::jsonToNetworkName($tint->getPieceType()));
			$colors = $tint->getColors();
			for ($j = 0; $j < self::PERSONA_TINT_COLOR_COUNT; ++$j) {
				$this->putLInt(self::hexStringToArgb($colors[$j] ?? ""));
			}
		}

		$this->putBool($skin->isPremiumSkin());
		$this->putBool($skin->isPersonaSkin());
		$this->putBool($skin->isCapeOnClassicSkin());
		$this->putBool($skin->isPrimaryUser());
		$this->putBool($skin->isOverride());
		$this->putString($skin->isTrustedSkin() ? self::TRUSTED_SKIN_TRUE : self::TRUSTED_SKIN_FALSE);
		$this->putString($skin->getProfileHash());
	}

	private static function argbToHexString(int $argb) : string
	{
		return sprintf("#%08x", $argb & 0xffffffff);
	}

	private static function hexStringToArgb(string $hex) : int
	{
		if (preg_match('/^#?([0-9a-fA-F]{1,8})$/', $hex, $matches) !== 1) {
			return 0;
		}
		return (int) hexdec($matches[1]);
	}

	private static function uuidFromStringSafe(string $uuid) : UUID
	{
		$hex = str_replace("-", "", trim($uuid));
		if (strlen($hex) !== 32 || !ctype_xdigit($hex)) {
			return new UUID();
		}
		return UUID::fromString($hex);
	}

	private function getSkinImage() : SkinImage
	{
		$width = $this->getLInt();
		$height = $this->getLInt();
		$data = $this->getString();
		return new SkinImage($height, $width, $data);
	}

	private function putSkinImage(SkinImage $image) : void
	{
		$this->putLInt($image->getWidth());
		$this->putLInt($image->getHeight());
		$this->putString($image->getData());
	}

	/**
	 * @return int[]
	 * @phpstan-return array{0: int, 1: int, 2: int}
	 * @throws BinaryDataException
	 */
	private function getItemStackHeader() : array{
		$id = $this->getVarInt();
		if($id === 0){
			return [0, 0, 0];
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_431) {
			$count = $this->getLShort();
			$meta = $this->getUnsignedVarInt();
		} else {
			$auxValue = $this->getVarInt();
			$meta = $auxValue >> 8;
			if ($this->protocol < ProtocolInfo::PROTOCOL_407 && $meta === 0x7fff) {
				$meta = -1;
			}
			$count = $auxValue & 0xff;
		}

		return [$id, $count, $meta];
	}

	private function putItemStackHeader(ItemStack $itemStack) : bool{
		if($itemStack->getId() === 0){
			$this->putVarInt(0);
			return false;
		}

		$this->putVarInt($itemStack->getId());
		if ($this->protocol >= ProtocolInfo::PROTOCOL_431) {
			$this->putLShort($itemStack->getCount());
			$this->putUnsignedVarInt($itemStack->getMeta());
		} else {
			$auxValue = (($itemStack->getMeta() & 0x7fff) << 8) | $itemStack->getCount();
			$this->putVarInt($auxValue);
		}

		return true;
	}

	private function getItemStackFooter(int $id, int $meta, int $count) : ItemStack{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_431) {
			$blockRuntimeId = $this->getVarInt();
			$binaryExtraData = new NetworkBinaryStream($this->getString());
		} else {
			$binaryExtraData = $this;
			$blockRuntimeId = 0;
		}

		[$compound, $canPlaceOn, $canDestroy, $shieldBlockingTick] = $this->getItemStackExtraData($id, $binaryExtraData);
		return new ItemStack($id, $meta, $count, $blockRuntimeId, $compound, $canPlaceOn, $canDestroy, $shieldBlockingTick);
	}

	private function putItemStackFooter(ItemStack $itemStack) : void{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_431) {
			$this->putVarInt($itemStack->getBlockRuntimeId());
			$extraData = new NetworkBinaryStream();
			$extraData->setProtocol($this->protocol);
			$this->putItemStackExtraData($itemStack, $extraData);
			$this->putString($extraData->getBuffer());
		} else {
			$this->putItemStackExtraData($itemStack, $this);
		}
	}

	public function getItemStackExtraData(int $id, NetworkBinaryStream $extraData) : array{
		$nbtLen = $extraData->getLShort();

		/** @var CompoundTag|null $compound */
		$compound = null;
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			if ($nbtLen === 0xffff) {
				$nbtDataVersion = $extraData->getByte();
				if ($nbtDataVersion !== 1) {
					throw new PacketDecodeException("Unexpected NBT data version $nbtDataVersion");
				}

				if ($this->protocol >= ProtocolInfo::PROTOCOL_431) {
					$decodedNBT = (new LittleEndianNBTStream())->read($extraData->buffer, false, $extraData->offset, 512);
				} else {
					$decodedNBT = (new NetworkLittleEndianNBTStream())->read($extraData->buffer, false, $extraData->offset, 512);
				}

				if (!($decodedNBT instanceof CompoundTag)) {
					throw new PacketDecodeException("Unexpected root tag type for itemstack");
				}

				$compound = $decodedNBT;
			} elseif ($nbtLen !== 0) {
				throw new PacketDecodeException("Unexpected fake NBT length $nbtLen");
			}
		} elseif ($nbtLen > 0) {
			$decodedNBT = (new LittleEndianNBTStream())->read($extraData->get($nbtLen));
			if (!($decodedNBT instanceof CompoundTag)) {
				throw new PacketDecodeException("Unexpected root tag type for itemstack");
			}

			$compound = $decodedNBT;
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_431) {
			$canPlaceOn = [];
			for ($i = 0, $canPlaceOnCount = $extraData->getLInt(); $i < $canPlaceOnCount; ++$i) {
				$canPlaceOn[] = $extraData->get($extraData->getLShort());
			}

			$canDestroy = [];
			for ($i = 0, $canDestroyCount = $extraData->getLInt(); $i < $canDestroyCount; ++$i) {
				$canDestroy[] = $extraData->get($extraData->getLShort());
			}
		} else {
			$canPlaceOn = [];
			for ($i = 0, $canPlaceOnCount = $extraData->getVarInt(); $i < $canPlaceOnCount; ++$i) {
				$canPlaceOn[] = $extraData->getString();
			}

			$canDestroy = [];
			for ($i = 0, $canDestroyCount = $extraData->getVarInt(); $i < $canDestroyCount; ++$i) {
				$canDestroy[] = $extraData->getString();
			}
		}

		$shieldBlockingTick = null;
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			if (!isset(self::$shieldItemRuntimeIds[$this->protocol])) {
				if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
					self::$shieldItemRuntimeIds[$this->protocol] = ItemTranslator::getInstance($this->protocol)->toNetworkId(ItemIds::SHIELD, 0)[0];
				} else {
					self::$shieldItemRuntimeIds[$this->protocol] = ItemIds::SHIELD;
				}
			}

			if ($id === self::$shieldItemRuntimeIds[$this->protocol]) {
				if ($this->protocol >= ProtocolInfo::PROTOCOL_431) {
					$shieldBlockingTick = $extraData->getLLong();
				} else {
					$shieldBlockingTick = $extraData->getVarLong();
				}
			}
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_431) {
			if(!$extraData->feof()){
				throw new PacketDecodeException("Unexpected trailing extradata for network item $id");
			}
		}

		return [$compound, $canPlaceOn, $canDestroy, $shieldBlockingTick];
	}

	public function putItemStackExtraData(ItemStack $itemStack, NetworkBinaryStream $extraData) : void{
		$nbt = $itemStack->getNbt();

		if ($nbt !== null) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
				$extraData->putLShort(0xffff);
				$extraData->putByte(1); //TODO: some kind of count field? always 1 as of 1.9.0
				if ($this->protocol >= ProtocolInfo::PROTOCOL_431) {
					$extraData->put((new LittleEndianNBTStream())->write($nbt));
				} else {
					$extraData->put((new NetworkLittleEndianNBTStream())->write($nbt));
				}
			} else {
				$nbt = (new LittleEndianNBTStream())->write($nbt);
				$extraData->putLShort(strlen($nbt));
				$extraData->put($nbt);
			}
		} else {
			$extraData->putLShort(0);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_431) {
			$extraData->putLInt(count($itemStack->getCanPlaceOn()));
			foreach ($itemStack->getCanPlaceOn() as $entry) {
				$extraData->putLShort(strlen($entry));
				$extraData->put($entry);
			}
			$extraData->putLInt(count($itemStack->getCanDestroy()));
			foreach ($itemStack->getCanDestroy() as $entry) {
				$extraData->putLShort(strlen($entry));
				$extraData->put($entry);
			}
		} else {
			$extraData->putVarInt(count($itemStack->getCanPlaceOn()));
			foreach ($itemStack->getCanPlaceOn() as $entry) {
				$extraData->putString($entry);
			}
			$extraData->putVarInt(count($itemStack->getCanDestroy()));
			foreach ($itemStack->getCanDestroy() as $entry) {
				$extraData->putString($entry);
			}
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			if (!isset(self::$shieldItemRuntimeIds[$this->protocol])) {
				if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
					self::$shieldItemRuntimeIds[$this->protocol] = ItemTranslator::getInstance($this->protocol)->toNetworkId(ItemIds::SHIELD, 0)[0];
				} else {
					self::$shieldItemRuntimeIds[$this->protocol] = ItemIds::SHIELD;
				}
			}

			if ($itemStack->getId() === self::$shieldItemRuntimeIds[$this->protocol]) {
				$blockingTick = $itemStack->getShieldBlockingTick() ?? 0; //"blocking tick" (ffs mojang)
				if ($this->protocol >= ProtocolInfo::PROTOCOL_431) {
					$extraData->putLLong($blockingTick);
				} else {
					$extraData->putVarLong($blockingTick);
				}
			}
		}
	}

	/**
	 * @throws PacketDecodeException
	 * @throws BinaryDataException
	 */
	public function getItemStackWithoutStackId() : ItemStack{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			//since 1.26.40, all fields are always present, even for air
			$id = $this->getVarInt();
			$count = $this->getLShort();
			$meta = $this->getUnsignedVarInt();
			$blockRuntimeId = $this->getVarInt();
			$extraData = $this->getString();
			if ($id === 0) {
				return ItemStack::null();
			}

			[$compound, $canPlaceOn, $canDestroy, $shieldBlockingTick] = $this->getItemStackExtraData($id, new NetworkBinaryStream($extraData));
			return new ItemStack($id, $meta, $count, $blockRuntimeId, $compound, $canPlaceOn, $canDestroy, $shieldBlockingTick);
		}

		[$id, $count, $meta] = $this->getItemStackHeader();

		return $id !== 0 ? $this->getItemStackFooter($id, $meta, $count) : ItemStack::null();
	}

	public function putItemStackWithoutStackId(Item|ItemStack $itemStack) : void{
		if ($itemStack instanceof Item) {
			$itemStack = TypeConverter::getInstance()->coreItemStackToNet($itemStack, $this->protocol);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->putVarInt($itemStack->getId());
			$this->putLShort($itemStack->getCount());
			$this->putUnsignedVarInt($itemStack->getMeta());
			$this->putVarInt($itemStack->getBlockRuntimeId());
			$this->putItemStackExtraDataString($itemStack);
			return;
		}

		if($this->putItemStackHeader($itemStack)){
			$this->putItemStackFooter($itemStack);
		}
	}

	/**
	 * Writes the item's extra data as a length-prefixed string (empty for air), as used by 1.21.20+ item formats
	 */
	private function putItemStackExtraDataString(ItemStack $itemStack) : void{
		if ($itemStack->getId() === 0) {
			$this->putUnsignedVarInt(0);
			return;
		}

		$extraData = new NetworkBinaryStream();
		$extraData->setProtocol($this->protocol);
		$this->putItemStackExtraData($itemStack, $extraData);
		$this->putString($extraData->getBuffer());
	}

	public function getItemStackWrapper() : ItemStackWrapper{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			//since 1.26.40, the "network item stack descriptor" format replaced this one everywhere
			return $this->getNetworkItemStackDescriptor(ProtocolInfo::PROTOCOL_2168);
		}

		[$id, $count, $meta] = $this->getItemStackHeader();
		if($id === 0){
			return new ItemStackWrapper(0, ItemStack::null());
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_431) {
			$hasNetId = $this->getBool();
			$stackId = $hasNetId ? $this->readServerItemStackId() : 0;
		}

		$itemStack = $this->getItemStackFooter($id, $meta, $count);

		return new ItemStackWrapper($stackId ?? 1, $itemStack);
	}

	public function putItemStackWrapper(Item|ItemStackWrapper $itemStackWrapper) : void{
		if ($itemStackWrapper instanceof Item) {
			$itemStackWrapper = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($itemStackWrapper, $this->protocol));
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->putNetworkItemStackDescriptor($itemStackWrapper, ProtocolInfo::PROTOCOL_2168);
			return;
		}

		$itemStack = $itemStackWrapper->getItemStack();
		if($this->putItemStackHeader($itemStack)){
			if ($this->protocol >= ProtocolInfo::PROTOCOL_431) {
				$hasNetId = $itemStackWrapper->getStackId() !== 0;
				$this->putBool($hasNetId);
				if ($hasNetId) {
					$this->writeServerItemStackId($itemStackWrapper->getStackId());
				}
			}

			$this->putItemStackFooter($itemStack);
		}
	}

	public function getNetworkItemStackDescriptor(int $minimalProtocol = ProtocolInfo::PROTOCOL_1001) : ItemStackWrapper{
		if ($this->getProtocol() < $minimalProtocol) {
			return $this->getItemStackWrapper();
		}

		$id = $this->getSignedLShort();
		$count = $this->getLShort();
		$meta = $this->getUnsignedVarInt();

		if ($this->getBool()) {
			if ($this->protocol < ProtocolInfo::PROTOCOL_2168) {
				$this->getUnsignedVarInt(); //stack ID variant, removed in 1.26.40
			}
			$stackId = $this->readServerItemStackId();
		}

		$blockRuntimeId = $this->getUnsignedVarInt();

		$length = $this->getUnsignedVarInt();
		if ($length === 0) {
			[$compound, $canPlaceOn, $canDestroy, $shieldBlockingTick] = [null, [], [], null];
		} else {
			[$compound, $canPlaceOn, $canDestroy, $shieldBlockingTick] = $this->getItemStackExtraData($id, new NetworkBinaryStream($this->get($length)));
		}

		return new ItemStackWrapper($stackId ?? 0, new ItemStack($id, $meta, $count, $blockRuntimeId, $compound, $canPlaceOn, $canDestroy, $shieldBlockingTick));
	}

	public function putNetworkItemStackDescriptor(Item|ItemStackWrapper $itemStackWrapper, int $minimalProtocol = ProtocolInfo::PROTOCOL_1001) : void{
		if ($itemStackWrapper instanceof Item) {
			$itemStackWrapper = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($itemStackWrapper, $this->protocol));
		}

		if ($this->getProtocol() < $minimalProtocol) {
			$this->putItemStackWrapper($itemStackWrapper);
			return;
		}

		$itemStack = $itemStackWrapper->getItemStack();
		$this->putLShort($itemStack->getId());
		$this->putLShort($itemStack->getCount());
		$this->putUnsignedVarInt($itemStack->getMeta());

		$this->putBool($hasNetId = $itemStackWrapper->getStackId() !== 0);
		if($hasNetId){
			if ($this->protocol < ProtocolInfo::PROTOCOL_2168) {
				$this->putUnsignedVarInt(0); //stack ID variant, removed in 1.26.40
			}
			$this->writeServerItemStackId($itemStackWrapper->getStackId());
		}

		$this->putUnsignedVarInt($itemStack->getBlockRuntimeId());

		$this->putItemStackExtraDataString($itemStack);
	}

	public function getRecipeIngredient() : RecipeIngredient
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$variant = $this->getByte();
			if ($variant === 0) {
				$meta = $this->getVarInt();
				if ($meta !== TagItemDescriptor::DEFAULT_META) {
					throw new PacketDecodeException("Expected meta " . TagItemDescriptor::DEFAULT_META . " for empty item descriptor, got $meta");
				}
				$descriptor = null;
			} elseif ($variant === 1) {
				$descriptor = $this->readItemDescriptorByName($this->getString());
			} else {
				throw new PacketDecodeException("Expected 0 or 1 for item descriptor variant, got $variant");
			}

			return new RecipeIngredient($descriptor, $this->getVarInt());
		}

		if ($this->protocol < ProtocolInfo::PROTOCOL_554) {
			if ($this->protocol < ProtocolInfo::PROTOCOL_407) {
				$item = $this->getItemStackWithoutStackId();
				$id = $item->getId();
				$meta = $item->getMeta();
			} else {
				$id = $this->getVarInt();
				if ($id !== 0) {
					$meta = $this->getVarInt();
					$count = $this->getVarInt();
				}
			}

			$descriptor = new IntIdMetaItemDescriptor($id, $meta ?? 0);
		} else {
			$descriptorType = $this->getByte();
			$descriptor = match ($descriptorType) {
				ItemDescriptorType::INT_ID_META => IntIdMetaItemDescriptor::read($this),
				ItemDescriptorType::STRING_ID_META => StringIdMetaItemDescriptor::read($this),
				ItemDescriptorType::TAG => TagItemDescriptor::read($this),
				ItemDescriptorType::MOLANG => MolangItemDescriptor::read($this),
				ItemDescriptorType::COMPLEX_ALIAS => ComplexAliasItemDescriptor::read($this),
				default => null
			};

			$count = $this->getVarInt();
		}

		return new RecipeIngredient($descriptor, $count ?? 0);
	}

	public function putRecipeIngredient(RecipeIngredient $ingredient) : void
	{
		$type = $ingredient->getDescriptor();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$type = $this->toModernItemDescriptor($type);
			if ($type === null) {
				$this->putByte(0);
				$this->putVarInt(TagItemDescriptor::DEFAULT_META);
			} else {
				$this->putByte(1);
				$this->putString(self::ITEM_DESCRIPTOR_NAMES[$type->getTypeId()]);
				$type->write($this);
			}
			$this->putVarInt($ingredient->getCount());
			return;
		}

		if ($this->protocol < ProtocolInfo::PROTOCOL_554) {
			if (!($type instanceof IntIdMetaItemDescriptor)) {
				$this->putVarInt(0);
				return;
			}

			if ($this->protocol < ProtocolInfo::PROTOCOL_407) {
				$this->putItemStackWithoutStackId(new ItemStack(
					$type->getId(),
					$type->getMeta(),
					$ingredient->getCount(),
					0,
					null,
					[],
					[]
				));
			} else {
				$this->putVarInt($type->getId());
				$this->putVarInt($type->getMeta());
				$this->putVarInt($ingredient->getCount());
			}
		} else {
			if ($this->protocol < ProtocolInfo::PROTOCOL_575 && $type instanceof ComplexAliasItemDescriptor) {
				$type = null;
			}

			$this->putByte($type?->getTypeId() ?? 0);
			$type?->write($this);
			$this->putVarInt($ingredient->getCount());
		}
	}

	/** Item descriptor names used on the network since 1.26.40, by legacy descriptor type ID */
	private const ITEM_DESCRIPTOR_NAMES = [
		ItemDescriptorType::STRING_ID_META => "name",
		ItemDescriptorType::MOLANG => "molang",
		ItemDescriptorType::TAG => "item_tag",
	];
	/** ItemDescriptorType ordinals used by item stack requests since 1.26.40 (0 = empty) */
	private const ITEM_DESCRIPTOR_ORDINALS = [
		ItemDescriptorType::STRING_ID_META => 1,
		ItemDescriptorType::MOLANG => 2,
		ItemDescriptorType::TAG => 3,
	];

	private function readItemDescriptorByName(string $name) : ?ItemDescriptor
	{
		return match ($name) {
			"name" => StringIdMetaItemDescriptor::read($this),
			"molang" => MolangItemDescriptor::read($this),
			"item_tag" => TagItemDescriptor::read($this),
			"empty" => null,
			default => throw new PacketDecodeException("Unknown item descriptor type \"$name\"")
		};
	}

	/**
	 * Since 1.26.40, items can only be described by string ID, tag or molang expression.
	 */
	private function toModernItemDescriptor(?ItemDescriptor $descriptor) : ?ItemDescriptor
	{
		if ($descriptor instanceof IntIdMetaItemDescriptor) {
			if ($descriptor->getId() === 0) {
				return null;
			}
			try {
				$stringId = GlobalItemTypeDictionary::getInstance($this->protocol)->getDictionary()->fromIntId($descriptor->getId());
			} catch (\InvalidArgumentException) {
				return null;
			}
			return new StringIdMetaItemDescriptor($stringId, $descriptor->getMeta() & 0x7fff);
		}
		if ($descriptor instanceof ComplexAliasItemDescriptor) {
			return null; //no longer supported
		}
		return $descriptor;
	}

	/**
	 * Reads a recipe ingredient in the format used by item stack requests since 1.26.40
	 */
	public function getStackRequestIngredient() : RecipeIngredient
	{
		$typeOrdinal = $this->getUnsignedVarInt();
		$innerTypeOrdinal = $this->getByte();
		if ($typeOrdinal !== $innerTypeOrdinal) {
			throw new PacketDecodeException("Item descriptor type mismatch: outer type $typeOrdinal, inner type $innerTypeOrdinal");
		}
		$descriptor = match ($typeOrdinal) {
			0 => null,
			1 => StringIdMetaItemDescriptor::read($this),
			2 => MolangItemDescriptor::read($this),
			3 => TagItemDescriptor::readTagOnly($this),
			default => throw new PacketDecodeException("Unknown item descriptor type $typeOrdinal")
		};

		return new RecipeIngredient($descriptor, $this->getLShort());
	}

	public function putStackRequestIngredient(RecipeIngredient $ingredient) : void
	{
		$descriptor = $this->toModernItemDescriptor($ingredient->getDescriptor());
		$typeOrdinal = $descriptor !== null ? self::ITEM_DESCRIPTOR_ORDINALS[$descriptor->getTypeId()] : 0;
		$this->putUnsignedVarInt($typeOrdinal);
		$this->putByte($typeOrdinal);
		if ($descriptor instanceof TagItemDescriptor) {
			$descriptor->writeTagOnly($this);
		} else {
			$descriptor?->write($this);
		}
		$this->putLShort($ingredient->getCount());
	}

	/**
	 * Decodes entity metadata from the stream.
	 *
	 * @param bool $types Whether to include metadata types along with values in the returned array
	 */
	public function getEntityMetadata(bool $types = true) : array
	{
		$count = $this->getUnsignedVarInt();
		if ($count > 128) {
			throw new UnexpectedValueException("Too many actor metadata: $count");
		}
		$data = [];
		for ($i = 0; $i < $count; ++$i) {
			$key = $this->getUnsignedVarInt();
			$type = $this->getUnsignedVarInt();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
				if (isset($data[$key])) {
					throw new PacketDecodeException("Duplicate entity metadata key $key");
				}
				$innerType = $this->getByte();
				if ($innerType !== $type) {
					throw new PacketDecodeException("Entity metadata type mismatch: expected $type, got $innerType");
				}
			}
			$value = null;
			switch ($type) {
				case Entity::DATA_TYPE_BYTE:
					$value = (ord($this->get(1)));
					break;
				case Entity::DATA_TYPE_SHORT:
					$value = $this->getSignedLShort();
					break;
				case Entity::DATA_TYPE_INT:
					$value = $this->getVarInt();
					break;
				case Entity::DATA_TYPE_FLOAT:
					$value = $this->getLFloat();
					break;
				case Entity::DATA_TYPE_STRING:
					$value = $this->getString();
					break;
				case Entity::DATA_TYPE_SLOT:
					if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
						$value = (new NetworkLittleEndianNBTStream())->read($this->buffer, false, $this->offset, 512);
					} else {
						$value = $this->getItemStackWithoutStackId();
					}
					break;
				case Entity::DATA_TYPE_POS:
					$value = new Vector3(0, 0, 0);
					$this->getSignedBlockPosition($value->x, $value->y, $value->z);
					break;
				case Entity::DATA_TYPE_LONG:
					$value = $this->getVarLong();
					break;
				case Entity::DATA_TYPE_VECTOR3F:
					$value = $this->getVector3();
					break;
				default:
					throw new UnexpectedValueException("Invalid data type " . $type);
			}
			if ($types) {
				$data[$key] = [$type, $value];
			} else {
				$data[$key] = $value;
			}
		}

		return EntityMetadataTranslator::getInstance()->fromNetworkIds($data, $this->protocol);
	}

	/**
	 * Writes entity metadata to the packet buffer.
	 */
	public function putEntityMetadata(array $metadata) : void
	{
		$metadata = EntityMetadataTranslator::getInstance()->toNetworkIds($metadata, $this->protocol);

		$this->putUnsignedVarInt(count($metadata));
		foreach ($metadata as $key => $d) {
			$this->putUnsignedVarInt($key); //data key
			$this->putUnsignedVarInt($d[0]); //data type
			if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
				$this->putByte($d[0]); //inner type, must match the outer type
			}
			switch ($d[0]) {
				case Entity::DATA_TYPE_BYTE:
					$this->putByte($d[1]);
					break;
				case Entity::DATA_TYPE_SHORT:
					$this->putLShort($d[1]);
					break;
				case Entity::DATA_TYPE_INT:
					$this->putVarInt($d[1]);
					break;
				case Entity::DATA_TYPE_FLOAT:
					$this->putLFloat($d[1]);
					break;
				case Entity::DATA_TYPE_STRING:
					$this->putString($d[1]);
					break;
				case Entity::DATA_TYPE_SLOT:
					/** @var Item $item */
					$item = $d[1];
					if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
						$this->put((new NetworkLittleEndianNBTStream())->write($item->getNamedTag()));
					} else {
						$this->putItemStackWithoutStackId(TypeConverter::getInstance()->coreItemStackToNet($item, $this->protocol));
					}
					break;
				case Entity::DATA_TYPE_POS:
					$v = $d[1];
					if ($v !== null) {
						$this->putSignedBlockPosition($v->x, $v->y, $v->z);
					} else {
						$this->putSignedBlockPosition(0, 0, 0);
					}
					break;
				case Entity::DATA_TYPE_LONG:
					$this->putVarLong($d[1]);
					break;
				case Entity::DATA_TYPE_VECTOR3F:
					$this->putVector3Nullable($d[1]);
					break;
				default:
					throw new UnexpectedValueException("Invalid data type " . $d[0]);
			}
		}
	}

	/**
	 * Reads a list of Attributes from the stream.
	 * @return Attribute[]
	 *
	 * @throws UnexpectedValueException if reading an attribute with an unrecognized name
	 */
	public function getAttributeList() : array
	{
		$list = [];
		$count = $this->getUnsignedVarInt();

		for ($i = 0; $i < $count; ++$i) {
			$min = $this->getLFloat();
			$max = $this->getLFloat();
			$current = $this->getLFloat();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_729) {
				$this->getLFloat(); //default min value
				$this->getLFloat(); //default max value
			}
			$default = $this->getLFloat();
			$name = $this->getString();
			$modifiers = [];
			if ($this->protocol >= ProtocolInfo::PROTOCOL_544) {
				for ($j = 0, $modifierCount = $this->getUnsignedVarInt(); $j < $modifierCount; $j++) {
					$modifiers[] = AttributeModifier::read($this);
				}
			}

			$attr = Attribute::getAttributeByName($name);
			if ($attr !== null) {
				$attr->setMinValue($min);
				$attr->setMaxValue($max);
				$attr->setValue($current);
				$attr->setDefaultValue($default);
				$attr->setModifiers($modifiers);

				$list[] = $attr;
			} else {
				throw new UnexpectedValueException("Unknown attribute type \"$name\"");
			}
		}

		return $list;
	}

	/**
	 * Writes a list of Attributes to the packet buffer using the standard format.
	 */
	public function putAttributeList(Attribute ...$attributes) : void
	{
		$this->putUnsignedVarInt(count($attributes));
		foreach ($attributes as $attribute) {
			$this->putLFloat($attribute->getMinValue());
			$this->putLFloat($attribute->getMaxValue());
			$this->putLFloat($attribute->getValue());
			if ($this->protocol >= ProtocolInfo::PROTOCOL_729) {
				$this->putLFloat($attribute->getMinValue()); //default min value
				$this->putLFloat($attribute->getMaxValue()); //default max value
			}
			$this->putLFloat($attribute->getDefaultValue());
			$this->putString($attribute->getName());
			if ($this->protocol >= ProtocolInfo::PROTOCOL_544) {
				$this->putUnsignedVarInt(count($attribute->getModifiers()));
				foreach ($attribute->getModifiers() as $modifier) {
					$modifier->write($this);
				}
			}
		}
	}

	/**
	 * Reads and returns an EntityUniqueID
	 */
	final public function getEntityUniqueId() : int
	{
		return $this->getVarLong();
	}

	/**
	 * Writes an EntityUniqueID
	 */
	public function putEntityUniqueId(int $eid) : void
	{
		$this->putVarLong($eid);
	}

	/**
	 * Reads and returns an EntityRuntimeID
	 */
	final public function getEntityRuntimeId() : int
	{
		return $this->getUnsignedVarLong();
	}

	/**
	 * Writes an EntityRuntimeID
	 */
	public function putEntityRuntimeId(int $eid) : void
	{
		$this->putUnsignedVarLong($eid);
	}

	/**
	 * Reads a block position with a signed Y coordinate.
	 *
	 * @param int &$x
	 * @param int &$y
	 * @param int &$z
	 */
	public function getBlockPosition(&$x, &$y, &$z) : void
	{
		$x = $this->getVarInt();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_944) {
			$y = $this->getVarInt();
		} else {
			$y = $this->getUnsignedVarInt();
		}

		$z = $this->getVarInt();
	}

	/**
	 * Writes a block position with a signed Y coordinate.
	 */
	public function putBlockPosition(int $x, int $y, int $z) : void
	{
		$this->putVarInt($x);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_944) {
			$this->putVarInt($y);
		} else {
			$this->putUnsignedVarInt($y);
		}

		$this->putVarInt($z);
	}

	/**
	 * Reads a block position with a signed Y coordinate.
	 *
	 * @param int &$x
	 * @param int &$y
	 * @param int &$z
	 * @deprecated Use {@see getBlockPosition()} instead.
	 */
	public function getSignedBlockPosition(&$x, &$y, &$z) : void
	{
		$x = $this->getVarInt();
		$y = $this->getVarInt();
		$z = $this->getVarInt();
	}

	/**
	 * Writes a block position with a signed Y coordinate.
	 * @deprecated Use {@see putBlockPosition()} instead.
	 */
	public function putSignedBlockPosition(int $x, int $y, int $z) : void
	{
		$this->putVarInt($x);
		$this->putVarInt($y);
		$this->putVarInt($z);
	}

	/**
	 * Reads a floating-point Vector3 object with coordinates rounded to 4 decimal places.
	 */
	public function getVector3() : Vector3
	{
		return new Vector3(
			$this->getLFloat(),
			$this->getLFloat(),
			$this->getLFloat()
		);
	}

	/**
	 * Reads a floating-point Vector2 object with coordinates rounded to 4 decimal places.
	 *
	 * @throws BinaryDataException
	 */
	public function getVector2() : Vector2
	{
		$x = $this->getLFloat();
		$y = $this->getLFloat();
		return new Vector2($x, $y);
	}

	/**
	 * Writes a floating-point Vector3 object, or 3x zero if null is given.
	 *
	 * Note: ONLY use this where it is reasonable to allow not specifying the vector.
	 * For all other purposes, use the non-nullable version.
	 *
	 * @see NetworkBinaryStream::putVector3()
	 */
	public function putVector3Nullable(?Vector3 $vector) : void
	{
		if ($vector) {
			$this->putVector3($vector);
		} else {
			$this->putLFloat(0.0);
			$this->putLFloat(0.0);
			$this->putLFloat(0.0);
		}
	}

	/**
	 * Writes a floating-point Vector3 object
	 */
	public function putVector3(Vector3 $vector) : void
	{
		$this->putLFloat($vector->x);
		$this->putLFloat($vector->y);
		$this->putLFloat($vector->z);
	}

	/**
	 * Writes a floating-point Vector2 object
	 */
	public function putVector2(Vector2 $vector2) : void
	{
		$this->putLFloat($vector2->x);
		$this->putLFloat($vector2->y);
	}

	public function getByteRotation() : float
	{
		return (float) ((ord($this->get(1))) * (360 / 256));
	}

	public function putByteRotation(float $rotation) : void
	{
		($this->buffer .= chr((int) ($rotation / (360 / 256))));
	}

	/**
	 * Reads gamerules
	 * TODO: implement this properly
	 *
	 * @return array, members are in the structure [name => [type, value, isPlayerModifiable]]
	 */
	public function getGameRules(bool $isStartGame) : array
	{
		$count = $this->getUnsignedVarInt();
		$rules = [];
		for ($i = 0; $i < $count; ++$i) {
			$name = $this->getString();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_440) {
				$isPlayerModifiable = $this->getBool();
			}

			$type = $this->getUnsignedVarInt();
			$value = null;
			switch ($type) {
				case GameRuleType::NULL:
					if ($this->protocol < ProtocolInfo::PROTOCOL_2168) {
						throw new PacketDecodeException("Unknown gamerule type $type");
					}
					break;
				case GameRuleType::BOOL:
					$value = $this->getBool();
					break;
				case GameRuleType::INT:
					if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
						$value = $this->getLInt();
					} elseif ($this->protocol >= ProtocolInfo::PROTOCOL_844) {
						$value = $isStartGame ? $this->getUnsignedVarInt() : $this->getLInt();
					} else {
						$value = $this->getUnsignedVarInt();
					}
					break;
				case GameRuleType::FLOAT:
					$value = $this->getLFloat();
					break;
			}

			$rules[$name] = [$type, $value, $isPlayerModifiable ?? false];
		}

		return $rules;
	}

	/**
	 * Writes a gamerule array, members should be in the structure [name => [type, value, isPlayerModifiable]]
	 * TODO: implement this properly
	 */
	public function putGameRules(array $rules, bool $isStartGame) : void
	{
		$this->putUnsignedVarInt(count($rules));
		foreach ($rules as $name => $rule) {
			$this->putString($name);
			if ($this->protocol >= ProtocolInfo::PROTOCOL_440) {
				$this->putBool($rule[2] ?? false);
			}
			$this->putUnsignedVarInt($rule[0]);
			switch ($rule[0]) {
				case GameRuleType::BOOL:
					$this->putBool($rule[1]);
					break;
				case GameRuleType::NULL:
					break;
				case GameRuleType::INT:
					if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
						$this->putLInt($rule[1]);
					} elseif ($this->protocol >= ProtocolInfo::PROTOCOL_844) {
						if ($isStartGame) {
							$this->putUnsignedVarInt($rule[1]);
						} else {
							$this->putLInt($rule[1]);
						}
					} else {
						$this->putUnsignedVarInt($rule[1]);
					}
					break;
				case GameRuleType::FLOAT:
					$this->putLFloat($rule[1]);
					break;
			}
		}
	}

	protected function getEntityLink() : EntityLink
	{
		$link = new EntityLink();

		$link->fromEntityUniqueId = $this->getEntityUniqueId();
		$link->toEntityUniqueId = $this->getEntityUniqueId();
		$link->type = $this->getByte();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$link->immediate = $this->getBool();
			$link->causedByRider = $this->getBool();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_712) {
				$link->vehicleAngularVelocity = $this->getLFloat();
			}
		}

		return $link;
	}

	protected function putEntityLink(EntityLink $link) : void
	{
		$this->putEntityUniqueId($link->fromEntityUniqueId);
		$this->putEntityUniqueId($link->toEntityUniqueId);
		$this->putByte($link->type);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putBool($link->immediate);
			$this->putBool($link->causedByRider);
			if ($this->protocol >= ProtocolInfo::PROTOCOL_712) {
				$this->putLFloat($link->vehicleAngularVelocity);
			}
		}
	}

	protected function getCommandOriginData() : CommandOriginData{
		$result = new CommandOriginData();

		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$result->type = OriginDataType::fromName($this->getString());
		} else {
			$result->type = OriginDataType::fromPacket($this->getUnsignedVarInt());
		}

		$result->uuid = $this->getUUID();
		$result->requestId = $this->getString();

		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$result->playerActorUniqueId = $this->getLLong();
		} else {
			if ($result->type === OriginDataType::ORIGIN_DEV_CONSOLE || $result->type === OriginDataType::ORIGIN_TEST) {
				$result->playerActorUniqueId = $this->getVarLong();
			}
		}

		return $result;
	}

	protected function putCommandOriginData(CommandOriginData $data) : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->putString($data->type->getName());
		} else {
			$this->putUnsignedVarInt($data->type->value);
		}

		$this->putUUID($data->uuid);
		$this->putString($data->requestId);

		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->putLLong($data->playerActorUniqueId);
		} else {
			if ($data->type === OriginDataType::ORIGIN_DEV_CONSOLE || $data->type === OriginDataType::ORIGIN_TEST) {
				$this->putVarLong($data->playerActorUniqueId);
			}
		}
	}

	protected function getStructureSettings() : StructureSettings
	{
		$result = new StructureSettings();

		$result->paletteName = $this->getString();

		$result->ignoreEntities = $this->getBool();
		$result->ignoreBlocks = $this->getBool();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_557) {
			$result->allowNonTickingChunks = $this->getBool();
		}

		$result->structureSizeX = $result->structureSizeY = $result->structureSizeZ = 0;
		$result->structureOffsetX = $result->structureOffsetY = $result->structureOffsetZ = 0;
		$this->getBlockPosition($result->structureSizeX, $result->structureSizeY, $result->structureSizeZ);
		$this->getBlockPosition($result->structureOffsetX, $result->structureOffsetY, $result->structureOffsetZ);

		$result->lastTouchedByPlayerID = $this->getEntityUniqueId();
		$result->rotation = $this->getByte();
		$result->mirror = $this->getByte();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_475) {
			$result->animationMode = $this->getByte();
			$result->animationSeconds = $this->getLFloat();
		}
		$result->integrityValue = $this->getLFloat();
		$result->integritySeed = $this->getLInt();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$result->pivot = $this->getVector3();
		}

		return $result;
	}

	protected function putStructureSettings(StructureSettings $structureSettings) : void
	{
		$this->putString($structureSettings->paletteName);

		$this->putBool($structureSettings->ignoreEntities);
		$this->putBool($structureSettings->ignoreBlocks);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_557) {
			$this->putBool($structureSettings->allowNonTickingChunks);
		}

		$this->putBlockPosition($structureSettings->structureSizeX, $structureSettings->structureSizeY, $structureSettings->structureSizeZ);
		$this->putBlockPosition($structureSettings->structureOffsetX, $structureSettings->structureOffsetY, $structureSettings->structureOffsetZ);

		$this->putEntityUniqueId($structureSettings->lastTouchedByPlayerID);
		$this->putByte($structureSettings->rotation);
		$this->putByte($structureSettings->mirror);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_475) {
			$this->putByte($structureSettings->animationMode);
			$this->putLFloat($structureSettings->animationSeconds);
		}
		$this->putLFloat($structureSettings->integrityValue);
		$this->putLInt($structureSettings->integritySeed);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putVector3($structureSettings->pivot ?? new Vector3(0, 0, 0));
		}
	}

	protected function getStructureEditorData() : StructureEditorData
	{
		$result = new StructureEditorData();

		$result->structureName = $this->getString();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$result->filteredStructureName = $this->getOptional(fn() => $this->getString()) ?? "";
		} elseif ($this->protocol >= ProtocolInfo::PROTOCOL_776) {
			$result->filteredStructureName = $this->getString();
		}
		$result->structureDataField = $this->getString();

		$result->includePlayers = $this->getBool();
		$result->showBoundingBox = $this->getBool();

		$result->structureBlockType = $this->getVarInt();
		$result->structureSettings = $this->getStructureSettings();
		$result->structureRedstoneSaveMode = $this->protocol >= ProtocolInfo::PROTOCOL_2168 ? $this->getByte() : $this->getVarInt();

		return $result;
	}

	protected function putStructureEditorData(StructureEditorData $structureEditorData) : void
	{
		$this->putString($structureEditorData->structureName);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$filtered = $structureEditorData->filteredStructureName;
			$this->putOptional($filtered === "" ? null : $filtered, fn(string $v) => $this->putString($v));
		} elseif ($this->protocol >= ProtocolInfo::PROTOCOL_776) {
			$this->putString($structureEditorData->filteredStructureName);
		}
		$this->putString($structureEditorData->structureDataField);

		$this->putBool($structureEditorData->includePlayers);
		$this->putBool($structureEditorData->showBoundingBox);

		$this->putVarInt($structureEditorData->structureBlockType);
		$this->putStructureSettings($structureEditorData->structureSettings);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->putByte($structureEditorData->structureRedstoneSaveMode);
		} else {
			$this->putVarInt($structureEditorData->structureRedstoneSaveMode);
		}
	}

	public function getNbtRoot() : NamedTag
	{
		$offset = $this->getOffset();
		try {
			$result = (new NetworkLittleEndianNBTStream())->read($this->getBuffer(), false, $offset, 512);
			assert($result instanceof NamedTag, "doMultiple is false so we should definitely have a NamedTag here");
			return $result;
		} finally {
			$this->setOffset($offset);
		}
	}

	public function getNbtCompoundRoot() : CompoundTag
	{
		$root = $this->getNbtRoot();
		if (!($root instanceof CompoundTag)) {
			throw new UnexpectedValueException("Expected TAG_Compound root");
		}
		return $root;
	}

	public function readRecipeNetId() : int
	{
		return $this->getUnsignedVarInt();
	}

	public function writeRecipeNetId(int $id) : void
	{
		$this->putUnsignedVarInt($id);
	}

	public function readCreativeItemNetId() : int
	{
		return $this->getUnsignedVarInt();
	}

	public function writeCreativeItemNetId(int $id) : void
	{
		$this->putUnsignedVarInt($id);
	}

	/**
	 * This is a union of ItemStackRequestId, LegacyItemStackRequestId, and ServerItemStackId, used in serverbound
	 * packets to allow the client to refer to server known items, or items which may have been modified by a previous
	 * as-yet unacknowledged request from the client.
	 *
	 * - Server itemstack ID is positive
	 * - InventoryTransaction "legacy" request ID is negative and even
	 * - ItemStackRequest request ID is negative and odd
	 * - 0 refers to an empty itemstack (air)
	 */
	public function readItemStackNetIdVariant() : int
	{
		return $this->protocol >= ProtocolInfo::PROTOCOL_2168 ? $this->getLInt() : $this->getVarInt();
	}

	/**
	 * This is a union of ItemStackRequestId, LegacyItemStackRequestId, and ServerItemStackId, used in serverbound
	 * packets to allow the client to refer to server known items, or items which may have been modified by a previous
	 * as-yet unacknowledged request from the client.
	 */
	public function writeItemStackNetIdVariant(int $id) : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->putLInt($id);
		} else {
			$this->putVarInt($id);
		}
	}

	/**
	 * Reads a bool which must always be true, used by some 1.26.40+ structures ("dummy optionals")
	 * @throws PacketDecodeException
	 */
	public function getDummyOptional() : void
	{
		$dummy = $this->getByte();
		if ($dummy !== 1) {
			throw new PacketDecodeException("Dummy optional first byte should always be 1, got $dummy");
		}
	}

	public function putDummyOptional() : void
	{
		$this->putByte(1);
	}

	/**
	 * Reads an optional which is wrapped in a dummy optional in 1.26.40 and 1.26.45 (removed in 1.26.50)
	 *
	 * @phpstan-template T
	 * @phpstan-param \Closure() : T $reader
	 * @phpstan-return T|null
	 */
	public function getProtocolOptional(\Closure $reader) : mixed
	{
		if ($this->protocol < ProtocolInfo::PROTOCOL_2193) {
			$this->getDummyOptional();
		}
		return $this->getOptional($reader);
	}

	/**
	 * @phpstan-template T
	 * @phpstan-param T|null $value
	 * @phpstan-param \Closure(T) : void $writer
	 */
	public function putProtocolOptional(mixed $value, \Closure $writer) : void
	{
		if ($this->protocol < ProtocolInfo::PROTOCOL_2193) {
			$this->putDummyOptional();
		}
		$this->putOptional($value, $writer);
	}

	/**
	 * @phpstan-template T
	 * @phpstan-param \Closure() : T $reader
	 * @phpstan-return list<T>
	 */
	public function getList(\Closure $reader, int $maxCount = PHP_INT_MAX) : array
	{
		$count = $this->getUnsignedVarInt();
		if ($count > $maxCount) {
			throw new PacketDecodeException("Too many list entries: $count, maximum is $maxCount");
		}
		$result = [];
		for ($i = 0; $i < $count; ++$i) {
			$result[] = $reader();
		}
		return $result;
	}

	/**
	 * @phpstan-template T
	 * @phpstan-param T[] $list
	 * @phpstan-param \Closure(T) : void $writer
	 */
	public function putList(array $list, \Closure $writer) : void
	{
		$this->putUnsignedVarInt(count($list));
		foreach ($list as $item) {
			$writer($item);
		}
	}

	public function readItemStackRequestId() : int
	{
		return $this->getVarInt();
	}

	public function writeItemStackRequestId(int $id) : void
	{
		$this->putVarInt($id);
	}

	public function readLegacyItemStackRequestId() : int
	{
		return $this->getVarInt();
	}

	public function writeLegacyItemStackRequestId(int $id) : void
	{
		$this->putVarInt($id);
	}

	public function readServerItemStackId() : int
	{
		return $this->getVarInt();
	}

	public function writeServerItemStackId(int $id) : void
	{
		$this->putVarInt($id);
	}

	/**
	 * @phpstan-template T
	 * @phpstan-param \Closure() : T $reader
	 * @phpstan-return T|null
	 */
	public function getOptional(\Closure $reader) : mixed
	{
		if ($this->getBool()) {
			return $reader();
		}
		return null;
	}

	/**
	 * @phpstan-template T
	 * @phpstan-param T|null $value
	 * @phpstan-param \Closure(T) : void $writer
	 */
	public function putOptional(mixed $value, \Closure $writer) : void
	{
		if ($value !== null) {
			$this->putBool(true);
			$writer($value);
		} else {
			$this->putBool(false);
		}
	}
}
