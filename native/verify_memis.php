<?php

/*
 * Verificador de memis.so: compara la salida nativa contra la ruta PHP pura
 * (las mismas formulas del nucleo) y exige resultados BYTE-IDENTICOS.
 *
 * Uso: php native/verify_memis.php
 */

if(!class_exists(\FFI::class) or PHP_INT_SIZE !== 8){
	fwrite(STDERR, "FFI no disponible o PHP 32-bit\n");
	exit(1);
}

$cdef = <<<'CDEF'
int msi_version(void);
int msi_sky_light(const unsigned char *blocks, const unsigned char *solid, const int *hm,
                  const unsigned char *sky_in, unsigned char *sky_out, int layout);
int msi_sky_light_sections(const unsigned char *blocks, const unsigned char *solid, const int *hm,
                           const unsigned char *sky_in, unsigned char *sky_out, int sectionY);
int msi_heightmap(const unsigned char *blocks, unsigned char *out, int layout);
int msi_fast_noise3d(const int *perm, int octaves, double persistence, double expansion,
                     double offx, double offy, double offz, int xs, int ys, int zs,
                     int rx, int ry, int rz, int x, int y, int z, double *out);
int msi_fast_noise2d(const int *perm, int octaves, double persistence, double expansion,
                     double offx, double offy, double offz, int xs, int zs, int rate,
                     int x, int y, int z, double *out);
int msi_generate_normal(const int *perm, int octaves, double persistence, double expansion,
                        double offx, double offy, double offz, int chunkX, int chunkZ,
                        const double *minSum, const double *maxSum, int waterHeight, int layout,
                        unsigned char *out);
int msi_pack_nibbles(const unsigned char *in, size_t len, unsigned char *out);
int msi_unpack_nibbles(const unsigned char *in, size_t len, unsigned char *out);
int msi_pack_heightmap(const int64_t *hm, unsigned char *out);
int msi_unpack_heightmap(const unsigned char *hm, int *out);
int msi_pack_biomecolors(const int *colors, unsigned char *out);
int msi_unpack_biomecolors(const unsigned char *colors, int64_t *out);
int msi_vanilla_terrain(const int *perm, int octaves, double persistence, double expansion,
                        double offx, double offy, double offz, int chunkX, int chunkZ,
                        const double *envA, const double *envR, const double *envM,
                        const double *envD, const double *riverDepth,
                        int waterHeight, int layout, unsigned char *out);
CDEF;

$path = getenv("INFINITY_NATIVE_LIB");
if($path === false or $path === "" or !is_file($path)){
	$path = __DIR__ . "/lib/memis.so";
}
if(!is_file($path)){
	fwrite(STDERR, "No se encontro memis.so (compila con: sh ./native/build.sh)\n");
	exit(1);
}

$ffi = \FFI::cdef($cdef, $path);
$v = (int) $ffi->msi_version();
echo "memis.so: $path (API $v)\n";
if($v < 5){
	fwrite(STDERR, "API vieja, se requiere >= 5\n");
	exit(1);
}

/* tabla solid: 1 = solido (aeriforme/transparente = 0, como Block::$solid) */
$solidArr = array_fill(0, 256, 1);
$solidArr[0] = 0;   // air
$solidArr[8] = 0;   // water
$solidArr[9] = 0;
$solidArr[10] = 0;  // lava
$solidArr[11] = 0;
$solidArr[20] = 0;  // glass
$solidArr[26] = 0;  // bed
$solidArr[30] = 0;  // cobweb
$solidArr[31] = 0;  // tallgrass
$solidArr[32] = 0;  // dead bush
$solidArr[37] = 0;  // flower
$solidArr[38] = 0;
$solidArr[39] = 0;
$solidArr[40] = 0;
$solidArr[50] = 0;  // torch
$solidArr[51] = 0;  // fire
$solidArr[55] = 0;  // redstone wire
$solidArr[59] = 0;  // crops
$solidArr[63] = 0;  // sign
$solidArr[64] = 0;  // door
$solidArr[65] = 0;  // ladder
$solidArr[66] = 0;  // rail
$solidArr[68] = 0;  // wall sign
$solidArr[71] = 0;  // door
$solidArr[78] = 0;  // snow layer
$solidArr[83] = 0;  // reeds
$solidArr[90] = 0;  // portal
$solidArr[101] = 0; // iron bars (aprox)
$solidArr[102] = 0; // glass pane
$solidArr[104] = 0; // pumpkin stem
$solidArr[105] = 0; // melon stem
$solidArr[106] = 0; // vines
$solidArr[111] = 0; // lily pad
$solidArr[115] = 0; // nether wart
$solidArr[117] = 0; // brewing stand
$solidArr[118] = 0; // cauldron
$solidArr[127] = 0; // cocoa

$solid = $ffi->new("unsigned char[256]");
foreach($solidArr as $id => $s){
	$solid[$id] = $s;
}

/* --- helpers de salida PHP (identicos al nucleo) -------------------- */

