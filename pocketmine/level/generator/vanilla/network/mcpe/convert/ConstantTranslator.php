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

use Closure;
use pocketmine\level\generator\vanilla\level\particle\Particle;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags113;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags407;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags428;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags475;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags560;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags575;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags594;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags630;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags671;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags776;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags786;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags800;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags818;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags844;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags897;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags975;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties113;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties407;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties428;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties594;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties712;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties776;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties800;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties924;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties975;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\bossBarColor\BossBarColor113;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\bossBarColor\BossBarColor622;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds1001;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds2193;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds407;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds428;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds503;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds527;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds582;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds662;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\itemStackRequestActionTypeIds\ItemStackRequestActionType2168;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\itemStackRequestActionTypeIds\ItemStackRequestActionType407;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\itemStackRequestActionTypeIds\ItemStackRequestActionType422;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\itemStackRequestActionTypeIds\ItemStackRequestActionType428;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\itemStackRequestActionTypeIds\ItemStackRequestActionType486;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds1001;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds113;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds407;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds428;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds475;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds486;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds560;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds567;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds575;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds594;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds622;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds630;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds662;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds671;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds685;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds712;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds729;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds766;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds818;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds819;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds827;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds844;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds897;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds924;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds944;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds975;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\particleIds\ParticleIds113;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\particleIds\ParticleIds407;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\particleIds\ParticleIds431;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\particleIds\ParticleIds448;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\particleIds\ParticleIds630;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\particleIds\ParticleIds649;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\particleIds\ParticleIds662;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\particleIds\ParticleIds712;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\particleIds\ParticleIds766;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\particleIds\ParticleIds844;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\particleIds\ParticleIds944;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\playerActionIds\PlayerActionIds113;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\playerActionIds\PlayerActionIds407;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\playerActionIds\PlayerActionIds419;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\playerActionIds\PlayerActionIds428;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\playerActionIds\PlayerActionIds527;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\playerActionIds\PlayerActionIds567;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\playerActionIds\PlayerActionIds594;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\playerActionIds\PlayerActionIds618;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\playerActionIds\PlayerActionIds622;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\playerActionIds\PlayerActionIds748;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\playerActionIds\PlayerActionIds818;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\resourcePackTypeIds\ResourcePackTypeIds407;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\textPacketTypeIds\TextPacketTypeIds113;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\textPacketTypeIds\TextPacketTypeIds407;
use pocketmine\level\generator\vanilla\network\mcpe\convert\constants\textPacketTypeIds\TextPacketTypeIds554;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\TextPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\BossBarColor;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\stackrequest\ItemStackRequestActionType;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\resourcepacks\ResourcePackType;
use pocketmine\level\generator\vanilla\utils\SingletonTrait;
use ReflectionClass;
use SplFixedArray;

use function array_filter;
use function max;
use function str_starts_with;

use const ARRAY_FILTER_USE_BOTH;

class ConstantTranslator
{
	use SingletonTrait;

	private const int CORE_TO_NETWORK = 0;
	private const int NETWORK_TO_CORE = 1;

	/** @var SplFixedArray<int>[][][] */
	private array $constantsMapping = [];

