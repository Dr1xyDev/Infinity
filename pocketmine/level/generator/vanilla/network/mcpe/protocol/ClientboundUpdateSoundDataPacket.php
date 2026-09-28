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
use function array_key_first;
use function array_search;
use function count;

class ClientboundUpdateSoundDataPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_UPDATE_SOUND_DATA_PACKET;

	/** Sound data event types (1.26.40+), also the order of the event slots in the packet */
	public const EVENT_STOP = 0;
	public const EVENT_SET_VOLUME = 1;
	public const EVENT_SET_PITCH = 2;
	public const EVENT_FADE = 3;
	public const EVENT_SEEK_TO = 4;
	public const EVENT_PAUSE = 5;
	public const EVENT_RESUME = 6;

	/** Number of float parameters carried by each event type */
	private const EVENT_PARAMETER_COUNT = [
		self::EVENT_STOP => 0,
		self::EVENT_SET_VOLUME => 1,
		self::EVENT_SET_PITCH => 1,
		self::EVENT_FADE => 2,
		self::EVENT_SEEK_TO => 1,
		self::EVENT_PAUSE => 0,
		self::EVENT_RESUME => 0,
	];

	/** Legacy (pre-1.26.40) sound event names mapped to their 1.26.40 event type */
	private const LEGACY_EVENT_NAMES = [
		"stop" => self::EVENT_STOP,
		"pause" => self::EVENT_PAUSE,
		"resume" => self::EVENT_RESUME,
	];

	private int $serverSoundHandle;
	private string $soundEvent = "";
	/**
	 * @var float[][] event type => parameters
	 * @phpstan-var array<int, list<float>>
	 */
	private array $events = [];

	/**
	 * @generate-create-func
	 */
	public static function create(int $serverSoundHandle, string $soundEvent) : self{
		$result = new self();
		$result->serverSoundHandle = $serverSoundHandle;
		$result->soundEvent = $soundEvent;
		if (isset(self::LEGACY_EVENT_NAMES[$soundEvent])) {
			$result->events[self::LEGACY_EVENT_NAMES[$soundEvent]] = [];
		}
		return $result;
	}

	/**
	 * @param float[][] $events event type => parameters (volume / pitch / [duration, target volume] / seconds)
	 * @phpstan-param array<int, list<float>> $events
	 */
	public static function createEvents(int $serverSoundHandle, array $events) : self{
		$result = new self();
		$result->serverSoundHandle = $serverSoundHandle;
		foreach ($events as $type => $parameters) {
			if (!isset(self::EVENT_PARAMETER_COUNT[$type]) || count($parameters) !== self::EVENT_PARAMETER_COUNT[$type]) {
				throw new \InvalidArgumentException("Invalid sound data event $type");
			}
			$result->events[$type] = $parameters;
		}
		$result->soundEvent = (string) (array_search(array_key_first($events), self::LEGACY_EVENT_NAMES, true) ?: "");
		return $result;
	}

	public function getServerSoundHandle() : int{ return $this->serverSoundHandle; }

	public function getSoundEvent() : string{ return $this->soundEvent; }

	/**
	 * @return float[][]
	 * @phpstan-return array<int, list<float>>
	 */
	public function getEvents() : array{ return $this->events; }

	protected function decodePayload() : void{
		$this->serverSoundHandle = $this->getLLong();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->events = [];
			foreach (self::EVENT_PARAMETER_COUNT as $slot => $_) {
				$event = $this->getOptional(function() : array{
					$type = $this->getLInt();
					if (!isset(self::EVENT_PARAMETER_COUNT[$type])) {
						throw new PacketDecodeException("Unknown sound data event type $type");
					}
					$parameters = [];
					for ($i = 0; $i < self::EVENT_PARAMETER_COUNT[$type]; ++$i) {
						$parameters[] = $this->getLFloat();
					}
					return [$type, $parameters];
				});
				if ($event !== null) {
					$this->events[$event[0]] = $event[1];
				}
			}
			$this->soundEvent = (string) (array_search(array_key_first($this->events), self::LEGACY_EVENT_NAMES, true) ?: "");
			return;
		}
		$this->soundEvent = $this->getString();
	}

	protected function encodePayload() : void{
		$this->putLLong($this->serverSoundHandle);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			foreach (self::EVENT_PARAMETER_COUNT as $slot => $_) {
				$this->putOptional($this->events[$slot] ?? null, function(array $parameters) use ($slot) : void{
					$this->putLInt($slot);
					foreach ($parameters as $parameter) {
						$this->putLFloat($parameter);
					}
				});
			}
			return;
		}
		$this->putString($this->soundEvent);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleClientboundUpdateSoundData($this);
	}
}
