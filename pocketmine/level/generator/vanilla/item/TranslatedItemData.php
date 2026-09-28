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

namespace pocketmine\level\generator\vanilla\item;

class TranslatedItemData {

	public static function fromItem(Item $item) : TranslatedItemData{
		return new self(
			$item->getId(),
			$item->getDamage(),
			$item->getName(),
		);
	}

	public function __construct(
		private int $id,
		private ?int $meta,
		private string $name = "",
	){}

	public function getId() : int {
		return $this->id;
	}

	public function getMeta() : ?int {
		return $this->meta;
	}

	public function getName() : string {
		return $this->name;
	}

	public function hasName() : bool{
		return $this->name !== "";
	}
}
