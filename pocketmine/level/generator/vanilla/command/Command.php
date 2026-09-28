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

/**
 * Command handling related classes
 */

namespace pocketmine\level\generator\vanilla\command;

use pocketmine\level\generator\vanilla\command\utils\CommandException;
use pocketmine\level\generator\vanilla\lang\TextContainer;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandPermissions;
use pocketmine\level\generator\vanilla\permission\PermissionManager;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function array_values;
use function count;
use function explode;
use function in_array;
use function str_replace;
use function strlen;
use function strtolower;
use function ucfirst;

abstract class Command
{
	private CommandData $commandData;
	private array $jsonCommandData = [];

	/** @var string */
	private $nextLabel;

	/** @var string */
	private $label;

	/** @var string[] */
	private $aliases = [];

	/** @var CommandMap|null */
	private $commandMap = null;

	/** @var string */
	protected $description = "";

	/** @var string */
	protected $usageMessage;

	/** @var string|null */
	private $permission = null;

	/** @var string|null */
	private $permissionMessage = null;

	/**
	 * @param string[]          $aliases
	 * @param CommandOverload[] $overloads
	 */
	public function __construct(string $name, string $description = "", ?string $usageMessage = null, array $aliases = [], ?array $overloads = null)
	{
		if (strlen($description) > 0 && $description[0] == '%') {
			$description = Server::getInstance()->getLanguage()->translateString($description);
		}

		$this->commandData = new CommandData($name, $description, 0, CommandPermissions::NORMAL, null, $overloads ?? [new CommandOverload(false, [])], []);

		$this->setLabel($name);
		$this->setAliases($aliases);
		$this->usageMessage = $usageMessage ?? ("/" . $name);
	}

	/**
	 * @param string[] $args
	 *
	 * @return mixed
	 * @throws CommandException
	 */
	abstract public function execute(CommandSender $sender, string $commandLabel, array $args);

	public function getCommandData() : CommandData
	{
		return clone $this->commandData;
	}

	public function getJsonCommandData() : array
	{
		if (count($this->jsonCommandData) === 0) {
			$data = $this->getCommandData();

			$lname = strtolower($this->getLabel());
			$aliases = $this->getAliases();
			$aliasObj = null;
			if (count($aliases) > 0) {
				if (!in_array($lname, $aliases, true)) {
					//work around a client bug which makes the original name not show when aliases are used
					$aliases[] = $lname;
				}
				$aliasObj = new CommandEnum(ucfirst($this->getLabel()) . "Aliases", array_values($aliases));
			}

			$overloads = $this->getOverloads();
			if (!isset($overloads[0])) {
				$overloads = [new CommandOverload(false, [CommandParameter::standard("args", AvailableCommandsPacket::ARG_TYPE_RAWTEXT)])];
			} else {
				if (count($overloads[0]->getParameters()) === 0) {
					$overloads = [new CommandOverload(false, [CommandParameter::standard("args", AvailableCommandsPacket::ARG_TYPE_RAWTEXT)])];
				}
			}

			$description = $data->getDescription();

			$commandData = new CommandData(
				$lname, //TODO: commands containing uppercase letters in the name crash 1.9.0 client
				Server::getInstance()->getLanguage()->translateString($description),
				$data->getFlags(),
				$data->getPermission(),
				$aliasObj,
				$overloads,
				$data->getChainedSubCommandData()
			);

			$this->jsonCommandData = CommandListJson::dataJson($commandData);
		}

		return $this->jsonCommandData;
	}

	public function getName() : string
	{
		return $this->commandData->name;
	}

	/**
	 * @return string|null
	 */
	public function getPermission()
	{
		return $this->permission;
	}

	/**
	 * @return void
	 */
	public function setPermission(?string $permission = null)
	{
		$this->permission = $permission;
	}

	public function testPermission(CommandSender $target) : bool
	{
		if ($this->testPermissionSilent($target)) {
			return true;
		}

		if ($this->permissionMessage === null) {
			$target->sendMessage($target->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));
		} elseif ($this->permissionMessage !== "") {
			$target->sendMessage(str_replace("<permission>", $this->permission, $this->permissionMessage));
		}

