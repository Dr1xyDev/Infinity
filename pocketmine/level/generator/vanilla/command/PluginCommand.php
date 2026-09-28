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

use pocketmine\level\generator\vanilla\command\utils\InvalidCommandSyntaxException;
use pocketmine\level\generator\vanilla\plugin\Plugin;

class PluginCommand extends Command implements PluginIdentifiableCommand
{
	/** @var Plugin */
	private $owningPlugin;

	/** @var CommandExecutor */
	private $executor;

	public function __construct(string $name, Plugin $owner)
	{
		parent::__construct($name);
		$this->owningPlugin = $owner;
		$this->executor = $owner;
		$this->usageMessage = "";
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{

		if (!$this->owningPlugin->isEnabled()) {
			return false;
		}

		if (!$this->testPermission($sender)) {
			return false;
		}

		$success = $this->executor->onCommand($sender, $this, $commandLabel, $args);

		if (!$success && $this->usageMessage !== "") {
			throw new InvalidCommandSyntaxException();
		}

		return $success;
	}

	public function getExecutor() : CommandExecutor
	{
		return $this->executor;
	}

	/**
	 * @return void
	 */
	public function setExecutor(CommandExecutor $executor)
	{
		$this->executor = $executor;
	}

	public function getPlugin() : Plugin
	{
		return $this->owningPlugin;
	}
}