function php_anvil_idx($x, $y, $z){ return (($y & 0xF0) << 8) + ($y & 0x0F) * 256 + ($z << 4) + $x; }
function php_flat_idx($x, $y, $z){ return ($x << 11) | ($z << 7) | $y; }

function php_set_sky(&$sky, $layout, $x, $y, $z, $v){
	if($layout === 0){
		$i = ($y << 7) + ($z << 3) + ($x >> 1);
		if($x & 1){ $sky[$i] = chr((ord($sky[$i]) & 0x0F) | (($v & 0x0F) << 4)); }
		else{ $sky[$i] = chr((ord($sky[$i]) & 0xF0) | ($v & 0x0F)); }
	}else{
		$i = ($x << 10) | ($z << 6) | ($y >> 1);
		if($y & 1){ $sky[$i] = chr((ord($sky[$i]) & 0x0F) | (($v & 0x0F) << 4)); }
		else{ $sky[$i] = chr((ord($sky[$i]) & 0xF0) | ($v & 0x0F)); }
	}
}

/** Relleno vertical del fallback PHP de BaseFullChunk::populateSkyLight() */
function php_populate_sky($blocks, $skyIn, $hm, $layout){
	$sky = $skyIn;
	$idx = $layout === 0 ? "php_anvil_idx" : "php_flat_idx";
	for($x = 0; $x < 16; ++$x){
		for($z = 0; $z < 16; ++$z){
			$top = $hm[($z << 4) + $x];
			for($y = 127; $y > $top; --$y){
				php_set_sky($sky, $layout, $x, $y, $z, 15);
			}
			for($y = $top; $y >= 0; --$y){
				if($GLOBALS["solidArr"][ord($blocks[$idx($x, $y, $z)])]){
					break;
				}
				php_set_sky($sky, $layout, $x, $y, $z, 15);
			}
		}
	}
	return $sky;
}

/** getHighestBlockAt del fallback PHP */
function php_heightmap($blocks, $layout){
	$idx = $layout === 0 ? "php_anvil_idx" : "php_flat_idx";
	$hm = [];
	for($z = 0; $z < 16; ++$z){
		for($x = 0; $x < 16; ++$x){
			for($y = 127; $y >= 0; --$y){
				if(ord($blocks[$idx($x, $y, $z)]) !== 0){
					break;
				}
			}
			$hm[($z << 4) + $x] = $y >= 0 ? $y : 0;
		}
	}
	return $hm;
}

/** Simplex 3D portado del nucleo (mismas constantes y orden) */
class PhpSimplex{
	public $perm = [];
	public $offsetX = 0; public $offsetY = 0; public $offsetZ = 0;

	const GRAD3 = [
		[1, 1, 0], [-1, 1, 0], [1, -1, 0], [-1, -1, 0],
		[1, 0, 1], [-1, 0, 1], [1, 0, -1], [-1, 0, -1],
		[0, 1, 1], [0, -1, 1], [0, 1, -1], [0, -1, -1]
	];

	function __construct(array $perm, $ox, $oy, $oz){
		$this->perm = $perm;
		$this->offsetX = $ox; $this->offsetY = $oy; $this->offsetZ = $oz;
	}

