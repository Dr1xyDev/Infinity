<?php    
    
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0    
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
    
        /** @var Populator[] */    
        protected $populators = [];    
        /** @var ChunkManager */    
        protected $level;    
        /** @var Random */    
        protected $random;    
        protected $waterHeight = 62;    
        protected $bedrockDepth = 5;    
    
        /** @var Populator[] */    
        protected $generationPopulators = [];    
        /** @var Simplex */    
        protected $noiseBase;    
    
        /** @var BiomeSelector */    
        protected $selector;    
    
        /** Rainfall para HighSavanna. */    
        const HIGH_SAVANNA_RAINFALL_THRESHOLD = 0.65;    
    
        /** Rango de Hills. */    
        const HILLS_DETECTION_RADIUS = 80.0;    
        const HILLS_SAMPLE_RINGS = 4;    
        const HILLS_SAMPLE_DIRECTIONS = 6;    
    
        /** No crear Hills cerca de ríos. */    
        const HILLS_MIN_RIVER_DISTANCE = 40.0;    
    
        /** Límite de océano. */    
        const CONTINENT_OCEAN_THRESHOLD = -0.05;    
    
        /** Banda costera. */    
        const CONTINENT_COAST_BAND = 0.12;    
    
        /** Banda de ríos para Plains. */    
        const PLAINS_RIVER_BAND = 80.0;    
    
        /** Gato de Plains. */    
        const PLAINS_GATE_THRESHOLD = 0.85;    
    
        /** Oak Plains más común. */    
        const PLAINS_OAK_FRACTION = 0.65;    
    
        /** @var Simplex Noise de continentes */    
        protected $continentNoise;    
    
        /** @var RiverNoise Noise de ríos */    
        protected $riverNoise;    
    
        /** @var RiverChunkProcessor Procesador de ríos */    
        protected $riverProcessor;    
    
        /** @var RiverDecorator Decorador de ríos */    
        protected $riverDecorator;    
    
        /** @var Simplex Noise de Savanna/Jungle */    
        protected $savannaJungleNoise;    
    
        /** Noise para hacer Swamp raro. */    
        protected $swampRarityNoise;    
        const SWAMP_RARITY_THRESHOLD = 0.88; // MODIFIED: Increased from 0.82 to 0.88 -> top ~12% only -> much rarer    
    
        /** Noise de entrada para Plains. */    
        protected $plainsGateNoise;    
    
        /** Noise de variante para Plains. */    
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
    
                // Continente / océano.    
                $continent = $this->continentNoise->noise2D($px, $pz, true);    
                if($continent < self::CONTINENT_OCEAN_THRESHOLD){    
                        return Biome::getBiome(Biome::OCEAN);    
                }    
    
                $biome = $this->selector->pickBiome($px, $pz);    
    
                // Savanna / Jungle.    
                if($biome->getId() === Biome::SAVANNA){    
                        $split = ($this->savannaJungleNoise->noise2D($px, $pz, true) + 1) / 2;    
                        // Jungle más raro.    
                        if($split > 0.65){    
                                $biome = Biome::getBiome(Biome::JUNGLE);    
                        }    
                }    
    
                // Swamp raro.    
                if($biome->getId() === Biome::SWAMP){    
                        $rarity = ($this->swampRarityNoise->noise2D($px, $pz, true) + 1) / 2;    
                        if($rarity < self::SWAMP_RARITY_THRESHOLD){    
                                $temperature = $this->selector->getTemperature($px, $pz);    
                                $biome = $temperature < 0.55    
                                        ? Biome::getBiome(Biome::SAVANNA)    
                                        : Biome::getBiome(Biome::ROOFED_FOREST);    
                        }    
                }    
    
                // High Savanna.    
                if($biome->getId() === Biome::SAVANNA){    
                        $rainfall = $this->selector->getRainfall($px, $pz);    
                        if($rainfall > self::HIGH_SAVANNA_RAINFALL_THRESHOLD){    
                                $biome = Biome::getBiome(Biome::HIGH_SAVANNA);    
                        }    
                }    
    
                // Hills cerca de Forest.    
                if($biome->getId() === Biome::FOREST){    
                        $distanceToRiver = $this->riverNoise->getEstimatedDistance($px, $pz);    
                        if($distanceToRiver > self::HILLS_MIN_RIVER_DISTANCE    
                                && $this->isNearMountainRoofedOrJungle($px, $pz)){    
                                $biome = Biome::getBiome(Biome::HILLS);    
                        }    
                }    
    
                // Plains cerca de agua.    
                $biome = $this->maybeConvertToPlainsSubbiome($biome, $px, $pz);    
    
                return $biome;    
        }    
    
        /** Cambia a Plains si toca. */    
        private function maybeConvertToPlainsSubbiome(Biome $biome, int $px, int $pz) : Biome{    
                // Mapa de variantes.    
                static $plainsVariant = [    
                        Biome::JUNGLE         => Biome::JUNGLE_PLAINS,    
                        Biome::TAIGA          => Biome::TAIGA_PLAINS,    
                        Biome::DESERT         => Biome::DESERT_PLAINS,    
                        Biome::ROOFED_FOREST  => Biome::ROOFED_PLAINS,    
                        Biome::ICE_PLAINS     => Biome::SNOW_PLAINS,    
                        Biome::BIRCH_FOREST   => Biome::BIRCH_PLAINS,    
                        // Estas usan Oak Plains.    
                        Biome::PLAINS         => Biome::OAK_PLAINS,    
                        Biome::FOREST         => Biome::OAK_PLAINS,    
                        Biome::SAVANNA        => Biome::OAK_PLAINS,    
                        Biome::SWAMP          => Biome::OAK_PLAINS,    
                ];    
    
                $currentId = $biome->getId();    
                if(!isset($plainsVariant[$currentId])){    
                        return $biome;    
                }    
    
                // Distancia al río y al continente.    
                $distanceToRiver = $this->riverNoise->getEstimatedDistance($px, $pz);    
                $continent = $this->continentNoise->noise2D($px, $pz, true);    
    
                // Cerca de agua.    
                $nearRiver = $distanceToRiver < self::PLAINS_RIVER_BAND;    
                $nearCoast = $continent >= self::CONTINENT_OCEAN_THRESHOLD    
                        && $continent < (self::CONTINENT_OCEAN_THRESHOLD + self::CONTINENT_COAST_BAND);    
    
                if(!$nearRiver && !$nearCoast){    
                        return $biome;    
                }    
    
                // La mayoría pasa a Plains.    
                $gate = ($this->plainsGateNoise->noise2D($px, $pz, true) + 1.0) / 2.0;    
                if($gate >= self::PLAINS_GATE_THRESHOLD){    
                        return $biome;    
                }    
    
                // Oak Plains o variante.    
                $variant = ($this->plainsVariantNoise->noise2D($px, $pz, true) + 1.0) / 2.0;    
                if($variant < self::PLAINS_OAK_FRACTION){    
                        return Biome::getBiome(Biome::OAK_PLAINS);    
                }    
    
                return Biome::getBiome($plainsVariant[$currentId]);    
        }    
    
        /** Revisa si hay biomas altos cerca. */    
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
    
        /** Revisión rápida del clima. */    
        private function isRawMountainRoofedOrJungle(int $x, int $z) : bool{    
                $raw = $this->selector->pickBiome($x, $z);    
                $id = $raw->getId();    
    
                if($id === Biome::MOUNTAINS || $id === Biome::SMALL_MOUNTAINS || $id === Biome::ROOFED_FOREST){    
                        return true;    
                }    
    
                if($id === Biome::SAVANNA){    
                        $split = ($this->savannaJungleNoise->noise2D($x, $z, true) + 1) / 2;    
                        if($split > 0.5){    
                                return true; // Jungle    
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
    
                // Continentes y océanos.    
                $this->continentNoise = new Simplex($this->random, 3, 1 / 2, 1 / 1536);    
                $this->random->setSeed($this->level->getSeed());    
    
                // Ríos.    
                $this->riverNoise = new RiverNoise($this->level->getSeed());    
    
                // Selector de biomas.    
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
                                if($temperature < 0.20){    
                                        return Biome::MOUNTAINS;    
                                }elseif($temperature < 0.40){    
                                        return Biome::ROOFED_FOREST;    
                                }elseif($temperature < 0.70){    
                                        return Biome::SWAMP;    
                                }else{    
                                        // Jungle comparte zona con Savanna.    
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
    
                // Plains sub-biomes.    
                $this->selector->addBiome(Biome::getBiome(Biome::OAK_PLAINS));    
                $this->selector->addBiome(Biome::getBiome(Biome::JUNGLE_PLAINS));    
                $this->selector->addBiome(Biome::getBiome(Biome::TAIGA_PLAINS));    
                $this->selector->addBiome(Biome::getBiome(Biome::DESERT_PLAINS));    
                $this->selector->addBiome(Biome::getBiome(Biome::ROOFED_PLAINS));    
                $this->selector->addBiome(Biome::getBiome(Biome::SNOW_PLAINS));    
                $this->selector->addBiome(Biome::getBiome(Biome::BIRCH_PLAINS));    
    
                $this->selector->recalculate();    
    
                // Savanna / Jungle.    
                $this->savannaJungleNoise = new Simplex($this->random, 2, 1 / 4, 1 / 768);    
    
                // Swamp raro.    
                $this->swampRarityNoise = new Simplex($this->random, 2, 1 / 2, 1 / 350);    
    
                // Plains gate.    
                $this->plainsGateNoise = new Simplex($this->random, 2, 1 / 4, 1 / 768);    
    
                // Plains variant.    
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
    
                // Paso 1: ríos.    
                $riverData = $this->riverProcessor->processChunk($chunkX, $chunkZ);    
    
                // Paso 2: biomas.    
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
    
                // Colores de río.    
                $this->riverProcessor->applyBiomeOverrides($chunk, $riverData);    
    
                // Paso 3: terreno.    
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
    
                                                // Color fuera de río.    
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
    
                                // Paso 4: corte de río.    
                                // River: terrain surface AT waterHeight (shallow channel)    
                                // Bank zone: gentle Beach-like descent toward water level    
                                // Always computed (not gated on isRiver/isBankZone flags):    
                                // the carve formula itself is continuous and safely    
                                // returns the terrain unchanged once a column is outside    
                                // its own (adaptive) influence radius, so calling it    
                                // unconditionally can never introduce a seam.    
                                $carvedElev = $this->riverProcessor->computeCarvedElevation($maxSum, $minSum, $rd);    
                                $maxSum = $carvedElev['maxSum'];    
                                $minSum = $carvedElev['minSum'];    
    
                                // Paso 5: color del bioma.    
                                if(!$rd['isRiver'] && !$rd['isBankZone']){    
                                        $chunk->setBiomeColor($x, $z, sqrt($color[0] / $weightSum), sqrt($color[1] / $weightSum), sqrt($color[2] / $weightSum));    
                                }    
    
                                // Paso 6: bloques.    
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
                                                // River bed.    
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
    
                // Paso 7: GroundCover.    
                // Bank zone columns get their biome cover first.    
                foreach($this->generationPopulators as $populator){    
                        $populator->populate($this->level, $chunkX, $chunkZ, $this->random);    
                }    
    
                // Paso 8: cover de río.    
                $this->riverProcessor->applyRiverGroundCover($this->level, $chunkX, $chunkZ, $riverData);    
    
                // Paso 9: overlay de bank.    
                // Sand/gravel overlay on bank zone columns near river    
                // This creates the Beach-like transition appearance    
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