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

/**
 * Sent by 1.26.50+ clients when they change the recipe book tab / filter / layout of a furnace-like UI.
 * The server doesn't need to do anything with it.
 */
class SetPlayerFurnaceOptionsPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::SET_PLAYER_FURNACE_OPTIONS_PACKET;

	public const FURNACE_TYPE_NONE = 0;
	public const FURNACE_TYPE_FURNACE = 1;
	public const FURNACE_TYPE_BLAST_FURNACE = 2;
	public const FURNACE_TYPE_SMOKER = 3;

	public const LEFT_TAB_NONE = 0;
	public const LEFT_TAB_RECIPE_FOOD = 1;
	public const LEFT_TAB_RECIPE_ITEMS = 2;
	public const LEFT_TAB_RECIPE_BLOCKS = 3;
	public const LEFT_TAB_RECIPE_SEARCH = 4;
	public const LEFT_TAB_INVENTORY = 5;

	public const LAYOUT_NONE = 0;
	public const LAYOUT_INVENTORY_ONLY = 1;
	public const LAYOUT_DEFAULT = 2;

	private int $furnaceType;
	private int $leftTab;
	private bool $filtering;
	private int $layout;

	/**
	 * @generate-create-func
	 */
	public static function create(int $furnaceType, int $leftTab, bool $filtering, int $layout) : self{
		$result = new self();
		$result->furnaceType = $furnaceType;
		$result->leftTab = $leftTab;
		$result->filtering = $filtering;
		$result->layout = $layout;
		return $result;
	}

	public function getFurnaceType() : int{ return $this->furnaceType; }

	public function getLeftTab() : int{ return $this->leftTab; }

	public function isFiltering() : bool{ return $this->filtering; }

	public function getLayout() : int{ return $this->layout; }

	protected function decodePayload() : void{
		$this->furnaceType = $this->getByte();
		if ($this->furnaceType > self::FURNACE_TYPE_SMOKER) {
			throw new PacketDecodeException("Invalid furnace type $this->furnaceType");
		}
		$this->leftTab = $this->getVarInt();
		$this->filtering = $this->getBool();
		$this->layout = $this->getVarInt();
	}

	protected function encodePayload() : void{
		$this->putByte($this->furnaceType);
		$this->putVarInt($this->leftTab);
		$this->putBool($this->filtering);
		$this->putVarInt($this->layout);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleSetPlayerFurnaceOptions($this);
	}
}