	function noise3D($x, $y, $z){
		$x += $this->offsetX; $y += $this->offsetY; $z += $this->offsetZ;
		$F3 = 1.0 / 3.0; $G3 = 1.0 / 6.0;
		$s = ($x + $y + $z) * $F3;
		$i = (int) ($x + $s); $j = (int) ($y + $s); $k = (int) ($z + $s);
		$t = ($i + $j + $k) * $G3;
		$x0 = $x - ($i - $t); $y0 = $y - ($j - $t); $z0 = $z - ($k - $t);
		if($x0 >= $y0){
			if($y0 >= $z0){ $i1 = 1; $j1 = 0; $k1 = 0; $i2 = 1; $j2 = 1; $k2 = 0; }
			elseif($x0 >= $z0){ $i1 = 1; $j1 = 0; $k1 = 0; $i2 = 1; $j2 = 0; $k2 = 1; }
			else{ $i1 = 0; $j1 = 0; $k1 = 1; $i2 = 1; $j2 = 0; $k2 = 1; }
		}else{
			if($y0 < $z0){ $i1 = 0; $j1 = 0; $k1 = 1; $i2 = 0; $j2 = 1; $k2 = 1; }
			elseif($x0 < $z0){ $i1 = 0; $j1 = 1; $k1 = 0; $i2 = 0; $j2 = 1; $k2 = 1; }
			else{ $i1 = 0; $j1 = 1; $k1 = 0; $i2 = 1; $j2 = 1; $k2 = 0; }
		}
		$x1 = $x0 - $i1 + $G3; $y1 = $y0 - $j1 + $G3; $z1 = $z0 - $k1 + $G3;
		$x2 = $x0 - $i2 + 2.0 * $G3; $y2 = $y0 - $j2 + 2.0 * $G3; $z2 = $z0 - $k2 + 2.0 * $G3;
		$x3 = $x0 - 1.0 + 3.0 * $G3; $y3 = $y0 - 1.0 + 3.0 * $G3; $z3 = $z0 - 1.0 + 3.0 * $G3;
		$ii = $i & 255; $jj = $j & 255; $kk = $k & 255;
		$n = 0;
		$t0 = 0.6 - $x0 * $x0 - $y0 * $y0 - $z0 * $z0;
		if($t0 > 0){
			$g = self::GRAD3[$this->perm[$ii + $this->perm[$jj + $this->perm[$kk]]] % 12];
			$n += $t0 * $t0 * $t0 * $t0 * ($g[0] * $x0 + $g[1] * $y0 + $g[2] * $z0);
		}
		$t1 = 0.6 - $x1 * $x1 - $y1 * $y1 - $z1 * $z1;
		if($t1 > 0){
			$g = self::GRAD3[$this->perm[$ii + $i1 + $this->perm[$jj + $j1 + $this->perm[$kk + $k1]]] % 12];
			$n += $t1 * $t1 * $t1 * $t1 * ($g[0] * $x1 + $g[1] * $y1 + $g[2] * $z1);
		}
		$t2 = 0.6 - $x2 * $x2 - $y2 * $y2 - $z2 * $z2;
		if($t2 > 0){
			$g = self::GRAD3[$this->perm[$ii + $i2 + $this->perm[$jj + $j2 + $this->perm[$kk + $k2]]] % 12];
			$n += $t2 * $t2 * $t2 * $t2 * ($g[0] * $x2 + $g[1] * $y2 + $g[2] * $z2);
		}
		$t3 = 0.6 - $x3 * $x3 - $y3 * $y3 - $z3 * $z3;
		if($t3 > 0){
			$g = self::GRAD3[$this->perm[$ii + 1 + $this->perm[$jj + 1 + $this->perm[$kk + 1]]] % 12];
			$n += $t3 * $t3 * $t3 * $t3 * ($g[0] * $x3 + $g[1] * $y3 + $g[2] * $z3);
		}
		return 32.0 * $n;
	}
}

function php_octave_noise(PhpSimplex $noise, $octaves, $persistence, $expansion, $ox, $oy, $oz, $x, $y, $z, $normalized){
	$result = 0; $amp = 1; $freq = 1; $max = 0;
	$x *= $expansion; $y *= $expansion; $z *= $expansion;
	for($i = 0; $i < $octaves; ++$i){
		$result += $noise->noise3D($x * $freq, $y * $freq, $z * $freq) * $amp;
		$max += $amp;
		$freq *= 2;
		$amp *= $persistence;
	}
	if($normalized === true){
		$result /= $max;
	}
	return $result;
}

/* --- generacion de casos de prueba ---------------------------------- */

mt_srand(20260924);

function make_blocks_anvil(){
	$b = str_repeat("\x00", 32768);
	for($x = 0; $x < 16; ++$x){
		for($z = 0; $z < 16; ++$z){
			$h = mt_rand(40, 90);
			for($y = 0; $y <= $h; ++$y){
				$b[php_anvil_idx($x, $y, $z)] = chr(mt_rand(1, 255));
			}
		}
	}
	return $b;
}

function make_blocks_flat(){
	$b = str_repeat("\x00", 32768);
	for($x = 0; $x < 16; ++$x){
		for($z = 0; $z < 16; ++$z){
			$h = mt_rand(40, 90);
			for($y = 0; $y <= $h; ++$y){
				$b[php_flat_idx($x, $y, $z)] = chr(mt_rand(1, 255));
			}
		}
	}
	return $b;
}

function make_sky(){ return str_repeat("\x00", 16384); }

$fail = 0;
function check($name, $phpBytes, $natBytes){
	global $fail;
	if($phpBytes === $natBytes){
		echo "  OK  $name (" . strlen($natBytes) . " bytes)\n";
	}else{
		++$fail;
		echo "FAIL  $name\n";
		$pl = strlen($phpBytes); $nl = strlen($natBytes);
		echo "      PHP $pl bytes vs nativo $nl bytes\n";
		$diffs = 0;
		for($i = 0; $i < max($pl, $nl); ++$i){
			if(($phpBytes[$i] ?? "\0") !== ($natBytes[$i] ?? "\0")){
				echo "      primer diff en byte $i: " . ord($phpBytes[$i] ?? "\0") . " vs " . ord($natBytes[$i] ?? "\0") . "\n";
				if(++$diffs >= 3) break;
			}
		}
	}
}

/* --- 1. heightmap (ambos layouts) ----------------------------------- */

