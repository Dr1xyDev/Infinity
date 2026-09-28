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

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\entity\Attribute;
use pocketmine\level\generator\vanilla\entity\EntityIds;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\EntityLink;

use function array_search;
use function count;

class AddActorPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::ADD_ACTOR_PACKET;

	public const LEGACY_ID_MAP_BC = [
		EntityIds::CHICKEN => "minecraft:chicken",
		EntityIds::COW => "minecraft:cow",
		EntityIds::PIG => "minecraft:pig",
		EntityIds::SHEEP => "minecraft:sheep",
		EntityIds::WOLF => "minecraft:wolf",
		EntityIds::VILLAGER => "minecraft:villager",
		EntityIds::MOOSHROOM => "minecraft:mooshroom",
		EntityIds::SQUID => "minecraft:squid",
		EntityIds::RABBIT => "minecraft:rabbit",
		EntityIds::BAT => "minecraft:bat",
		EntityIds::IRON_GOLEM => "minecraft:iron_golem",
		EntityIds::SNOW_GOLEM => "minecraft:snow_golem",
		EntityIds::OCELOT => "minecraft:ocelot",
		EntityIds::HORSE => "minecraft:horse",
		EntityIds::DONKEY => "minecraft:donkey",
		EntityIds::MULE => "minecraft:mule",
		EntityIds::SKELETON_HORSE => "minecraft:skeleton_horse",
		EntityIds::ZOMBIE_HORSE => "minecraft:zombie_horse",
		EntityIds::POLAR_BEAR => "minecraft:polar_bear",
		EntityIds::LLAMA => "minecraft:llama",
		EntityIds::PARROT => "minecraft:parrot",
		EntityIds::DOLPHIN => "minecraft:dolphin",
		EntityIds::ZOMBIE => "minecraft:zombie",
		EntityIds::CREEPER => "minecraft:creeper",
		EntityIds::SKELETON => "minecraft:skeleton",
		EntityIds::SPIDER => "minecraft:spider",
		EntityIds::ZOMBIE_PIGMAN => "minecraft:zombie_pigman",
		EntityIds::SLIME => "minecraft:slime",
		EntityIds::ENDERMAN => "minecraft:enderman",
		EntityIds::SILVERFISH => "minecraft:silverfish",
		EntityIds::CAVE_SPIDER => "minecraft:cave_spider",
		EntityIds::GHAST => "minecraft:ghast",
		EntityIds::MAGMA_CUBE => "minecraft:magma_cube",
		EntityIds::BLAZE => "minecraft:blaze",
		EntityIds::ZOMBIE_VILLAGER => "minecraft:zombie_villager",
		EntityIds::WITCH => "minecraft:witch",
		EntityIds::STRAY => "minecraft:stray",
		EntityIds::HUSK => "minecraft:husk",
		EntityIds::WITHER_SKELETON => "minecraft:wither_skeleton",
		EntityIds::GUARDIAN => "minecraft:guardian",
		EntityIds::ELDER_GUARDIAN => "minecraft:elder_guardian",
		EntityIds::NPC => "minecraft:npc",
		EntityIds::WITHER => "minecraft:wither",
		EntityIds::ENDER_DRAGON => "minecraft:ender_dragon",
		EntityIds::SHULKER => "minecraft:shulker",
		EntityIds::ENDERMITE => "minecraft:endermite",
		EntityIds::AGENT => "minecraft:agent",
		EntityIds::VINDICATOR => "minecraft:vindicator",
		EntityIds::PHANTOM => "minecraft:phantom",
		EntityIds::RAVAGER => "minecraft:ravager",
		EntityIds::ARMOR_STAND => "minecraft:armor_stand",
		EntityIds::TRIPOD_CAMERA => "minecraft:tripod_camera",
		EntityIds::ITEM => "minecraft:item",
		EntityIds::TNT => "minecraft:tnt",
		EntityIds::FALLING_BLOCK => "minecraft:falling_block",
		EntityIds::XP_BOTTLE => "minecraft:xp_bottle",
		EntityIds::XP_ORB => "minecraft:xp_orb",
		EntityIds::EYE_OF_ENDER_SIGNAL => "minecraft:eye_of_ender_signal",
		EntityIds::ENDER_CRYSTAL => "minecraft:ender_crystal",
		EntityIds::FIREWORKS_ROCKET => "minecraft:fireworks_rocket",
		EntityIds::THROWN_TRIDENT => "minecraft:thrown_trident",
		EntityIds::TURTLE => "minecraft:turtle",
		EntityIds::CAT => "minecraft:cat",
		EntityIds::SHULKER_BULLET => "minecraft:shulker_bullet",
		EntityIds::FISHING_HOOK => "minecraft:fishing_hook",
		EntityIds::DRAGON_FIREBALL => "minecraft:dragon_fireball",
		EntityIds::ARROW => "minecraft:arrow",
		EntityIds::SNOWBALL => "minecraft:snowball",
		EntityIds::EGG => "minecraft:egg",
		EntityIds::PAINTING => "minecraft:painting",
		EntityIds::MINECART => "minecraft:minecart",
		EntityIds::FIREBALL => "minecraft:fireball",
		EntityIds::SPLASH_POTION => "minecraft:splash_potion",
		EntityIds::ENDER_PEARL => "minecraft:ender_pearl",
		EntityIds::LEASH_KNOT => "minecraft:leash_knot",
		EntityIds::WITHER_SKULL => "minecraft:wither_skull",
		EntityIds::BOAT => "minecraft:boat",
		EntityIds::WITHER_SKULL_DANGEROUS => "minecraft:wither_skull_dangerous",
		EntityIds::LIGHTNING_BOLT => "minecraft:lightning_bolt",
		EntityIds::SMALL_FIREBALL => "minecraft:small_fireball",
		EntityIds::AREA_EFFECT_CLOUD => "minecraft:area_effect_cloud",
		EntityIds::HOPPER_MINECART => "minecraft:hopper_minecart",
		EntityIds::TNT_MINECART => "minecraft:tnt_minecart",
		EntityIds::CHEST_MINECART => "minecraft:chest_minecart",
		EntityIds::COMMAND_BLOCK_MINECART => "minecraft:command_block_minecart",
		EntityIds::LINGERING_POTION => "minecraft:lingering_potion",
		EntityIds::LLAMA_SPIT => "minecraft:llama_spit",
		EntityIds::EVOCATION_FANG => "minecraft:evocation_fang",
		EntityIds::EVOCATION_ILLAGER => "minecraft:evocation_illager",
		EntityIds::VEX => "minecraft:vex",
		EntityIds::ICE_BOMB => "minecraft:ice_bomb",
		EntityIds::BALLOON => "minecraft:balloon",
		EntityIds::PUFFERFISH => "minecraft:pufferfish",
		EntityIds::SALMON => "minecraft:salmon",
		EntityIds::DROWNED => "minecraft:drowned",
		EntityIds::TROPICALFISH => "minecraft:tropicalfish",
		EntityIds::COD => "minecraft:cod",
		EntityIds::PANDA => "minecraft:panda",
		EntityIds::PILLAGER => "minecraft:pillager",
		EntityIds::VILLAGER_V2 => "minecraft:villager_v2",
		EntityIds::ZOMBIE_VILLAGER_V2 => "minecraft:zombie_villager_v2",
		EntityIds::WANDERING_TRADER => "minecraft:wandering_trader",
		EntityIds::ELDER_GUARDIAN_GHOST => "minecraft:elder_guardian_ghost",
		EntityIds::FOX => "minecraft:fox",
		EntityIds::BEE => "minecraft:bee",
		EntityIds::PIGLIN => "minecraft:piglin",
		EntityIds::HOGLIN => "minecraft:hoglin",
		EntityIds::STRIDER => "minecraft:strider",
		EntityIds::ZOGLIN => "minecraft:zoglin",
		EntityIds::PIGLIN_BRUTE => "minecraft:piglin_brute",
		EntityIds::GOAT => "minecraft:goat",
		EntityIds::GLOW_SQUID => "minecraft:glow_squid",
		EntityIds::AXOLOTL => "minecraft:axolotl",
		EntityIds::WARDEN => "minecraft:warden",
		EntityIds::FROG => "minecraft:frog",
		EntityIds::TADPOLE => "minecraft:tadpole",
		EntityIds::ALLAY => "minecraft:allay",
		EntityIds::CAMEL => "minecraft:camel",
		EntityIds::SNIFFER => "minecraft:sniffer",
		EntityIds::BREEZE => "minecraft:breeze",
		EntityIds::BREEZE_WIND_CHARGE_PROJECTILE => "minecraft:breeze_wind_charge_projectile",
		EntityIds::ARMADILLO => "minecraft:armadillo",
		EntityIds::WIND_CHARGE_PROJECTILE => "minecraft:wind_charge_projectile",
		EntityIds::BOGGED => "minecraft:bogged",
		EntityIds::OMINOUS_ITEM_SPAWNER => "minecraft:ominous_item_spawner",
		EntityIds::CREAKING => "minecraft:creaking",
		EntityIds::HAPPY_GHAST => "minecraft:happy_ghast",
		EntityIds::COPPER_GOLEM => "minecraft:copper_golem",
		EntityIds::NAUTILUS => "minecraft:nautilus",
		EntityIds::ZOMBIE_NAUTILUS => "minecraft:zombie_nautilus",
		EntityIds::PARCHED => "minecraft:parched",
		EntityIds::CAMEL_HUSK => "minecraft:camel_husk",
		EntityIds::TRADER_LLAMA => "minecraft:trader_llama",
		EntityIds::CHEST_BOAT => "minecraft:chest_boat",
		EntityIds::PLAYER => "minecraft:player",
	];

	public ?int $entityUniqueId = null; //TODO
	public int $entityRuntimeId;
	public int $type;
	public Vector3 $position;
	public ?Vector3 $motion = null;
	public float $pitch = 0.0;
	public float $yaw = 0.0;
	public float $headYaw = 0.0;
	public float $bodyYaw = 0.0;

	/** @var Attribute[] */
	public array $attributes = [];
	public array $metadata = [];
	public ?PropertySyncData $syncedProperties = null;
	/** @var EntityLink[] */
	public array $links = [];

	/**
	 * @generate-create-func
	 *
	 * @param Attribute[]  $attributes
	 * @param EntityLink[] $links
	 */
	public static function create(
		int $entityUniqueId,
		int $entityRuntimeId,
		int $type,
		Vector3 $position,
		?Vector3 $motion,
		float $pitch,
		float $yaw,
		float $headYaw,
		float $bodyYaw,
		array $attributes,
		array $metadata,
		PropertySyncData $syncedProperties,
		array $links
	) : self
	{
		$result = new self();
		$result->entityUniqueId = $entityUniqueId;
		$result->entityRuntimeId = $entityRuntimeId;
		$result->type = $type;
		$result->position = $position;
		$result->motion = $motion;
		$result->pitch = $pitch;
		$result->yaw = $yaw;
		$result->headYaw = $headYaw;
		$result->bodyYaw = $bodyYaw;
		$result->attributes = $attributes;
		$result->metadata = $metadata;
		$result->syncedProperties = $syncedProperties;
		$result->links = $links;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->entityUniqueId = $this->getEntityUniqueId();
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->type = array_search($t = $this->getString(), self::LEGACY_ID_MAP_BC, true);
			if ($this->type === false) {
				throw new PacketDecodeException("Can't map ID $t to legacy ID");
			}
		} else {
			$this->type = $this->getUnsignedVarInt();
		}
		$this->position = $this->getVector3();
		$this->motion = $this->getVector3();
		$this->pitch = $this->getLFloat();
		$this->yaw = $this->getLFloat();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->headYaw = $this->getLFloat();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_534) {
				$this->bodyYaw = $this->getLFloat();
			}
		}

		$attrCount = $this->getUnsignedVarInt();
		for ($i = 0; $i < $attrCount; ++$i) {
			$name = $this->getString();
			$min = $this->getLFloat();
			$current = $this->getLFloat();
			$max = $this->getLFloat();
			$attr = Attribute::getAttributeByName($name);

			if ($attr !== null) {
				$attr->setMinValue($min);
				$attr->setMaxValue($max);
				$attr->setValue($current);
				$attr->setModifiers([]);
				$this->attributes[] = $attr;
			} else {
				throw new PacketDecodeException("Unknown attribute type \"$name\"");
			}
		}

		$this->metadata = $this->getEntityMetadata();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_557) {
			$this->syncedProperties = PropertySyncData::read($this);
		}
		$linkCount = $this->getUnsignedVarInt();
		for ($i = 0; $i < $linkCount; ++$i) {
			$this->links[] = $this->getEntityLink();
		}
	}

	protected function encodePayload() : void
	{
		$this->putEntityUniqueId($this->entityUniqueId ?? $this->entityRuntimeId);
		$this->putEntityRuntimeId($this->entityRuntimeId);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			if (!isset(self::LEGACY_ID_MAP_BC[$this->type])) {
				throw new InvalidArgumentException("Unknown entity numeric ID $this->type");
			}
			$this->putString(self::LEGACY_ID_MAP_BC[$this->type]);
		} else {
			$this->putUnsignedVarInt($this->type);
		}
		$this->putVector3($this->position);
		$this->putVector3Nullable($this->motion);
		$this->putLFloat($this->pitch);
		$this->putLFloat($this->yaw);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putLFloat($this->headYaw);
			if ($this->protocol >= ProtocolInfo::PROTOCOL_534) {
				$this->putLFloat($this->bodyYaw);
			}
		}

		$this->putUnsignedVarInt(count($this->attributes));
		foreach ($this->attributes as $attribute) {
			$this->putString($attribute->getName());
			$this->putLFloat($attribute->getMinValue());
			$this->putLFloat($attribute->getValue());
			$this->putLFloat($attribute->getMaxValue());
		}

		$this->putEntityMetadata($this->metadata);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_557) {
			if ($this->syncedProperties === null) {
				$this->syncedProperties = new PropertySyncData([], []);
			}

			$this->syncedProperties->write($this);
		}
		$this->putUnsignedVarInt(count($this->links));
		foreach ($this->links as $link) {
			$this->putEntityLink($link);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleAddActor($this);
	}
}
