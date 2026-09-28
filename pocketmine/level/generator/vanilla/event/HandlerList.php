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

namespace pocketmine\level\generator\vanilla\event;

use Exception;
use InvalidStateException;
use pocketmine\level\generator\vanilla\plugin\Plugin;
use pocketmine\level\generator\vanilla\plugin\RegisteredListener;
use pocketmine\level\generator\vanilla\utils\Utils;
use ReflectionClass;
use ReflectionException;

use function array_fill_keys;
use function in_array;
use function spl_object_hash;

class HandlerList
{
	/** @var HandlerList[] classname => HandlerList */
	private static $allLists = [];

	/**
	 * Unregisters all the listeners
	 * If a Plugin or Listener is passed, all the listeners with that object will be removed
	 *
	 * @param Plugin|Listener|null $object
	 */
	public static function unregisterAll($object = null) : void
	{
		if ($object instanceof Listener || $object instanceof Plugin) {
			foreach (self::$allLists as $h) {
				$h->unregister($object);
			}
		} else {
			foreach (self::$allLists as $h) {
				foreach ($h->handlerSlots as $key => $list) {
					$h->handlerSlots[$key] = [];
				}
			}
		}
	}

	/**
	 * Returns the HandlerList for listeners that explicitly handle this event.
	 *
	 * Calling this method also lazily initializes the $classMap inheritance tree of handler lists.
	 *
	 * @throws ReflectionException
	 */
	public static function getHandlerListFor(string $event) : ?HandlerList
	{
		if (isset(self::$allLists[$event])) {
			return self::$allLists[$event];
		}

		$class = new ReflectionClass($event);
		$tags = Utils::parseDocComment((string) $class->getDocComment());

		if ($class->isAbstract() && !isset($tags["allowHandle"])) {
			return null;
		}

		$super = $class;
		$parentList = null;
		while ($parentList === null && ($super = $super->getParentClass()) !== false) {
			// skip $noHandle events in the inheritance tree to go to the nearest ancestor
			// while loop to allow skipping $noHandle events in the inheritance tree
			$parentList = self::getHandlerListFor($super->getName());
		}

		return new HandlerList($event, $parentList);
	}

	/**
	 * @return HandlerList[]
	 */
	public static function getHandlerLists() : array
	{
		return self::$allLists;
	}

	/** @var string */
	private $class;
	/** @var RegisteredListener[][] */
	private $handlerSlots = [];
	/** @var HandlerList|null */
	private $parentList;

	public function __construct(string $class, ?HandlerList $parentList)
	{
		$this->class = $class;
		$this->handlerSlots = array_fill_keys(EventPriority::ALL, []);
		$this->parentList = $parentList;
		self::$allLists[$this->class] = $this;
	}

	/**
	 * @throws Exception
	 */
	public function register(RegisteredListener $listener) : void
	{
		if (!in_array($listener->getPriority(), EventPriority::ALL, true)) {
			return;
		}
		if (isset($this->handlerSlots[$listener->getPriority()][spl_object_hash($listener)])) {
			throw new InvalidStateException("This listener is already registered to priority {$listener->getPriority()} of event {$this->class}");
		}
		$this->handlerSlots[$listener->getPriority()][spl_object_hash($listener)] = $listener;
	}

	/**
	 * @param RegisteredListener[] $listeners
	 */
	public function registerAll(array $listeners) : void
	{
		foreach ($listeners as $listener) {
			$this->register($listener);
		}
	}

	/**
	 * @param RegisteredListener|Listener|Plugin $object
	 */
	public function unregister($object) : void
	{
		if ($object instanceof Plugin || $object instanceof Listener) {
			foreach ($this->handlerSlots as $priority => $list) {
				foreach ($list as $hash => $listener) {
					if (($object instanceof Plugin && $listener->getPlugin() === $object)
						|| ($object instanceof Listener && $listener->getListener() === $object)
					) {
						unset($this->handlerSlots[$priority][$hash]);
					}
				}
			}
		} elseif ($object instanceof RegisteredListener) {
			if (isset($this->handlerSlots[$object->getPriority()][spl_object_hash($object)])) {
				unset($this->handlerSlots[$object->getPriority()][spl_object_hash($object)]);
			}
		}
	}

	/**
	 * @return RegisteredListener[]
	 */
	public function getListenersByPriority(int $priority) : array
	{
		return $this->handlerSlots[$priority];
	}

	public function getParent() : ?HandlerList
	{
		return $this->parentList;
	}
}
