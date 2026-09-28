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

namespace pocketmine\level\generator\vanilla\command;

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\lang\TextContainer;
use pocketmine\level\generator\vanilla\permission\PermissibleBase;
use pocketmine\level\generator\vanilla\permission\Permission;
use pocketmine\level\generator\vanilla\permission\PermissionAttachment;
use pocketmine\level\generator\vanilla\permission\PermissionAttachmentInfo;
use pocketmine\level\generator\vanilla\plugin\Plugin;
use pocketmine\Server;

use function explode;
use function sprintf;
use function trim;

use const PHP_INT_MAX;

class ConsoleCommandSender implements CommandSender
{
	/** @var PermissibleBase */
	private $perm;

	/** @var int|null */
	protected $lineHeight = null;

	public function __construct()
	{
		$this->perm = new PermissibleBase($this);
	}

	/**
	 * @param Permission|string $name
	 */
	public function isPermissionSet($name) : bool
	{
		return $this->perm->isPermissionSet($name);
	}

	/**
	 * @param Permission|string $name
	 */
	public function hasPermission($name) : bool
	{
		return $this->perm->hasPermission($name);
	}

	public function addAttachment(Plugin $plugin, ?string $name = null, ?bool $value = null) : PermissionAttachment
	{
		return $this->perm->addAttachment($plugin, $name, $value);
	}

	/**
	 * @return void
	 */
	public function removeAttachment(PermissionAttachment $attachment)
	{
		$this->perm->removeAttachment($attachment);
	}

	public function recalculatePermissions()
	{
		$this->perm->recalculatePermissions();
	}

	/**
	 * @return PermissionAttachmentInfo[]
	 */
	public function getEffectivePermissions() : array
	{
		return $this->perm->getEffectivePermissions();
	}

	/**
	 * @return Server
	 */
	public function getServer()
	{
		return Server::getInstance();
	}

	/**
	 * @param TextContainer|string $message
	 *
	 * @return void
	 */
	public function sendMessage($message)
	{
		if ($message instanceof TextContainer) {
			$message = $this->getServer()->getLanguage()->translate($message);
		} else {
			$message = $this->getServer()->getLanguage()->translateString($message);
		}

		foreach (explode("\n", trim($message)) as $line) {
			\GlobalLogger::get()->info($line);
		}
	}

	public function sendMessagef(string $format, mixed ...$args) : void
	{
		$this->sendMessage(sprintf($format, ...$args));
	}

	public function getName() : string
	{
		return "CONSOLE";
	}

	public function isOp() : bool
	{
		return true;
	}

	/**
	 * @return void
	 */
	public function setOp(bool $value)
	{

	}

	public function getScreenLineHeight() : int
	{
		return $this->lineHeight ?? PHP_INT_MAX;
	}

	public function setScreenLineHeight(?int $height = null)
	{
		if ($height !== null && $height < 1) {
			throw new InvalidArgumentException("Line height must be at least 1");
		}
		$this->lineHeight = $height;
	}
}
