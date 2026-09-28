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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\level\generator\vanilla\utils\UUID;
use function count;

final class ShapedRecipe extends RecipeWithTypeId{
	private string $blockName;

	/**
	 * @param RecipeIngredient[][] $input
	 * @param ItemStack[]          $output
	 * @phpstan-param list<list<RecipeIngredient>> $input
	 * @phpstan-param list<ItemStack> $output
	 */
	public function __construct(
		int $typeId,
		private string $recipeId,
		private array $input,
		private array $output,
		private UUID $uuid,
		string $blockType, //TODO: rename this
		private int $priority,
		private bool $symmetric,
		private RecipeUnlockingRequirement $unlockingRequirement,
		private int $recipeNetId
	){
		parent::__construct($typeId);
		$rows = count($input);
		if($rows < 1 || $rows > 3){
			throw new \InvalidArgumentException("Expected 1, 2 or 3 input rows");
		}
		$columns = null;
		foreach($input as $rowNumber => $row){
			if($columns === null){
				$columns = count($row);
			}elseif(count($row) !== $columns){
				throw new \InvalidArgumentException("Expected each row to be $columns columns, but have " . count($row) . " in row $rowNumber");
			}
		}
		$this->blockName = $blockType;
	}

	public function getRecipeId() : string{
		return $this->recipeId;
	}

	public function getWidth() : int{
		return count($this->input[0]);
	}

	public function getHeight() : int{
		return count($this->input);
	}

	/**
	 * @return RecipeIngredient[][]
	 * @phpstan-return list<list<RecipeIngredient>>
	 */
	public function getInput() : array{
		return $this->input;
	}

	/**
	 * @return ItemStack[]
	 * @phpstan-return list<ItemStack>
	 */
	public function getOutput() : array{
		return $this->output;
	}

	public function getUuid() : UUID{
		return $this->uuid;
	}

	public function getBlockName() : string{
		return $this->blockName;
	}

	public function getPriority() : int{
		return $this->priority;
	}

	public function isSymmetric() : bool{ return $this->symmetric; }

	public function getUnlockingRequirement() : RecipeUnlockingRequirement{ return $this->unlockingRequirement; }

	public function getRecipeNetId() : int{
		return $this->recipeNetId;
	}

	public static function decode(int $recipeType, NetworkBinaryStream $in) : self{
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_407) {
			$recipeId = $in->getString();
		}

		$width = $in->getVarInt();
		$height = $in->getVarInt();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$count = $in->getUnsignedVarInt();
			if ($count !== $width * $height) {
				throw new PacketDecodeException("Provided ingredient count $count does not match width $width * height $height");
			}
		}
		$input = [];
		for($row = 0; $row < $height; ++$row){
			for($column = 0; $column < $width; ++$column){
				$input[$row][$column] = $in->getRecipeIngredient();
			}
		}

		$output = [];
		for($k = 0, $resultCount = $in->getUnsignedVarInt(); $k < $resultCount; ++$k){
			$output[] = $in->getItemStackWithoutStackId();
		}
		$uuid = $in->getUUID();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_407) {
			$block = $in->getString();
			$priority = $in->getVarInt();
			if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_671) {
				$symmetric = $in->getBool();
				if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
					$unlockingRequirement = $in->getOptional(fn() => RecipeUnlockingRequirement::read($in));
				} elseif ($in->getProtocol() >= ProtocolInfo::PROTOCOL_685) {
					$unlockingRequirement = RecipeUnlockingRequirement::read($in);
				}
			}

			$recipeNetId = $in->readRecipeNetId();
		}

		return new self(
			$recipeType,
			$recipeId ?? "",
			$input,
			$output,
			$uuid,
			$block ?? CraftingRecipeBlockName::CRAFTING_TABLE,
			$priority ?? 1,
			$symmetric ?? true,
			$unlockingRequirement ?? new RecipeUnlockingRequirement([]),
			$recipeNetId ?? 1
		);
	}

	public function encode(NetworkBinaryStream $out) : void{
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_407) {
			$out->putString($this->recipeId);
		}

		$out->putVarInt($this->getWidth());
		$out->putVarInt($this->getHeight());
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$out->putUnsignedVarInt($this->getWidth() * $this->getHeight());
		}
		foreach ($this->input as $row) {
			foreach ($row as $ingredient) {
				$out->putRecipeIngredient($ingredient);
			}
		}

		$out->putUnsignedVarInt(count($this->output));
		foreach ($this->output as $item) {
			$out->putItemStackWithoutStackId($item);
		}

		$out->putUUID($this->uuid);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_407) {
			$out->putString($this->blockName);
			$out->putVarInt($this->priority);
			if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_671) {
				$out->putBool($this->symmetric);
				if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
					$out->putOptional($this->unlockingRequirement, fn(RecipeUnlockingRequirement $v) => $v->write($out));
				} elseif ($out->getProtocol() >= ProtocolInfo::PROTOCOL_685) {
					$this->unlockingRequirement->write($out);
				}
			}

			$out->writeRecipeNetId($this->recipeNetId);
		}
	}
}