echo "Test: heightmap\n";
foreach([0, 1] as $layout){
	$blocks = $layout === 0 ? make_blocks_anvil() : make_blocks_flat();
	$phpHm = php_heightmap($blocks, $layout);

	$in = $ffi->new("unsigned char[32768]");
	\FFI::memcpy($in, $blocks, 32768);
	$out = $ffi->new("unsigned char[256]");
	$rc = (int) $ffi->msi_heightmap($in, $out, $layout);
	if($rc !== 0){ echo "FAIL  heightmap layout=$layout rc=$rc\n"; ++$fail; continue; }
	$natHm = "";
	for($i = 0; $i < 256; ++$i){ $natHm .= chr($out[$i]); }
	$phpPacked = "";
	for($i = 0; $i < 256; ++$i){ $phpPacked .= chr($phpHm[$i]); }
	check("heightmap layout=$layout", $phpPacked, $natHm);
}

/* --- 2. skylight (ambos layouts, con heightmap consistente) ---------- */

echo "Test: skylight\n";
foreach([0, 1] as $layout){
	$blocks = $layout === 0 ? make_blocks_anvil() : make_blocks_flat();
	$phpHm = php_heightmap($blocks, $layout);
	$hm = $ffi->new("int[256]");
	foreach($phpHm as $i => $h){ $hm[$i] = $h; }

	$blocksIn = $ffi->new("unsigned char[32768]");
	\FFI::memcpy($blocksIn, $blocks, 32768);
	$skyIn = $ffi->new("unsigned char[16384]");
	\FFI::memcpy($skyIn, make_sky(), 16384);
	$skyOut = $ffi->new("unsigned char[16384]");
	$rc = (int) $ffi->msi_sky_light($blocksIn, $solid, $hm, $skyIn, $skyOut, $layout);
	if($rc !== 0){ echo "FAIL  skylight layout=$layout rc=$rc\n"; ++$fail; continue; }
	$natSky = \FFI::string($skyOut, 16384);

	$phpSky = php_populate_sky($blocks, make_sky(), $phpHm, $layout);
	check("skylight layout=$layout", $phpSky, $natSky);
}

/* --- 3. skylight por secciones anvil --------------------------------- */

echo "Test: skylight secciones (anvil)\n";
$blocks = make_blocks_anvil();
$phpHm = php_heightmap($blocks, 0);
$hm = $ffi->new("int[256]");
foreach($phpHm as $i => $h){ $hm[$i] = $h; }
$blocksIn = $ffi->new("unsigned char[32768]");
\FFI::memcpy($blocksIn, $blocks, 32768);

$phpSky = php_populate_sky($blocks, make_sky(), $phpHm, 0);
$phpSections = [];
for($y = 0; $y < 8; ++$y){
	$phpSections[$y] = substr($phpSky, $y * 2048, 2048);
}

$natSections = [];
for($y = 0; $y < 8; ++$y){
	/* el nucleo pasa el buffer real de la seccion; aqui el estado inicial */
	$secIn = $ffi->new("unsigned char[2048]");
	\FFI::memcpy($secIn, str_repeat("\x00", 2048), 2048);
	$secOut = $ffi->new("unsigned char[2048]");
	$rc = (int) $ffi->msi_sky_light_sections($blocksIn, $solid, $hm, $secIn, $secOut, $y);
	if($rc !== 0){ echo "FAIL  skylight section $y rc=$rc\n"; ++$fail; continue; }
	$natSections[$y] = \FFI::string($secOut, 2048);
}
check("skylight secciones concatenadas", implode("", $phpSections), implode("", $natSections));

/* --- 4. fastNoise3D y fastNoise2D ------------------------------------ */

echo "Test: fastNoise3D/2D\n";

$permArr = [];
for($i = 0; $i < 512; ++$i){ $permArr[$i] = mt_rand(0, 255); }
$perm = $ffi->new("int[512]");
foreach($permArr as $i => $p){ $perm[$i] = $p; }

$noise = new PhpSimplex($permArr, 12.5, 90.25, 160.75);
$oct = 4; $pers = 0.25; $exp = 0.03125;
$ox = 12.5; $oy = 90.25; $oz = 160.75;

