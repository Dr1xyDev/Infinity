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

use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

/**
 * Sends some (or all) items from the source slot to /dev/null. This happens when the player clicks items into the
 * creative inventory menu in creative mode.
 */
final class DestroyStackRequestAction extends ItemStackRequestAction
{
	use DisappearStackRequestActionTrait;
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::DESTROY;
}