	public function __construct()
	{
		$this->collect(LevelSoundEventPacket::class, [
			ProtocolInfo::PROTOCOL_1001 => LevelSoundIds1001::class,
			ProtocolInfo::PROTOCOL_975 => LevelSoundIds975::class,
			ProtocolInfo::PROTOCOL_944 => LevelSoundIds944::class,
			ProtocolInfo::PROTOCOL_924 => LevelSoundIds924::class,
			ProtocolInfo::PROTOCOL_897 => LevelSoundIds897::class,
			ProtocolInfo::PROTOCOL_844 => LevelSoundIds844::class,
			ProtocolInfo::PROTOCOL_827 => LevelSoundIds827::class,
			ProtocolInfo::PROTOCOL_819 => LevelSoundIds819::class,
			ProtocolInfo::PROTOCOL_818 => LevelSoundIds818::class,
			ProtocolInfo::PROTOCOL_766 => LevelSoundIds766::class,
			ProtocolInfo::PROTOCOL_729 => LevelSoundIds729::class,
			ProtocolInfo::PROTOCOL_712 => LevelSoundIds712::class,
			ProtocolInfo::PROTOCOL_685 => LevelSoundIds685::class,
			ProtocolInfo::PROTOCOL_671 => LevelSoundIds671::class,
			ProtocolInfo::PROTOCOL_662 => LevelSoundIds662::class,
			ProtocolInfo::PROTOCOL_630 => LevelSoundIds630::class,
			ProtocolInfo::PROTOCOL_622 => LevelSoundIds622::class,
			ProtocolInfo::PROTOCOL_594 => LevelSoundIds594::class,
			ProtocolInfo::PROTOCOL_575 => LevelSoundIds575::class,
			ProtocolInfo::PROTOCOL_567 => LevelSoundIds567::class,
			ProtocolInfo::PROTOCOL_560 => LevelSoundIds560::class,
			ProtocolInfo::PROTOCOL_486 => LevelSoundIds486::class,
			ProtocolInfo::PROTOCOL_475 => LevelSoundIds475::class,
			ProtocolInfo::PROTOCOL_428 => LevelSoundIds428::class,
			ProtocolInfo::PROTOCOL_407 => LevelSoundIds407::class,
			ProtocolInfo::PROTOCOL_113 => LevelSoundIds113::class,
		], function ($value, string $name) : bool {
			return str_starts_with($name, "SOUND_");
		});

		$this->collect(AvailableCommandsPacket::class, [
			ProtocolInfo::PROTOCOL_2193 => CommandArgumentTypeIds2193::class,
			ProtocolInfo::PROTOCOL_1001 => CommandArgumentTypeIds1001::class,
			ProtocolInfo::PROTOCOL_662 => CommandArgumentTypeIds662::class,
			ProtocolInfo::PROTOCOL_582 => CommandArgumentTypeIds582::class,
			ProtocolInfo::PROTOCOL_527 => CommandArgumentTypeIds527::class,
			ProtocolInfo::PROTOCOL_503 => CommandArgumentTypeIds503::class,
			ProtocolInfo::PROTOCOL_428 => CommandArgumentTypeIds428::class,
			ProtocolInfo::PROTOCOL_407 => CommandArgumentTypeIds407::class
		], function ($value, string $name) : bool {
			return str_starts_with($name, "ARG_TYPE");
		});

		$this->collect(PlayerActionPacket::class, [
			ProtocolInfo::PROTOCOL_818 => PlayerActionIds818::class,
			ProtocolInfo::PROTOCOL_748 => PlayerActionIds748::class,
			ProtocolInfo::PROTOCOL_622 => PlayerActionIds622::class,
			ProtocolInfo::PROTOCOL_618 => PlayerActionIds618::class,
			ProtocolInfo::PROTOCOL_594 => PlayerActionIds594::class,
			ProtocolInfo::PROTOCOL_567 => PlayerActionIds567::class,
			ProtocolInfo::PROTOCOL_527 => PlayerActionIds527::class,
			ProtocolInfo::PROTOCOL_428 => PlayerActionIds428::class,
			ProtocolInfo::PROTOCOL_419 => PlayerActionIds419::class,
			ProtocolInfo::PROTOCOL_407 => PlayerActionIds407::class,
			ProtocolInfo::PROTOCOL_113 => PlayerActionIds113::class
		], function ($value, string $name) : bool {
			return str_starts_with($name, "ACTION_");
		});

		$this->collect(ResourcePackType::class, [
			ProtocolInfo::PROTOCOL_407 => ResourcePackTypeIds407::class
		]);

		$this->collect(TextPacket::class, [
			ProtocolInfo::PROTOCOL_554 => TextPacketTypeIds554::class,
			ProtocolInfo::PROTOCOL_407 => TextPacketTypeIds407::class,
			ProtocolInfo::PROTOCOL_113 => TextPacketTypeIds113::class
		], function ($value, string $name) : bool {
			return str_starts_with($name, "TYPE_");
		});

		$this->collect(Particle::class, [
			ProtocolInfo::PROTOCOL_944 => ParticleIds944::class,
			ProtocolInfo::PROTOCOL_844 => ParticleIds844::class,
			ProtocolInfo::PROTOCOL_766 => ParticleIds766::class,
			ProtocolInfo::PROTOCOL_712 => ParticleIds712::class,
			ProtocolInfo::PROTOCOL_662 => ParticleIds662::class,
			ProtocolInfo::PROTOCOL_649 => ParticleIds649::class,
			ProtocolInfo::PROTOCOL_630 => ParticleIds630::class,
			ProtocolInfo::PROTOCOL_448 => ParticleIds448::class,
			ProtocolInfo::PROTOCOL_431 => ParticleIds431::class,
			ProtocolInfo::PROTOCOL_407 => ParticleIds407::class,
			ProtocolInfo::PROTOCOL_113 => ParticleIds113::class
		], function ($value, string $name) : bool {
			return str_starts_with($name, "TYPE_");
		});

		$this->collect(ItemStackRequestActionType::class, [
			ProtocolInfo::PROTOCOL_2168 => ItemStackRequestActionType2168::class,
			ProtocolInfo::PROTOCOL_486 => ItemStackRequestActionType486::class,
			ProtocolInfo::PROTOCOL_428 => ItemStackRequestActionType428::class,
			ProtocolInfo::PROTOCOL_422 => ItemStackRequestActionType422::class,
			ProtocolInfo::PROTOCOL_407 => ItemStackRequestActionType407::class
		]);

		$this->collect(BossBarColor::class, [
			ProtocolInfo::PROTOCOL_622 => BossBarColor622::class,
			ProtocolInfo::PROTOCOL_113 => BossBarColor113::class,
		]);

		$this->collect(EntityMetadataFlags::class, [
			ProtocolInfo::PROTOCOL_975 => ActorFlags975::class,
			ProtocolInfo::PROTOCOL_897 => ActorFlags897::class,
			ProtocolInfo::PROTOCOL_844 => ActorFlags844::class,
			ProtocolInfo::PROTOCOL_818 => ActorFlags818::class,
			ProtocolInfo::PROTOCOL_800 => ActorFlags800::class,
			ProtocolInfo::PROTOCOL_786 => ActorFlags786::class,
			ProtocolInfo::PROTOCOL_776 => ActorFlags776::class,
			ProtocolInfo::PROTOCOL_671 => ActorFlags671::class,
			ProtocolInfo::PROTOCOL_630 => ActorFlags630::class,
			ProtocolInfo::PROTOCOL_594 => ActorFlags594::class,
			ProtocolInfo::PROTOCOL_575 => ActorFlags575::class,
			ProtocolInfo::PROTOCOL_560 => ActorFlags560::class,
			ProtocolInfo::PROTOCOL_475 => ActorFlags475::class,
			ProtocolInfo::PROTOCOL_428 => ActorFlags428::class,
			ProtocolInfo::PROTOCOL_407 => ActorFlags407::class,
			ProtocolInfo::PROTOCOL_113 => ActorFlags113::class
		]);

		$this->collect(EntityMetadataProperties::class, [
			ProtocolInfo::PROTOCOL_975 => ActorProperties975::class,
			ProtocolInfo::PROTOCOL_924 => ActorProperties924::class,
			ProtocolInfo::PROTOCOL_800 => ActorProperties800::class,
			ProtocolInfo::PROTOCOL_776 => ActorProperties776::class,
			ProtocolInfo::PROTOCOL_712 => ActorProperties712::class,
			ProtocolInfo::PROTOCOL_594 => ActorProperties594::class,
			ProtocolInfo::PROTOCOL_428 => ActorProperties428::class,
			ProtocolInfo::PROTOCOL_407 => ActorProperties407::class,
			ProtocolInfo::PROTOCOL_113 => ActorProperties113::class
		]);
	}

