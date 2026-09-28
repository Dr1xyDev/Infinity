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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\cereal;

/**
 * These values aren't present in the spec.
 * As of 1.26.30, these were obtained from BDS symbols for the following types:
 *
 * Bedrock::DDUI::DataStoreChange
 * cereal::DynamicValue
 *
 * The positions of the types in the std::variant used by cereal::DynamicValue are the values of this enum.
 *
 * Since cereal::DynamicValue appears non-specific to DDUI, it's possible this type may appear elsewhere in the protocol
 * in the future.
 */
final class DynamicValueType{
	public const NULL = 0;
	public const BOOL = 1;
	public const LONG = 2;
	public const DOUBLE = 3;
	public const STRING = 4;
	public const LIST = 5;
	public const MAP = 6;
}
