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

use pocketmine\level\generator\vanilla\command\defaults\BanCommand;
use pocketmine\level\generator\vanilla\command\defaults\BanIpCommand;
use pocketmine\level\generator\vanilla\command\defaults\BanListCommand;
use pocketmine\level\generator\vanilla\command\defaults\ClearCommand;
use pocketmine\level\generator\vanilla\command\defaults\DefaultGamemodeCommand;
use pocketmine\level\generator\vanilla\command\defaults\DeopCommand;
use pocketmine\level\generator\vanilla\command\defaults\DifficultyCommand;
use pocketmine\level\generator\vanilla\command\defaults\DumpMemoryCommand;
use pocketmine\level\generator\vanilla\command\defaults\EffectCommand;
use pocketmine\level\generator\vanilla\command\defaults\EnchantCommand;
use pocketmine\level\generator\vanilla\command\defaults\GamemodeCommand;
use pocketmine\level\generator\vanilla\command\defaults\GameRuleCommand;
use pocketmine\level\generator\vanilla\command\defaults\GarbageCollectorCommand;
use pocketmine\level\generator\vanilla\command\defaults\GiveCommand;
use pocketmine\level\generator\vanilla\command\defaults\HelpCommand;
use pocketmine\level\generator\vanilla\command\defaults\HudCommand;
use pocketmine\level\generator\vanilla\command\defaults\KickCommand;
use pocketmine\level\generator\vanilla\command\defaults\KillCommand;
use pocketmine\level\generator\vanilla\command\defaults\ListCommand;
use pocketmine\level\generator\vanilla\command\defaults\MeCommand;
use pocketmine\level\generator\vanilla\command\defaults\OpCommand;
use pocketmine\level\generator\vanilla\command\defaults\PardonCommand;
use pocketmine\level\generator\vanilla\command\defaults\PardonIpCommand;
use pocketmine\level\generator\vanilla\command\defaults\ParticleCommand;
use pocketmine\level\generator\vanilla\command\defaults\PlaySoundCommand;
use pocketmine\level\generator\vanilla\command\defaults\PluginsCommand;
use pocketmine\level\generator\vanilla\command\defaults\ReloadCommand;
use pocketmine\level\generator\vanilla\command\defaults\SaveCommand;
use pocketmine\level\generator\vanilla\command\defaults\SaveOffCommand;
use pocketmine\level\generator\vanilla\command\defaults\SaveOnCommand;
use pocketmine\level\generator\vanilla\command\defaults\SayCommand;
use pocketmine\level\generator\vanilla\command\defaults\SeedCommand;
use pocketmine\level\generator\vanilla\command\defaults\SetBlockCommand;
use pocketmine\level\generator\vanilla\command\defaults\SetWorldSpawnCommand;
use pocketmine\level\generator\vanilla\command\defaults\SpawnpointCommand;
use pocketmine\level\generator\vanilla\command\defaults\StatusCommand;
use pocketmine\level\generator\vanilla\command\defaults\StopCommand;
use pocketmine\level\generator\vanilla\command\defaults\StopSoundCommand;
use pocketmine\level\generator\vanilla\command\defaults\TeleportCommand;
use pocketmine\level\generator\vanilla\command\defaults\TellCommand;
use pocketmine\level\generator\vanilla\command\defaults\TimeCommand;
use pocketmine\level\generator\vanilla\command\defaults\TimingsCommand;
use pocketmine\level\generator\vanilla\command\defaults\TitleCommand;
use pocketmine\level\generator\vanilla\command\defaults\TransferServerCommand;
use pocketmine\level\generator\vanilla\command\defaults\VanillaCommand;
use pocketmine\level\generator\vanilla\command\defaults\VersionCommand;
use pocketmine\level\generator\vanilla\command\defaults\WhitelistCommand;
use pocketmine\level\generator\vanilla\command\defaults\WorldCommand;
use pocketmine\level\generator\vanilla\command\defaults\XpCommand;
use pocketmine\level\generator\vanilla\command\utils\InvalidCommandSyntaxException;
use pocketmine\level\generator\vanilla\command\utils\NoSelectorMatchException;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\timings\Timings;
use Throwable;

