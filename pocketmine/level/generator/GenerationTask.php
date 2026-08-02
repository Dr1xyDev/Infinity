<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\level\generator;

use pocketmine\level\format\FullChunk;

use pocketmine\level\Level;
use pocketmine\level\SimpleChunkManager;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class GenerationTask extends AsyncTask{

	public $state;
	public $levelId;
	public $chunk;
	public $chunkClass;

	public function __construct(Level $level, FullChunk $chunk){
		$this->state = true;
		$this->levelId = $level->getId();
		$this->chunk = $chunk->toFastBinary();
		$this->chunkClass = get_class($chunk);
	}

	public function onRun(){
		
		$manager = $this->getFromThreadStore("generation.level{$this->levelId}.manager");
		
		$generator = $this->getFromThreadStore("generation.level{$this->levelId}.generator");
		if($manager === null or $generator === null){
			$this->state = false;
			return;
		}

		
		$chunk = $this->chunkClass;
		$chunk = $chunk::fromFastBinary($this->chunk);
		if($chunk === null){
			
			return;
		}

		$manager->setChunk($chunk->getX(), $chunk->getZ(), $chunk);

		$generator->generateChunk($chunk->getX(), $chunk->getZ());

		$chunk = $manager->getChunk($chunk->getX(), $chunk->getZ());
		$chunk->setGenerated();
		$this->chunk = $chunk->toFastBinary();

		$manager->setChunk($chunk->getX(), $chunk->getZ(), null);
	}

	public function onCompletion(Server $server){
		$level = $server->getLevel($this->levelId);
		if($level !== null){
			if($this->state === false){
				$level->registerGenerator();
				return;
			}
			
			$chunk = $this->chunkClass;
			$chunk = $chunk::fromFastBinary($this->chunk, $level->getProvider());
			if($chunk === null){
				
				return;
			}
			$level->generateChunkCallback($chunk->getX(), $chunk->getZ(), $chunk);
		}
	}
}
