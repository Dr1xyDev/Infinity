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

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\RecipeIngredient;
use function count;

/**
 * Not clear what this is needed for, but it is very clearly marked as deprecated, so hopefully it'll go away before I
 * have to write a proper description for it.
 */
final class DeprecatedCraftingResultsStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::CRAFTING_RESULTS_DEPRECATED_ASK_TY_LAING;

	/**
	 * @param ItemStack[] $results
	 */
	public function __construct(
		private array $results,
		private int $iterations
	) {
	}

	/** @return Item[] */
	public function getResults() : array
	{
		return $this->results;
	}

	public function getIterations() : int
	{
		return $this->iterations;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$results = [];
		$len = $in->getUnsignedVarInt();
		if ($len > 256) {
			throw new PacketDecodeException("Too many crafting results: $len");
		}
		for ($i = 0; $i < $len; ++$i) {
			if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
				//since 1.26.40 the results are item descriptors instead of item stacks; the server doesn't use them
				$in->getStackRequestIngredient();
				$in->getUnsignedVarInt(); //block runtime ID
				$in->getString(); //extra data
				$results[] = ItemStack::null();
			} else {
				$results[] = $in->getItemStackWithoutStackId();
			}
		}
		$iterations = $in->getByte();
		return new self($results, $iterations);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putUnsignedVarInt(count($this->results));
		foreach ($this->results as $result) {
			if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
				$out->putStackRequestIngredient(new RecipeIngredient(null, 0));
				$out->putUnsignedVarInt(0);
				$out->putString("");
			} else {
				$out->putItemStackWithoutStackId($result);
			}
		}
		$out->putByte($this->iterations);
	}
}
