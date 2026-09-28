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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\inventory\EnchantInventory;
use pocketmine\level\generator\vanilla\inventory\ExactRecipeIngredient;
use pocketmine\level\generator\vanilla\inventory\FakeInventory;
use pocketmine\level\generator\vanilla\inventory\FakeResultInventory;
use pocketmine\level\generator\vanilla\inventory\MetaWildcardRecipeIngredient;
use pocketmine\level\generator\vanilla\inventory\RecipeIngredient;
use pocketmine\level\generator\vanilla\inventory\TagWildcardRecipeIngredient;
use pocketmine\level\generator\vanilla\inventory\transaction\action\CreativeInventoryAction;
use pocketmine\level\generator\vanilla\inventory\transaction\action\DropItemAction;
use pocketmine\level\generator\vanilla\inventory\transaction\action\EnchantAction;
use pocketmine\level\generator\vanilla\inventory\transaction\action\InventoryAction;
use pocketmine\level\generator\vanilla\inventory\transaction\action\SlotChangeAction;
use pocketmine\level\generator\vanilla\item\Durable;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\LongTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\RuntimeBlockMapping;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GameMode as ProtocolGameMode;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\NetworkInventoryAction;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\IntIdMetaItemDescriptor;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\RecipeIngredient as ProtocolRecipeIngredient;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\TagItemDescriptor;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\SingletonTrait;
use function get_class;

class TypeConverter
{
	use SingletonTrait;

	private const DAMAGE_TAG = "Damage"; //TAG_Int
	private const DAMAGE_TAG_CONFLICT_RESOLUTION = "___Damage_ProtocolCollisionResolution___";
	private const PM_ID_TAG = "___Id___";
	private const PM_META_TAG = "___Meta___";
	private const string HAS_CONVERTED_NAME_TAG = "HasConvertedName";
	private const string REAL_CUSTOM_NAME_TAG = "RealName";

	private const RECIPE_INPUT_WILDCARD_META = 0x7fff;

	public function __construct()
	{
		//NOOP
	}

	/**
	 * Returns a client-friendly gamemode of the specified real gamemode
	 * This function takes care of handling gamemodes known to MCPE (as of 1.1.0.3, that includes Survival, Creative and Adventure)
	 *
	 * @internal
	 */
	public function coreGameModeToProtocol(int $gamemode) : int
	{
		return match($gamemode) {
			Player::SURVIVAL => ProtocolGameMode::SURVIVAL,
			//TODO: native spectator support
			Player::CREATIVE, Player::SPECTATOR => ProtocolGameMode::CREATIVE,
			Player::ADVENTURE => ProtocolGameMode::ADVENTURE,
		};
	}

	public function protocolGameModeToCore(int $gameMode) : ?int
	{
		return match($gameMode) {
			ProtocolGameMode::SURVIVAL => Player::SURVIVAL,
			ProtocolGameMode::CREATIVE => Player::CREATIVE,
			ProtocolGameMode::ADVENTURE => Player::ADVENTURE,
			ProtocolGameMode::SURVIVAL_VIEWER, ProtocolGameMode::CREATIVE_VIEWER, ProtocolGameMode::SPECTATOR => Player::SPECTATOR,
			//TODO: native spectator support
			default => null,
		};
	}

	public function coreRecipeIngredientToNet(?RecipeIngredient $ingredient, int $protocol) : ProtocolRecipeIngredient{
		if($ingredient === null){
			return new ProtocolRecipeIngredient(null, 0);
		}

		if ($ingredient instanceof MetaWildcardRecipeIngredient || $ingredient instanceof ExactRecipeIngredient) {
			if ($ingredient instanceof MetaWildcardRecipeIngredient) {
				$item = ItemFactory::get($ingredient->getItemId(), -1);
			} else {
				$item = $ingredient->getItem();
			}

			$internalId = $item->getId();
			$internalMeta = $item->getDamage();
			$protocolItem = $item->getItemProtocol($protocol);
			if ($protocolItem !== null) {
				$internalId = $protocolItem->getId();
				$internalMeta = $protocolItem->getMeta();
			}

			if ($ingredient instanceof MetaWildcardRecipeIngredient) {
				$internalMeta = -1;
			}

			if($internalId === 0){
				return new ProtocolRecipeIngredient(null, 0);
			}

			if ($protocol >= ProtocolInfo::PROTOCOL_419) {
				if ($internalMeta === -1) {
					[$id,] = ItemTranslator::getInstance($protocol)->toNetworkId($internalId, 0);
					$meta = self::RECIPE_INPUT_WILDCARD_META;
				} else {
					[$id, $meta] = ItemTranslator::getInstance($protocol)->toNetworkId($internalId, $internalMeta);
				}
			} else {
				[$id, $meta] = [$internalId, $internalMeta & self::RECIPE_INPUT_WILDCARD_META];
			}

			$descriptor = new IntIdMetaItemDescriptor($id, $meta);
		}elseif($ingredient instanceof TagWildcardRecipeIngredient){
			$descriptor = new TagItemDescriptor($ingredient->getTagName());
		}else{
			throw new \LogicException("Unsupported recipe ingredient type " . get_class($ingredient) . ", only " . ExactRecipeIngredient::class . " and " . MetaWildcardRecipeIngredient::class . " are supported");
		}

		return new ProtocolRecipeIngredient($descriptor, 1);
	}

