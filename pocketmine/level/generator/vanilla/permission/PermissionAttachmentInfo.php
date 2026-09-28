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

namespace pocketmine\level\generator\vanilla\permission;

use InvalidStateException;

class PermissionAttachmentInfo
{
	/** @var Permissible */
	private $permissible;

	/** @var string */
	private $permission;

	/** @var PermissionAttachment|null */
	private $attachment;

	/** @var bool */
	private $value;

	/**
	 * @throws InvalidStateException
	 */
	public function __construct(Permissible $permissible, string $permission, ?PermissionAttachment $attachment, bool $value)
	{
		$this->permissible = $permissible;
		$this->permission = $permission;
		$this->attachment = $attachment;
		$this->value = $value;
	}

	public function getPermissible() : Permissible
	{
		return $this->permissible;
	}

	public function getPermission() : string
	{
		return $this->permission;
	}

	/**
	 * @return PermissionAttachment|null
	 */
	public function getAttachment()
	{
		return $this->attachment;
	}

	public function getValue() : bool
	{
		return $this->value;
	}
}
