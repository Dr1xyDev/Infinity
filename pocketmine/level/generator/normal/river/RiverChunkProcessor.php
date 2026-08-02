<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\level\generator\normal\river;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\generator\biome\BiomeSelector;
use pocketmine\level\format\FullChunk;

class RiverChunkProcessor{

        
        private $riverNoise;

        
        private $waterHeight;

        
        private $selector;

        public function __construct(RiverNoise $riverNoise, int $waterHeight, BiomeSelector $selector){
                $this->riverNoise = $riverNoise;
                $this->waterHeight = $waterHeight;
                $this->selector = $selector;
        }

        
        public function processChunk(int $chunkX, int $chunkZ) : array{
                $riverData = [];

                for($x = 0; $x < 16; ++$x){
                        for($z = 0; $z < 16; ++$z){
                                $worldX = $chunkX * 16 + $x;
                                $worldZ = $chunkZ * 16 + $z;

                                $estimatedDistance = $this->riverNoise->getEstimatedDistance($worldX, $worldZ);

                                $baseBiome = $this->selector->pickBiome($worldX, $worldZ);
                                $originalBiomeId = $baseBiome->getId();

                                $noiseData = $this->riverNoise->compute($worldX, $worldZ);

                                $pathValue = $this->riverNoise->getMainPathValue($worldX, $worldZ);
                                $targetWidth = RiverWidthGenerator::compute(
                                        $this->riverNoise, $worldX, $worldZ,
                                        $pathValue, $originalBiomeId
                                );

                                $maskData = RiverMask::compute(
                                        $estimatedDistance,
                                        $targetWidth,
                                        $noiseData['bank']
                                );

                                
                                
                                
                                
                                
                                
                                $depth = RiverDepthGenerator::compute(
                                        $this->riverNoise, $worldX, $worldZ,
                                        $targetWidth,
                                        $maskData['riverIntensity'],
                                        RiverNetwork::getSizeCategory($targetWidth)
                                );

                                $bankData = ['bankZone' => 4.0, 'bankShape' => 'beach', 'steepnessFactor' => 0.55, 'bankNoise' => 0];
                                if($maskData['isRiver'] || $maskData['isBankZone']){
                                        $bankData = RiverBankGenerator::compute(
                                                $noiseData['bank'],
                                                $targetWidth,
                                                $originalBiomeId
                                        );
                                }

                                $networkData = RiverNetwork::compute($maskData, $originalBiomeId, $targetWidth);

                                $layerData = RiverLayer::compute($baseBiome, $maskData);

                                $groundCover = [];
                                $blendedColor = 0;
                                if($layerData['isRiver']){
                                        $groundCover = RiverBiomeMixer::getGroundCover($layerData['originalBiomeId']);
                                        $blendedColor = RiverBiomeMixer::getBlendedColor(
                                                $layerData['originalBiomeId'],
                                                $maskData['riverIntensity'],
                                                $maskData['bankIntensity']
                                        );
                                }elseif($layerData['isBankZone']){
                                        $blendedColor = RiverBiomeMixer::getBlendedColor(
                                                $layerData['originalBiomeId'],
                                                0.0,
                                                $maskData['bankIntensity']
                                        );
                                }

                                
                                $riverData[$x][$z] = [
                                        'isRiver'          => $layerData['isRiver'],
                                        'isBankZone'       => $layerData['isBankZone'],
                                        'finalBiomeId'     => $layerData['finalBiomeId'],
                                        'originalBiomeId'  => $layerData['originalBiomeId'],
                                        'mask'             => $maskData['riverIntensity'],
                                        'bankIntensity'    => $maskData['bankIntensity'],
                                        'width'            => $targetWidth,
                                        'depth'            => $depth,
                                        'bankZone'         => $bankData['bankZone'],
                                        'bankShape'        => $bankData['bankShape'],
                                        'steepnessFactor'  => $bankData['steepnessFactor'],
                                        'groundCover'      => $groundCover,
                                        'blendedColor'     => $blendedColor,
                                        'riverType'        => $networkData['sizeCategory'],
                                        'estimatedDistance' => $estimatedDistance,
                                        'halfWidth'        => $maskData['halfWidth'],
                                ];
                        }
                }

                return $riverData;
        }

        
        public function applyBiomeOverrides(FullChunk $chunk, array $riverData){
                for($x = 0; $x < 16; ++$x){
                        for($z = 0; $z < 16; ++$z){
                                $data = $riverData[$x][$z];

                                if($data['isRiver']){
                                        $chunk->setBiomeId($x, $z, Biome::RIVER);
                                        $color = $data['blendedColor'];
                                        $r = ($color >> 16) & 0xff;
                                        $g = ($color >> 8) & 0xff;
                                        $b = $color & 0xff;
                                        $chunk->setBiomeColor($x, $z, $r, $g, $b);
                                }elseif($data['isBankZone']){
                                        $color = $data['blendedColor'];
                                        if($color !== 0){
                                                $r = ($color >> 16) & 0xff;
                                                $g = ($color >> 8) & 0xff;
                                                $b = $color & 0xff;
                                                $chunk->setBiomeColor($x, $z, $r, $g, $b);
                                        }
                                }
                        }
                }
        }

        
        public function applyRiverGroundCover(ChunkManager $level, int $chunkX, int $chunkZ, array $riverData){
                $chunk = $level->getChunk($chunkX, $chunkZ);

                for($x = 0; $x < 16; ++$x){
                        for($z = 0; $z < 16; ++$z){
                                $data = $riverData[$x][$z];

                                if(!$data['isRiver']){
                                        continue;
                                }

                                $cover = $data['groundCover'];
                                if(count($cover) === 0){
                                        continue;
                                }

                                $surfaceY = $this->findRiverSurface($chunk, $x, $z);
                                if($surfaceY <= 0) continue;

                                $diffY = 0;
                                if(!$cover[0]->isSolid()){
                                        $diffY = 1;
                                }

                                $startY = min(127, $surfaceY + $diffY);
                                $endY = max(0, $startY - count($cover));

                                for($y = $startY; $y > $endY && $y >= 0; --$y){
                                        $b = $cover[$startY - $y];

                                        
                                        if($y <= $this->waterHeight && $b->getId() === Block::GRASS){
                                                $blockAbove = $chunk->getBlockId($x, $y + 1, $z);
                                                if($blockAbove === Block::STILL_WATER || $blockAbove === Block::WATER){
                                                        $b = Block::get(Block::DIRT);
                                                }
                                        }

                                        
                                        $currentBlock = $chunk->getBlockId($x, $y, $z);
                                        if($currentBlock === Block::AIR && $b->isSolid()){
                                                $below = $chunk->getBlockId($x, $y - 1, $z);
                                                if($below === Block::AIR || $below === Block::STILL_WATER || $below === Block::WATER){
                                                        continue;
                                                }
                                        }

                                        if($b->getDamage() === 0){
                                                $chunk->setBlockId($x, $y, $z, $b->getId());
                                        }else{
                                                $chunk->setBlock($x, $y, $z, $b->getId(), $b->getDamage());
                                        }
                                }
                        }
                }
        }

        
        public function applyBankOverlay(ChunkManager $level, int $chunkX, int $chunkZ, array $riverData){
                $chunk = $level->getChunk($chunkX, $chunkZ);

                for($x = 0; $x < 16; ++$x){
                        for($z = 0; $z < 16; ++$z){
                                $data = $riverData[$x][$z];

                                if(!$data['isBankZone']){
                                        continue;
                                }

                                $bankIntensity = $data['bankIntensity'];
                                if($bankIntensity < 0.15){
                                        continue; 
                                }

                                $worldX = $chunkX * 16 + $x;
                                $worldZ = $chunkZ * 16 + $z;

                                
                                
                                $surfaceNoise = $this->riverNoise->getBankNoise($worldX, $worldZ);

                                
                                $surfaceY = $this->findBankSurface($chunk, $x, $z);
                                if($surfaceY <= 0) continue;

                                
                                
                                $surfaceBlock = $chunk->getBlockId($x, $surfaceY, $z);
                                if($surfaceBlock === Block::STILL_WATER || $surfaceBlock === Block::WATER){
                                        continue;
                                }

                                
                                $adaptiveSurface = RiverBiomeMixer::getAdaptiveBankSurface(
                                        $data['originalBiomeId'],
                                        $bankIntensity,
                                        $surfaceNoise
                                );

                                
                                if($adaptiveSurface !== $surfaceBlock){
                                        
                                        if($surfaceY <= $this->waterHeight && $adaptiveSurface === Block::GRASS){
                                                $adaptiveSurface = Block::DIRT;
                                        }

                                        $chunk->setBlockId($x, $surfaceY, $z, $adaptiveSurface);
                                }

                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                $fillDepth = 0;
                                if($bankIntensity > 0.65){
                                        $fillDepth = 4;
                                }elseif($bankIntensity > 0.45){
                                        $fillDepth = 3;
                                }elseif($bankIntensity > 0.30){
                                        $fillDepth = 2;
                                }elseif($bankIntensity > 0.15){
                                        $fillDepth = 1;
                                }

                                for($dy = 1; $dy <= $fillDepth; ++$dy){
                                        $yy = $surfaceY - $dy;
                                        if($yy < 1) break;

                                        $current = $chunk->getBlockId($x, $yy, $z);

                                        
                                        
                                        if($current === Block::STILL_WATER
                                                || $current === Block::WATER
                                                || $current === Block::STILL_LAVA
                                                || $current === Block::LAVA
                                                || $current === Block::AIR){
                                                break;
                                        }

                                        $adaptiveSubsurface = RiverBiomeMixer::getAdaptiveBankSubsurface(
                                                $data['originalBiomeId'],
                                                $bankIntensity,
                                                $surfaceNoise
                                        );

                                        if($adaptiveSubsurface !== $current){
                                                $chunk->setBlockId($x, $yy, $z, $adaptiveSubsurface);
                                        }
                                }
                        }
                }
        }

