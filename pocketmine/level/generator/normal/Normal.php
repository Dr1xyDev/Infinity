<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

    
    
    
    
namespace pocketmine\level\generator\normal;    
    
use pocketmine\block\Block;    
use pocketmine\block\CoalOre;    
use pocketmine\block\DiamondOre;    
use pocketmine\block\Dirt;    
use pocketmine\block\GoldOre;    
use pocketmine\block\Gravel;    
use pocketmine\block\IronOre;    
use pocketmine\block\LapisOre;    
use pocketmine\block\RedstoneOre;    
use pocketmine\block\Stone;    
use pocketmine\level\ChunkManager;    
use pocketmine\level\generator\biome\Biome;    
use pocketmine\level\generator\biome\BiomeSelector;    
use pocketmine\level\generator\Generator;    
use pocketmine\level\generator\noise\Simplex;    
use pocketmine\level\generator\normal\river\RiverNoise;    
use pocketmine\level\generator\normal\biome\HighSavannaBiome;    
use pocketmine\level\generator\normal\river\RiverChunkProcessor;    
use pocketmine\level\generator\normal\river\RiverDecorator;    
use pocketmine\level\generator\normal\river\RiverMask;    
use pocketmine\level\generator\normal\river\RiverCarver;    
use pocketmine\level\generator\object\OreType;    
use pocketmine\level\generator\populator\Cave;    
use pocketmine\level\generator\populator\GroundCover;    
use pocketmine\level\generator\populator\Ore;    
use pocketmine\level\generator\populator\Populator;    
use pocketmine\level\Level;    
use pocketmine\math\Vector3 as Vector3;    
use pocketmine\utils\Random;    
    
class Normal extends Generator{    
        const NAME = "Normal";    
    
            
        protected $populators = [];    
            
        protected $level;    
            
        protected $random;    
        protected $waterHeight = 62;    
        protected $bedrockDepth = 5;    
    
            
        protected $generationPopulators = [];    
            
        protected $noiseBase;    
    
            
        protected $selector;    
    
            
        const HIGH_SAVANNA_RAINFALL_THRESHOLD = 0.65;    
    
            
        const HILLS_DETECTION_RADIUS = 80.0;    
        const HILLS_SAMPLE_RINGS = 4;    
        const HILLS_SAMPLE_DIRECTIONS = 6;    
    
            
        const HILLS_MIN_RIVER_DISTANCE = 40.0;    
    
            
        const CONTINENT_OCEAN_THRESHOLD = -0.05;    
    
            
        const CONTINENT_COAST_BAND = 0.12;    
    
            
        const PLAINS_RIVER_BAND = 80.0;    
    
            
        const PLAINS_GATE_THRESHOLD = 0.85;    
    
            
        const PLAINS_OAK_FRACTION = 0.65;    
    
            
        protected $continentNoise;    
    
            
        protected $riverNoise;    
    
            
        protected $riverProcessor;    
    
            
        protected $riverDecorator;    
    
            
        protected $savannaJungleNoise;    
    
            
        protected $swampRarityNoise;    
        const SWAMP_RARITY_THRESHOLD = 0.88; 
    
            
        protected $plainsGateNoise;    
    
            
        protected $plainsVariantNoise;    
    
        private static $GAUSSIAN_KERNEL = null;    
        private static $SMOOTH_SIZE = 8;    
    
        public function __construct(array $options = []){    
                if(self::$GAUSSIAN_KERNEL === null){    
                        self::generateKernel();    
                }    
        }    
    
        private static function generateKernel(){    
                self::$GAUSSIAN_KERNEL = [];    
    
                $bellSize = 1 / self::$SMOOTH_SIZE;    
                $bellHeight = 2 * self::$SMOOTH_SIZE;    
    
                for($sx = -self::$SMOOTH_SIZE; $sx <= self::$SMOOTH_SIZE; ++$sx){    
                        self::$GAUSSIAN_KERNEL[$sx + self::$SMOOTH_SIZE] = [];    
    
                        for($sz = -self::$SMOOTH_SIZE; $sz <= self::$SMOOTH_SIZE; ++$sz){    
                                $bx = $bellSize * $sx;    
                                $bz = $bellSize * $sz;    
                                self::$GAUSSIAN_KERNEL[$sx + self::$SMOOTH_SIZE][$sz + self::$SMOOTH_SIZE] = $bellHeight * exp(-($bx * $bx + $bz * $bz) / 2);    
                        }    
                }    
        }    
    
