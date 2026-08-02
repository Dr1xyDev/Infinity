<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace {
	function safe_var_dump(){
		static $cnt = 0;
		foreach(func_get_args() as $var){
			switch(true){
				case is_array($var):
					echo str_repeat("  ", $cnt) . "array(" . count($var) . ") {" . PHP_EOL;
					foreach($var as $key => $value){
						echo str_repeat("  ", $cnt + 1) . "[" . (is_integer($key) ? $key : '"' . $key . '"') . "]=>" . PHP_EOL;
						++$cnt;
						safe_var_dump($value);
						--$cnt;
					}
					echo str_repeat("  ", $cnt) . "}" . PHP_EOL;
					break;
				case is_int($var):
					echo str_repeat("  ", $cnt) . "int(" . $var . ")" . PHP_EOL;
					break;
				case is_float($var):
					echo str_repeat("  ", $cnt) . "float(" . $var . ")" . PHP_EOL;
					break;
				case is_bool($var):
					echo str_repeat("  ", $cnt) . "bool(" . ($var === true ? "true" : "false") . ")" . PHP_EOL;
					break;
				case is_string($var):
					echo str_repeat("  ", $cnt) . "string(" . strlen($var) . ") \"$var\"" . PHP_EOL;
					break;
				case is_resource($var):
					echo str_repeat("  ", $cnt) . "resource() of type (" . get_resource_type($var) . ")" . PHP_EOL;
					break;
				case is_object($var):
					echo str_repeat("  ", $cnt) . "object(" . get_class($var) . ")" . PHP_EOL;
					break;
				case is_null($var):
					echo str_repeat("  ", $cnt) . "NULL" . PHP_EOL;
					break;
			}
		}
	}

	function dummy(){

	}
}

namespace pocketmine {
	use pocketmine\utils\Binary;
	use pocketmine\utils\MainLogger;
	use pocketmine\utils\ServerKiller;
	use pocketmine\utils\Terminal;
	use pocketmine\utils\Utils;
	use pocketmine\wizard\Installer;

	const VERSION = "v1.1"; 
	const API_VERSION = "2.0.0";
	const CODENAME = "https://github.com/Dr1xyDev/Infinity";
	const MINECRAFT_VERSION = "v0.15.10";
	const MINECRAFT_VERSION_NETWORK = "0.15.10";
	const GENISYS_API_VERSION = '1.9.3';

	

	if(\Phar::running(true) !== ""){
		@define('pocketmine\PATH', \Phar::running(true) . "/");
	}else{
		@define('pocketmine\PATH', \getcwd() . DIRECTORY_SEPARATOR);
	}

	if(version_compare("8.0.0", PHP_VERSION) > 0){
		echo "[CRITICAL] This software requires PHP 8.0+, but you have PHP " . PHP_VERSION . "." . PHP_EOL;
		echo "[CRITICAL] Please use the installer provided on the homepage." . PHP_EOL;
		exit(1);
	}

	if(!extension_loaded("pthreads")){
		echo "[CRITICAL] Unable to find the pthreads extension." . PHP_EOL;
		echo "[CRITICAL] Please use the installer provided on the homepage." . PHP_EOL;
		exit(1);
	}

	if(!class_exists("ClassLoader", false)){
		require_once(\pocketmine\PATH . "src/spl/ClassLoader.php");
		require_once(\pocketmine\PATH . "src/spl/BaseClassLoader.php");
		require_once(\pocketmine\PATH . "src/pocketmine/CompatibleClassLoader.php");
	}

	$autoloader = new CompatibleClassLoader();
	$autoloader->addPath(\pocketmine\PATH . "src");
	$autoloader->addPath(\pocketmine\PATH . "src" . DIRECTORY_SEPARATOR . "spl");
	$autoloader->register(true);

	set_time_limit(0); 

	gc_enable();
	error_reporting(-1);
	ini_set("allow_url_fopen", 1);
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
	ini_set("default_charset", "utf-8");

	ini_set("memory_limit", -1);
	define('pocketmine\START_TIME', microtime(true));

	$opts = getopt("", ["data:", "plugins:", "no-wizard", "enable-profiler"]);

	define('pocketmine\DATA', isset($opts["data"]) ? $opts["data"] . DIRECTORY_SEPARATOR : \getcwd() . DIRECTORY_SEPARATOR);
	define('pocketmine\PLUGIN_PATH', isset($opts["plugins"]) ? $opts["plugins"] . DIRECTORY_SEPARATOR : \getcwd() . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR);

	Terminal::init();

	define('pocketmine\ANSI', Terminal::hasFormattingCodes());

	if(!file_exists(\pocketmine\DATA)){
		mkdir(\pocketmine\DATA, 0777, true);
	}

	
	date_default_timezone_set("UTC");

	$logger = new MainLogger(\pocketmine\DATA . "server.log", \pocketmine\ANSI);

