<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | | | |_____| |  | |  __/
 * |_|   \__,_|\___|_|\_\_|\__,_|_| |_| |_|\__,_.__/      |_|  |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Infinity Team
 * @link https://github.com/Dr1xyDev
 *
 *
*/

namespace pocketmine\utils;

/**
 * Aceleracion nativa del nucleo via memis.so (PHP FFI).
 *
 * La libreria NO viene incluida: hay que colocarla MANUALMENTE (asi cada
 * dispositivo usa el .so correcto para su SO/arquitectura: android,
 * linux arm64, linux x86_64...). Se busca solo en:
 *  - la variable de entorno INFINITY_NATIVE_LIB (ruta absoluta)
 *  - <carpeta del server>/libs/memis.so  (la carpeta libs/ se crea sola
 *    al arrancar; copia ahi el memis.so que corresponda a tu sistema)
 *
 * Si FFI no esta disponible, la libreria no existe o su version de API no
 * es compatible, TODOS los metodos caen automaticamente a rutas PHP puras
 * con salida byte-identica (ver native/verify_memis.php). El servidor
 * nunca se cae por la libreria: nativo es solo un atajo rapido.
 */
class Memis{

	/** Version minima de API que exige el nucleo */
	const MIN_API_VERSION = 2;

	/** Nombre del archivo de la libreria dentro de <server>/libs */
	const LIBS_FILE_NAME = "memis.so";

	/** @var \FFI|null */
	private static $ffi = null;

	/** @var bool */
	private static $enabled = false;

	/** @var bool */
	private static $initialized = false;

	/** @var string|null */
	private static $libPath = null;

	/** @var string|null ultimo error de carga (para diagnosticar en el boot) */
	private static $lastError = null;

	private static $CDEF = <<<'CDEF'
int msi_version(void);
int msi_sky_light(const unsigned char *blocks, const unsigned char *solid, const int *hm,
                  const unsigned char *sky_in, unsigned char *sky_out, int layout);
int msi_sky_light_sections(const unsigned char *blocks, const unsigned char *solid, const int *hm,
                           const unsigned char *sky_in, unsigned char *sky_out, int sectionY);
int msi_heightmap(const unsigned char *blocks, unsigned char *out, int layout);
int msi_fast_noise3d(const int *perm,
                     int octaves, double persistence, double expansion,
                     double offx, double offy, double offz,
                     int xs, int ys, int zs, int rx, int ry, int rz,
                     int x, int y, int z, double *out);
int msi_fast_noise2d(const int *perm,
                     int octaves, double persistence, double expansion,
                     double offx, double offy, double offz,
                     int xs, int zs, int rate,
                     int x, int y, int z, double *out);
int msi_pack_nibbles(const unsigned char *in, size_t len, unsigned char *out);
int msi_unpack_nibbles(const unsigned char *in, size_t len, unsigned char *out);
int msi_pack_heightmap(const int64_t *hm, unsigned char *out);
int msi_pack_biomecolors(const int *colors, unsigned char *out);
CDEF;

	private function __construct(){
	}

	/**
	 * Carga la libreria una unica vez. Nunca lanza excepciones.
	 */
	public static function init(){
		if(self::$initialized){
			return;
		}
		self::$initialized = true;

		if(!class_exists(\FFI::class) or PHP_INT_SIZE !== 8){
			return;
		}

		$path = self::findLibrary();
		if($path === null){
			return;
		}

		try{
			$ffi = \FFI::cdef(self::$CDEF, $path);
			if(((int) $ffi->msi_version()) < self::MIN_API_VERSION){
				return; // libreria vieja: usar PHP
			}
			self::$ffi = $ffi;
			self::$libPath = $path;
			self::$enabled = true;
		}catch(\Throwable $e){
			self::$ffi = null;
			self::$enabled = false;
			self::$lastError = $e->getMessage();
		}
	}

