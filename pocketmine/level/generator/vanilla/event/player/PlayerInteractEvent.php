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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

use function assert;

/**
 * Called when a player interacts or touches a block (including air?)
 */
class PlayerInteractEvent extends PlayerEvent implements Cancellable
{
	public const LEFT_CLICK_BLOCK = 0;
	public const RIGHT_CLICK_BLOCK = 1;
	public const LEFT_CLICK_AIR = 2;
	public const RIGHT_CLICK_AIR = 3;
	public const PHYSICAL = 4;

	/** @var Block */
	protected $blockTouched;

	/** @var Vector3 */
	protected $touchVector;

	/** @var int */
	protected $blockFace;

	/** @var Item */
	protected $item;

	/** @var int */
	protected $action;

	public function __construct(Player $player, Item $item, ?Block $block, ?Vector3 $touchVector, int $face, int $action = PlayerInteractEvent::RIGHT_CLICK_BLOCK)
	{
		assert($block !== null || $touchVector !== null);
		$this->player = $player;
		$this->item = $item;
		$this->blockTouched = $block ?? BlockFactory::get(0, 0, new Position(0, 0, 0, $player->level));
		$this->touchVector = $touchVector ?? new Vector3(0, 0, 0);
		$this->blockFace = $face;
		$this->action = $action;
	}

	public function getAction() : int
	{
		return $this->action;
	}

	public function getItem() : Item
	{
		return $this->item;
	}

	public function getBlock() : Block
	{
		return $this->blockTouched;
	}

	public function getTouchVector() : Vector3
	{
		return $this->touchVector;
	}

	public function getFace() : int
	{
		return $this->blockFace;
	}
}