	if(!ini_get("date.timezone")){
		if(($timezone = detect_system_timezone()) and date_default_timezone_set($timezone)){
			
			
			ini_set("date.timezone", $timezone);
		}else{
			
			if($response = Utils::getURL("http://ip-api.com/json")
				and $ip_geolocation_data = json_decode($response, true)
				and $ip_geolocation_data['status'] !== 'fail'
				and date_default_timezone_set($ip_geolocation_data['timezone'])
			){
				
				ini_set("date.timezone", $ip_geolocation_data['timezone']);
			}else{
				ini_set("date.timezone", "UTC");
				date_default_timezone_set("UTC");
				$logger->warning("Timezone could not be automatically determined. An incorrect timezone will result in incorrect timestamps on console logs. It has been set to \"UTC\" by default. You can change it on the php.ini file.");
			}
		}
	}else{
		
		$timezone = ini_get("date.timezone");
		if(strpos($timezone, "/") === false){
			$default_timezone = timezone_name_from_abbr($timezone);
			ini_set("date.timezone", $default_timezone);
			date_default_timezone_set($default_timezone);
		} else {
			date_default_timezone_set($timezone);
		}
	}

	function detect_system_timezone(){
		switch(Utils::getOS()){
			case 'win':
				$regex = '/(UTC)(\+*\-*\d*\d*\:*\d*\d*)/';

				
				exec("wmic timezone get Caption", $output);

				$string = trim(implode("\n", $output));

				
				preg_match($regex, $string, $matches);

				if(!isset($matches[2])){
					return false;
				}

				$offset = $matches[2];

				if($offset == ""){
					return "UTC";
				}

				return parse_offset($offset);
				break;
			case 'linux':
				
				if(file_exists('/etc/timezone')){
					$data = file_get_contents('/etc/timezone');
					if($data){
						return trim($data);
					}
				}

				
				if(file_exists('/etc/sysconfig/clock')){
					$data = parse_ini_file('/etc/sysconfig/clock');
					if(!empty($data['ZONE'])){
						return trim($data['ZONE']);
					}
				}

				

				$offset = trim(exec('date +%:z'));

				if($offset == "+00:00"){
					return "UTC";
				}

				return parse_offset($offset);
				break;
			case 'mac':
				if(is_link('/etc/localtime')){
					$filename = readlink('/etc/localtime');
					if(strpos($filename, '/usr/share/zoneinfo/') === 0){
						$timezone = substr($filename, 20);
						return trim($timezone);
					}
				}

				return false;
				break;
			default:
				return false;
				break;
		}
	}

	
	function parse_offset($offset){
		
		if(strpos($offset, '-') !== false){
			$negative_offset = true;
			$offset = str_replace('-', '', $offset);
		}else{
			if(strpos($offset, '+') !== false){
				$negative_offset = false;
				$offset = str_replace('+', '', $offset);
			}else{
				return false;
			}
		}

		$parsed = date_parse($offset);
		$offset = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];

		
		if($negative_offset == true){
			$offset = -abs($offset);
		}

		
		
		
		foreach(timezone_abbreviations_list() as $zones){
			foreach($zones as $timezone){
				if($timezone['offset'] == $offset){
					return $timezone['timezone_id'];
				}
			}
		}

