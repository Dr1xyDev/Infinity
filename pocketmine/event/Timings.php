<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\event;

use pocketmine\entity\Entity;
use pocketmine\network\protocol\DataPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginManager;
use pocketmine\scheduler\PluginTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\tile\Tile;

abstract class Timings{

	
	public static $fullTickTimer;
	
	public static $serverTickTimer;
	
	public static $memoryManagerTimer;
	
	public static $garbageCollectorTimer;
	
	public static $playerListTimer;
	
	public static $playerNetworkTimer;
	
	public static $playerNetworkReceiveTimer;
	
	public static $playerChunkOrderTimer;
	
	public static $playerChunkSendTimer;
	
	public static $connectionTimer;
	
	public static $tickablesTimer;
	
	public static $schedulerTimer;
	
	public static $chunkIOTickTimer;
	
	public static $timeUpdateTimer;
	
	public static $serverCommandTimer;
	
	public static $worldSaveTimer;
	
	public static $generationTimer;
	
	public static $populationTimer;
	
	public static $generationCallbackTimer;
	
	public static $permissibleCalculationTimer;
	
	public static $permissionDefaultTimer;

	
	public static $entityMoveTimer;
	
	public static $tickEntityTimer;
	
	public static $activatedEntityTimer;
	
	public static $tickTileEntityTimer;

	
	public static $timerEntityBaseTick;
	
	public static $timerLivingEntityBaseTick;
	
	public static $timerEntityAI;
	
	public static $timerEntityAICollision;
	
	public static $timerEntityAIMove;
	
	public static $timerEntityTickRest;

	
	public static $schedulerSyncTimer;
	
	public static $schedulerAsyncTimer;

	
	public static $playerCommandTimer;

	
	public static $entityTypeTimingMap = [];
	
	public static $tileEntityTypeTimingMap = [];
	
	public static $packetReceiveTimingMap = [];
	
	public static $packetSendTimingMap = [];
	
	public static $pluginTaskTimingMap = [];