	public function collect(string $coreClassConstants, array $netClassesConstants, ?Closure $filter = null) : void
	{
		try {
			$coreConstants = (new ReflectionClass($coreClassConstants))->getConstants();
			if ($filter !== null) {
				$coreConstants = array_filter($coreConstants, $filter, ARRAY_FILTER_USE_BOTH);
			}

			foreach ($netClassesConstants as $protocolVersion => $netClassConstants) {
				$netConstants = (new ReflectionClass($netClassConstants))->getConstants();
				$coreToNet = new SplFixedArray(max($coreConstants) + 1);
				$netToCore = new SplFixedArray(max($netConstants) + 1);

				foreach ($coreConstants as $name => $value) {
					if (isset($netConstants[$name])) {
						$coreToNet[$value] = $netConstants[$name];
					}
				}

				foreach ($netConstants as $name => $value) {
					if (isset($coreConstants[$name])) {
						$netToCore[$value] = $coreConstants[$name];
					}
				}

				$protocolValues = new SplFixedArray(2);
				$protocolValues[self::CORE_TO_NETWORK] = $coreToNet;
				$protocolValues[self::NETWORK_TO_CORE] = $netToCore;

				$this->constantsMapping[$coreClassConstants][$protocolVersion] = $protocolValues;
			}

		} catch (\ReflectionException $exception) {
			\GlobalLogger::get()->error($exception->getMessage());
		}
	}