foreach([[16, 128, 16, 4, 8, 4], [8, 8, 8, 2, 4, 2]] as $t){
	[$xs, $ys, $zs, $rx, $ry, $rz] = $t;
	$x = mt_rand(-1000, 1000); $y = 0; $z = mt_rand(-1000, 1000);
	$outCount = ($xs + 1) * ($zs + 1) * ($ys + 1);
	$nat = $ffi->new("double[$outCount]");
	$rc = (int) $ffi->msi_fast_noise3d($perm, $oct, $pers, $exp, $ox, $oy, $oz,
		$xs, $ys, $zs, $rx, $ry, $rz, $x, $y, $z, $nat);
	if($rc !== 0){ echo "FAIL  fastNoise3D xs=$xs rc=$rc\n"; ++$fail; continue; }

	$php = [];
	for($xx = 0; $xx <= $xs; $xx += $rx){
		for($zz = 0; $zz <= $zs; $zz += $rz){
			for($yy = 0; $yy <= $ys; $yy += $ry){
				$php[$xx][$zz][$yy] = php_octave_noise($noise, $oct, $pers, $exp, $ox, $oy, $oz, $x + $xx, $y + $yy, $z + $zz, true);
			}
		}
	}
	for($xx = 0; $xx < $xs; ++$xx){
		for($zz = 0; $zz < $zs; ++$zz){
			for($yy = 0; $yy < $ys; ++$yy){
				if($xx % $rx !== 0 or $zz % $rz !== 0 or $yy % $ry !== 0){
					$nx = (int) ($xx / $rx) * $rx;
					$ny = (int) ($yy / $ry) * $ry;
					$nz = (int) ($zz / $rz) * $rz;
					$nnx = $nx + $rx; $nny = $ny + $ry; $nnz = $nz + $rz;
					$dx1 = (($nnx - $xx) / ($nnx - $nx));
					$dx2 = (($xx - $nx) / ($nnx - $nx));
					$dy1 = (($nny - $yy) / ($nny - $ny));
					$dy2 = (($yy - $ny) / ($nny - $ny));
					$php[$xx][$zz][$yy] = (($nnz - $zz) / ($nnz - $nz)) * (
							$dy1 * ($dx1 * $php[$nx][$nz][$ny] + $dx2 * $php[$nnx][$nz][$ny])
							+ $dy2 * ($dx1 * $php[$nx][$nz][$nny] + $dx2 * $php[$nnx][$nz][$nny])
						) + (($zz - $nz) / ($nnz - $nz)) * (
							$dy1 * ($dx1 * $php[$nx][$nnz][$ny] + $dx2 * $php[$nnx][$nnz][$ny])
							+ $dy2 * ($dx1 * $php[$nx][$nnz][$nny] + $dx2 * $php[$nnx][$nnz][$nny])
						);
				}
			}
		}
	}

	$pack = function(array $php) use ($xs, $zs, $ys){
		$s = "";
		$zspan = $zs + 1; $yspan = $ys + 1;
		for($xx = 0; $xx < $xs; ++$xx){
			for($zz = 0; $zz < $zs; ++$zz){
				for($yy = 0; $yy < $ys; ++$yy){
					$s .= pack("E", $php[$xx][$zz][$yy]);
				}
			}
		}
		return $s;
	};

	$natStr = "";
	$zspan = $zs + 1; $yspan = $ys + 1;
	for($xx = 0; $xx < $xs; ++$xx){
		for($zz = 0; $zz < $zs; ++$zz){
			for($yy = 0; $yy < $ys; ++$yy){
				$natStr .= pack("E", $nat[($xx * $zspan + $zz) * $yspan + $yy]);
			}
		}
	}

	/* comparacion exacta de bits dobles */
	check("fastNoise3D xs=$xs (bits exactos)", $pack($php), $natStr);
}

foreach([[16, 16, 4], [16, 16, 8]] as $t){
	[$xs, $zs, $rate] = $t;
	$x = mt_rand(-1000, 1000); $y = mt_rand(0, 100); $z = mt_rand(-1000, 1000);
	$outCount = ($xs + 1) * ($zs + 1);
	$nat = $ffi->new("double[$outCount]");
	$rc = (int) $ffi->msi_fast_noise2d($perm, $oct, $pers, $exp, $ox, $oy, $oz,
		$xs, $zs, $rate, $x, $y, $z, $nat);
	if($rc !== 0){ echo "FAIL  fastNoise2D rate=$rate rc=$rc\n"; ++$fail; continue; }

	$php = [];
	for($xx = 0; $xx <= $xs; $xx += $rate){
		for($zz = 0; $zz <= $zs; $zz += $rate){
			$php[$xx][$zz] = php_octave_noise($noise, $oct, $pers, $exp, $ox, $oy, $oz, $x + $xx, $y, $z + $zz, false);
		}
	}
	for($xx = 0; $xx < $xs; ++$xx){
		for($zz = 0; $zz < $zs; ++$zz){
			if($xx % $rate !== 0 or $zz % $rate !== 0){
				$nx = (int) ($xx / $rate) * $rate;
				$nz = (int) ($zz / $rate) * $rate;
				$nnx = $nx + $rate; $nnz = $nz + $rate;
				$dx1 = (($nnx - $xx) / ($nnx - $nx));
				$dx2 = (($xx - $nx) / ($nnx - $nx));
				$php[$xx][$zz] = (($nnz - $zz) / ($nnz - $nz)) * ($dx1 * $php[$nx][$nz] + $dx2 * $php[$nnx][$nz])
					+ (($zz - $nz) / ($nnz - $nz)) * ($dx1 * $php[$nx][$nnz] + $dx2 * $php[$nnx][$nnz]);
			}
		}
	}

	$phpStr = "";
	$natStr = "";
	$zspan = $zs + 1;
	for($xx = 0; $xx < $xs; ++$xx){
		for($zz = 0; $zz < $zs; ++$zz){
			$phpStr .= pack("E", $php[$xx][$zz]);
			$natStr .= pack("E", $nat[$xx * $zspan + $zz]);
		}
	}
	check("fastNoise2D rate=$rate (bits exactos)", $phpStr, $natStr);
}

