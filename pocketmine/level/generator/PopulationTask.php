<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
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


/**
 * Bugs of Trees in generation fixed xd
 */
class PopulationTask extends AsyncTask{


        public $state;
        public $levelId;
        public $chunk;
        public $chunkClass;

        public $chunk0;
        public $chunk1;
        public $chunk2;
        public $chunk3;
        //center chunk
        public $chunk5;
        public $chunk6;
        public $chunk7;
        public $chunk8;

        public function __construct(Level $level, FullChunk $chunk){
                $this->state = true;
                $this->levelId = $level->getId();
                $this->chunk = $chunk->toFastBinary();
                $this->chunkClass = get_class($chunk);

                for($i = 0; $i < 9; ++$i){
                        if($i === 4){
                                continue;
                        }
                        $xx = -1 + $i % 3;
                        $zz = -1 + (int) ($i / 3);
                        $ck = $level->getChunk($chunk->getX() + $xx, $chunk->getZ() + $zz, false);
                        $this->{"chunk$i"} = $ck !== null ? $ck->toFastBinary() : null;
                }
        }

        public function onRun(){
                /** @var SimpleChunkManager $manager */
                $manager = $this->getFromThreadStore("generation.level{$this->levelId}.manager");
                /** @var Generator $generator */
                $generator = $this->getFromThreadStore("generation.level{$this->levelId}.generator");
                if($manager === null or $generator === null){
                        $this->state = false;
                        return;
                }

                /** @var FullChunk[] $chunks */
                $chunks = [];
                /** @var FullChunk $chunkC */
                $chunkC = $this->chunkClass;

                $chunk = $chunkC::fromFastBinary($this->chunk);

                for($i = 0; $i < 9; ++$i){
                        if($i === 4){
                                continue;
                        }
                        $xx = -1 + $i % 3;
                        $zz = -1 + (int) ($i / 3);
                        $ck = $this->{"chunk$i"};
                        if($ck === null){
                                $chunks[$i] = $chunkC::getEmptyChunk($chunk->getX() + $xx, $chunk->getZ() + $zz);
                        }else{
                                $chunks[$i] = $chunkC::fromFastBinary($ck);
                        }
                }

                if($chunk === null){
                        //TODO error
                        return;
                }

                $manager->setChunk($chunk->getX(), $chunk->getZ(), $chunk);
                if(!$chunk->isGenerated()){
                        $generator->generateChunk($chunk->getX(), $chunk->getZ());
                        $chunk = $manager->getChunk($chunk->getX(), $chunk->getZ());
                        if($chunk === null){
                                // Generator removed the chunk — abort.
                                return;
                        }
                        $chunk->setGenerated();
                }

                $generatedNeighbors = [];
                foreach($chunks as $i => $c){
                        if($c !== null){
                                $manager->setChunk($c->getX(), $c->getZ(), $c);
                                if(!$c->isGenerated()){
                                        $generator->generateChunk($c->getX(), $c->getZ());
                                        $c = $manager->getChunk($c->getX(), $c->getZ());
                                        if($c !== null){
                                                $c->setGenerated();
                                                $chunks[$i] = $c;
                                                $generatedNeighbors[$i] = true;
                                        }
                                }
                        }
                }

                $generator->populateChunk($chunk->getX(), $chunk->getZ());

                $chunk = $manager->getChunk($chunk->getX(), $chunk->getZ());
                if($chunk === null){
                        return;
                }
                $chunk->recalculateHeightMap();
                $chunk->populateSkyLight();
                $chunk->setLightPopulated();
                $chunk->setPopulated();
                $this->chunk = $chunk->toFastBinary();

                // Remove the center chunk from the manager so the
                // neighbor re-fetch below only touches neighbors.
                $manager->setChunk($chunk->getX(), $chunk->getZ(), null);

                foreach($chunks as $i => $c){
                        if($c !== null){
                                $c = $manager->getChunk($c->getX(), $c->getZ());
                                if($c === null){
                                        $chunks[$i] = null;
                                        continue;
                                }
                                $chunks[$i] = $c;
                                if(isset($generatedNeighbors[$i])){
                                        // Always persist freshly-generated neighbors.
                                        $c->setChanged(true);
                                }elseif(!$c->hasChanged()){
                                        $chunks[$i] = null;
                                }
                        }else{
                                //This way non-changed chunks are not set
                                $chunks[$i] = null;
                        }
                }

                $manager->cleanChunks();

                for($i = 0; $i < 9; ++$i){
                        if($i === 4){
                                continue;
                        }

                        $this->{"chunk$i"} = $chunks[$i] !== null ? $chunks[$i]->toFastBinary() : null;
                }
        }

        public function onCompletion(Server $server){
                $level = $server->getLevel($this->levelId);
                if($level !== null){
                        if($this->state === false){
                                $level->registerGenerator();
                                return;
                        }

                        /** @var FullChunk $chunkC */
                        $chunkC = $this->chunkClass;

                        $chunk = $chunkC::fromFastBinary($this->chunk, $level->getProvider());

                        if($chunk === null){
                                //TODO error
                                return;
                        }

                        for($i = 0; $i < 9; ++$i){
                                if($i === 4){
                                        continue;
                                }
                                $c = $this->{"chunk$i"};
                                if($c !== null){
                                        $c = $chunkC::fromFastBinary($c, $level->getProvider());
                                        if($c !== null){
                                                $level->generateChunkCallback($c->getX(), $c->getZ(), $c);
                                        }
                                }
                        }

                        $level->generateChunkCallback($chunk->getX(), $chunk->getZ(), $chunk);
                }
        }
}