	/**
	 * Carpeta <server>/libs (junto a worlds/, plugins/, etc.). Se crea sola.
	 *
	 * @return string ruta absoluta con separador final
	 */
	public static function getLibsDirectory(){
		// Base del server: la carpeta real donde viven worlds/, plugins/,
		// server.properties, etc. Se detecta mirando alrededor del proceso:
		//  - pocketmine\DATA si ya esta definida (ruta usada por el nucleo)
		//  - PHP_BINARY: php vive en <server>/bin/php7/bin/php -> subir hasta
		//    la carpeta que contenga worlds/ (asi funciona en Termux/Android
		//    aunque el cwd sea otro)
		//  - __DIR__ del nucleo como ultimo recurso
		$base = null;
		if(\defined('pocketmine\DATA')){
			$base = \constant('pocketmine\DATA');
		}
		if(($base === null or $base === "" or $base === ".") and \defined('pocketmine\PATH') and \strpos(\constant('pocketmine\PATH'), "phar://") !== 0){
			$base = \constant('pocketmine\PATH');
		}
		if($base === null or $base === "" or $base === "."){
			$probe = \dirname(\PHP_BINARY);
			for($i = 0; $i < 6; ++$i){
				if(\is_dir($probe . DIRECTORY_SEPARATOR . "worlds")){
					$base = $probe . DIRECTORY_SEPARATOR;
					break;
				}
				$parent = \dirname($probe);
				if($parent === $probe){
					break;
				}
				$probe = $parent;
			}
		}
		if($base === null or $base === "" or $base === "."){
			$base = \dirname(\dirname(__DIR__)) . DIRECTORY_SEPARATOR; // <src>/pocketmine -> raiz del nucleo
		}

		$base = \rtrim((string) $base, "/\\");
		if($base === "" || $base === "." || ($base[0] !== "/" && $base[1] !== ":")){
			// Ruta relativa: anclarla al cwd real del proceso
			// NOTA: && dentro del ternario; and/or tienen precedencia MUY baja
			// y rompen la asignacion (base acabaria valiendo "1")
			$cwd = \getcwd();
			$base = (\is_string($cwd) && $cwd !== "") ? \rtrim($cwd, "/\\") : \dirname(\PHP_BINARY);
		}
		$dir = $base . DIRECTORY_SEPARATOR . "libs" . DIRECTORY_SEPARATOR;
		if(!\is_dir($dir)){
			@\mkdir($dir, 0777, true);
		}
		return $dir;
	}

	/**
	 * @return string|null ruta absoluta de la libreria o null
	 */
	private static function findLibrary(){
		$env = \getenv("INFINITY_NATIVE_LIB");
		if(\is_string($env) && $env !== "" && \is_file($env)){
			return \realpath($env) ?: $env;
		}

		// Unica ubicacion soportada: <server>/libs/ (colocacion manual)
		$dir = self::getLibsDirectory();

		// 1) memis.so con el nombre exacto
		$libs = $dir . self::LIBS_FILE_NAME;
		if(\is_file($libs)){
			return \realpath($libs) ?: $libs;
		}

		// 2) aceptar cualquier memis-*.so o *.so que haya dentro de libs/
		//    (asi no hay que renombrar memis-android-arm64-v8a.so, etc.)
		foreach(@\scandir($dir) ?: [] as $f){
			if(\is_string($f) && \substr($f, -3) === ".so"){
				$full = $dir . $f;
				if(\is_file($full)){
					return \realpath($full) ?: $full;
				}
			}
		}

		return null;
	}

	/**
	 * @return bool true si el nucleo usa memis.so
	 */
	public static function isEnabled() : bool{
		self::init();
		return self::$enabled;
	}

	/**
	 * @return string|null ruta de la libreria cargada (null si hay fallback PHP)
	 */
	public static function getLibraryPath(){
		self::init();
		return self::$libPath;
	}

	/**
	 * @return string|null "2 (nativo: /ruta/memis.so)" o "0 (PHP puro; coloca memis.so en ...)"
	 */
	public static function getStatus(){
		self::init();
		if(self::$enabled){
			try{
				$version = (int) self::$ffi->msi_version();
			}catch(\Throwable $e){
				$version = -1;
			}
			return $version . " (nativo: " . self::$libPath . ")";
		}

		$libs = self::getLibsDirectory();
		$soFiles = [];
		foreach(@\scandir($libs) ?: [] as $f){
			if(\is_string($f) && \substr($f, -3) === ".so" && \is_file($libs . $f)){
				$soFiles[] = $f;
			}
		}
		if(\count($soFiles) > 0){
			// Hay .so en libs/ pero no se pudo cargar ninguno: decir por que
			$reason = self::$lastError !== null ? " (error: " . \substr(self::$lastError, 0, 140) . ")" : "";
			return "0 (PHP puro; no se pudo cargar " . $libs . $soFiles[0] . $reason . ")";
		}
		if(!\class_exists(\FFI::class)){
			return "0 (PHP puro; FFI no disponible en este PHP)";
		}
		return "0 (PHP puro; coloca memis.so en " . $libs . self::LIBS_FILE_NAME . ")";
	}