        private function findRiverSurface(FullChunk $chunk, int $x, int $z) : int{
                $column = $chunk->getBlockIdColumn($x, $z);
                for($y = 127; $y > 0; --$y){
                        $block = ord($column[$y]);
                        if($block !== 0 && $block !== 8 && $block !== 9
                                && $block !== 31 && $block !== 78
                                && $block !== 18 && $block !== 161){
                                return $y;
                        }
                }
                return 0;
        }

        
        private function findBankSurface(FullChunk $chunk, int $x, int $z) : int{
                $column = $chunk->getBlockIdColumn($x, $z);
                for($y = 127; $y > 0; --$y){
                        $block = ord($column[$y]);
                        if($block !== 0 && $block !== 8 && $block !== 9
                                && $block !== 31 && $block !== 78
                                && $block !== 175 && $block !== 18 && $block !== 161){
                                return $y;
                        }
                }
                return 0;
        }

        
        public function computeCarvedElevation(float $normalMaxSum, float $normalMinSum, array $riverData) : array{
                
                
                
                
                
                
                
                
                
                static $riverImmuneBiomes = [3, 20, 36]; 
                if(in_array($riverData['originalBiomeId'], $riverImmuneBiomes, true)){
                        return ['maxSum' => $normalMaxSum, 'minSum' => $normalMinSum];
                }

                $bankData = [
                        'bankZone'         => $riverData['bankZone'],
                        'bankShape'        => $riverData['bankShape'],
                        'steepnessFactor'  => $riverData['steepnessFactor'],
                        'bankNoise'        => 0,
                ];

                return RiverCarver::compute(
                        $normalMaxSum,
                        $normalMinSum,
                        $riverData['depth'],
                        $bankData,
                        $this->waterHeight,
                        $riverData['estimatedDistance'],
                        $riverData['halfWidth']
                );
        }

        public function getRiverNoise() : RiverNoise{
                return $this->riverNoise;
        }
}