	public function coreItemStackToNet(Item $itemStack, int $protocol) : ItemStack{
		if($itemStack->isNull()){
			return ItemStack::null();
		}

		$itemStack->checkCompoundTag();
		$nbt = $itemStack->getNamedTag();

		if($nbt->count() === 0){
			$nbt = null;
		}else{
			$nbt = clone $nbt;
		}

		$internalId = $itemStack->getId();
		$internalMeta = $itemStack->getDamage();

		$protocolItem = $itemStack->getItemProtocol($protocol);
		if ($protocolItem !== null) {
			if ($nbt === null) {
				$nbt = new CompoundTag();
			}
			$nbt->setInt(self::PM_ID_TAG, $internalId);
			$nbt->setInt(self::PM_META_TAG, $internalMeta);

			if($protocolItem->hasName()){
				$nbt->setByte(self::HAS_CONVERTED_NAME_TAG, 1);

				if($nbt->hasTag(Item::TAG_DISPLAY, CompoundTag::class)){
					$nbt->getCompoundTag(Item::TAG_DISPLAY)->setString(Item::TAG_DISPLAY_NAME, $protocolItem->getName());
				}else{
					$nbt->setTag(new CompoundTag(Item::TAG_DISPLAY, [
						new StringTag(Item::TAG_DISPLAY_NAME, $protocolItem->getName()),
					]));
				}

				if($itemStack->hasCustomName()){
					$nbt->setString(self::REAL_CUSTOM_NAME_TAG, $itemStack->getCustomName());
				}
			}

			[$internalId, $internalMeta] = [$protocolItem->getId(), $protocolItem->getMeta()];
		}

		if ($protocol >= ProtocolInfo::PROTOCOL_419) {
			$itemTranslator = ItemTranslator::getInstance($protocol);
			$idMeta = $itemTranslator->toNetworkIdQuiet($internalId, $internalMeta);
			if ($idMeta === null) {
				//Display unmapped items as INFO_UPDATE, but stick something in their NBT to make sure they don't stack with
				//other unmapped items.
				[$id, $meta] = $itemTranslator->toNetworkId(ItemIds::INFO_UPDATE, 0);
				if ($nbt === null) {
					$nbt = new CompoundTag();
				}
				$nbt->setInt(self::PM_ID_TAG, $internalId);
				$nbt->setInt(self::PM_META_TAG, $internalMeta);
			} else {
				[$id, $meta] = $idMeta;
			}
		} else {
			if (LegacyItemIdToStringIdMap::getInstance($protocol)->legacyToString($internalId) === null) {
				[$internalId, $internalMeta] = [ItemIds::INFO_UPDATE, 0];
			}

			[$id, $meta] = [$internalId, $internalMeta];
		}

		if ($itemStack instanceof Durable && $itemStack->getDamage() > 0) {
			if ($nbt !== null) {
				if (($existing = $nbt->getTag(self::DAMAGE_TAG)) !== null) {
					$nbt->removeTag(self::DAMAGE_TAG);
					$existing->setName(self::DAMAGE_TAG_CONFLICT_RESOLUTION);
					$nbt->setTag($existing);
				}
			} else {
				$nbt = new CompoundTag();
			}
			$nbt->setInt(self::DAMAGE_TAG, $itemStack->getDamage());
		}

		if ($protocol < ProtocolInfo::PROTOCOL_407 && $itemStack->getId() === ItemIds::FILLED_MAP && $nbt !== null) {
			if ($nbt->hasTag("map_uuid", LongTag::class)) {
				$uuid = $nbt->getLong("map_uuid");
				$nbt->setString("map_uuid", (string) $uuid, true);
			}
		}

		if ($nbt !== null && $nbt->getName() !== "tag") {
			$nbt->setName("tag");
		}

		$blockRuntimeId = 0;
		if ($internalId < 256) {
			$block = $itemStack->getBlock();
			if ($block->getId() !== BlockIds::AIR) {
				if ($protocol >= ProtocolInfo::PROTOCOL_431) {
					$blockRuntimeId = RuntimeBlockMapping::getInstance($protocol)->toRuntimeId($block->getFullId());
					$meta = 0;
				} else {
					$blockRuntimeId = $block->getFullId();
				}
			}
		}

		return new ItemStack(
			$id,
			$meta,
			$itemStack->getCount(),
			$blockRuntimeId,
			$nbt,
			[],
			[],
			$itemStack->getId() === ItemIds::SHIELD ? 0 : null
		);
	}

