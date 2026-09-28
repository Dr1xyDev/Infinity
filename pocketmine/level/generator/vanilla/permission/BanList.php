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

use DateTime;
use pocketmine\Server;
use Throwable;

use function date;
use function fclose;
use function fgets;
use function fopen;
use function fwrite;
use function is_resource;
use function strtolower;

class BanList
{
	/** @var BanEntry[] */
	private $list = [];

	/** @var string */
	private $file;

	/** @var bool */
	private $enabled = true;

	public function __construct(string $file)
	{
		$this->file = $file;
	}

	public function isEnabled() : bool
	{
		return $this->enabled;
	}

	public function setEnabled(bool $flag)
	{
		$this->enabled = $flag;
	}

	public function getEntry(string $name) : ?BanEntry
	{
		$this->removeExpired();

		return $this->list[strtolower($name)] ?? null;
	}

	/**
	 * @return BanEntry[]
	 */
	public function getEntries() : array
	{
		$this->removeExpired();

		return $this->list;
	}

	public function isBanned(string $name) : bool
	{
		$name = strtolower($name);
		if (!$this->isEnabled()) {
			return false;
		} else {
			$this->removeExpired();

			return isset($this->list[$name]);
		}
	}

	public function add(BanEntry $entry)
	{
		$this->list[$entry->getName()] = $entry;
		$this->save();
	}

	public function addBan(string $target, ?string $reason = null, ?DateTime $expires = null, ?string $source = null) : BanEntry
	{
		$entry = new BanEntry($target);
		$entry->setSource($source ?? $entry->getSource());
		$entry->setExpires($expires);
		$entry->setReason($reason ?? $entry->getReason());

		$this->list[$entry->getName()] = $entry;
		$this->save();

		return $entry;
	}

	public function remove(string $name)
	{
		$name = strtolower($name);
		if (isset($this->list[$name])) {
			unset($this->list[$name]);
			$this->save();
		}
	}

	public function removeExpired()
	{
		foreach ($this->list as $name => $entry) {
			if ($entry->hasExpired()) {
				unset($this->list[$name]);
			}
		}
	}

	public function load()
	{
		$this->list = [];
		$fp = @fopen($this->file, "r");
		if (is_resource($fp)) {
			while (($line = fgets($fp)) !== false) {
				if ($line[0] !== "#") {
					try {
						$entry = BanEntry::fromString($line);
						if ($entry instanceof BanEntry) {
							$this->list[$entry->getName()] = $entry;
						}
					} catch (Throwable $e) {
						$logger = \GlobalLogger::get();
						$logger->critical("Failed to parse ban entry from string \"$line\": " . $e->getMessage());
						$logger->logException($e);
					}
				}
			}
			fclose($fp);
		} else {
			\GlobalLogger::get()->error("Could not load ban list");
		}
	}

	public function save(bool $writeHeader = true)
	{
		$this->removeExpired();
		$fp = @fopen($this->file, "w");
		if (is_resource($fp)) {
			if ($writeHeader) {
				fwrite($fp, "# Updated " . date("d/m/Y H:i") . " by " . Server::getInstance()->getName() . " " . Server::getInstance()->getPocketMineVersion() . "\n");
				fwrite($fp, "# victim name | ban date | banned by | banned until | reason\n\n");
			}

			foreach ($this->list as $entry) {
				fwrite($fp, $entry->getString() . "\n");
			}
			fclose($fp);
		} else {
			\GlobalLogger::get()->error("Could not save ban list");
		}
	}
}
