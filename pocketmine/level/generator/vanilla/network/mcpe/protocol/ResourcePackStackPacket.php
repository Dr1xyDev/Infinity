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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\Experiments;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\resourcepacks\ResourcePackStackEntry;

use function count;

class ResourcePackStackPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACK_STACK_PACKET;

	/** @var ResourcePackStackEntry[] */
	public array $resourcePackStack = [];
	/** @var ResourcePackStackEntry[] */
	public array $behaviorPackStack = [];
	public bool $mustAccept = false;
	public bool $isExperimental = false;
	public string $baseGameVersion = ProtocolInfo::MINECRAFT_VERSION_NETWORK;
	public Experiments $experiments;
	public bool $useVanillaEditorPacks;

	/**
	 * @generate-create-func
	 * @param ResourcePackStackEntry[] $resourcePackStack
	 * @param ResourcePackStackEntry[] $behaviorPackStack
	 */
	public static function create(array $resourcePackStack, array $behaviorPackStack, bool $mustAccept, bool $isExperimental, string $baseGameVersion, Experiments $experiments, bool $useVanillaEditorPacks) : self
	{
		$result = new self();
		$result->resourcePackStack = $resourcePackStack;
		$result->behaviorPackStack = $behaviorPackStack;
		$result->mustAccept = $mustAccept;
		$result->isExperimental = $isExperimental;
		$result->baseGameVersion = $baseGameVersion;
		$result->experiments = $experiments;
		$result->useVanillaEditorPacks = $useVanillaEditorPacks;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->mustAccept = $this->getBool();

		if ($this->protocol < ProtocolInfo::PROTOCOL_897) {
			$behaviorPackCount = $this->getUnsignedVarInt();
			while ($behaviorPackCount-- > 0) {
				$this->behaviorPackStack[] = ResourcePackStackEntry::read($this);
			}
		}

		$resourcePackCount = $this->getUnsignedVarInt();
		while ($resourcePackCount-- > 0) {
			$this->resourcePackStack[] = ResourcePackStackEntry::read($this);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			if ($this->protocol < ProtocolInfo::PROTOCOL_419) {
				$this->isExperimental = $this->getBool();
			}

			$this->baseGameVersion = $this->getString();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
				$this->experiments = Experiments::read($this);
				if ($this->protocol >= ProtocolInfo::PROTOCOL_671) {
					$this->useVanillaEditorPacks = $this->getBool();
				}
			}
		}
	}

	protected function encodePayload() : void
	{
		$this->putBool($this->mustAccept);

		if ($this->protocol < ProtocolInfo::PROTOCOL_897) {
			$this->putUnsignedVarInt(count($this->behaviorPackStack));
			foreach ($this->behaviorPackStack as $entry) {
				$entry->write($this);
			}
		}

		$this->putUnsignedVarInt(count($this->resourcePackStack));
		foreach ($this->resourcePackStack as $entry) {
			$entry->write($this);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			if ($this->protocol < ProtocolInfo::PROTOCOL_419) {
				$this->putBool($this->isExperimental);
			}
			$this->putString($this->baseGameVersion);
			if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
				$this->experiments->write($this);
				if ($this->protocol >= ProtocolInfo::PROTOCOL_671) {
					$this->putBool($this->useVanillaEditorPacks);
				}
			}
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleResourcePackStack($this);
	}
}