	/**
	 * @throws TypeConversionException
	 */
	public function netItemStackToCore(ItemStack $itemStack, int $protocol) : Item{
		if($itemStack->getId() === 0){
			return ItemFactory::air();
		}

		$compound = $itemStack->getNbt();

		if ($protocol >= ProtocolInfo::PROTOCOL_419) {
			[$id, $meta] = ItemTranslator::getInstance($protocol)->fromNetworkId($itemStack->getId(), $itemStack->getMeta());
			if ($protocol >= ProtocolInfo::PROTOCOL_431) {
				if ($itemStack->getBlockRuntimeId() !== 0) {
					//blockitem meta is zeroed out by the client, so we have to infer it from the block runtime ID
					$blockFullId = RuntimeBlockMapping::getInstance($protocol)->fromRuntimeId($itemStack->getBlockRuntimeId());
					$meta = $blockFullId & Block::INTERNAL_METADATA_MASK;
				}
			}
		} else {
			[$id, $meta] = [$itemStack->getId(), $itemStack->getMeta()];

			if($meta === self::RECIPE_INPUT_WILDCARD_META){
				$meta = -1;
			}
		}

		if($compound !== null){
			$compound = clone $compound;
			if(($idTag = $compound->getTag(self::PM_ID_TAG)) instanceof IntTag){
				$id = $idTag->getValue();
				$compound->removeTag(self::PM_ID_TAG);
			}
			if(($damageTag = $compound->getTag(self::DAMAGE_TAG)) instanceof IntTag){
				$meta = $damageTag->getValue();
				$compound->removeTag(self::DAMAGE_TAG);
				if(($conflicted = $compound->getTag(self::DAMAGE_TAG_CONFLICT_RESOLUTION)) !== null){
					$compound->removeTag(self::DAMAGE_TAG_CONFLICT_RESOLUTION);
					$conflicted->setName(self::DAMAGE_TAG);
					$compound->setTag($conflicted);
				}
			}
			if (($metaTag = $compound->getTag(self::PM_META_TAG)) instanceof IntTag) {
				$meta = $metaTag->getValue();
				$compound->removeTag(self::PM_META_TAG);
			}

			if($compound->getByte(self::HAS_CONVERTED_NAME_TAG, 0) === 1 && $compound->hasTag(Item::TAG_DISPLAY, CompoundTag::class)){
				$compound->removeTag(self::HAS_CONVERTED_NAME_TAG);

				if($compound->hasTag(self::REAL_CUSTOM_NAME_TAG, StringTag::class)){
					$compound->getCompoundTag(Item::TAG_DISPLAY)->setString(Item::TAG_DISPLAY_NAME, $compound->getString(self::REAL_CUSTOM_NAME_TAG));
					$compound->removeTag(self::REAL_CUSTOM_NAME_TAG);
				}else{
					$compound->getCompoundTag(Item::TAG_DISPLAY)->removeTag(Item::TAG_DISPLAY_NAME);
					if($compound->getCompoundTag(Item::TAG_DISPLAY)->count() === 0){
						$compound->removeTag(Item::TAG_DISPLAY);
					}
				}
			}

			if ($protocol < ProtocolInfo::PROTOCOL_407 && $id === ItemIds::FILLED_MAP && $compound->hasTag("map_uuid", StringTag::class)) {
				$compound->setLong("map_uuid", (int) $compound->getString("map_uuid"), true);
			}

			if($compound->count() === 0){
				$compound = null;
			} else {
				$compound->setName("");
			}
		}

		try{
			return ItemFactory::get(
				$id,
				$meta,
				$itemStack->getCount(),
				$compound
			);
		}catch(\RuntimeException $e){
			throw TypeConversionException::wrap($e, "Bad itemstack NBT data");
		}
	}

