<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\level\generator\populator;

use pocketmine\block\Block;
use pocketmine\block\Stone;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\Level;
use pocketmine\level\SimpleChunkManager;
use pocketmine\utils\Random;

class GroundCover extends Populator{

    /*
     * Ruido de valor rápido basado en hash entero.
     * Devuelve un float [0.0, 1.0) sin dependencias externas.
     * Se usa para fragmentar la superficie de Mountains sin
     * crear sub-biomas separados (el bioma sigue siendo MOUNTAINS
     * en todo momento; solo cambia la capa superficial).
     */
    private static function valueNoise(int $x, int $z, int $seed) : float{
        $h = $x * 374761393 ^ $z * 668265263 ^ $seed;
        $h = ($h ^ ($h >> 13)) * 1274126177;
        $h = $h ^ ($h >> 16);
        return (($h & 0x7FFFFFFF) % 1000) / 1000.0;
    }

    /*
     * Ruido de valor de más baja frecuencia (bloques más grandes).
     * Usamos coordenadas divididas por 8 para obtener "manchas"
     * de unos 8×8 bloques de ancho.
     */
    private static function patchNoise(int $x, int $z, int $seed) : float{
        $px = (int)floor($x / 8);
        $pz = (int)floor($z / 8);
        return self::valueNoise($px, $pz, $seed ^ 0xDEAD);
    }

    /*
     * Determina la cobertura superficial para Mountains.
     *
     * Distribución objetivo:
     *   60 % piedra     (Stone 0)
     *   30 % césped     (Grass + Dirt debajo)
     *    5 % andesita   (Stone:5) — manchas pequeñas
     *    5 % granito    (Stone:1) — manchas pequeñas
     *
     * La fragmentación proviene de dos capas de ruido:
     *   - patchNoise  → decide si la "mancha" base es piedra o césped
     *   - valueNoise  → dentro de las manchas de piedra, decide si
     *                   aparece andesita o granito de forma puntual
     *
     * Esto produce parches coherentes (no pixel-art) y evita la
     * fragmentación interna del bioma porque el propio biome ID
     * nunca cambia; solo la capa de cobertura varía.
     */
    private static function getMountainCover(int $worldX, int $worldZ) : array{
        $patch  = self::patchNoise($worldX, $worldZ, 0x4D4F554E);
        $detail = self::valueNoise($worldX, $worldZ, 0x53544F4E);

        if($patch < 0.60){
            /*
             * Zona de piedra (60 %).
             * Dentro, un 5/60 ≈ 8.3 % es andesita y otro 5/60 ≈ 8.3 % es granito,
             * repartidos en manchas pequeñas mediante un ruido de detalle.
             */
            $spotPatch = self::patchNoise($worldX, $worldZ, 0xAEC00001);
            if($spotPatch < 0.0833){
                return [Block::get(Block::STONE, Stone::ANDESITE)];
            }elseif($spotPatch < 0.1666){
                return [Block::get(Block::STONE, Stone::GRANITE)];
            }else{
                return [Block::get(Block::STONE, 0)];
            }
        }else{
            /* Zona de césped (30 % restante) */
            return [
                Block::get(Block::GRASS, 0),
                Block::get(Block::DIRT,  0),
                Block::get(Block::DIRT,  0),
                Block::get(Block::STONE, 0),
            ];
        }
    }

    public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
        $chunk = $level->getChunk($chunkX, $chunkZ);
        if($level instanceof Level or $level instanceof SimpleChunkManager){
            $waterHeight = $level->getWaterHeight();
        }else{
            $waterHeight = 0;
        }

        for($x = 0; $x < 16; ++$x){
            for($z = 0; $z < 16; ++$z){
                $biomeId = $chunk->getBiomeId($x, $z);

                if($biomeId === Biome::RIVER){
                    continue;
                }

                $worldX = $chunkX * 16 + $x;
                $worldZ = $chunkZ * 16 + $z;

                if($biomeId === Biome::MOUNTAINS){
                    $cover = self::getMountainCover($worldX, $worldZ);
                }else{
                    $biome = Biome::getBiome($biomeId);
                    $cover = $biome->getGroundCover();
                }

                if(count($cover) === 0){
                    continue;
                }

                $diffY = 0;
                if(!$cover[0]->isSolid()){
                    $diffY = 1;
                }

                $column = $chunk->getBlockIdColumn($x, $z);
                for($y = 127; $y > 0; --$y){
                    if($column[$y] !== "\x00" and !Block::get(ord($column[$y]))->isTransparent()){
                        break;
                    }
                }
                $startY = min(127, $y + $diffY);
                $endY   = $startY - count($cover);

                for($y = $startY; $y > $endY and $y >= 0; --$y){
                    $b = $cover[$startY - $y];
                    if($column[$y] === "\x00" and $b->isSolid()){
                        break;
                    }
                    if($y <= $waterHeight
                        and $b->getId() == Block::GRASS
                        and $chunk->getBlockId($x, $y + 1, $z) == Block::STILL_WATER
                    ){
                        $b = Block::get(Block::DIRT);
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
}