	/**
	 * Empaqueta el heightmap de un chunk: array 256 -> string 256 bytes.
	 *
	 * @param int[] $heightMap
	 *
	 * @return string
	 */
	public static function packHeightMap(array $heightMap) : string{
		self::init();
		if(self::$enabled and \count($heightMap) === 256){
			$ok = true;
			$hm = self::$ffi->new("int64_t[256]");
			for($i = 0; $i < 256; ++$i){
				$v = $heightMap[$i];
				if(\is_int($v) and $v >= 0 and $v <= 0x7f){
					$hm[$i] = $v;
				}else{
					$ok = false;
					break;
				}
			}
			if($ok){
				$out = self::$ffi->new("unsigned char[256]");
				if(((int) self::$ffi->msi_pack_heightmap($hm, $out)) === 0){
					return \FFI::string($out, 256);
				}
			}
		}

		$packed = "";
		foreach($heightMap as $value){
			$packed .= \chr($value);
		}
		return $packed;
	}

	/**
	 * Empaqueta biomeColors: array 256 de colores -> 1024 bytes (pack("N*")).
	 *
	 * @param int[] $biomeColors
	 *
	 * @return string
	 */
	public static function packBiomeColors(array $biomeColors) : string{
		self::init();
		if(self::$enabled and \count($biomeColors) === 256){
			$ok = true;
			$colors = self::$ffi->new("int[256]");
			for($i = 0; $i < 256; ++$i){
				$v = $biomeColors[$i];
				if(\is_int($v) and $v >= 0 and $v <= 0x7FFFFFFF){
					$colors[$i] = $v;
				}else{
					$ok = false;
					break;
				}
			}
			if($ok){
				$out = self::$ffi->new("unsigned char[1024]");
				if(((int) self::$ffi->msi_pack_biomecolors($colors, $out)) === 0){
					return \FFI::string($out, 1024);
				}
			}
		}

		$packed = "";
		foreach($biomeColors as $color){
			$packed .= \pack("N", $color);
		}
		return $packed;
	}

	/**
	 * Empaqueta un array plano de nibbles (0-15) a bytes: el nibble de indice
	 * par va en la parte baja y el impar en la alta.
	 *
	 * @param int[] $nibbles longitud par en el uso real
	 *
	 * @return string
	 */
	public static function packNibbles(array $nibbles) : string{
		$length = \count($nibbles);
		self::init();
		if(self::$enabled and $length > 0){
			$ok = true;
			$in = self::$ffi->new("unsigned char[" . $length . "]");
			for($i = 0; $i < $length; ++$i){
				$v = $nibbles[$i];
				if(\is_int($v) and $v >= 0 and $v <= 0x0f){
					$in[$i] = $v;
				}else{
					$ok = false;
					break;
				}
			}
			if($ok){
				$outLen = $length >> 1;
				$out = self::$ffi->new("unsigned char[" . ($outLen + 1) . "]");
				if(((int) self::$ffi->msi_pack_nibbles($in, $length, $out)) === 0){
					return \FFI::string($out, $outLen);
				}
			}
		}

		$packed = "";
		for($i = 0; $i < $length; $i += 2){
			$packed .= \chr((($nibbles[$i] ?? 0) & 0x0f) | ((($nibbles[$i + 1] ?? 0) & 0x0f) << 4));
		}
		return $packed;
	}

	/**
	 * Recalcula el heightmap de un chunk (anvil y mcregion/leveldb).
	 * Devuelve null si se usa la ruta PHP (el caller recalcula como siempre).
	 *
	 * @param string $blocks 32768 bytes del chunk
	 * @param int    $layout 0 = anvil (y-dominante), 1 = mcregion/leveldb (x-dominante)
	 *
	 * @return int[]|null heightmap[256] o null
	 */
	public static function recalculateHeightMap(string $blocks, int $layout){
		self::init();
		if(self::$enabled and \strlen($blocks) === 32768 and ($layout === 0 or $layout === 1)){
			$in = self::$ffi->new("unsigned char[32768]");
			\FFI::memcpy($in, $blocks, 32768);
			$out = self::$ffi->new("unsigned char[256]");
			if(((int) self::$ffi->msi_heightmap($in, $out, $layout)) === 0){
				$hm = [];
				for($i = 0; $i < 256; ++$i){
					$hm[$i] = $out[$i];
				}
				return $hm;
			}
		}

		return null;
	}

