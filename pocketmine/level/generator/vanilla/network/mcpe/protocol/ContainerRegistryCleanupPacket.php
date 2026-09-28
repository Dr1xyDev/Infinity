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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\FullContainerName;

use function count;

class ContainerRegistryCleanupPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CONTAINER_REGISTRY_CLEANUP_PACKET;
	/** @var FullContainerName[] */
	private array $removedContainers = [];
	/**
	 * @generate-create-func
	 * @param FullContainerName[] $removedContainers
	 */
	public static function create(array $removedContainers) : self
	{
		$result = new self();
		$result->removedContainers = $removedContainers;
		return $result;
	}
	/**
	 * @return FullContainerName[]
	 */
	public function getRemovedContainers() : array
	{
		return $this->removedContainers;
	}

	protected function decodePayload() : void
	{
		$this->removedContainers = [];
		for ($i = 0, $len = $this->getUnsignedVarInt(); $i < $len; ++$i) {
			$this->removedContainers[] = FullContainerName::read($this);
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt(count($this->removedContainers));
		foreach ($this->removedContainers as $container) {
			$container->write($this);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleContainerRegistryCleanup($this);
	}
}