/* --- 4b. generador Normal (ambos layouts) ----------------------------- */

echo "Test: generate_normal\n";

/** Relleno exacto de Normal::generateChunk (referencia PHP) */
function php_normal_fill($permArr, $ox, $oy, $oz, $oct, $pers, $exp, $cx, $cz, $mn, $mx, $water, $layout){
	$noise = new PhpSimplex($permArr, $ox, $oy, $oz);
	$xs = 16; $ys = 128; $zs = 16; $rx = 4; $ry = 8; $rz = 4;
	$grid = [];
	for($xx = 0; $xx <= $xs; $xx += $rx){
		for($zz = 0; $zz <= $zs; $zz += $rz){
			for($yy = 0; $yy <= $ys; $yy += $ry){
				$grid[$xx][$zz][$yy] = php_octave_noise($noise, $oct, $pers, $exp, $ox, $oy, $oz, $cx * 16 + $xx, $yy, $cz * 16 + $zz, true);
			}
		}
	}
	$field = [];
	for($xx = 0; $xx < $xs; ++$xx){
		for($zz = 0; $zz < $zs; ++$zz){
			for($yy = 0; $yy < $ys; ++$yy){
				if($xx % $rx !== 0 or $zz % $rz !== 0 or $yy % $ry !== 0){
					$nx = (int) ($xx / $rx) * $rx;
					$ny = (int) ($yy / $ry) * $ry;
					$nz = (int) ($zz / $rz) * $rz;
					$nnx = $nx + $rx; $nny = $ny + $ry; $nnz = $nz + $rz;
					$dx1 = (($nnx - $xx) / ($nnx - $nx));
					$dx2 = (($xx - $nx) / ($nnx - $nx));
					$dy1 = (($nny - $yy) / ($nny - $ny));
					$dy2 = (($yy - $ny) / ($nny - $ny));
					$field[$xx][$zz][$yy] = (($nnz - $zz) / ($nnz - $nz)) * (
							$dy1 * ($dx1 * $grid[$nx][$nz][$ny] + $dx2 * $grid[$nnx][$nz][$ny])
							+ $dy2 * ($dx1 * $grid[$nx][$nz][$nny] + $dx2 * $grid[$nnx][$nz][$nny])
						) + (($zz - $nz) / ($nnz - $nz)) * (
							$dy1 * ($dx1 * $grid[$nx][$nnz][$ny] + $dx2 * $grid[$nnx][$nnz][$ny])
							+ $dy2 * ($dx1 * $grid[$nx][$nnz][$nny] + $dx2 * $grid[$nnx][$nnz][$nny])
						);
				}else{
					$field[$xx][$zz][$yy] = $grid[$xx][$zz][$yy];
				}
			}
		}
	}

	$b = str_repeat("\x00", 32768);
	$idx = $layout === 0 ? "php_anvil_idx" : "php_flat_idx";
	for($x = 0; $x < 16; ++$x){
		for($z = 0; $z < 16; ++$z){
			$minSum = $mn[($z << 4) + $x];
			$maxSum = $mx[($z << 4) + $x];
			$caveLevel = $minSum - 10;
			$solidLand = false;
			for($y = 127; $y >= 0; --$y){
				if($y === 0){
					$b[$idx($x, 0, $z)] = "\x07";
					continue;
				}
				$na = 2 * (($maxSum - $y) / ($maxSum - $minSum)) - 1;
				$d = max(0, $y - $caveLevel);
				$na = min($na, 0.4 + ($d / 10));
				$nv = $field[$x][$z][$y] + $na;
				if($nv > 0){
					$b[$idx($x, $y, $z)] = "\x01";
					$solidLand = true;
				}elseif($y <= $water and !$solidLand){
					$b[$idx($x, $y, $z)] = "\x09";
				}
			}
		}
	}
	return $b;
}

$mnArr = []; $mxArr = [];
for($i = 0; $i < 256; ++$i){
	$mnArr[$i] = 50 + (mt_rand() / mt_getrandmax()) * 30;
	$mxArr[$i] = $mnArr[$i] + 8 + (mt_rand() / mt_getrandmax()) * 40;
}
$mnBuf = $ffi->new("double[256]"); $mxBuf = $ffi->new("double[256]");
foreach($mnArr as $i => $v){ $mnBuf[$i] = $v; }
foreach($mxArr as $i => $v){ $mxBuf[$i] = $v; }
$cx2 = mt_rand(-50, 50); $cz2 = mt_rand(-50, 50);
$genOut = $ffi->new("unsigned char[32768]");
foreach([0, 1] as $layout){
	$rc = (int) $ffi->msi_generate_normal($perm, 4, 0.25, 0.03125, $ox, $oy, $oz, $cx2, $cz2, $mnBuf, $mxBuf, 62, $layout, $genOut);
	if($rc !== 0){ echo "FAIL  generate_normal layout=$layout rc=$rc\n"; ++$fail; continue; }
	$natStr = \FFI::string($genOut, 32768);
	$phpStr = php_normal_fill($permArr, $ox, $oy, $oz, 4, 0.25, 0.03125, $cx2, $cz2, $mnArr, $mxArr, 62, $layout);
	check("generate_normal layout=$layout", $phpStr, $natStr);
}