	/**
	 * Rellena el skylight de un chunk completo en un solo paso nativo.
	 * Devuelve null si se usa la ruta PHP.
	 *
	 * @param string $blocks 32768 bytes del chunk
	 * @param string $sky    16384 bytes de skylight actual (packed)
	 * @param int[]  $hm     heightmap[256]
	 * @param int    $layout 0 = anvil, 1 = mcregion/leveldb
	 *
	 * @return string|null skylight packed de 16384 bytes o null
	 */
	public static function populateSkyLight(string $blocks, string $sky, array $hm, int $layout){
		self::init();
		if(self::$enabled and \strlen($blocks) === 32768 and \strlen($sky) === 16384 and \count($hm) === 256 and ($layout === 0 or $layout === 1)){
			$ok = true;
			$hmIn = self::$ffi->new("int[256]");
			for($i = 0; $i < 256; ++$i){
				$v = $hm[$i];
				if(\is_int($v)){
					$hmIn[$i] = $v;
				}else{
					$ok = false;
					break;
				}
			}
			if(!$ok){
				return null;
			}
			$blocksIn = self::$ffi->new("unsigned char[32768]");
			\FFI::memcpy($blocksIn, $blocks, 32768);
			$skyIn = self::$ffi->new("unsigned char[16384]");
			\FFI::memcpy($skyIn, $sky, 16384);
			$skyOut = self::$ffi->new("unsigned char[16384]");
			if(((int) self::$ffi->msi_sky_light($blocksIn, self::getSolidTable(), $hmIn, $skyIn, $skyOut, $layout)) === 0){
				return \FFI::string($skyOut, 16384);
			}
		}

		return null;
	}

	/**
	 * @return \FFI\CData tabla Block::$solid (256 unsigned char, 1 = solido)
	 */
	private static function getSolidTable(){
		static $table = null;
		if($table === null){
			$table = self::$ffi->new("unsigned char[256]");
			foreach(\pocketmine\block\Block::$solid as $id => $isSolid){
				$table[$id] = $isSolid ? 1 : 0;
			}
		}
		return $table;
	}

	/**
	 * Rellena el skylight de un chunk anvil seccion por seccion: llama al
	 * callback una vez por seccion para leer su buffer real (2048 bytes),
	 * lo recalcula nativamente y devuelve un array [$y => packed 2048].
	 * Devuelve null si no hay nativo o el callback no existe.
	 *
	 * @param string   $blocks 32768 bytes concatenados de secciones (y-dominante)
	 * @param int[]    $hm     heightmap[256]
	 * @param callable $getSectionSky [$this, "getSectionSkyLightArray"]($y) : string
	 *
	 * @return string[]|null
	 */
	public static function populateSkyLightSections(string $blocks, array $hm, callable $getSectionSky){
		self::init();
		if(self::$enabled and \strlen($blocks) === 32768 and \count($hm) === 256){
			$ok = true;
			$hmIn = self::$ffi->new("int[256]");
			for($i = 0; $i < 256; ++$i){
				$v = $hm[$i];
				if(\is_int($v)){
					$hmIn[$i] = $v;
				}else{
					$ok = false;
					break;
				}
			}
			if($ok){
				$sections = [];
				$blocksIn = self::$ffi->new("unsigned char[32768]");
				\FFI::memcpy($blocksIn, $blocks, 32768);
				$skyOut = self::$ffi->new("unsigned char[16384]");
				$rc = 0;
				for($y = 0; $y < 8 && $rc === 0; ++$y){
					$sec = (string) \call_user_func($getSectionSky, $y);
					if(\strlen($sec) !== 2048){
						$rc = 3;
						break;
					}
					$skyIn = self::$ffi->new("unsigned char[2048]");
					\FFI::memcpy($skyIn, $sec, 2048);
					$rc = (int) self::$ffi->msi_sky_light_sections($blocksIn, self::getSolidTable(), $hmIn, $skyIn, $skyOut, $y);
					if($rc === 0){
						$sections[$y] = \FFI::string($skyOut, 2048);
					}
				}
				if($rc === 0 and \count($sections) === 8){
					return $sections;
				}
			}
		}

		return null;
	}