        public function getName() : string{    
                return self::NAME;    
        }    
    
        public function getWaterHeight() : int{    
                return $this->waterHeight;    
        }    
    
        public function getSettings(){    
                return [];    
        }    
    
        public function pickBiome($x, $z){    
                $hash = $x * 2345803 ^ $z * 9236449 ^ $this->level->getSeed();    
                $hash *= $hash + 223;    
                $xNoise = $hash >> 20 & 3;    
                $zNoise = $hash >> 22 & 3;    
                if($xNoise == 3){    
                        $xNoise = 1;    
                }    
                if($zNoise == 3){    
                        $zNoise = 1;    
                }    
    
                $px = $x + $xNoise - 1;    
                $pz = $z + $zNoise - 1;    
    
                
                $continent = $this->continentNoise->noise2D($px, $pz, true);    
                if($continent < self::CONTINENT_OCEAN_THRESHOLD){    
                        return Biome::getBiome(Biome::OCEAN);    
                }    
    
                $biome = $this->selector->pickBiome($px, $pz);    
    
                
                if($biome->getId() === Biome::SAVANNA){    
                        $split = ($this->savannaJungleNoise->noise2D($px, $pz, true) + 1) / 2;    
                        
                        if($split > 0.65){    
                                $biome = Biome::getBiome(Biome::JUNGLE);    
                        }    
                }    
    
                
                if($biome->getId() === Biome::SWAMP){    
                        $rarity = ($this->swampRarityNoise->noise2D($px, $pz, true) + 1) / 2;    
                        if($rarity < self::SWAMP_RARITY_THRESHOLD){    
                                $temperature = $this->selector->getTemperature($px, $pz);    
                                $biome = $temperature < 0.55    
                                        ? Biome::getBiome(Biome::SAVANNA)    
                                        : Biome::getBiome(Biome::ROOFED_FOREST);    
                        }    
                }    
    
                
                if($biome->getId() === Biome::SAVANNA){    
                        $rainfall = $this->selector->getRainfall($px, $pz);    
                        if($rainfall > self::HIGH_SAVANNA_RAINFALL_THRESHOLD){    
                                $biome = Biome::getBiome(Biome::HIGH_SAVANNA);    
                        }    
                }    
    
                
                if($biome->getId() === Biome::FOREST){    
                        $distanceToRiver = $this->riverNoise->getEstimatedDistance($px, $pz);    
                        if($distanceToRiver > self::HILLS_MIN_RIVER_DISTANCE    
                                && $this->isNearMountainRoofedOrJungle($px, $pz)){    
                                $biome = Biome::getBiome(Biome::HILLS);    
                        }    
                }    
    
                
                $biome = $this->maybeConvertToPlainsSubbiome($biome, $px, $pz);    
    
                return $biome;    
        }    
    
            
        private function maybeConvertToPlainsSubbiome(Biome $biome, int $px, int $pz) : Biome{    
                
                static $plainsVariant = [    
                        Biome::JUNGLE         => Biome::JUNGLE_PLAINS,    
                        Biome::TAIGA          => Biome::TAIGA_PLAINS,    
                        Biome::DESERT         => Biome::DESERT_PLAINS,    
                        Biome::ROOFED_FOREST  => Biome::ROOFED_PLAINS,    
                        Biome::ICE_PLAINS     => Biome::SNOW_PLAINS,    
                        Biome::BIRCH_FOREST   => Biome::BIRCH_PLAINS,    
                        
                        Biome::PLAINS         => Biome::OAK_PLAINS,    
                        Biome::FOREST         => Biome::OAK_PLAINS,    
                        Biome::SAVANNA        => Biome::OAK_PLAINS,    
                        Biome::SWAMP          => Biome::OAK_PLAINS,    
                ];    
    
                $currentId = $biome->getId();    
                if(!isset($plainsVariant[$currentId])){    
                        return $biome;    
                }    
    
                
                $distanceToRiver = $this->riverNoise->getEstimatedDistance($px, $pz);    
                $continent = $this->continentNoise->noise2D($px, $pz, true);    
    
                
                $nearRiver = $distanceToRiver < self::PLAINS_RIVER_BAND;    
                $nearCoast = $continent >= self::CONTINENT_OCEAN_THRESHOLD    
                        && $continent < (self::CONTINENT_OCEAN_THRESHOLD + self::CONTINENT_COAST_BAND);    
    
                if(!$nearRiver && !$nearCoast){    
                        return $biome;    
                }    
    
                
                $gate = ($this->plainsGateNoise->noise2D($px, $pz, true) + 1.0) / 2.0;    
                if($gate >= self::PLAINS_GATE_THRESHOLD){    
                        return $biome;    
                }    
    
                
                $variant = ($this->plainsVariantNoise->noise2D($px, $pz, true) + 1.0) / 2.0;    
                if($variant < self::PLAINS_OAK_FRACTION){    
                        return Biome::getBiome(Biome::OAK_PLAINS);    
                }    
    
                return Biome::getBiome($plainsVariant[$currentId]);    
        }    
    
            
        private function isNearMountainRoofedOrJungle(int $x, int $z) : bool{    
                for($ring = 1; $ring <= self::HILLS_SAMPLE_RINGS; ++$ring){    
                        $dist = self::HILLS_DETECTION_RADIUS * $ring / self::HILLS_SAMPLE_RINGS;    
    
                        for($a = 0; $a < self::HILLS_SAMPLE_DIRECTIONS; ++$a){    
                                $angle = (2 * M_PI * $a) / self::HILLS_SAMPLE_DIRECTIONS;    
                                $nx = (int) round($x + cos($angle) * $dist);    
                                $nz = (int) round($z + sin($angle) * $dist);    
    
                                if($this->isRawMountainRoofedOrJungle($nx, $nz)){    
                                        return true;    
                                }    
                        }    
                }    
    
                return false;    
        }    
    
            
        private function isRawMountainRoofedOrJungle(int $x, int $z) : bool{    
                $raw = $this->selector->pickBiome($x, $z);    
                $id = $raw->getId();    
    
                if($id === Biome::MOUNTAINS || $id === Biome::SMALL_MOUNTAINS || $id === Biome::ROOFED_FOREST){    
                        return true;    
                }    
    
                if($id === Biome::SAVANNA){    
                        $split = ($this->savannaJungleNoise->noise2D($x, $z, true) + 1) / 2;    
                        if($split > 0.5){    
                                return true; 
                        }    
                }    
    
                return false;    
        }    
    