use function array_shift;
use function count;
use function explode;
use function implode;
use function min;
use function preg_match_all;
use function strcasecmp;
use function stripslashes;
use function strpos;
use function strtolower;
use function trim;

class SimpleCommandMap implements CommandMap
{
	/** @var Command[] */
	protected $knownCommands = [];

	/** @var Server */
	private $server;

	public function __construct(Server $server)
	{
		$this->server = $server;
		$this->setDefaultCommands();
	}

	private function setDefaultCommands() : void
	{
		$this->registerAll("pocketmine", [
			new BanCommand("ban"),
			new BanIpCommand("ban-ip"),
			new BanListCommand("banlist"),
			new DefaultGamemodeCommand("defaultgamemode"),
			new DeopCommand("deop"),
			new DifficultyCommand("difficulty"),
			new DumpMemoryCommand("dumpmemory"),
			new EffectCommand("effect"),
			new EnchantCommand("enchant"),
			new GamemodeCommand("gamemode"),
			new GarbageCollectorCommand("gc"),
			new GiveCommand("give"),
			new HelpCommand("help"),
			new KickCommand("kick"),
			new KillCommand("kill"),
			new ListCommand("list"),
			new MeCommand("me"),
			new OpCommand("op"),
			new PardonCommand("pardon"),
			new PardonIpCommand("pardon-ip"),
			new ParticleCommand("particle"),
			new PluginsCommand("plugins"),
			new ReloadCommand("reload"),
			new SaveCommand("save-all"),
			new SaveOffCommand("save-off"),
			new SaveOnCommand("save-on"),
			new SayCommand("say"),
			new SeedCommand("seed"),
			new SetWorldSpawnCommand("setworldspawn"),
			new SpawnpointCommand("spawnpoint"),
			new StatusCommand("status"),
			new StopCommand("stop"),
			new TeleportCommand("tp"),
			new TellCommand("tell"),
			new TimeCommand("time"),
			new TimingsCommand("timings"),
			new TitleCommand("title"),
			new TransferServerCommand("transferserver"),
			new VersionCommand("version"),
			new WhitelistCommand("whitelist"),
			new ClearCommand("clear"),
			new SetBlockCommand("setblock"),
			new WorldCommand("world"),
			new GameRuleCommand("gamerule"),
			new PlaySoundCommand("playsound"),
			new StopSoundCommand("stopsound"),
			new XpCommand("xp"),
			new HudCommand("hud"),
		]);
	}

	public function registerAll(string $fallbackPrefix, array $commands)
	{
		foreach ($commands as $command) {
			$this->register($fallbackPrefix, $command);
		}
	}

	public function register(string $fallbackPrefix, Command $command, ?string $label = null) : bool
	{
		if ($label === null) {
			$label = $command->getName();
		}
		$label = trim($label);
		$fallbackPrefix = strtolower(trim($fallbackPrefix));

		$registered = $this->registerAlias($command, false, $fallbackPrefix, $label);

		$aliases = $command->getAliases();
		foreach ($aliases as $index => $alias) {
			if (!$this->registerAlias($command, true, $fallbackPrefix, $alias)) {
				unset($aliases[$index]);
			}
		}
		$command->setAliases($aliases);

		if (!$registered) {
			$command->setLabel($fallbackPrefix . ":" . $label);
		}

		$command->register($this);

		return $registered;
	}

	public function unregister(Command $command) : bool
	{
		foreach ($this->knownCommands as $lbl => $cmd) {
			if ($cmd === $command) {
				unset($this->knownCommands[$lbl]);
			}
		}

		$command->unregister($this);

		return true;
	}

	private function registerAlias(Command $command, bool $isAlias, string $fallbackPrefix, string $label) : bool
	{
		$this->knownCommands[$fallbackPrefix . ":" . $label] = $command;
		if (($command instanceof VanillaCommand || $isAlias) && isset($this->knownCommands[$label])) {
			return false;
		}

		if (isset($this->knownCommands[$label]) && $this->knownCommands[$label]->getLabel() === $label) {
			return false;
		}

		if (!$isAlias) {
			$command->setLabel($label);
		}

		$this->knownCommands[$label] = $command;

		return true;
	}

