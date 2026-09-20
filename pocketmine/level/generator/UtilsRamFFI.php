<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.2 Release
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
 * Puente FFI a libutilsram.so (acelerador nativo de generacion)
*/

namespace pocketmine\level\generator;

class UtilsRamFFI{
	/** @var \FFI|null */
	private static $ffi = null;
	/** @var bool */
	private static $tried = false;

	const HEADER = '
typedef unsigned char uint8_t;
typedef int int32_t;
typedef unsigned int uint32_t;
double utilsram_noise3d(const uint8_t *perm, int octaves, double persistence, double expansion, double ox, double oy, double oz, double x, double y, double z);
int utilsram_noise_field(const uint8_t *perm, int octaves, double persistence, double expansion, double ox, double oy, double oz, int startX, int startY, int startZ);
const uint8_t* utilsram_generate_terrain(const uint8_t *biomeIds, const int32_t *minElev, const int32_t *maxElev, int waterHeight, uint8_t *heightOut);
void utilsram_populate_sky_light(const uint8_t *blocks, uint8_t *skyLight, uint8_t *heightMap, const uint8_t *solid);
double utilsram_noise_at(int xx, int zz, int yy);
uint32_t utilsram_mem_usage(void);
';

	public static function isAvailable() : bool{
		if(self::$tried){
			return self::$ffi !== null;
		}
		self::$tried = true;
		if(!extension_loaded("FFI") or PHP_INT_SIZE !== 8){
			return false;
		}
		try{
			self::$ffi = \FFI::cdef(self::HEADER, self::libPath());
		}catch(\Throwable $e){
			self::$ffi = null;
		}
		return self::$ffi !== null;
	}

	public static function libPath() : string{
		// search the .so of the lib
		$ext = \Phar::running(true);
		if($ext !== ""){
			$candidates = [dirname($ext) . "/libutilsram.so", \getcwd() . "/libutilsram.so"];
		}else{
			$candidates = [\getcwd() . "/libutilsram.so"];
		}
		foreach($candidates as $p){
			if(file_exists($p)){
				return $p;
			}
		}
		return __DIR__ . "/../../../libutilsram.so";
	}

	public static function getFFI(){
		if(self::$ffi === null){
			self::isAvailable();
		}
		return self::$ffi;
	}
}
