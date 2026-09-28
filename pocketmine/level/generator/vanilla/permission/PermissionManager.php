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

use pocketmine\level\generator\vanilla\timings\Timings;

use function count;
use function spl_object_hash;

class PermissionManager
{
	/** @var PermissionManager|null */
	private static $instance = null;

	public static function getInstance() : PermissionManager
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/** @var Permission[] */
	protected $permissions = [];
	/** @var Permission[] */
	protected $defaultPerms = [];
	/** @var Permission[] */
	protected $defaultPermsOp = [];
	/** @var Permissible[][] */
	protected $permSubs = [];
	/** @var Permissible[] */
	protected $defSubs = [];
	/** @var Permissible[] */
	protected $defSubsOp = [];

	/**
	 * @return null|Permission
	 */
	public function getPermission(string $name)
	{
		return $this->permissions[$name] ?? null;
	}

	public function addPermission(Permission $permission) : bool
	{
		if (!isset($this->permissions[$permission->getName()])) {
			$this->permissions[$permission->getName()] = $permission;
			$this->calculatePermissionDefault($permission);

			return true;
		}

		return false;
	}

	/**
	 * @param string|Permission $permission
	 */
	public function removePermission($permission)
	{
		if ($permission instanceof Permission) {
			unset($this->permissions[$permission->getName()]);
		} else {
			unset($this->permissions[$permission]);
		}
	}

	/**
	 * @return Permission[]
	 */
	public function getDefaultPermissions(bool $op) : array
	{
		if ($op) {
			return $this->defaultPermsOp;
		} else {
			return $this->defaultPerms;
		}
	}

	public function recalculatePermissionDefaults(Permission $permission)
	{
		if (isset($this->permissions[$permission->getName()])) {
			unset($this->defaultPermsOp[$permission->getName()]);
			unset($this->defaultPerms[$permission->getName()]);
			$this->calculatePermissionDefault($permission);
		}
	}

	private function calculatePermissionDefault(Permission $permission) : void
	{
		Timings::$permissibleDefault->startTiming();
		if ($permission->getDefault() === Permission::DEFAULT_OP || $permission->getDefault() === Permission::DEFAULT_TRUE) {
			$this->defaultPermsOp[$permission->getName()] = $permission;
			$this->dirtyPermissibles(true);
		}

		if ($permission->getDefault() === Permission::DEFAULT_NOT_OP || $permission->getDefault() === Permission::DEFAULT_TRUE) {
			$this->defaultPerms[$permission->getName()] = $permission;
			$this->dirtyPermissibles(false);
		}
		Timings::$permissibleDefault->startTiming();
	}

	private function dirtyPermissibles(bool $op)
	{
		foreach ($this->getDefaultPermSubscriptions($op) as $p) {
			$p->recalculatePermissions();
		}
	}

	public function subscribeToPermission(string $permission, Permissible $permissible)
	{
		if (!isset($this->permSubs[$permission])) {
			$this->permSubs[$permission] = [];
		}
		$this->permSubs[$permission][spl_object_hash($permissible)] = $permissible;
	}

	public function unsubscribeFromPermission(string $permission, Permissible $permissible)
	{
		if (isset($this->permSubs[$permission])) {
			unset($this->permSubs[$permission][spl_object_hash($permissible)]);
			if (count($this->permSubs[$permission]) === 0) {
				unset($this->permSubs[$permission]);
			}
		}
	}

	public function unsubscribeFromAllPermissions(Permissible $permissible) : void
	{
		foreach ($this->permSubs as $permission => &$subs) {
			unset($subs[spl_object_hash($permissible)]);
			if (empty($subs)) {
				unset($this->permSubs[$permission]);
			}
		}
	}

	/**
	 * @return array|Permissible[]
	 */
	public function getPermissionSubscriptions(string $permission) : array
	{
		return $this->permSubs[$permission] ?? [];
	}

	public function subscribeToDefaultPerms(bool $op, Permissible $permissible)
	{
		if ($op) {
			$this->defSubsOp[spl_object_hash($permissible)] = $permissible;
		} else {
			$this->defSubs[spl_object_hash($permissible)] = $permissible;
		}
	}

	public function unsubscribeFromDefaultPerms(bool $op, Permissible $permissible)
	{
		if ($op) {
			unset($this->defSubsOp[spl_object_hash($permissible)]);
		} else {
			unset($this->defSubs[spl_object_hash($permissible)]);
		}
	}

	/**
	 * @return Permissible[]
	 */
	public function getDefaultPermSubscriptions(bool $op) : array
	{
		if ($op) {
			return $this->defSubsOp;
		}

		return $this->defSubs;
	}

	/**
	 * @return Permission[]
	 */
	public function getPermissions() : array
	{
		return $this->permissions;
	}

	public function clearPermissions() : void
	{
		$this->permissions = [];
		$this->defaultPerms = [];
		$this->defaultPermsOp = [];
	}
}