	/**
	 * Returns a command to match the specified command line, or null if no matching command was found.
	 * This method is intended to provide capability for handling commands with spaces in their name.
	 * The referenced parameters will be modified accordingly depending on the resulting matched command.
	 *
	 * @param string   $commandName reference parameter
	 * @param string[] $args        reference parameter
	 *
	 * @return Command|null
	 */
	public function matchCommand(string &$commandName, array &$args)
	{
		$count = min(count($args), 255);

		for ($i = 0; $i < $count; ++$i) {
			$commandName .= array_shift($args);
			if (($command = $this->getCommand($commandName)) instanceof Command) {
				return $command;
			}

			$commandName .= " ";
		}

		return null;
	}

	public function dispatch(CommandSender $sender, string $commandLine) : bool
	{
		$args = [];
		preg_match_all('/"((?:\\\\.|[^\\\\"])*)"|(\S+)/u', $commandLine, $matches);
		foreach ($matches[0] as $k => $_) {
			for ($i = 1; $i <= 2; ++$i) {
				if ($matches[$i][$k] !== "") {
					$args[$k] = stripslashes($matches[$i][$k]);
					break;
				}
			}
		}
		$sentCommandLabel = "";
		$target = $this->matchCommand($sentCommandLabel, $args);

		if ($target === null) {
			return false;
		}

		$timings = Timings::getCommandDispatchTimings($target->getLabel());
		$timings->startTiming();

		try {
			$target->execute($sender, $sentCommandLabel, $args);
		} catch (InvalidCommandSyntaxException $e) {
			$sender->sendMessage($this->server->getLanguage()->translateString("commands.generic.usage", [$target->getUsage()]));
		} catch (NoSelectorMatchException $e) {
			$sender->sendMessage($this->server->getLanguage()->translateString("commands.generic.noTargetMatch"));
		} catch (Throwable $e) {
			$this->server->getLogger()->logException($e);
		} finally {
			$timings->stopTiming();
		}

		return true;
	}

	public function clearCommands()
	{
		foreach ($this->knownCommands as $command) {
			$command->unregister($this);
		}
		$this->knownCommands = [];
		$this->setDefaultCommands();
	}

	public function getCommand(string $name)
	{
		return $this->knownCommands[$name] ?? null;
	}

	/**
	 * @return Command[]
	 */
	public function getCommands() : array
	{
		return $this->knownCommands;
	}

	/**
	 * @return void
	 */
	public function registerServerAliases()
	{
		$values = $this->server->getCommandAliases();

		foreach ($values as $alias => $commandStrings) {
			if (strpos($alias, ":") !== false) {
				$this->server->getLogger()->warning($this->server->getLanguage()->translateString("pocketmine.command.alias.illegal", [$alias]));
				continue;
			}

			$targets = [];
			$bad = [];
			$recursive = [];

			foreach ($commandStrings as $commandString) {
				$args = explode(" ", $commandString);
				$commandName = "";
				$command = $this->matchCommand($commandName, $args);

				if ($command === null) {
					$bad[] = $commandString;
				} elseif (strcasecmp($commandName, $alias) === 0) {
					$recursive[] = $commandString;
				} else {
					$targets[] = $commandString;
				}
			}

			if (count($recursive) > 0) {
				$this->server->getLogger()->warning($this->server->getLanguage()->translateString("pocketmine.command.alias.recursive", [$alias, implode(", ", $recursive)]));
				continue;
			}

			if (count($bad) > 0) {
				$this->server->getLogger()->warning($this->server->getLanguage()->translateString("pocketmine.command.alias.notFound", [$alias, implode(", ", $bad)]));
				continue;
			}

			//These registered commands have absolute priority
			if (count($targets) > 0) {
				$this->knownCommands[strtolower($alias)] = new FormattedCommandAlias(strtolower($alias), $targets);
			} else {
				unset($this->knownCommands[strtolower($alias)]);
			}

		}
	}
}
