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
use pocketmine\level\generator\vanilla\timings\Timings;

use function spl_object_hash;

class PermissibleBase implements Permissible
{
	/** @var ServerOperator */
	private $opable;

	/** @var Permissible */
	private $parent = null;

	/** @var PermissionAttachment[] */
	private $attachments = [];

	/** @var PermissionAttachmentInfo[] */
	private $permissions = [];

	public function __construct(ServerOperator $opable)
	{
		$this->opable = $opable;
		if ($opable instanceof Permissible) {
			$this->parent = $opable;
		}
	}

	public function isOp() : bool
	{
		return $this->opable->isOp();
	}

	public function setOp(bool $value)
	{
		$this->opable->setOp($value);
	}

	/**
	 * @param Permission|string $name
	 */
	public function isPermissionSet($name) : bool
	{
		return isset($this->permissions[$name instanceof Permission ? $name->getName() : $name]);
	}

	public function hasPermission(Permission|string $name) : bool
	{
		if ($name instanceof Permission) {
			$name = $name->getName();
		}

		if ($this->isPermissionSet($name)) {
			return $this->permissions[$name]->getValue();
		}

		if (($perm = PermissionManager::getInstance()->getPermission($name)) !== null) {
			$perm = $perm->getDefault();

			return $perm === Permission::DEFAULT_TRUE || ($this->isOp() && $perm === Permission::DEFAULT_OP) || (!$this->isOp() && $perm === Permission::DEFAULT_NOT_OP);
		} else {
			return Permission::$DEFAULT_PERMISSION === Permission::DEFAULT_TRUE || ($this->isOp() && Permission::$DEFAULT_PERMISSION === Permission::DEFAULT_OP) || (!$this->isOp() && Permission::$DEFAULT_PERMISSION === Permission::DEFAULT_NOT_OP);
		}

	}

	/**
	 * //TODO: tick scheduled attachments
	 */
	public function addAttachment(Plugin $plugin, ?string $name = null, ?bool $value = null) : PermissionAttachment
	{
		if (!$plugin->isEnabled()) {
			throw new PluginException("Plugin " . $plugin->getDescription()->getName() . " is disabled");
		}

		$result = new PermissionAttachment($plugin, $this->parent ?? $this);
		$this->attachments[spl_object_hash($result)] = $result;
		if ($name !== null && $value !== null) {
			$result->setPermission($name, $value);
		}

		$this->recalculatePermissions();

		return $result;
	}

	public function removeAttachment(PermissionAttachment $attachment)
	{
		if (isset($this->attachments[spl_object_hash($attachment)])) {
			unset($this->attachments[spl_object_hash($attachment)]);
			if (($ex = $attachment->getRemovalCallback()) !== null) {
				$ex->attachmentRemoved($attachment);
			}

			$this->recalculatePermissions();

		}

	}

	public function recalculatePermissions()
	{
		Timings::$permissibleCalculation->startTiming();

		$this->clearPermissions();
		$permManager = PermissionManager::getInstance();
		$defaults = $permManager->getDefaultPermissions($this->isOp());
		$permManager->subscribeToDefaultPerms($this->isOp(), $this->parent ?? $this);

		foreach ($defaults as $perm) {
			$name = $perm->getName();
			$this->permissions[$name] = new PermissionAttachmentInfo($this->parent ?? $this, $name, null, true);
			$permManager->subscribeToPermission($name, $this->parent ?? $this);
			$this->calculateChildPermissions($perm->getChildren(), false, null);
		}

		foreach ($this->attachments as $attachment) {
			$this->calculateChildPermissions($attachment->getPermissions(), false, $attachment);
		}

		Timings::$permissibleCalculation->stopTiming();
	}

	public function clearPermissions()
	{
		$permManager = PermissionManager::getInstance();
		$permManager->unsubscribeFromAllPermissions($this->parent ?? $this);

		$permManager->unsubscribeFromDefaultPerms(false, $this->parent ?? $this);
		$permManager->unsubscribeFromDefaultPerms(true, $this->parent ?? $this);

		$this->permissions = [];
	}

	/**
	 * @param bool[] $children
	 */
	private function calculateChildPermissions(array $children, bool $invert, ?PermissionAttachment $attachment)
	{
		$permManager = PermissionManager::getInstance();
		foreach ($children as $name => $v) {
			$perm = $permManager->getPermission($name);
			$value = ($v xor $invert);
			$this->permissions[$name] = new PermissionAttachmentInfo($this->parent ?? $this, $name, $attachment, $value);
			$permManager->subscribeToPermission($name, $this->parent ?? $this);

			if ($perm instanceof Permission) {
				$this->calculateChildPermissions($perm->getChildren(), !$value, $attachment);
			}
		}
	}

	/**
	 * @return PermissionAttachmentInfo[]
	 */
	public function getEffectivePermissions() : array
	{
		return $this->permissions;
	}
}
