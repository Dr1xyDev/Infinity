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

namespace pocketmine\level\generator\vanilla\command\defaults;

use pocketmine\level\generator\vanilla\command\Command;
use pocketmine\level\generator\vanilla\command\CommandSender;
use pocketmine\level\generator\vanilla\command\utils\InvalidCommandSyntaxException;
use pocketmine\level\generator\vanilla\errorhandler\ErrorToExceptionHandler;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\scheduler\BulkCurlTask;
use pocketmine\level\generator\vanilla\scheduler\BulkCurlTaskOperation;
use pocketmine\level\generator\vanilla\timings\TimingsHandler;
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use pocketmine\level\generator\vanilla\utils\InternetException;
use pocketmine\level\generator\vanilla\utils\InternetRequestResult;
use Symfony\Component\Filesystem\Path;

use function count;
use function fclose;
use function file_exists;
use function fopen;
use function fwrite;
use function http_build_query;
use function implode;
use function is_array;
use function json_decode;
use function mkdir;
use function strtolower;

use const CURLOPT_AUTOREFERER;
use const CURLOPT_FOLLOWLOCATION;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const PHP_EOL;

class TimingsCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.timings.description", "%pocketmine.command.timings.usage", [], [
			new CommandOverload(false, [
				CommandParameter::enum("args", new CommandEnum("args", [
					"on", "off", "paste", "reset", "merged", "report"
				]), 0)
			])
		]);
		$this->setPermission("pocketmine.command.timings");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (count($args) !== 1) {
			throw new InvalidCommandSyntaxException();
		}

		$mode = strtolower($args[0]);

		if ($mode === "on") {
			TimingsHandler::setEnabled();
			Command::broadcastCommandMessage($sender, new TranslationContainer("pocketmine.command.timings.enable"));

			return true;
		} elseif ($mode === "off") {
			TimingsHandler::setEnabled(false);
			$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.disable"));
			return true;
		}

		if (!TimingsHandler::isEnabled()) {
			$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.timingsDisabled"));

			return true;
		}

		$paste = $mode === "paste";

		if ($mode === "reset") {
			TimingsHandler::reload();
			$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.reset"));
		} elseif ($mode === "merged" || $mode === "report" || $paste) {
			$timingsPromise = TimingsHandler::requestPrintTimings();
			Command::broadcastCommandMessage($sender, new TranslationContainer("pocketmine.command.timings.collect"));
			$timingsPromise->onCompletion(
				fn (array $lines) => $paste ? $this->uploadReport($lines, $sender) : $this->createReportFile($lines, $sender),
				fn () => throw new AssumptionFailedError("This promise is not expected to be rejected")
			);
		} else {
			throw new InvalidCommandSyntaxException();
		}

		return true;
	}

	/**
	 * @param string[] $lines
	 * @phpstan-param list<string> $lines
	 */
	private function createReportFile(array $lines, CommandSender $sender) : void
	{
		$index = 0;
		$timingFolder = Path::join($sender->getServer()->getDataPath(), "timings");
		if (!file_exists($timingFolder)) {
			mkdir($timingFolder, 0777);
		}
		$timings = Path::join($timingFolder, "timings.txt");
		while (file_exists($timings)) {
			$timings = Path::join($timingFolder, "timings" . (++$index) . ".txt");
		}
		$fileTimings = ErrorToExceptionHandler::trapAndRemoveFalse(fn () => fopen($timings, "a+b"));
		foreach ($lines as $line) {
			fwrite($fileTimings, $line . PHP_EOL);
		}
		fclose($fileTimings);
		Command::broadcastCommandMessage($sender, new TranslationContainer("pocketmine.command.timings.timingsWrite", [
			$timings
		]));
	}
	/**
	 * @param string[] $lines
	 * @phpstan-param list<string> $lines
	 */
	private function uploadReport(array $lines, CommandSender $sender) : void
	{
		$data = [
			"browser" => $agent = $sender->getServer()->getName() . " " . $sender->getServer()->getPocketMineVersion(),
			"data" => implode("\n", $lines)
		];
		$host = $sender->getServer()->getProperty("timings.host", "timings.pmmp.io");
		$sender->getServer()->getAsyncPool()->submitTask(new BulkCurlTask(
			[new BulkCurlTaskOperation(
				"https://$host?upload=true",
				10,
				[],
				[
					CURLOPT_HTTPHEADER => [
						"User-Agent: $agent",
						"Content-Type: application/x-www-form-urlencoded"
					],
					CURLOPT_POST => true,
					CURLOPT_POSTFIELDS => http_build_query($data),
					CURLOPT_AUTOREFERER => false,
					CURLOPT_FOLLOWLOCATION => false
				]
			)],
			function (array $results) use ($sender, $host) : void {
				/** @phpstan-var array<InternetRequestResult|InternetException> $results */
				if ($sender instanceof Player && !$sender->isOnline()) { // TODO replace with a more generic API method for checking availability of CommandSender
					return;
				}
				$result = $results[0];
				if ($result instanceof InternetException) {
					$sender->getServer()->getLogger()->logException($result);
					return;
				}
				$response = json_decode($result->getBody(), true);
				if (is_array($response) && isset($response["id"])) {
					Command::broadcastCommandMessage($sender, new TranslationContainer("pocketmine.command.timings.timingsRead", [
						"https://" . $host . "/?id=" . $response["id"]
					]));
				} else {
					$sender->getServer()->getLogger()->error("Invalid response from timings server (" . $result->getCode() . "): " . $result->getBody());
					Command::broadcastCommandMessage($sender, new TranslationContainer("pocketmine.command.timings.pasteError"));
				}
			}
		));
	}
}