/* --- 5. empaquetado -------------------------------------------------- */

echo "Test: empaquetado\n";

$nib = [];
for($i = 0; $i < 16384; ++$i){ $nib[$i] = mt_rand(0, 15); }
$in = $ffi->new("unsigned char[16384]");
foreach($nib as $i => $n){ $in[$i] = $n; }
$out = $ffi->new("unsigned char[8192]");
$ffi->msi_pack_nibbles($in, 16384, $out);
$natPacked = \FFI::string($out, 8192);
$phpPacked = "";
for($i = 0; $i < 16384; $i += 2){
	$phpPacked .= chr(($nib[$i] & 0x0f) | (($nib[$i + 1] & 0x0f) << 4));
}
check("pack_nibbles", $phpPacked, $natPacked);

$bytes = str_repeat("\x00", 8192);
for($i = 0; $i < 8192; ++$i){ $bytes[$i] = chr(mt_rand(0, 255)); }
\FFI::memcpy($in, $bytes, 8192);
$out2 = $ffi->new("unsigned char[16384]");
$ffi->msi_unpack_nibbles($in, 16384, $out2);
$phpUn = "";
for($i = 0; $i < 16384; ++$i){
	$phpUn .= chr(($i & 1) ? ((ord($bytes[$i >> 1]) >> 4) & 0x0F) : (ord($bytes[$i >> 1]) & 0x0F));
}
$natUn = \FFI::string($out2, 16384);
check("unpack_nibbles", $phpUn, $natUn);

$hmPhp = [];
for($i = 0; $i < 256; ++$i){ $hmPhp[$i] = mt_rand(0, 127); }
$hmIn = $ffi->new("int64_t[256]");
foreach($hmPhp as $i => $h){ $hmIn[$i] = $h; }
$hmOut = $ffi->new("unsigned char[256]");
$ffi->msi_pack_heightmap($hmIn, $hmOut);
$phpPacked = "";
foreach($hmPhp as $h){ $phpPacked .= chr($h & 0xFF); }
check("pack_heightmap", $phpPacked, \FFI::string($hmOut, 256));

/* unpack_heightmap: roundtrip + contra unpack("C*") de PHP */
$hmUn = $ffi->new("int[256]");
$ffi->msi_unpack_heightmap($hmOut, $hmUn);
$phpUn = [];
foreach(unpack("C*", \FFI::string($hmOut, 256)) as $c){ $phpUn[] = $c; }
$phpUnStr = ""; $natUnStr = "";
for($i = 0; $i < 256; ++$i){
	$phpUnStr .= pack("E", $phpUn[$i]);
	$natUnStr .= pack("E", $hmUn[$i]);
}
check("unpack_heightmap", $phpUnStr, $natUnStr);
$colPhp = [];
for($i = 0; $i < 256; ++$i){ $colPhp[$i] = mt_rand(0, 0x7FFFFFFF); }
$colIn = $ffi->new("int[256]");
foreach($colPhp as $i => $c){ $colIn[$i] = $c; }
$colOut = $ffi->new("unsigned char[1024]");
$ffi->msi_pack_biomecolors($colIn, $colOut);
$phpPacked = "";
foreach($colPhp as $c){ $phpPacked .= pack("N", $c); }
check("pack_biomecolors", $phpPacked, \FFI::string($colOut, 1024));

/* unpack_biomecolors: contra unpack("N*") de PHP (incluye colores con bit alto) */
echo "Test: unpack serializacion\n";
$bcBytes = str_repeat("\x00", 1024);
for($i = 0; $i < 1024; ++$i){ $bcBytes[$i] = chr(mt_rand(0, 255)); }
$bcIn = $ffi->new("unsigned char[1024]");
\FFI::memcpy($bcIn, $bcBytes, 1024);
$bcOut = $ffi->new("int64_t[256]");
$ffi->msi_unpack_biomecolors($bcIn, $bcOut);
$phpUn = [];
foreach(unpack("N*", $bcBytes) as $c){ $phpUn[] = $c; }
$phpUnStr = ""; $natUnStr = "";
for($i = 0; $i < 256; ++$i){
	$phpUnStr .= pack("E", $phpUn[$i]);
	$natUnStr .= pack("E", $bcOut[$i]);
}
check("unpack_biomecolors (N unsigned)", $phpUnStr, $natUnStr);

/* --- 4c. terreno vanilla (ambos layouts) ------------------------------ */

echo "Test: vanilla_terrain\n";