        public function init(ChunkManager $level, Random $random){    
                $this->level = $level;    
                $this->random = $random;    
                $this->random->setSeed($this->level->getSeed());    
                $this->noiseBase = new Simplex($this->random, 4, 1 / 4, 1 / 32);    
                $this->random->setSeed($this->level->getSeed());    
    
                
                $this->continentNoise = new Simplex($this->random, 3, 1 / 2, 1 / 1536);    
                $this->random->setSeed($this->level->getSeed());    
    
                
                $this->riverNoise = new RiverNoise($this->level->getSeed());    
    
                
                $this->selector = new BiomeSelector($this->random, function($temperature, $rainfall){    
                        if($rainfall < 0.15){    
                                if($temperature < 0.15){    
                                        return Biome::OCEAN;    
                                }elseif($temperature < 0.30){    
                                        return Biome::ICE_PLAINS;    
                                }elseif($temperature < 0.80){    
                                        return Biome::SAVANNA;    
                                }else{    
                                        return Biome::DESERT;    
                                }    
                        }elseif($rainfall < 0.40){    
                                if($temperature < 0.25){    
                                        return Biome::ICE_PLAINS;    
                                }elseif($temperature < 0.40){    
                                        return Biome::PLAINS;    
                                }elseif($temperature < 0.80){    
                                        return Biome::SAVANNA;    
                                }else{    
                                        return Biome::DESERT;    
                                }    
                        }elseif($rainfall < 0.60){    
                                if($temperature < 0.25){    
                                        return Biome::TAIGA;    
                                }elseif($temperature < 0.50){    
                                        return Biome::PLAINS;    
                                }elseif($temperature < 0.55){    
                                        return Biome::FOREST;    
                                }elseif($temperature < 0.80){    
                                        return Biome::SAVANNA;    
                                }else{    
                                        return Biome::DESERT;    
                                }    
                        }elseif($rainfall < 0.75){    
                                if($temperature < 0.20){    
                                        return Biome::TAIGA;    
                                }elseif($temperature < 0.40){    
                                        return Biome::FOREST;    
                                }elseif($temperature < 0.55){    
                                        return Biome::ROOFED_FOREST;    
                                }elseif($temperature < 0.65){    
                                        return Biome::SAVANNA;    
                                }elseif($temperature < 0.95){    
                                        return Biome::SWAMP;    
                                }else{    
                                        return Biome::BIRCH_FOREST;    
                                }    
                        }else{    
                                /*
                                 * Rainfall >= 0.75 → zona fría-húmeda.
                                 * Mountains ocupa temperature 0.0–0.55 (antes solo 0.0–0.20),
                                 * lo que le da ~55 % del eje de temperatura en este band de rainfall.
                                 * Eso hace el bioma amplio, cohesivo y dominante:
                                 * los biomas vecinos son "empujados" porque el suavizado
                                 * gaussiano del generador interpola elevaciones, y Mountains
                                 * tiene maxElevation=145 vs ~80-100 de los demás.
                                 * La transición ocurre naturalmente por ese gradiente de altura.
                                 */
                                if($temperature < 0.55){    
                                        return Biome::MOUNTAINS;    
                                }elseif($temperature < 0.70){    
                                        return Biome::ROOFED_FOREST;    
                                }elseif($temperature < 0.85){    
                                        return Biome::SWAMP;    
                                }else{    
                                        return Biome::SAVANNA;    
                                }    
                        }    
                }, Biome::getBiome(Biome::PLAINS));    
    
                $this->selector->addBiome(Biome::getBiome(Biome::OCEAN));    
                $this->selector->addBiome(Biome::getBiome(Biome::PLAINS));    
                $this->selector->addBiome(Biome::getBiome(Biome::DESERT));    
                $this->selector->addBiome(Biome::getBiome(Biome::MOUNTAINS));    
                $this->selector->addBiome(Biome::getBiome(Biome::FOREST));    
                $this->selector->addBiome(Biome::getBiome(Biome::TAIGA));    
                $this->selector->addBiome(Biome::getBiome(Biome::SWAMP));    
                $this->selector->addBiome(Biome::getBiome(Biome::RIVER));    
                $this->selector->addBiome(Biome::getBiome(Biome::ICE_PLAINS));    
                $this->selector->addBiome(Biome::getBiome(Biome::SMALL_MOUNTAINS));    
                $this->selector->addBiome(Biome::getBiome(Biome::BIRCH_FOREST));    
                $this->selector->addBiome(Biome::getBiome(Biome::JUNGLE));    
                $this->selector->addBiome(Biome::getBiome(Biome::SAVANNA));    
                $this->selector->addBiome(Biome::getBiome(Biome::HIGH_SAVANNA));    
                $this->selector->addBiome(Biome::getBiome(Biome::HILLS));    
                $this->selector->addBiome(Biome::getBiome(Biome::ROOFED_FOREST));    
    
                
                $this->selector->addBiome(Biome::getBiome(Biome::OAK_PLAINS));    
                $this->selector->addBiome(Biome::getBiome(Biome::JUNGLE_PLAINS));    
                $this->selector->addBiome(Biome::getBiome(Biome::TAIGA_PLAINS));    
                $this->selector->addBiome(Biome::getBiome(Biome::DESERT_PLAINS));    
                $this->selector->addBiome(Biome::getBiome(Biome::ROOFED_PLAINS));    
                $this->selector->addBiome(Biome::getBiome(Biome::SNOW_PLAINS));    
                $this->selector->addBiome(Biome::getBiome(Biome::BIRCH_PLAINS));    
    
                $this->selector->recalculate();    
    
                
                $this->savannaJungleNoise = new Simplex($this->random, 2, 1 / 4, 1 / 768);    
    
                
                $this->swampRarityNoise = new Simplex($this->random, 2, 1 / 2, 1 / 350);    
    
                
                $this->plainsGateNoise = new Simplex($this->random, 2, 1 / 4, 1 / 768);    
    
                
                $this->plainsVariantNoise = new Simplex($this->random, 2, 1 / 4, 1 / 384);    
    
                $this->riverProcessor = new RiverChunkProcessor(    
                        $this->riverNoise,    
                        $this->waterHeight,    
                        $this->selector    
                );    
    
                $this->riverDecorator = new RiverDecorator($this->riverNoise, $this->waterHeight);    
    
                $cover = new GroundCover();    
                $this->generationPopulators[] = $cover;    
    
                $cave = new Cave();    
                $this->populators[] = $cave;    
    
                $this->populators[] = $this->riverDecorator;    
    
                $ores = new Ore();    
                $ores->setOreTypes([    
                        new OreType(new CoalOre(), 20, 16, 0, 128),    
                        new OreType(New IronOre(), 20, 8, 0, 64),    
                        new OreType(new RedstoneOre(), 8, 7, 0, 16),    
                        new OreType(new LapisOre(), 1, 6, 0, 32),    
                        new OreType(new GoldOre(), 2, 8, 0, 32),    
                        new OreType(new DiamondOre(), 1, 7, 0, 16),    
                        new OreType(new Dirt(), 20, 32, 0, 128),    
                        new OreType(new Stone(Stone::GRANITE), 20, 32, 0, 128),    
                        new OreType(new Stone(Stone::DIORITE), 20, 32, 0, 128),    
                        new OreType(new Stone(Stone::ANDESITE), 20, 32, 0, 128),    
                        new OreType(new Gravel(), 10, 16, 0, 128)    
                ]);    
                $this->populators[] = $ores;    
        }    
    