	/**
	 * Generator::getFastNoise3D con muestreo e interpolacion trilineal
	 * identicos a la ruta PHP. Devuelve null si no hay nativo.
	 *
	 * @param \pocketmine\level\generator\noise\Noise $noise
	 * @param int                                     $xSize
	 * @param int                                     $ySize
	 * @param int                                     $zSize
	 * @param int                                     $xSamplingRate
	 * @param int                                     $ySamplingRate
	 * @param int                                     $zSamplingRate
	 * @param int                                     $x
	 * @param int                                     $y
	 * @param int                                     $z
	 *
	 * @return \SplFixedArray|null [$xx][$zz][$yy]
	 */
	public static function getFastNoise3D($noise, $xSize, $ySize, $zSize, $xSamplingRate, $ySamplingRate, $zSamplingRate, $x, $y, $z){
		self::init();
		if(self::$enabled and $xSamplingRate !== 0 and $ySamplingRate !== 0 and $zSamplingRate !== 0
			and ($xSize % $xSamplingRate) === 0 and ($ySize % $ySamplingRate) === 0 and ($zSize % $zSamplingRate) === 0
			and $xSamplingRate > 0 and $ySamplingRate > 0 and $zSamplingRate > 0
		){
			$perm = self::getPermTable($noise);
			if($perm === null){
				return null;
			}

			$outCount = ($xSize + 1) * ($zSize + 1) * ($ySize + 1);
			$out = self::$ffi->new("double[" . $outCount . "]");
			$rc = (int) self::$ffi->msi_fast_noise3d(
				$perm,
				(int) $noise->getOctaves(), (double) $noise->getPersistence(), (double) $noise->getExpansion(),
				(double) $noise->getOffsetX(), (double) $noise->getOffsetY(), (double) $noise->getOffsetZ(),
				$xSize, $ySize, $zSize, $xSamplingRate, $ySamplingRate, $zSamplingRate,
				$x, $y, $z,
				$out
			);
			if($rc === 0){
				$zspan = $zSize + 1;
				$yspan = $ySize + 1;
				$result = new \SplFixedArray($xSize);
				for($xx = 0; $xx < $xSize; ++$xx){
					$rowZ = new \SplFixedArray($zSize);
					$baseZ = $xx * $zspan;
					for($zz = 0; $zz < $zSize; ++$zz){
						$rowY = new \SplFixedArray($ySize);
						$baseY = ($baseZ + $zz) * $yspan;
						for($yy = 0; $yy < $ySize; ++$yy){
							$rowY[$yy] = $out[$baseY + $yy];
						}
						$rowZ[$zz] = $rowY;
					}
					$result[$xx] = $rowZ;
				}
				return $result;
			}
		}

		return null;
	}

	/**
	 * Generator::getFastNoise2D con muestreo e interpolacion bilineal
	 * identicos a la ruta PHP. Devuelve null si no hay nativo.
	 *
	 * @param \pocketmine\level\generator\noise\Noise $noise
	 * @param int                                     $xSize
	 * @param int                                     $zSize
	 * @param int                                     $samplingRate
	 * @param int                                     $x
	 * @param int                                     $y
	 * @param int                                     $z
	 *
	 * @return \SplFixedArray|null [$xx][$zz]
	 */
	public static function getFastNoise2D($noise, $xSize, $zSize, $samplingRate, $x, $y, $z){
		self::init();
		if(self::$enabled and $samplingRate !== 0 and $samplingRate > 0
			and ($xSize % $samplingRate) === 0 and ($zSize % $samplingRate) === 0
		){
			$perm = self::getPermTable($noise);
			if($perm === null){
				return null;
			}

			$outCount = ($xSize + 1) * ($zSize + 1);
			$out = self::$ffi->new("double[" . $outCount . "]");
			$rc = (int) self::$ffi->msi_fast_noise2d(
				$perm,
				(int) $noise->getOctaves(), (double) $noise->getPersistence(), (double) $noise->getExpansion(),
				(double) $noise->getOffsetX(), (double) $noise->getOffsetY(), (double) $noise->getOffsetZ(),
				$xSize, $zSize, $samplingRate,
				$x, $y, $z,
				$out
			);
			if($rc === 0){
				$zspan = $zSize + 1;
				$result = new \SplFixedArray($xSize);
				for($xx = 0; $xx < $xSize; ++$xx){
					$rowZ = new \SplFixedArray($zSize);
					$base = $xx * $zspan;
					for($zz = 0; $zz < $zSize; ++$zz){
						$rowZ[$zz] = $out[$base + $zz];
					}
					$result[$xx] = $rowZ;
				}
				return $result;
			}
		}

		return null;
	}

	/**
	 * Extrae la tabla perm (512 ints) de un Noise a un buffer nativo.
	 *
	 * @return \FFI\CData|null
	 */
	private static function getPermTable($noise){
		try{
			$prop = new \ReflectionProperty(\get_class($noise), "perm");
			$prop->setAccessible(true);
			$permArray = $prop->getValue($noise);
			if(\count($permArray) !== 512){
				return null;
			}
			$perm = self::$ffi->new("int[512]");
			for($i = 0; $i < 512; ++$i){
				$v = $permArray[$i];
				if(\is_int($v)){
					$perm[$i] = $v;
				}else{
					return null;
				}
			}
			return $perm;
		}catch(\ReflectionException $e){
			return null;
		}
	}
}