/** Fallback PHP exacto del pipeline vanilla de Normal::generateChunk */
function php_vanilla_fill($permArr, $ox, $oy, $oz, $oct, $pers, $exp, $cx, $cz,
                          $envA, $envR, $envM, $envD, $riverDepth, $water, $layout){
	$noise = new PhpSimplex($permArr, $ox, $oy, $oz);
	$makeField = function($rate, $y) use ($noise, $oct, $pers, $exp, $ox, $oy, $oz, $cx, $cz){
		$xs = 16; $zs = 16;
		$f = [];
		for($xx = 0; $xx <= $xs; $xx += $rate){
			for($zz = 0; $zz <= $zs; $zz += $rate){
				$f[$xx][$zz] = php_octave_noise($noise, $oct, $pers, $exp, $ox, $oy, $oz, $cx * 16 + $xx, $y, $cz * 16 + $zz, false);
			}
		}
		for($xx = 0; $xx < $xs; ++$xx){
			for($zz = 0; $zz < $zs; ++$zz){
				if($xx % $rate !== 0 or $zz % $rate !== 0){
					$nx = (int) ($xx / $rate) * $rate;
					$nz = (int) ($zz / $rate) * $rate;
					$nnx = $nx + $rate; $nnz = $nz + $rate;
					$dx1 = (($nnx - $xx) / ($nnx - $nx));
					$dx2 = (($xx - $nx) / ($nnx - $nx));
					$f[$xx][$zz] = (($nnz - $zz) / ($nnz - $nz)) * ($dx1 * $f[$nx][$nz] + $dx2 * $f[$nnx][$nz])
						+ (($zz - $nz) / ($nnz - $nz)) * ($dx1 * $f[$nx][$nnz] + $dx2 * $f[$nnx][$nnz]);
				}
			}
		}
		return $f;
	};
	$hills = $makeField(4, 64);
	$detail = $makeField(2, 96);
	$ridge = $makeField(4, 160);

	$b = str_repeat("\x00", 32768);
	$idx = $layout === 0 ? "php_anvil_idx" : "php_flat_idx";
	for($x = 0; $x < 16; ++$x){
		for($z = 0; $z < 16; ++$z){
			$i = ($z << 4) + $x;
			$r = 1.0 - $ridge[$x][$z];
			if($r < 0){ $r = 0; }
			$r *= $r;
			$height = $envA[$i] + $hills[$x][$z] * $envR[$i]
				+ $ridge[$x][$z] * $envM[$i] * $r
				+ $detail[$x][$z] * $envD[$i]
				- $riverDepth[$i];
			$hi = (int) $height;
			if($hi > 127){ $hi = 127; }
			if($hi < 1){ $hi = 1; }
			for($y = 1; $y <= $hi; ++$y){ $b[$idx($x, $y, $z)] = "\x01"; }
			$b[$idx($x, 0, $z)] = "\x07";
			for($y = $hi + 1; $y <= $water; ++$y){ $b[$idx($x, $y, $z)] = "\x09"; }
		}
	}
	return $b;
}

$vA = []; $vR = []; $vM = []; $vD = []; $vRiv = [];
for($i = 0; $i < 256; ++$i){
	$vA[$i] = 58 + (mt_rand() / mt_getrandmax()) * 10;
	$vR[$i] = 2 + (mt_rand() / mt_getrandmax()) * 14;
	$vM[$i] = (mt_rand() / mt_getrandmax()) < 0.35 ? (mt_rand() / mt_getrandmax()) * 30 : 0.0;
	$vD[$i] = 1.6;
	$vRiv[$i] = (mt_rand() / mt_getrandmax()) < 0.2 ? 2 + (mt_rand() / mt_getrandmax()) * 5 : 0.0;
}
$vBufs = [];
foreach([$vA, $vR, $vM, $vD, $vRiv] as $k => $arr){
	$b = $ffi->new("double[256]");
	foreach($arr as $i => $val){ $b[$i] = $val; }
	$vBufs[$k] = $b;
}
$cx3 = mt_rand(-50, 50); $cz3 = mt_rand(-50, 50);
$vanOut = $ffi->new("unsigned char[32768]");
foreach([0, 1] as $layout){
	$rc = (int) $ffi->msi_vanilla_terrain($perm, 4, 0.25, 0.03125, $ox, $oy, $oz, $cx3, $cz3,
		$vBufs[0], $vBufs[1], $vBufs[2], $vBufs[3], $vBufs[4], 62, $layout, $vanOut);
	if($rc !== 0){ echo "FAIL  vanilla_terrain layout=$layout rc=$rc\n"; ++$fail; continue; }
	$natStr = \FFI::string($vanOut, 32768);
	$phpStr = php_vanilla_fill($permArr, $ox, $oy, $oz, 4, 0.25, 0.03125, $cx3, $cz3, $vA, $vR, $vM, $vD, $vRiv, 62, $layout);
	check("vanilla_terrain layout=$layout", $phpStr, $natStr);
}

/* --- resultado -------------------------------------------------------- */

if($fail > 0){
	echo "\n$fail test(s) FALLARON\n";
	exit(1);
}
echo "\nTodos los tests OK: salida nativa byte-identica al fallback PHP\n";
exit(0);
