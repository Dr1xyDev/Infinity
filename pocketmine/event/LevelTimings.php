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

use pocketmine\level\Level;

class LevelTimings{

	
	public $mobSpawn;
	
	public $doChunkUnload;
	
	public $doPortalForcer;
	
	public $doTickPending;
	
	public $doTickTiles;
	
	public $doVillages;
	
	public $doChunkMap;
	
	public $doChunkGC;
	
	public $doSounds;
	
	public $entityTick;
	
	public $tileEntityTick;
	
	public $tileEntityPending;
	
	public $tracker;
	
	public $doTick;
	
	public $tickEntities;

	
	public $syncChunkSendTimer;
	
	public $syncChunkSendPrepareTimer;

	
	public $syncChunkLoadTimer;
	
	public $syncChunkLoadDataTimer;
	
	public $syncChunkLoadStructuresTimer;
	
	public $syncChunkLoadEntitiesTimer;
	
	public $syncChunkLoadTileEntitiesTimer;
	
	public $syncChunkLoadTileTicksTimer;
	
	public $syncChunkLoadPostTimer;

	public function __construct(Level $level){
		$name = $level->getFolderName() . " - ";

		$this->mobSpawn = new TimingsHandler("** " . $name . "mobSpawn");
		$this->doChunkUnload = new TimingsHandler("** " . $name . "doChunkUnload");
		$this->doTickPending = new TimingsHandler("** " . $name . "doTickPending");
		$this->doTickTiles = new TimingsHandler("** " . $name . "doTickTiles");
		$this->doVillages = new TimingsHandler("** " . $name . "doVillages");
		$this->doChunkMap = new TimingsHandler("** " . $name . "doChunkMap");
		$this->doSounds = new TimingsHandler("** " . $name . "doSounds");
		$this->doChunkGC = new TimingsHandler("** " . $name . "doChunkGC");
		$this->doPortalForcer = new TimingsHandler("** " . $name . "doPortalForcer");
		$this->entityTick = new TimingsHandler("** " . $name . "entityTick");
		$this->tileEntityTick = new TimingsHandler("** " . $name . "tileEntityTick");
		$this->tileEntityPending = new TimingsHandler("** " . $name . "tileEntityPending");

		$this->syncChunkSendTimer = new TimingsHandler("** " . $name . "syncChunkSend");
		$this->syncChunkSendPrepareTimer = new TimingsHandler("** " . $name . "syncChunkSendPrepare");

		$this->syncChunkLoadTimer = new TimingsHandler("** " . $name . "syncChunkLoad");
		$this->syncChunkLoadDataTimer = new TimingsHandler("** " . $name . "syncChunkLoad - Data");
		$this->syncChunkLoadStructuresTimer = new TimingsHandler("** " . $name . "syncChunkLoad - Structures");
		$this->syncChunkLoadEntitiesTimer = new TimingsHandler("** " . $name . "syncChunkLoad - Entities");
		$this->syncChunkLoadTileEntitiesTimer = new TimingsHandler("** " . $name . "syncChunkLoad - TileEntities");
		$this->syncChunkLoadTileTicksTimer = new TimingsHandler("** " . $name . "syncChunkLoad - TileTicks");
		$this->syncChunkLoadPostTimer = new TimingsHandler("** " . $name . "syncChunkLoad - Post");

		$this->tracker = new TimingsHandler($name . "tracker");
		$this->doTick = new TimingsHandler($name . "doTick");
		$this->tickEntities = new TimingsHandler($name . "tickEntities");
	}

}