	public function fromNetworkId(string $coreClassConstants, int $id, int $playerProtocol) : int
	{
		try {
			if (!isset($this->constantsMapping[$coreClassConstants])) {
				throw new ConstantTranslatorException("The $coreClassConstants class for translating constants was not found.");
			}

			foreach ($this->constantsMapping[$coreClassConstants] as $protocol => $ids) {
				if ($playerProtocol >= $protocol) {
					return
						$ids[self::NETWORK_TO_CORE][$id] ??
						throw new ConstantTranslatorException("ID not found $id for class $coreClassConstants");
				}
			}

			throw new ConstantTranslatorException("ID not found $id for class $coreClassConstants");
		} catch (ConstantTranslatorException $exception) {
			throw PacketDecodeException::wrap($exception);
		}
	}

	public function toNetworkId(string $coreClassConstants, int $id, int $playerProtocol, ?int $default = null) : int
	{
		if (!isset($this->constantsMapping[$coreClassConstants])) {
			throw new ConstantTranslatorException("The $coreClassConstants class for translating constants was not found.");
		}

		foreach ($this->constantsMapping[$coreClassConstants] as $protocol => $ids) {
			if ($playerProtocol >= $protocol) {
				return
					$ids[self::CORE_TO_NETWORK][$id] ??
					$ids[self::CORE_TO_NETWORK][$default] ??
					$default ??
					throw new ConstantTranslatorException("ID not found $id for class $coreClassConstants");
			}
		}

		throw new ConstantTranslatorException("ID not found $id for class $coreClassConstants");
	}

	public function fromNetworkIds(string $coreClassConstants, int $playerProtocol) : array
	{
		try {
			if (!isset($this->constantsMapping[$coreClassConstants])) {
				throw new ConstantTranslatorException("The $coreClassConstants class for translating constants was not found.");
			}

			foreach ($this->constantsMapping[$coreClassConstants] as $protocol => $ids) {
				if ($playerProtocol >= $protocol) {
					return $ids[self::NETWORK_TO_CORE]->toArray();
				}
			}

			return [];
		} catch (ConstantTranslatorException $exception) {
			throw PacketDecodeException::wrap($exception);
		}
	}

	public function toNetworkIds(string $coreClassConstants, int $playerProtocol) : array
	{
		if (!isset($this->constantsMapping[$coreClassConstants])) {
			throw new ConstantTranslatorException("The $coreClassConstants class for translating constants was not found.");
		}

		foreach ($this->constantsMapping[$coreClassConstants] as $protocol => $ids) {
			if ($playerProtocol >= $protocol) {
				return $ids[self::CORE_TO_NETWORK]->toArray();
			}
		}

		return [];
	}
}
