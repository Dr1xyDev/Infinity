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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CraftingDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStack;

final class FurnaceRecipe extends RecipeWithTypeId{
	public function __construct(
		int $typeId,
		private int $inputId,
		private ?int $inputMeta,
		private ItemStack $result,
		private string $blockName
	){
		parent::__construct($typeId);
	}

	public function getInputId() : int{
		return $this->inputId;
	}

	public function getInputMeta() : ?int{
		return $this->inputMeta;
	}

	public function getResult() : ItemStack{
		return $this->result;
	}

	public function getBlockName() : string{
		return $this->blockName;
	}

	public static function decode(int $typeId, NetworkBinaryStream $in) : self{
		$inputId = $in->getVarInt();
		$inputData = null;
		if($typeId === CraftingDataPacket::ENTRY_FURNACE_DATA){
			$inputData = $in->getVarInt();
		}
		$output = $in->getItemStackWithoutStackId();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_407) {
			$block = $in->getString();
		}

		return new self($typeId, $inputId, $inputData, $output, $block ?? FurnaceRecipeBlockName::FURNACE);
	}

	public function encode(NetworkBinaryStream $out) : void{
		$out->putVarInt($this->inputId);
		if($this->getTypeId() === CraftingDataPacket::ENTRY_FURNACE_DATA){
			$out->putVarInt($this->inputMeta);
		}
		$out->putItemStackWithoutStackId($this->result);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_407) {
			$out->putString($this->blockName);
		}
	}
}