		return false;
	}

	if(isset($opts["enable-profiler"])){
		if(function_exists("profiler_enable")){
			\profiler_enable();
			$logger->notice("Execution is being profiled");
		}else{
			$logger->notice("No profiler found. Please install https://github.com/krakjoe/profiler");
		}
	}

	function kill($pid){
		switch(Utils::getOS()){
			case "win":
				exec("taskkill.exe /F /PID " . ((int) $pid) . " > NUL");
				break;
			case "mac":
			case "linux":
			default:
				if(function_exists("posix_kill")){
					posix_kill($pid, SIGKILL);
				}else{
					exec("kill -9 " . ((int)$pid) . " > /dev/null 2>&1");
				}
		}
	}

	
	function getReferenceCount($value, $includeCurrent = true){
		ob_start();
		debug_zval_dump($value);
		$ret = explode("\n", ob_get_contents());
		ob_end_clean();

		if(count($ret) >= 1 and preg_match('/^.* refcount\\(([0-9]+)\\)\\{$/', trim($ret[0]), $m) > 0){
			return ((int) $m[1]) - ($includeCurrent ? 3 : 4); 
		}
		return -1;
	}

	function getTrace($start = 1, $trace = null){
		if($trace === null){
			if(function_exists("xdebug_get_function_stack")){
				$trace = array_reverse(xdebug_get_function_stack());
			}else{
				$e = new \Exception();
				$trace = $e->getTrace();
			}
		}

		$messages = [];
		$j = 0;
		for($i = (int) $start; isset($trace[$i]); ++$i, ++$j){
			$params = "";
			if(isset($trace[$i]["args"]) or isset($trace[$i]["params"])){
				if(isset($trace[$i]["args"])){
					$args = $trace[$i]["args"];
				}else{
					$args = $trace[$i]["params"];
				}
				foreach($args as $name => $value){
					$params .= (is_object($value) ? get_class($value) . " " . (method_exists($value, "__toString") ? $value->__toString() : "object") : gettype($value) . " " . (is_array($value) ? "Array()" : Utils::printable(@strval($value)))) . ", ";
				}
			}
			$messages[] = "#$j " . (isset($trace[$i]["file"]) ? cleanPath($trace[$i]["file"]) : "") . "(" . (isset($trace[$i]["line"]) ? $trace[$i]["line"] : "") . "): " . (isset($trace[$i]["class"]) ? $trace[$i]["class"] . (($trace[$i]["type"] === "dynamic" or $trace[$i]["type"] === "->") ? "->" : "::") : "") . $trace[$i]["function"] . "(" . Utils::printable(substr($params, 0, -2)) . ")";
		}

		return $messages;
	}

	function cleanPath($path){
		return rtrim(str_replace(["\\", ".php", "phar://", rtrim(str_replace(["\\", "phar://"], ["/", ""], \pocketmine\PATH), "/"), rtrim(str_replace(["\\", "phar://"], ["/", ""], \pocketmine\PLUGIN_PATH), "/")], ["/", "", "", "", ""], $path), "/");
	}

	$errors = 0;

	if(php_sapi_name() !== "cli"){
		$logger->critical("You must run PocketMine-MP using the CLI.");
		++$errors;
	}

	if(!extension_loaded("sockets")){
		$logger->critical("Unable to find the Socket extension.");
		++$errors;
	}

	$pthreads_version = phpversion("pthreads");
	if(substr_count($pthreads_version, ".") < 2){
		$pthreads_version = "0.$pthreads_version";
	}
	if(version_compare($pthreads_version, "3.2.0") < 0){
		$logger->critical("pthreads >= 3.2.0 is required, while you have $pthreads_version.");
		++$errors;
	}

	if(!extension_loaded("uopz")){
		
	}

	if(extension_loaded("pocketmine")){
		if(version_compare(phpversion("pocketmine"), "0.0.1") < 0){
			$logger->critical("You have the native PocketMine extension, but your version is lower than 0.0.1.");
			++$errors;
		}elseif(version_compare(phpversion("pocketmine"), "0.0.4") > 0){
			$logger->critical("You have the native PocketMine extension, but your version is higher than 0.0.4.");
			++$errors;
		}
	}
	
	if(extension_loaded("xdebug")){
		$logger->warning("

	You are running PocketMine with xdebug enabled. This has a major impact on performance.

		");
	}

	if(!extension_loaded("curl")){
		$logger->critical("Unable to find the cURL extension.");
		++$errors;
	}

	if(!extension_loaded("yaml")){
		$logger->critical("Unable to find the YAML extension.");
		++$errors;
	}

	if(!extension_loaded("sqlite3")){
		$logger->critical("Unable to find the SQLite3 extension.");
		++$errors;
	}

	if(!extension_loaded("zlib")){
		$logger->critical("Unable to find the Zlib extension.");
		++$errors;
	}

	if($errors > 0){
		$logger->critical("Please update your PHP from itxtech.org/download, or recompile PHP again.");
		$logger->shutdown();
		$logger->join();
		exit(1); 
	}

	if(file_exists(\pocketmine\PATH . ".git/refs/heads/master")){ 
		define('pocketmine\GIT_COMMIT', strtolower(trim(file_get_contents(\pocketmine\PATH . ".git/refs/heads/master"))));
	}else{
		define('pocketmine\GIT_COMMIT', "0000000000000000000000000000000000000000");
	}

	@define("ENDIANNESS", (pack("d", 1) === "\77\360\0\0\0\0\0\0" ? Binary::BIG_ENDIAN : Binary::LITTLE_ENDIAN));
	@define("INT32_MASK", is_int(0xffffffff) ? 0xffffffff : -1);
	@ini_set("opcache.mmap_base", bin2hex(random_bytes(8))); 

	$lang = "unknown";
	if(!file_exists(\pocketmine\DATA . "server.properties") and !isset($opts["no-wizard"])){
		$inst = new Installer();
		$lang = $inst->getDefaultLang();
	}

	

	ThreadManager::init();
	$server = new Server($autoloader, $logger, \pocketmine\PATH, \pocketmine\DATA, \pocketmine\PLUGIN_PATH, $lang);

	$logger->info("Stopping other threads");

	foreach(ThreadManager::getInstance()->getAll() as $id => $thread){
		$logger->debug("Stopping " . (new \ReflectionClass($thread))->getShortName() . " thread");
		$thread->quit();
	}

	$killer = new ServerKiller(8);
	$killer->start();

	$logger->shutdown();
	$logger->join();

	echo "Server has stopped" . Terminal::$FORMAT_RESET . "\n";

	exit(0);

}
