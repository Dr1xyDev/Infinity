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

use pocketmine\level\generator\vanilla\plugin\Plugin;
use pocketmine\level\generator\vanilla\plugin\PluginException;

class PermissionAttachment
{
	/** @var PermissionRemovedExecutor */
	private $removed = null;

	/** @var bool[] */
	private $permissions = [];

	/** @var Permissible */
	private $permissible;

	/** @var Plugin */
	private $plugin;

	/**
	 * @throws PluginException
	 */
	public function __construct(Plugin $plugin, Permissible $permissible)
	{
		if (!$plugin->isEnabled()) {
			throw new PluginException("Plugin " . $plugin->getDescription()->getName() . " is disabled");
		}

		$this->permissible = $permissible;
		$this->plugin = $plugin;
	}

	public function getPlugin() : Plugin
	{
		return $this->plugin;
	}

	public function setRemovalCallback(PermissionRemovedExecutor $ex)
	{
		$this->removed = $ex;
	}

	/**
	 * @return PermissionRemovedExecutor|null
	 */
	public function getRemovalCallback()
	{
		return $this->removed;
	}

	public function getPermissible() : Permissible
	{
		return $this->permissible;
	}

	/**
	 * @return bool[]
	 */
	public function getPermissions() : array
	{
		return $this->permissions;
	}

	public function clearPermissions()
	{
		$this->permissions = [];
		$this->permissible->recalculatePermissions();
	}

	/**
	 * @param bool[] $permissions
	 */
	public function setPermissions(array $permissions)
	{
		foreach ($permissions as $key => $value) {
			$this->permissions[$key] = (bool) $value;
		}
		$this->permissible->recalculatePermissions();
	}

	/**
	 * @param string[] $permissions
	 */
	public function unsetPermissions(array $permissions)
	{
		foreach ($permissions as $node) {
			unset($this->permissions[$node]);
		}
		$this->permissible->recalculatePermissions();
	}

	/**
	 * @param string|Permission $name
	 */
	public function setPermission($name, bool $value)
	{
		$name = $name instanceof Permission ? $name->getName() : $name;
		if (isset($this->permissions[$name])) {
			if ($this->permissions[$name] === $value) {
				return;
			}
			unset($this->permissions[$name]); //Fixes children getting overwritten
		}
		$this->permissions[$name] = $value;
		$this->permissible->recalculatePermissions();
	}

	/**
	 * @param string|Permission $name
	 */
	public function unsetPermission($name)
	{
		$name = $name instanceof Permission ? $name->getName() : $name;
		if (isset($this->permissions[$name])) {
			unset($this->permissions[$name]);
			$this->permissible->recalculatePermissions();
		}
	}

	/**
	 * @return void
	 */
	public function remove()
	{
		$this->permissible->removeAttachment($this);
	}
}