	/**
	 * @throws TypeConversionException
	 */
	public function createInventoryAction(NetworkInventoryAction $action, Player $player) : ?InventoryAction
	{
		if($action->oldItem->getItemStack()->equals($action->newItem->getItemStack())){
			//filter out useless noise in 1.13
			return null;
		}
		try{
			$old = $this->netItemStackToCore($action->oldItem->getItemStack(), $player->getProtocolVersion());
		}catch(TypeConversionException $e){
			throw TypeConversionException::wrap($e, "Inventory action: oldItem");
		}

		try{
			$new = $this->netItemStackToCore($action->newItem->getItemStack(), $player->getProtocolVersion());
		}catch(TypeConversionException $e){
			throw TypeConversionException::wrap($e, "Inventory action: newItem");
		}

		switch ($action->sourceType) {
			case NetworkInventoryAction::SOURCE_CONTAINER:
				[$window, $slotId] = $player->locateWindowAndSlot($action->windowId, $action->inventorySlot);
				if ($window !== null) {
					return new SlotChangeAction($window, $slotId, $old, $new);
				}

				throw new TypeConversionException("No open container with window ID $action->windowId");
			case NetworkInventoryAction::SOURCE_WORLD:
				if ($action->inventorySlot !== NetworkInventoryAction::ACTION_MAGIC_SLOT_DROP_ITEM) {
					throw new TypeConversionException("Only expecting drop-item world actions from the client!");
				}

				return new DropItemAction($new);
			case NetworkInventoryAction::SOURCE_CREATIVE:
				switch ($action->inventorySlot) {
					case NetworkInventoryAction::ACTION_MAGIC_SLOT_CREATIVE_DELETE_ITEM:
						$type = CreativeInventoryAction::TYPE_DELETE_ITEM;
						break;
					case NetworkInventoryAction::ACTION_MAGIC_SLOT_CREATIVE_CREATE_ITEM:
						$type = CreativeInventoryAction::TYPE_CREATE_ITEM;
						break;
					default:
						throw new TypeConversionException("Unexpected creative action type $action->inventorySlot");

				}

				return new CreativeInventoryAction($old, $new, $type);
			case NetworkInventoryAction::SOURCE_UNTRACKED_INTERACTION_UI:
			case NetworkInventoryAction::SOURCE_TODO:
				$window = $player->findWindow(FakeInventory::class);

				switch ($action->windowId) {
					case NetworkInventoryAction::SOURCE_TYPE_CRAFTING_ADD_INGREDIENT:
					case NetworkInventoryAction::SOURCE_TYPE_CRAFTING_REMOVE_INGREDIENT:
					case NetworkInventoryAction::SOURCE_TYPE_CONTAINER_DROP_CONTENTS: //TODO: this type applies to all fake windows, not just crafting
						return new SlotChangeAction($window ?? $player->getCraftingGrid(), $action->inventorySlot, $old, $new);
					case NetworkInventoryAction::SOURCE_TYPE_CRAFTING_RESULT:
					case NetworkInventoryAction::SOURCE_TYPE_CRAFTING_USE_INGREDIENT:
						return null;
					case NetworkInventoryAction::SOURCE_TYPE_ENCHANT_OUTPUT:
						if ($window instanceof EnchantInventory) {
							return new EnchantAction($window, $action->inventorySlot, $old, $new);
						} else {
							if ($window === null) {
								throw new TypeConversionException("Window not found");
							} else {
								throw new TypeConversionException("Unexpected fake inventory given. Expected " . EnchantInventory::class . " , given " . get_class($window));
							}
						}
						// no break
					case NetworkInventoryAction::SOURCE_TYPE_FAKE_INVENTORY_INPUT:
					case NetworkInventoryAction::SOURCE_TYPE_FAKE_INVENTORY_MATERIAL:
						return null; // useless noise
					case NetworkInventoryAction::SOURCE_TYPE_FAKE_INVENTORY_RESULT:
						if ($window instanceof FakeResultInventory) {
							if (!$window->onResult($player, $old)) {
								$player->getInventory()->sendContents($player);

								throw new TypeConversionException("Output doesnt match for Player " . $player->getName() . " in " . get_class($window));
							}
						}

						return null;
				}

				throw new TypeConversionException("Player " . $player->getName() . " has no open container with window ID $action->windowId");
			default:
				throw new TypeConversionException("Unknown inventory source type $action->sourceType");
		}
	}
}