		return false;
	}

	public function testPermissionSilent(CommandSender $target) : bool
	{
		if ($this->permission === null || $this->permission === "") {
			return true;
		}

		foreach (explode(";", $this->permission) as $permission) {
			if ($target->hasPermission($permission)) {
				return true;
			}
		}

		return false;
	}

	public function getLabel() : string
	{
		return $this->label;
	}

	public function setLabel(string $name) : bool
	{
		$this->nextLabel = $name;
		if (!$this->isRegistered()) {
			$this->label = $name;

			return true;
		}

		return false;
	}

	/**
	 * Registers the command into a Command map
	 */
	public function register(CommandMap $commandMap) : bool
	{
		if ($this->allowChangesFrom($commandMap)) {
			$this->commandMap = $commandMap;

			return true;
		}

		return false;
	}

	public function unregister(CommandMap $commandMap) : bool
	{
		if ($this->allowChangesFrom($commandMap)) {
			$this->commandMap = null;
			$this->setAliases($this->aliases);
			$this->label = $this->nextLabel;

			return true;
		}

		return false;
	}

	private function allowChangesFrom(CommandMap $commandMap) : bool
	{
		return $this->commandMap === null || $this->commandMap === $commandMap;
	}

	public function isRegistered() : bool
	{
		return $this->commandMap !== null;
	}

	/**
	 * @return string[]
	 */
	public function getAliases() : array
	{
		return $this->aliases;
	}

	/**
	 * @param string[] $aliases
	 */
	public function setAliases(array $aliases)
	{
		$this->aliases = $aliases;
	}

	public function getPermissionMessage() : ?string
	{
		return $this->permissionMessage;
	}

	public function setPermissionMessage(string $permissionMessage)
	{
		$this->permissionMessage = $permissionMessage;
	}

	public function getDescription() : string
	{
		return $this->commandData->description;
	}

	public function setDescription(string $description)
	{
		if (strlen($description) > 0 && $description[0] == '%') {
			$description = Server::getInstance()->getLanguage()->translateString($description);
		}

		$this->commandData->description = $description;
	}

	public function getUsage() : string
	{
		return $this->usageMessage;
	}

	/**
	 * @return void
	 */
	public function setUsage(string $usage)
	{
		$this->usageMessage = $usage;
	}

	/**
	 * @param TextContainer|string $message
	 *
	 * @return void
	 */
	public static function broadcastCommandMessage(CommandSender $source, $message, bool $sendToSource = true)
	{
		if ($message instanceof TextContainer) {
			$m = clone $message;
			$result = "[" . $source->getName() . ": " . ($source->getServer()->getLanguage()->get($m->getText()) !== $m->getText() ? "%" : "") . $m->getText() . "]";

			$users = PermissionManager::getInstance()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
			$colored = TextFormat::GRAY . TextFormat::ITALIC . $result;

			$m->setText($result);
			$result = clone $m;
			$m->setText($colored);
			$colored = clone $m;
		} else {
			$users = PermissionManager::getInstance()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
			$result = new TranslationContainer("chat.type.admin", [$source->getName(), $message]);
			$colored = new TranslationContainer(TextFormat::GRAY . TextFormat::ITALIC . "%chat.type.admin", [$source->getName(), $message]);
		}

		if ($sendToSource && !($source instanceof ConsoleCommandSender)) {
			$source->sendMessage($message);
		}

		foreach ($users as $user) {
			if ($user instanceof CommandSender) {
				if ($user instanceof ConsoleCommandSender) {
					$user->sendMessage($result);
				} elseif ($user !== $source) {
					$user->sendMessage($colored);
				}
			}
		}
	}

	/**
	 * Adds parameter to overload
	 */
	public function addParameter(CommandParameter $parameter, int $overloadIndex = 0) : void
	{
		$overload = $this->commandData->overloads[$overloadIndex] ?? null;
		if ($overload === null) {
			$this->commandData->overloads[$overloadIndex] = new CommandOverload(false, [$parameter]);
			return;
		}

		$parameters = $overload->getParameters();
		$parameters[] = $parameter;
		$this->commandData->overloads[$overloadIndex] = new CommandOverload($overload->isChaining(), array_values($parameters));
	}

	/**
	 * Sets parameter to overload
	 */
	public function setParameter(CommandParameter $parameter, int $parameterIndex, int $overloadIndex = 0) : void
	{
		$overload = $this->commandData->overloads[$overloadIndex] ?? null;
		if ($overload === null) {
			$this->commandData->overloads[$overloadIndex] = new CommandOverload(false, [$parameterIndex => $parameter]);
			return;
		}

		$parameters = $overload->getParameters();
		$parameters[$parameterIndex] = $parameter;
		$this->commandData->overloads[$overloadIndex] = new CommandOverload($overload->isChaining(), array_values($parameters));
	}

	/**
	 * Sets parameters to overload
	 *
	 * @param CommandParameter[] $parameters
	 */
	public function setParameters(array $parameters, int $overloadIndex = 0) : void
	{
		$overload = $this->commandData->overloads[$overloadIndex] ?? null;
		if ($overload === null) {
			$this->commandData->overloads[$overloadIndex] = new CommandOverload(false, $parameters);
			return;
		}

		$this->commandData->overloads[$overloadIndex] = new CommandOverload($overload->isChaining(), array_values($parameters));
	}

	/**
	 * Removes parameter from overload
	 */
	public function removeParameter(int $parameterIndex, int $overloadIndex = 0) : void
	{
		$overload = $this->commandData->overloads[$overloadIndex];
		$parameters = $overload->getParameters();
		unset($parameters[$parameterIndex]);

		$this->commandData->overloads[$overloadIndex] = new CommandOverload($overload->isChaining(), array_values($parameters));
	}

	/**
	 * Remove all overloads
	 */
	public function removeAllParameters() : void
	{
		$this->commandData->overloads = [];
	}

	/**
	 * Removes overload and includes.
	 */
	public function removeOverload(int $overloadIndex) : void
	{
		unset($this->commandData->overloads[$overloadIndex]);
	}

	/**
	 * Returns overload
	 */
	public function getOverload(int $index) : ?CommandOverload
	{
		return $this->commandData->overloads[$index] ?? null;
	}

	/**
	 * Returns all overloads
	 *
	 * @return CommandOverload[]
	 */
	public function getOverloads() : array
	{
		return $this->commandData->overloads;
	}

	public function __toString() : string
	{
		return $this->commandData->name;
	}
}
