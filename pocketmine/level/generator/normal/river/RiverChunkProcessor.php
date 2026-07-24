<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
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

        /** @var RiverNoise */
        private $riverNoise;

        /** @var int */
        private $waterHeight;

        /** @var BiomeSelector */
        private $selector;

        public function __construct(RiverNoise $riverNoise, int $waterHeight, BiomeSelector $selector){
                $this->riverNoise = $riverNoise;
                $this->waterHeight = $waterHeight;
                $this->selector = $selector;
        }

        /**
         * Processes an entire chunk, computing river data for all 16x16 columns.
         */
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

                                // Always compute a depth value (not only when isRiver): the
                                // unified elevation blend in RiverCarver now spans the whole
                                // river+bank radius, so bank-only columns near the river edge
                                // also need a sensible target riverbed depth to blend towards -
                                // otherwise the curve would aim at an inconsistent depth right
                                // at the river/bank boundary and reintroduce a seam.
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

                                // Store all data (NO fixed bankOverlayCover - computed adaptively per-column)
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

        /**
         * Applies biome overrides to chunk.
         */
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

        /**
         * Applies river ground cover to chunk (river columns only).
         * Uses only 3 blocks (shallow riverbed, no deep clay columns).
         */
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

                                        // Don't place grass underwater
                                        if($y <= $this->waterHeight && $b->getId() === Block::GRASS){
                                                $blockAbove = $chunk->getBlockId($x, $y + 1, $z);
                                                if($blockAbove === Block::STILL_WATER || $blockAbove === Block::WATER){
                                                        $b = Block::get(Block::DIRT);
                                                }
                                        }

                                        // Don't place non-solid blocks where solid needed
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

        /**
         * Applies ADAPTIVE bank overlay on bank zone columns.
         *
         * NO fixed columns. Each column gets a different surface material
         * based on its world coordinates + noise + bankIntensity + biome.
         *
         * Only modifies the top 1-2 blocks per column:
         * 1. Surface block: getAdaptiveBankSurface() - adapts to biome, varies by noise
         * 2. Subsurface block: getAdaptiveBankSubsurface() - only near river
         *
         * Result per column (varies by noise):
         *   Far from river: grass (biome surface)
         *   Mid:            sometimes grass, sometimes sand (noise picks)
         *   Near river:     mostly sand, sometimes grass (noise picks)
         *   Rare gravel:    ~15% of near-river columns (noise threshold)
         */
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
                                        continue; // Too far from river, keep biome surface
                                }

                                $worldX = $chunkX * 16 + $x;
                                $worldZ = $chunkZ * 16 + $z;

                                // Use micro noise for per-column surface variation
                                // This ensures NO uniform columns - each column is different
                                $surfaceNoise = $this->riverNoise->getBankNoise($worldX, $worldZ);

                                // Find the surface (after GroundCover placed biome cover)
                                $surfaceY = $this->findBankSurface($chunk, $x, $z);
                                if($surfaceY <= 0) continue;

                                // Check if surface is underwater (below waterHeight)
                                // Underwater columns don't need bank overlay - they're in the river
                                $surfaceBlock = $chunk->getBlockId($x, $surfaceY, $z);
                                if($surfaceBlock === Block::STILL_WATER || $surfaceBlock === Block::WATER){
                                        continue;
                                }

                                // Determine adaptive surface block for this specific column
                                $adaptiveSurface = RiverBiomeMixer::getAdaptiveBankSurface(
                                        $data['originalBiomeId'],
                                        $bankIntensity,
                                        $surfaceNoise
                                );

                                // Only replace if the adaptive block differs from current
                                if($adaptiveSurface !== $surfaceBlock){
                                        // Grass underwater → dirt (never grass below water)
                                        if($surfaceY <= $this->waterHeight && $adaptiveSurface === Block::GRASS){
                                                $adaptiveSurface = Block::DIRT;
                                        }

                                        $chunk->setBlockId($x, $surfaceY, $z, $adaptiveSurface);
                                }

                                // ------------------------------------------------------------
                                // BUGFIX: "sand platforms suspended in the air"
                                // ------------------------------------------------------------
                                // Previously this method only ever replaced ONE subsurface
                                // block (and only when bankIntensity > 0.35). That left a
                                // thin sand cap sitting on top of GroundCover's DIRT, which
                                // the Cave populator (running later in populateChunk) would
                                // happily carve away, leaving the sand floating on air.
                                //
                                // We now drive the subsurface fill depth from bankIntensity
                                // itself - up to 4 blocks deep right next to the river,
                                // tapering down to 1 block at the outer bank edge. The
                                // subsurface material is chosen with the existing helper
                                // (sand / sandstone / dirt / gravel / clay per biome), so
                                // the column always rests on its OWN material instead of
                                // on GroundCover's dirt that the cave can remove.
                                //
                                // This is a belt-and-suspenders fix; the primary fix is in
                                // Cave.php (which now also carves SAND/GRAVEL/SANDSTONE/
                                // CLAY_BLOCK so a cave opening is a clean hole instead of
                                // a floating cap), but filling the bank column solidly
                                // here as well keeps the bank intact even when a cave
                                // passes right underneath it.
                                // ------------------------------------------------------------
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

                                        // Never overwrite water/lava/air - those mean we hit
                                        // the river channel or a cave the bank should not seal.
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

        /**
         * Finds bank zone surface (after GroundCover placed biome cover).
         */
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

        /**
         * Computes carved terrain elevation for a column. Safe to call for
         * EVERY column (not just ones flagged isRiver/isBankZone) - the
         * underlying formula naturally returns the terrain unchanged once
         * the column is outside the river's (adaptive) influence radius,
         * so there's no classification boundary that could ever mismatch
         * the elevation formula and reintroduce a seam/wall.
         */
        public function computeCarvedElevation(float $normalMaxSum, float $normalMinSum, array $riverData) : array{
                // Some biomes must be fully immune to river carving - not just
                // excluded from classification (RiverWidthGenerator already
                // returns width=0 for these), but from the adaptive-width
                // elevation blend entirely. That blend is driven purely by
                // elevation delta and distance, so even at width=0 a nearby
                // river could still gently tug the terrain toward water level.
                // HighSavanna specifically was requested to never be affected
                // by rivers at all, so it's excluded outright here regardless
                // of distance to any river.
                static $riverImmuneBiomes = [3, 20, 36]; // MOUNTAINS, SMALL_MOUNTAINS, HIGH_SAVANNA
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
