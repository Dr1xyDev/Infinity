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
use function count;

final class RecipeUnlockingRequirement{

	/**
	 * @param RecipeIngredient[]|null $unlockingIngredients
	 * @phpstan-param list<RecipeIngredient>|null $unlockingIngredients
	 */
	public function __construct(
		private ?array $unlockingIngredients
	){}

	/**
	 * @return RecipeIngredient[]|null
	 * @phpstan-return list<RecipeIngredient>|null
	 */
	public function getUnlockingIngredients() : ?array{ return $this->unlockingIngredients; }

	/** Unlocking contexts (1.26.40+) */
	public const CONTEXT_NONE = 0;
	public const CONTEXT_ALWAYS_UNLOCKED = 1;
	public const CONTEXT_PLAYER_IN_WATER = 2;
	public const CONTEXT_PLAYER_HAS_MANY_ITEMS = 3;

	public static function read(NetworkBinaryStream $in) : self{
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$context = $in->getVarInt();
			$ingredients = $in->getOptional(fn() => $in->getList(fn() => $in->getRecipeIngredient(), 256));
			if ($context !== self::CONTEXT_NONE && $ingredients !== null) {
				throw new PacketDecodeException("Unlocking ingredients should only be set when context is NONE");
			}
			//the server only distinguishes "always unlocked" (no ingredients) and "unlocked by ingredients"
			return new self($context === self::CONTEXT_NONE ? ($ingredients ?? []) : null);
		}

		//I don't know what the point of this structure is. It could easily have been a list<RecipeIngredient> instead.
		//It's basically just an optional list, which could have been done by an empty list wherever it's not needed.
		$unlockingContext = $in->getBool();
		$unlockingIngredients = null;
		if(!$unlockingContext){
			$unlockingIngredients = [];
			for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; $i++){
				$unlockingIngredients[] = $in->getRecipeIngredient();
			}
		}

		return new self($unlockingIngredients);
	}

	public function write(NetworkBinaryStream $out) : void{
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$out->putVarInt($this->unlockingIngredients === null ? self::CONTEXT_ALWAYS_UNLOCKED : self::CONTEXT_NONE);
			$out->putOptional($this->unlockingIngredients, fn(array $ingredients) => $out->putList($ingredients, fn(RecipeIngredient $ingredient) => $out->putRecipeIngredient($ingredient)));
			return;
		}

		$out->putBool($this->unlockingIngredients === null);
		if($this->unlockingIngredients !== null){
			$out->putUnsignedVarInt(count($this->unlockingIngredients));
			foreach($this->unlockingIngredients as $ingredient){
				$out->putRecipeIngredient($ingredient);
			}
		}
	}
}
