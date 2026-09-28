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

namespace pocketmine\level\generator\vanilla\event\entity;

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\event\Cancellable;

class EntityDataPropertyChangeEvent extends EntityEvent implements Cancellable
{
	public function __construct(
		Entity $entity,
		protected readonly int $id,
		protected readonly int $type,
		protected mixed $value,
		protected bool $force
	) {
		parent::__construct($entity);
	}

	public function getId() : int
	{
		return $this->id;
	}

	public function getValue() : mixed
	{
		return $this->value;
	}

	public function setValue(mixed $value) : void
	{
		$this->value = $value;
	}

	public function isSend() : bool
	{
		return $this->force;
	}

	public function setSend(bool $force) : void
	{
		$this->force = $force;
	}
}