	public static function init(){
		if(self::$serverTickTimer instanceof TimingsHandler){
			return;
		}

		self::$fullTickTimer = new TimingsHandler("Full Server Tick");
		self::$serverTickTimer = new TimingsHandler("** Full Server Tick", self::$fullTickTimer);
		self::$memoryManagerTimer = new TimingsHandler("Memory Manager");
		self::$garbageCollectorTimer = new TimingsHandler("Garbage Collector", self::$memoryManagerTimer);
		self::$playerListTimer = new TimingsHandler("Player List");
		self::$playerNetworkTimer = new TimingsHandler("Player Network Send");
		self::$playerNetworkReceiveTimer = new TimingsHandler("Player Network Receive");
		self::$playerChunkOrderTimer = new TimingsHandler("Player Order Chunks");
		self::$playerChunkSendTimer = new TimingsHandler("Player Send Chunks");
		self::$connectionTimer = new TimingsHandler("Connection Handler");
		self::$tickablesTimer = new TimingsHandler("Tickables");
		self::$schedulerTimer = new TimingsHandler("Scheduler");
		self::$chunkIOTickTimer = new TimingsHandler("ChunkIOTick");
		self::$timeUpdateTimer = new TimingsHandler("Time Update");
		self::$serverCommandTimer = new TimingsHandler("Server Command");
		self::$worldSaveTimer = new TimingsHandler("World Save");
		self::$generationTimer = new TimingsHandler("World Generation");
		self::$populationTimer = new TimingsHandler("World Population");
		self::$generationCallbackTimer = new TimingsHandler("World Generation Callback");
		self::$permissibleCalculationTimer = new TimingsHandler("Permissible Calculation");
		self::$permissionDefaultTimer = new TimingsHandler("Default Permission Calculation");

		self::$entityMoveTimer = new TimingsHandler("** entityMove");
		self::$tickEntityTimer = new TimingsHandler("** tickEntity");
		self::$activatedEntityTimer = new TimingsHandler("** activatedTickEntity");
		self::$tickTileEntityTimer = new TimingsHandler("** tickTileEntity");

		self::$timerEntityBaseTick = new TimingsHandler("** entityBaseTick");
		self::$timerLivingEntityBaseTick = new TimingsHandler("** livingEntityBaseTick");
		self::$timerEntityAI = new TimingsHandler("** livingEntityAI");
		self::$timerEntityAICollision = new TimingsHandler("** livingEntityAICollision");
		self::$timerEntityAIMove = new TimingsHandler("** livingEntityAIMove");
		self::$timerEntityTickRest = new TimingsHandler("** livingEntityTickRest");

		self::$schedulerSyncTimer = new TimingsHandler("** Scheduler - Sync Tasks", PluginManager::$pluginParentTimer);
		self::$schedulerAsyncTimer = new TimingsHandler("** Scheduler - Async Tasks");

		self::$playerCommandTimer = new TimingsHandler("** playerCommand");

	}

	
	public static function getPluginTaskTimings(TaskHandler $task, $period){
		$ftask = $task->getTask();
		if($ftask instanceof PluginTask and $ftask->getOwner() !== null){
			$plugin = $ftask->getOwner()->getDescription()->getFullName();
		}elseif($task->timingName !== null){
			$plugin = "Scheduler";
		}else{
			$plugin = "Unknown";
		}

		$taskname = $task->getTaskName();

		$name = "Task: " . $plugin . " Runnable: " . $taskname;

		if($period > 0){
			$name .= "(interval:" . $period . ")";
		}else{
			$name .= "(Single)";
		}

		if(!isset(self::$pluginTaskTimingMap[$name])){
			self::$pluginTaskTimingMap[$name] = new TimingsHandler($name, self::$schedulerSyncTimer);
		}

		return self::$pluginTaskTimingMap[$name];
	}

	
	public static function getEntityTimings(Entity $entity){
		$entityType = (new \ReflectionClass($entity))->getShortName();
		if(!isset(self::$entityTypeTimingMap[$entityType])){
			if($entity instanceof Player){
				self::$entityTypeTimingMap[$entityType] = new TimingsHandler("** tickEntity - EntityPlayer", self::$tickEntityTimer);
			}else{
				self::$entityTypeTimingMap[$entityType] = new TimingsHandler("** tickEntity - " . $entityType, self::$tickEntityTimer);
			}
		}

		return self::$entityTypeTimingMap[$entityType];
	}

	
	public static function getTileEntityTimings(Tile $tile){
		$tileType = (new \ReflectionClass($tile))->getShortName();
		if(!isset(self::$tileEntityTypeTimingMap[$tileType])){
			self::$tileEntityTypeTimingMap[$tileType] = new TimingsHandler("** tickTileEntity - " . $tileType, self::$tickTileEntityTimer);
		}

		return self::$tileEntityTypeTimingMap[$tileType];
	}

	
	public static function getReceiveDataPacketTimings( $pk){
		if(!isset(self::$packetReceiveTimingMap[$pk::NETWORK_ID])){
			$pkName = (new \ReflectionClass($pk))->getShortName();
			self::$packetReceiveTimingMap[$pk::NETWORK_ID] = new TimingsHandler("** receivePacket - " . $pkName . " [0x" . dechex($pk::NETWORK_ID) . "]", self::$playerNetworkReceiveTimer);
		}

		return self::$packetReceiveTimingMap[$pk::NETWORK_ID];
	}

	
	public static function getSendDataPacketTimings( $pk){
		if(!isset(self::$packetSendTimingMap[$pk::NETWORK_ID])){
			$pkName = (new \ReflectionClass($pk))->getShortName();
			self::$packetSendTimingMap[$pk::NETWORK_ID] = new TimingsHandler("** sendPacket - " . $pkName . " [0x" . dechex($pk::NETWORK_ID) . "]", self::$playerNetworkTimer);
		}

		return self::$packetSendTimingMap[$pk::NETWORK_ID];
	}

}