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

namespace pocketmine\level\generator\vanilla\event\player;

use pocketmine\level\generator\vanilla\event\Event;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\resourcepacks\ResourcePack;

use function array_unshift;

/**
 * Called after a player authenticates and is being offered resource packs to download.
 *
 * This event should be used to decide which resource packs to offer the player and whether to require the player to
 * download the packs before they can join the server.
 */
class PlayerResourcePackOfferEvent extends Event
{
	/**
	 * @param ResourcePack[] $resourcePacks
	 * @param string[]       $encryptionKeys pack UUID => key, leave unset for any packs that are not encrypted
	 *
	 * @phpstan-param list<ResourcePack>    $resourcePacks
	 * @phpstan-param array<string, string> $encryptionKeys
	 */
	public function __construct(
		private readonly Player $player,
		private array $resourcePacks,
		private array $encryptionKeys,
		private bool $mustAccept
	) {
	}

	public function getPlayer() : Player
	{
		return $this->player;
	}

	/**
	 * Adds a resource pack to the top of the stack.
	 * The resources in this pack will be applied over the top of any existing packs.
	 */
	public function addResourcePack(ResourcePack $entry, ?string $encryptionKey = null) : void
	{
		array_unshift($this->resourcePacks, $entry);
		if ($encryptionKey !== null) {
			$this->encryptionKeys[$entry->getPackId()] = $encryptionKey;
		}
	}

	/**
	 * Sets the resource packs to offer. Packs are applied from the highest key to the lowest, with each pack
	 * overwriting any resources from the previous pack. This means that the pack at index 0 gets the final say on which
	 * resources are used.
	 *
	 * @param ResourcePack[] $resourcePacks
	 * @param string[]       $encryptionKeys pack UUID => key, leave unset for any packs that are not encrypted
	 *
	 * @phpstan-param list<ResourcePack>    $resourcePacks
	 * @phpstan-param array<string, string> $encryptionKeys
	 */
	public function setResourcePacks(array $resourcePacks, array $encryptionKeys) : void
	{
		$this->resourcePacks = $resourcePacks;
		$this->encryptionKeys = $encryptionKeys;
	}

	/**
	 * @return ResourcePack[]
	 * @phpstan-return list<ResourcePack>
	 */
	public function getResourcePacks() : array
	{
		return $this->resourcePacks;
	}

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
	 */
	public function getEncryptionKeys() : array
	{
		return $this->encryptionKeys;
	}

	public function setMustAccept(bool $mustAccept) : void
	{
		$this->mustAccept = $mustAccept;
	}

	public function mustAccept() : bool
	{
		return $this->mustAccept;
	}
}
