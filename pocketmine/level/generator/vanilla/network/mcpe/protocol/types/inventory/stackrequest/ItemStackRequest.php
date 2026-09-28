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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\level\generator\vanilla\network\mcpe\convert\ConstantTranslator;
use pocketmine\level\generator\vanilla\network\mcpe\convert\ConstantTranslatorException;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

use function count;

final class ItemStackRequest
{
	/**
	 * @param ItemStackRequestAction[] $actions
	 * @param string[]                 $filterStrings
	 * @phpstan-param list<string> $filterStrings
	 */
	public function __construct(
		private int $requestId,
		private array $actions,
		private array $filterStrings,
		private int $filterStringCause
	) {
	}

	public function getRequestId() : int
	{
		return $this->requestId;
	}

	/** @return ItemStackRequestAction[] */
	public function getActions() : array
	{
		return $this->actions;
	}

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getFilterStrings() : array
	{
		return $this->filterStrings;
	}

	public function getFilterStringCause() : int
	{
		return $this->filterStringCause;
	}

	private static function readAction(NetworkBinaryStream $in, int $typeId) : ItemStackRequestAction
	{
		$typeId = ConstantTranslator::getInstance()->fromNetworkId(ItemStackRequestActionType::class, $typeId, $in->getProtocol());
		return match($typeId) {
			TakeStackRequestAction::ID => TakeStackRequestAction::read($in),
			PlaceStackRequestAction::ID => PlaceStackRequestAction::read($in),
			SwapStackRequestAction::ID => SwapStackRequestAction::read($in),
			DropStackRequestAction::ID => DropStackRequestAction::read($in),
			DestroyStackRequestAction::ID => DestroyStackRequestAction::read($in),
			CraftingConsumeInputStackRequestAction::ID => CraftingConsumeInputStackRequestAction::read($in),
			CraftingCreateSpecificResultStackRequestAction::ID => CraftingCreateSpecificResultStackRequestAction::read($in),
			LabTableCombineStackRequestAction::ID => LabTableCombineStackRequestAction::read($in),
			BeaconPaymentStackRequestAction::ID => BeaconPaymentStackRequestAction::read($in),
			MineBlockStackRequestAction::ID => MineBlockStackRequestAction::read($in),
			CraftRecipeStackRequestAction::ID => CraftRecipeStackRequestAction::read($in),
			CraftRecipeAutoStackRequestAction::ID => CraftRecipeAutoStackRequestAction::read($in),
			CreativeCreateStackRequestAction::ID => CreativeCreateStackRequestAction::read($in),
			CraftRecipeOptionalStackRequestAction::ID => CraftRecipeOptionalStackRequestAction::read($in),
			GrindstoneStackRequestAction::ID => GrindstoneStackRequestAction::read($in),
			LoomStackRequestAction::ID => LoomStackRequestAction::read($in),
			DeprecatedCraftingNonImplementedStackRequestAction::ID => DeprecatedCraftingNonImplementedStackRequestAction::read($in),
			DeprecatedCraftingResultsStackRequestAction::ID => DeprecatedCraftingResultsStackRequestAction::read($in),
			default => throw new PacketDecodeException("Unhandled item stack request action type $typeId"),
		};
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$requestId = $in->readItemStackRequestId();
		$actions = [];
		$len = $in->getUnsignedVarInt();
		if ($len > 256) {
			throw new PacketDecodeException("Too many item stack request actions: $len");
		}
		for ($i = 0; $i < $len; ++$i) {
			if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
				//since 1.26.40: new (renumbered) type ID + the old type ID as the inner type
				$typeId = $in->getUnsignedVarInt();
				$innerTypeId = $in->getByte();
				$coreTypeId = ConstantTranslator::getInstance()->fromNetworkId(ItemStackRequestActionType::class, $typeId, $in->getProtocol());
				if ($innerTypeId !== $coreTypeId) {
					throw new PacketDecodeException("ItemStackRequestAction type mismatch: outer type $typeId, inner type $innerTypeId");
				}
			} else {
				$typeId = $in->getByte();
			}
			$actions[] = self::readAction($in, $typeId);
		}
		$filterStrings = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$filterStrings[] = $in->getString();
		}
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_557) {
			$filterStringCause = $in->getLInt();
		}
		return new self($requestId, $actions, $filterStrings, $filterStringCause ?? 0);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->writeItemStackRequestId($this->requestId);
		$out->putUnsignedVarInt(count($this->actions));
		foreach ($this->actions as $action) {
			try {
				$typeId = ConstantTranslator::getInstance()->toNetworkId(ItemStackRequestActionType::class, $action->getTypeId(), $out->getProtocol());
				if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
					$out->putUnsignedVarInt($typeId);
					$out->putByte($action->getTypeId());
				} else {
					$out->putByte($typeId);
				}
				$action->write($out);
			} catch (ConstantTranslatorException $exception) {
			}
		}
		$out->putUnsignedVarInt(count($this->filterStrings));
		foreach ($this->filterStrings as $string) {
			$out->putString($string);
		}
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_557) {
			$out->putLInt($this->filterStringCause);
		}
	}
}
