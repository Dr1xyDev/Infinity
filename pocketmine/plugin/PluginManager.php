<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\plugin;

use pocketmine\command\defaults\TimingsCommand;
use pocketmine\command\PluginCommand;
use pocketmine\command\SimpleCommandMap;
use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\HandlerList;
use pocketmine\event\Listener;
use pocketmine\event\Timings;
use pocketmine\event\TimingsHandler;
use pocketmine\permission\Permissible;
use pocketmine\permission\Permission;
use pocketmine\Server;
use pocketmine\utils\MainLogger;
use pocketmine\utils\PluginException;

class PluginManager{

	
	private $server;

	
	private $commandMap;

	
	protected $plugins = [];

	
	protected $permissions = [];

	
	protected $defaultPerms = [];

	
	protected $defaultPermsOp = [];

	
	protected $permSubs = [];

	
	protected $defSubs = [];

	
	protected $defSubsOp = [];

	
	protected $fileAssociations = [];

	
	public static $pluginParentTimer;

	public static $useTimings = false;

	
	public function __construct(Server $server, SimpleCommandMap $commandMap){
		$this->server = $server;
		$this->commandMap = $commandMap;
	}

	
	public function getPlugin($name){
		if(isset($this->plugins[$name])){
			return $this->plugins[$name];
		}

		return null;
	}

	
	public function registerInterface($loaderName){
		if(is_subclass_of($loaderName, PluginLoader::class)){
			$loader = new $loaderName($this->server);
		}else{
			return false;
		}

		$this->fileAssociations[$loaderName] = $loader;

		return true;
	}

	
	public function getPlugins(){
		return $this->plugins;
	}

	
	public function loadPlugin($path, $loaders = null){
		foreach(($loaders === null ? $this->fileAssociations : $loaders) as $loader){
			if(preg_match($loader->getPluginFilters(), basename($path)) > 0){
				$description = $loader->getPluginDescription($path);
				if($description instanceof PluginDescription){
					if(($plugin = $loader->loadPlugin($path)) instanceof Plugin){
						$this->plugins[$plugin->getDescription()->getName()] = $plugin;

						$pluginCommands = $this->parseYamlCommands($plugin);

						if(count($pluginCommands) > 0){
							$this->commandMap->registerAll($plugin->getDescription()->getName(), $pluginCommands);
						}

						return $plugin;
					}
				}
			}
		}

		return null;
	}

	
	public function loadPlugins($directory, $newLoaders = null){

		if(is_dir($directory)){
			$plugins = [];
			$loadedPlugins = [];
			$dependencies = [];
			$softDependencies = [];
			if(is_array($newLoaders)){
				$loaders = [];
				foreach($newLoaders as $key){
					if(isset($this->fileAssociations[$key])){
						$loaders[$key] = $this->fileAssociations[$key];
					}
				}
			}else{
				$loaders = $this->fileAssociations;
			}
			foreach($loaders as $loader){
				foreach(new \RegexIterator(new \DirectoryIterator($directory), $loader->getPluginFilters()) as $file){
					if($file === "." or $file === ".."){
						continue;
					}
					$file = $directory . $file;
					try{
						$description = $loader->getPluginDescription($file);
						if($description instanceof PluginDescription){
							$name = $description->getName();
							if(stripos($name, "pocketmine") !== false or stripos($name, "minecraft") !== false or stripos($name, "mojang") !== false){
								$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [$name, "%pocketmine.plugin.restrictedName"]));
								continue;
							}elseif(strpos($name, " ") !== false){
								$this->server->getLogger()->warning($this->server->getLanguage()->translateString("pocketmine.plugin.spacesDiscouraged", [$name]));
							}

							if(isset($plugins[$name]) or $this->getPlugin($name) instanceof Plugin){
								$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.duplicateError", [$name]));
								continue;
							}

							$compatible = false;
							
							foreach($description->getCompatibleApis() as $version){
								
								$version = array_map("intval", explode(".", $version));
								$apiVersion = array_map("intval", explode(".", $this->server->getApiVersion()));
								
								if($version[0] > $apiVersion[0]){
									continue;
								}
								
								if($version[0] < $apiVersion[0]){
									$compatible = true;
									break;
								}
								
								if($version[1] > $apiVersion[1]){
									continue;
								}

								$compatible = true;
								break;
							}

							$compatiblegeniapi = false;
							foreach($description->getCompatibleGeniApis() as $version){
								
								$version = array_map("intval", explode(".", $version));
								$apiVersion = array_map("intval", explode(".", $this->server->getGeniApiVersion()));
								
								if($version[0] > $apiVersion[0]){
									continue;
								}
								
								if($version[0] < $apiVersion[0]){
									$compatiblegeniapi = true;
									break;
								}
								
								if($version[1] > $apiVersion[1]){
									continue;
								}

								if($version[1] == $apiVersion[1] and $version[2] > $apiVersion[2]){
									continue;
								}

								$compatiblegeniapi = true;
								break;
							}

							if($compatible === false){
								$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [$name, "%pocketmine.plugin.incompatibleAPI"]));
								continue;
							}

							if($compatiblegeniapi === false){
								$this->server->getLogger()->error("Could not load plugin '{$description->getName()}': Incompatible GeniAPI version");
								continue;
							}

							$plugins[$name] = $file;

							$softDependencies[$name] = (array) $description->getSoftDepend();
							$dependencies[$name] = (array) $description->getDepend();

							foreach($description->getLoadBefore() as $before){
								if(isset($softDependencies[$before])){
									$softDependencies[$before][] = $name;
								}else{
									$softDependencies[$before] = [$name];
								}
							}
						}
					}catch(\Throwable $e){
						$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.fileError", [$file, $directory, $e->getMessage()]));
						$this->server->getLogger()->logException($e);
					}
				}
			}

			while(count($plugins) > 0){
				$missingDependency = true;
				foreach($plugins as $name => $file){
					if(isset($dependencies[$name])){
						foreach($dependencies[$name] as $key => $dependency){
							if(isset($loadedPlugins[$dependency]) or $this->getPlugin($dependency) instanceof Plugin){
								unset($dependencies[$name][$key]);
							}elseif(!isset($plugins[$dependency])){
								$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [$name, "%pocketmine.plugin.unknownDependency"]));
								break;
							}
						}

						if(count($dependencies[$name]) === 0){
							unset($dependencies[$name]);
						}
					}

					if(isset($softDependencies[$name])){
						foreach($softDependencies[$name] as $key => $dependency){
							if(isset($loadedPlugins[$dependency]) or $this->getPlugin($dependency) instanceof Plugin){
								unset($softDependencies[$name][$key]);
							}
						}

						if(count($softDependencies[$name]) === 0){
							unset($softDependencies[$name]);
						}
					}

					if(!isset($dependencies[$name]) and !isset($softDependencies[$name])){
						unset($plugins[$name]);
						$missingDependency = false;
						if($plugin = $this->loadPlugin($file, $loaders) and $plugin instanceof Plugin){
							$loadedPlugins[$name] = $plugin;
						}else{
							$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.genericLoadError", [$name]));
						}
					}
				}

				if($missingDependency === true){
					foreach($plugins as $name => $file){
						if(!isset($dependencies[$name])){
							unset($softDependencies[$name]);
							unset($plugins[$name]);
							$missingDependency = false;
							if($plugin = $this->loadPlugin($file, $loaders) and $plugin instanceof Plugin){
								$loadedPlugins[$name] = $plugin;
							}else{
								$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.genericLoadError", [$name]));
							}
						}
					}

					
					if($missingDependency === true){
						foreach($plugins as $name => $file){
							$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [$name, "%pocketmine.plugin.circularDependency"]));
						}
						$plugins = [];
					}
				}
			}

			TimingsCommand::$timingStart = microtime(true);

			return $loadedPlugins;
		}else{
			TimingsCommand::$timingStart = microtime(true);

			return [];
		}
	}

	
	public function getPermission($name){
		if(isset($this->permissions[$name])){
			return $this->permissions[$name];
		}

		return null;
	}

	
	public function addPermission(Permission $permission){
		if(!isset($this->permissions[$permission->getName()])){
			$this->permissions[$permission->getName()] = $permission;
			$this->calculatePermissionDefault($permission);

			return true;
		}

		return false;
	}

	
	public function removePermission($permission){
		if($permission instanceof Permission){
			unset($this->permissions[$permission->getName()]);
		}else{
			unset($this->permissions[$permission]);
		}
	}

	
	public function getDefaultPermissions($op){
		if($op === true){
			return $this->defaultPermsOp;
		}else{
			return $this->defaultPerms;
		}
	}

	
	public function recalculatePermissionDefaults(Permission $permission){
		if(isset($this->permissions[$permission->getName()])){
			unset($this->defaultPermsOp[$permission->getName()]);
			unset($this->defaultPerms[$permission->getName()]);
			$this->calculatePermissionDefault($permission);
		}
	}

	
	private function calculatePermissionDefault(Permission $permission){
		Timings::$permissionDefaultTimer->startTiming();
		if($permission->getDefault() === Permission::DEFAULT_OP or $permission->getDefault() === Permission::DEFAULT_TRUE){
			$this->defaultPermsOp[$permission->getName()] = $permission;
			$this->dirtyPermissibles(true);
		}

		if($permission->getDefault() === Permission::DEFAULT_NOT_OP or $permission->getDefault() === Permission::DEFAULT_TRUE){
			$this->defaultPerms[$permission->getName()] = $permission;
			$this->dirtyPermissibles(false);
		}
		Timings::$permissionDefaultTimer->stopTiming();
	}

	
	private function dirtyPermissibles($op){
		foreach($this->getDefaultPermSubscriptions($op) as $p){
			$p->recalculatePermissions();
		}
	}

	
	public function subscribeToPermission($permission, Permissible $permissible){
		if(!isset($this->permSubs[$permission])){
			$this->permSubs[$permission] = [];
		}
		$this->permSubs[$permission][spl_object_hash($permissible)] = $permissible;
	}

	
	public function unsubscribeFromPermission($permission, Permissible $permissible){
		if(isset($this->permSubs[$permission])){
			unset($this->permSubs[$permission][spl_object_hash($permissible)]);
			if(count($this->permSubs[$permission]) === 0){
				unset($this->permSubs[$permission]);
			}
		}
	}

	
	public function getPermissionSubscriptions($permission){
		if(isset($this->permSubs[$permission])){
			return $this->permSubs[$permission];
			$subs = [];
			foreach($this->permSubs[$permission] as $k => $perm){
				
				if($perm->acquire()){
					$subs[] = $perm->get();
					$perm->release();
				}else{
					unset($this->permSubs[$permission][$k]);
				}
			}

			return $subs;
		}

		return [];
	}

	
	public function subscribeToDefaultPerms($op, Permissible $permissible){
		if($op === true){
			$this->defSubsOp[spl_object_hash($permissible)] = $permissible;
		}else{
			$this->defSubs[spl_object_hash($permissible)] = $permissible;
		}
	}

	
	public function unsubscribeFromDefaultPerms($op, Permissible $permissible){
		if($op === true){
			unset($this->defSubsOp[spl_object_hash($permissible)]);
		}else{
			unset($this->defSubs[spl_object_hash($permissible)]);
		}
	}

	
	public function getDefaultPermSubscriptions($op){
		$subs = [];

		if($op === true){
			return $this->defSubsOp;
			foreach($this->defSubsOp as $k => $perm){
				
				if($perm->acquire()){
					$subs[] = $perm->get();
					$perm->release();
				}else{
					unset($this->defSubsOp[$k]);
				}
			}
		}else{
			return $this->defSubs;
			foreach($this->defSubs as $k => $perm){
				
				if($perm->acquire()){
					$subs[] = $perm->get();
					$perm->release();
				}else{
					unset($this->defSubs[$k]);
				}
			}
		}

		return $subs;
	}

	
	public function getPermissions(){
		return $this->permissions;
	}

	
	public function isPluginEnabled(Plugin $plugin){
		if($plugin instanceof Plugin and isset($this->plugins[$plugin->getDescription()->getName()])){
			return $plugin->isEnabled();
		}else{
			return false;
		}
	}

	
	public function enablePlugin(Plugin $plugin){
		if(!$plugin->isEnabled()){
			try{
				foreach($plugin->getDescription()->getPermissions() as $perm){
					$this->addPermission($perm);
				}
				$plugin->getPluginLoader()->enablePlugin($plugin);
			}catch(\Throwable $e){
				$this->server->getLogger()->logException($e);
				$this->disablePlugin($plugin);
			}
		}
	}

	
	protected function parseYamlCommands(Plugin $plugin){
		$pluginCmds = [];

		foreach($plugin->getDescription()->getCommands() as $key => $data){
			if(strpos($key, ":") !== false){
				$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.commandError", [$key, $plugin->getDescription()->getFullName()]));
				continue;
			}
			if(is_array($data)){
				$newCmd = new PluginCommand($key, $plugin);
				if(isset($data["description"])){
					$newCmd->setDescription($data["description"]);
				}

				if(isset($data["usage"])){
					$newCmd->setUsage($data["usage"]);
				}

				if(isset($data["aliases"]) and is_array($data["aliases"])){
					$aliasList = [];
					foreach($data["aliases"] as $alias){
						if(strpos($alias, ":") !== false){
							$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.aliasError", [$alias, $plugin->getDescription()->getFullName()]));
							continue;
						}
						$aliasList[] = $alias;
					}

					$newCmd->setAliases($aliasList);
				}

				if(isset($data["permission"])){
					$newCmd->setPermission($data["permission"]);
				}

				if(isset($data["permission-message"])){
					$newCmd->setPermissionMessage($data["permission-message"]);
				}

				$pluginCmds[] = $newCmd;
			}
		}

		return $pluginCmds;
	}

	public function disablePlugins(){
		foreach($this->getPlugins() as $plugin){
			$this->disablePlugin($plugin);
		}
	}

	
	public function disablePlugin(Plugin $plugin){
		if($plugin->isEnabled()){
			try{
				$plugin->getPluginLoader()->disablePlugin($plugin);
			}catch(\Throwable $e){
				$this->server->getLogger()->logException($e);
			}

			$this->server->getScheduler()->cancelTasks($plugin);
			HandlerList::unregisterAll($plugin);
			foreach($plugin->getDescription()->getPermissions() as $perm){
				$this->removePermission($perm);
			}
		}
	}

	public function clearPlugins(){
		$this->disablePlugins();
		$this->plugins = [];
		$this->fileAssociations = [];
		$this->permissions = [];
		$this->defaultPerms = [];
		$this->defaultPermsOp = [];
	}

	
	public function callEvent(Event $event){
		foreach($event->getHandlers()->getRegisteredListeners() as $registration){
			if(!$registration->getPlugin()->isEnabled()){
				continue;
			}

			try{
				$registration->callEvent($event);
			}catch(\Throwable $e){
				$this->server->getLogger()->critical(
					$this->server->getLanguage()->translateString("pocketmine.plugin.eventError", [
						$event->getEventName(),
						$registration->getPlugin()->getDescription()->getFullName(),
						$e->getMessage(),
						get_class($registration->getListener())
					]));
				$this->server->getLogger()->logException($e);
			}
		}
	}

	
	public function registerEvents(Listener $listener, Plugin $plugin){
		if(!$plugin->isEnabled()){
			throw new PluginException("Plugin attempted to register " . get_class($listener) . " while not enabled");
		}

		$reflection = new \ReflectionClass(get_class($listener));
		foreach($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
			if(!$method->isStatic()){
				$priority = EventPriority::NORMAL;
				$ignoreCancelled = false;
				if(preg_match("/^[\t ]*\\* @priority[\t ]{1,}([a-zA-Z]{1,})/m", (string) $method->getDocComment(), $matches) > 0){
					$matches[1] = strtoupper($matches[1]);
					if(defined(EventPriority::class . "::" . $matches[1])){
						$priority = constant(EventPriority::class . "::" . $matches[1]);
					}
				}
				if(preg_match("/^[\t ]*\\* @ignoreCancelled[\t ]{1,}([a-zA-Z]{1,})/m", (string) $method->getDocComment(), $matches) > 0){
					$matches[1] = strtolower($matches[1]);
					if($matches[1] === "false"){
						$ignoreCancelled = false;
					}elseif($matches[1] === "true"){
						$ignoreCancelled = true;
					}
				}

				$parameters = $method->getParameters();
				$firstParam = $parameters[0] ?? null;
				$paramType = $firstParam !== null ? $firstParam->getType() : null;
				$paramClassName = ($paramType instanceof \ReflectionNamedType && !$paramType->isBuiltin()) ? $paramType->getName() : null;
				if(count($parameters) === 1 and $paramClassName !== null and is_subclass_of($paramClassName, Event::class)){
					$class = $paramClassName;
					$reflection = new \ReflectionClass($class);
					if(strpos((string) $reflection->getDocComment(), "@deprecated") !== false and $this->server->getProperty("settings.deprecated-verbose", true)){
						$this->server->getLogger()->warning($this->server->getLanguage()->translateString("pocketmine.plugin.deprecatedEvent", [
							$plugin->getName(),
							$class,
							get_class($listener) . "->" . $method->getName() . "()"
						]));
					}
					$this->registerEvent($class, $listener, $priority, new MethodEventExecutor($method->getName()), $plugin, $ignoreCancelled);
				}
			}
		}
	}

	
	public function registerEvent($event, Listener $listener, $priority, EventExecutor $executor, Plugin $plugin, $ignoreCancelled = false){
		if(!is_subclass_of($event, Event::class)){
			throw new PluginException($event . " is not an Event");
		}
		$class = new \ReflectionClass($event);
		if($class->isAbstract()){
			throw new PluginException($event . " is an abstract Event");
		}
		if($class->getProperty("handlerList")->getDeclaringClass()->getName() !== $event){
			throw new PluginException($event . " does not have a handler list");
		}

		if(!$plugin->isEnabled()){
			throw new PluginException("Plugin attempted to register " . $event . " while not enabled");
		}

		$timings = new TimingsHandler("Plugin: " . $plugin->getDescription()->getFullName() . " Event: " . get_class($listener) . "::" . ($executor instanceof MethodEventExecutor ? $executor->getMethod() : "???") . "(" . (new \ReflectionClass($event))->getShortName() . ")", self::$pluginParentTimer);

		$this->getEventListeners($event)->register(new RegisteredListener($listener, $executor, $priority, $plugin, $ignoreCancelled, $timings));
	}

	
	private function getEventListeners($event){
		if($event::$handlerList === null){
			$event::$handlerList = new HandlerList();
		}

		return $event::$handlerList;
	}

	
	public function useTimings(){
		return self::$useTimings;
	}

	
	public function setUseTimings($use){
		self::$useTimings = (bool) $use;
	}

}