        public function generateChunk($chunkX, $chunkZ){    
                $this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());    
    
                $noise = Generator::getFastNoise3D($this->noiseBase, 16, 128, 16, 4, 8, 4, $chunkX * 16, 0, $chunkZ * 16);    
    
                $chunk = $this->level->getChunk($chunkX, $chunkZ);    
    
                
                $riverData = $this->riverProcessor->processChunk($chunkX, $chunkZ);    
    
                
                $biomes = [];    
                for($x = 0; $x < 16; ++$x){    
                        for($z = 0; $z < 16; ++$z){    
                                $worldX = $chunkX * 16 + $x;    
                                $worldZ = $chunkZ * 16 + $z;    
    
                                $biome = $this->pickBiome($worldX, $worldZ);    
    
                                $rd = $riverData[$x][$z];    
                                if($rd['isRiver']){    
                                        $biome = Biome::getBiome(Biome::RIVER);    
                                }    
    
                                $biomes[$x][$z] = $biome;    
                                $chunk->setBiomeId($x, $z, $biome->getId());    
                        }    
                }    
    
                
                $this->riverProcessor->applyBiomeOverrides($chunk, $riverData);    
    
                
                $biomeCache = [];    
    
                for($x = 0; $x < 16; ++$x){    
                        for($z = 0; $z < 16; ++$z){    
                                $rd = $riverData[$x][$z];    
                                $biome = $biomes[$x][$z];    
    
                                $minSum = 0;    
                                $maxSum = 0;    
                                $weightSum = 0;    
                                $color = [0, 0, 0];    
    
                                for($sx = -self::$SMOOTH_SIZE; $sx <= self::$SMOOTH_SIZE; ++$sx){    
                                        for($sz = -self::$SMOOTH_SIZE; $sz <= self::$SMOOTH_SIZE; ++$sz){    
    
                                                $weight = self::$GAUSSIAN_KERNEL[$sx + self::$SMOOTH_SIZE][$sz + self::$SMOOTH_SIZE];    
    
                                                if($sx === 0 and $sz === 0){    
                                                        $adjacent = $biome;    
                                                }else{    
                                                        $index = Level::chunkHash($chunkX * 16 + $x + $sx, $chunkZ * 16 + $z + $sz);    
                                                        if(isset($biomeCache[$index])){    
                                                                $adjacent = $biomeCache[$index];    
                                                        }else{    
                                                                $biomeCache[$index] = $adjacent = $this->pickBiome($chunkX * 16 + $x + $sx, $chunkZ * 16 + $z + $sz);    
                                                        }    
                                                }    
    
                                                $minSum += ($adjacent->getMinElevation() - 1) * $weight;    
                                                $maxSum += $adjacent->getMaxElevation() * $weight;    
    
                                                
                                                if(!$rd['isRiver'] && !$rd['isBankZone']){    
                                                        $bColor = $adjacent->getColor();    
                                                        $color[0] += (($bColor >> 16) ** 2) * $weight;    
                                                        $color[1] += ((($bColor >> 8) & 0xff) ** 2) * $weight;    
                                                        $color[2] += (($bColor & 0xff) ** 2) * $weight;    
                                                }    
    
                                                $weightSum += $weight;    
                                        }    
                                }    
    
                                $minSum /= $weightSum;    
                                $maxSum /= $weightSum;    
    
                                
                                
                                
                                
                                
                                
                                
                                
                                $carvedElev = $this->riverProcessor->computeCarvedElevation($maxSum, $minSum, $rd);    
                                $maxSum = $carvedElev['maxSum'];    
                                $minSum = $carvedElev['minSum'];    
    
                                
                                if(!$rd['isRiver'] && !$rd['isBankZone']){    
                                        $chunk->setBiomeColor($x, $z, sqrt($color[0] / $weightSum), sqrt($color[1] / $weightSum), sqrt($color[2] / $weightSum));    
                                }    
    
                                
                                $solidLand = false;    
                                for($y = 127; $y >= 0; --$y){    
                                        if($y === 0){    
                                                $chunk->setBlockId($x, $y, $z, Block::BEDROCK);    
                                                continue;    
                                        }    
    
                                        $noiseAdjustment = 2 * (($maxSum - $y) / ($maxSum - $minSum)) - 1;    
    
                                        $caveLevel = $minSum - 10;    
                                        $distAboveCaveLevel = max(0, $y - $caveLevel);    
    
                                        $noiseAdjustment = min($noiseAdjustment, 0.4 + ($distAboveCaveLevel / 10));    
                                        $noiseValue = $noise[$x][$z][$y] + $noiseAdjustment;    
    
                                        if($noiseValue > 0){    
                                                
                                                if($rd['isRiver'] && $y <= $this->waterHeight){    
                                                        $riverbedBlock = RiverCarver::getRiverbedBlock($y, $this->waterHeight, $rd['originalBiomeId']);    
                                                        $chunk->setBlockId($x, $y, $z, $riverbedBlock);    
                                                }else{    
                                                        $chunk->setBlockId($x, $y, $z, Block::STONE);    
                                                }    
                                                $solidLand = true;    
                                        }elseif($y <= $this->waterHeight && $solidLand == false){    
                                                $chunk->setBlockId($x, $y, $z, Block::STILL_WATER);    
                                        }    
                                }    
                        }    
                }    
    
                
                
                foreach($this->generationPopulators as $populator){    
                        $populator->populate($this->level, $chunkX, $chunkZ, $this->random);    
                }    
    
                
                $this->riverProcessor->applyRiverGroundCover($this->level, $chunkX, $chunkZ, $riverData);    
    
                
                
                
                $this->riverProcessor->applyBankOverlay($this->level, $chunkX, $chunkZ, $riverData);    
        }    
    
        public function populateChunk($chunkX, $chunkZ){    
                $this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());    
                foreach($this->populators as $populator){    
                        $populator->populate($this->level, $chunkX, $chunkZ, $this->random);    
                }    
    
                $chunk = $this->level->getChunk($chunkX, $chunkZ);    
                $biome = Biome::getBiome($chunk->getBiomeId(7, 7));    
                $biome->populateChunk($this->level, $chunkX, $chunkZ, $this->random);    
        }    
    
        public function getSpawn(){    
                return new Vector3(127.5, 128, 127.5);    
        }    
    
} 