/*
 * memis.so - Aceleracion nativa del nucleo Infinity (PHP FFI)
 *
 * Algoritmos portados 1:1 desde el nucleo PHP para que la salida sea
 * byte-identica a las rutas de fallback en pocketmine/utils/Memis.php:
 *  - msi_sky_light / msi_sky_light_sections : skylight de chunks (relleno vertical)
 *  - msi_heightmap                          : recalculateHeightMap (anvil y flat)
 *  - msi_fast_noise3d / msi_fast_noise2d    : Generator::getFastNoise3D/2D
 *  - msi_pack_nibbles / msi_unpack_nibbles  : empaquetado de nibbles
 *  - msi_pack_heightmap / msi_pack_biomecolors : serializacion de chunks
 *
 * El ruido usa la implementacion Simplex del nucleo (todos los generadores
 * del nucleo usan pocketmine\level\generator\noise\Simplex).
 *
 * Compatible con PHP 8.4 (Infinity, Dr1xyDev) via FFI::cdef().
 * Licencia: LGPL-3.0-or-later (igual que el nucleo).
 *
 * @author Infinity Team
 * @link https://github.com/Dr1xyDev
 */

#include <stdint.h>
#include <stddef.h>
#include <string.h>

#define MEMIS_API_VERSION 2

/* ------------------------------------------------------------------ */
/* Tabla de gradiente (Simplex::$grad3)                                */
/* ------------------------------------------------------------------ */

static const int8_t MSI_GRAD3[12][3] = {
	{ 1,  1, 0}, {-1,  1, 0}, { 1, -1, 0}, {-1, -1, 0},
	{ 1,  0, 1}, {-1,  0, 1}, { 1,  0,-1}, {-1,  0,-1},
	{ 0,  1, 1}, { 0, -1, 1}, { 0,  1,-1}, { 0, -1,-1}
};

/* ------------------------------------------------------------------ */
/* Simplex 3D (Simplex::getNoise3D, con offset)                        */
/* ------------------------------------------------------------------ */

static double msi_simplex3d(const int *perm, double sx, double sy, double sz,
                            double ox, double oy, double oz){
	double x = sx + ox;
	double y = sy + oy;
	double z = sz + oz;

	const double F3 = 1.0 / 3.0;
	const double G3 = 1.0 / 6.0;

	double s = (x + y + z) * F3;
	int i = (int) (x + s);
	int j = (int) (y + s);
	int k = (int) (z + s);
	double t = (i + j + k) * G3;

	double x0 = x - (i - t);
	double y0 = y - (j - t);
	double z0 = z - (k - t);

	int i1, j1, k1, i2, j2, k2;
	if(x0 >= y0){
		if(y0 >= z0){      /* X Y Z */
			i1 = 1; j1 = 0; k1 = 0; i2 = 1; j2 = 1; k2 = 0;
		}else if(x0 >= z0){/* X Z Y */
			i1 = 1; j1 = 0; k1 = 0; i2 = 1; j2 = 0; k2 = 1;
		}else{             /* Z X Y */
			i1 = 0; j1 = 0; k1 = 1; i2 = 1; j2 = 0; k2 = 1;
		}
	}else{
		if(y0 < z0){       /* Z Y X */
			i1 = 0; j1 = 0; k1 = 1; i2 = 0; j2 = 1; k2 = 1;
		}else if(x0 < z0){ /* Y Z X */
			i1 = 0; j1 = 1; k1 = 0; i2 = 0; j2 = 1; k2 = 1;
		}else{             /* Y X Z */
			i1 = 0; j1 = 1; k1 = 0; i2 = 1; j2 = 1; k2 = 0;
		}
	}

	double x1 = x0 - i1 + G3;
	double y1 = y0 - j1 + G3;
	double z1 = z0 - k1 + G3;
	double x2 = x0 - i2 + 2.0 * G3;
	double y2 = y0 - j2 + 2.0 * G3;
	double z2 = z0 - k2 + 2.0 * G3;
	double x3 = x0 - 1.0 + 3.0 * G3;
	double y3 = y0 - 1.0 + 3.0 * G3;
	double z3 = z0 - 1.0 + 3.0 * G3;

	int ii = i & 255;
	int jj = j & 255;
	int kk = k & 255;

	double n = 0.0;

	double t0 = 0.6 - x0 * x0 - y0 * y0 - z0 * z0;
	if(t0 > 0){
		const int8_t *g0 = MSI_GRAD3[perm[ii + perm[jj + perm[kk]]] % 12];
		n += t0 * t0 * t0 * t0 * ((double) g0[0] * x0 + (double) g0[1] * y0 + (double) g0[2] * z0);
	}

	double t1 = 0.6 - x1 * x1 - y1 * y1 - z1 * z1;
	if(t1 > 0){
		const int8_t *g1 = MSI_GRAD3[perm[ii + i1 + perm[jj + j1 + perm[kk + k1]]] % 12];
		n += t1 * t1 * t1 * t1 * ((double) g1[0] * x1 + (double) g1[1] * y1 + (double) g1[2] * z1);
	}

	double t2 = 0.6 - x2 * x2 - y2 * y2 - z2 * z2;
	if(t2 > 0){
		const int8_t *g2 = MSI_GRAD3[perm[ii + i2 + perm[jj + j2 + perm[kk + k2]]] % 12];
		n += t2 * t2 * t2 * t2 * ((double) g2[0] * x2 + (double) g2[1] * y2 + (double) g2[2] * z2);
	}

	double t3 = 0.6 - x3 * x3 - y3 * y3 - z3 * z3;
	if(t3 > 0){
		const int8_t *g3 = MSI_GRAD3[perm[ii + 1 + perm[jj + 1 + perm[kk + 1]]] % 12];
		n += t3 * t3 * t3 * t3 * ((double) g3[0] * x3 + (double) g3[1] * y3 + (double) g3[2] * z3);
	}

	return 32.0 * n;
}

/* Noise::noise3D de la clase base (octavas) */
static double msi_octave_noise3d(const int *perm, int octaves, double persistence,
                                 double expansion, double ox, double oy, double oz,
                                 double x, double y, double z, int normalized){
	double result = 0.0;
	double amp = 1.0;
	double freq = 1.0;
	double max = 0.0;

	x *= expansion;
	y *= expansion;
	z *= expansion;

	for(int i = 0; i < octaves; ++i){
		result += msi_simplex3d(perm, x * freq, y * freq, z * freq, ox, oy, oz) * amp;
		max += amp;
		freq *= 2.0;
		amp *= persistence;
	}

	if(normalized){
		result /= max;
	}

	return result;
}

/* ------------------------------------------------------------------ */
/* API: version                                                        */
/* ------------------------------------------------------------------ */

int msi_version(void){
	return MEMIS_API_VERSION;
}

/* ------------------------------------------------------------------ */
/* Skylight                                                            */
/* ------------------------------------------------------------------ */

/* Indice de bloque en buffer plano anvil (y-dominante, 32768 bytes):
 * seccion $y >> 4, dentro de seccion ($y & 0xF) << 8 + ($z << 4) + $x */
static inline int msi_anvil_idx(int x, int y, int z){
	return ((y & 0xF0) << 8) + (y & 0x0F) * 256 + (z << 4) + x;
}

/* Indice de bloque en buffer plano mcregion/leveldb (x-dominante) */
static inline int msi_flat_idx(int x, int y, int z){
	return (x << 11) | (z << 7) | y;
}

static inline int msi_solid_at(const unsigned char *blocks, int layout, int x, int y, int z, const unsigned char *solid){
	return solid[layout == 0 ? blocks[msi_anvil_idx(x, y, z)] : blocks[msi_flat_idx(x, y, z)]];
}

static inline void msi_sky_set(unsigned char *sky, int layout, int x, int y, int z, unsigned char v){
	if(layout == 0){
		int i = (y << 7) + (z << 3) + (x >> 1);
		if(x & 1){
			sky[i] = (unsigned char) ((sky[i] & 0x0F) | ((v & 0x0F) << 4));
		}else{
			sky[i] = (unsigned char) ((sky[i] & 0xF0) | (v & 0x0F));
		}
	}else{
		int i = (x << 10) | (z << 6) | (y >> 1);
		if(y & 1){
			sky[i] = (unsigned char) ((sky[i] & 0x0F) | ((v & 0x0F) << 4));
		}else{
			sky[i] = (unsigned char) ((sky[i] & 0xF0) | (v & 0x0F));
		}
	}
}

/* Relleno vertical de una columna, identico al fallback PHP:
 *  - celdas y > heightmap                  -> 15
 *  - desde heightmap hacia abajo, mientras no haya bloque solido -> 15
 *  - al primer solido (o por debajo)       -> se conserva skyIn
 * skyOut ya viene copiado de skyIn por el caller. */
static void msi_fill_column(unsigned char *skyOut, int layout,
                            const unsigned char *blocks, const unsigned char *solid,
                            const int *hm, int x, int z){
	int top = hm[(z << 4) + x];
	int y;
	for(y = 127; y > top; --y){
		msi_sky_set(skyOut, layout, x, y, z, 15);
	}
	for(y = top; y >= 0; --y){
		if(msi_solid_at(blocks, layout, x, y, z, solid)){
			break;
		}
		msi_sky_set(skyOut, layout, x, y, z, 15);
	}
}

int msi_sky_light(const unsigned char *blocks, const unsigned char *solid, const int *hm,
                  const unsigned char *sky_in, unsigned char *sky_out, int layout){
	if(layout != 0 && layout != 1){
		return 1;
	}
	memcpy(sky_out, sky_in, 16384);

	for(int x = 0; x < 16; ++x){
		for(int z = 0; z < 16; ++z){
			msi_fill_column(sky_out, layout, blocks, solid, hm, x, z);
		}
	}
	return 0;
}

/* Skylight por seccion anvil (2048 bytes por seccion, 16 niveles de y).
 * Reproduce el fallback PHP operando via secciones: las celdas sobre el
 * heightmap y las no-solidas hasta el primer solido valen 15; el resto
 * conserva los bytes originales de la seccion. sectionY: 0..7. */
int msi_sky_light_sections(const unsigned char *blocks, const unsigned char *solid, const int *hm,
                           const unsigned char *sky_in, unsigned char *sky_out, int sectionY){
	if(sectionY < 0 || sectionY > 7){
		return 2;
	}
	memcpy(sky_out, sky_in, 2048);

	int yBase = sectionY << 4;

	for(int x = 0; x < 16; ++x){
		for(int z = 0; z < 16; ++z){
			int top = hm[(z << 4) + x];
			/* primer bloque solido bajando desde top (o -1 si no hay) */
			int ySolid = -1;
			for(int y = top; y >= 0; --y){
				if(msi_solid_at(blocks, 0, x, y, z, solid)){
					ySolid = y;
					break;
				}
			}
			for(int yy = 0; yy < 16; ++yy){
				int y = yBase + yy;
				if(y > top || (y > ySolid && y <= top)){
					msi_sky_set(sky_out, 0, x, yy, z, 15);
				}
			}
		}
	}
	return 0;
}

/* ------------------------------------------------------------------ */
/* Heightmap                                                           */
/* ------------------------------------------------------------------ */

/* getHighestBlockAt: y del bloque no-aire mas alto (0 si la columna es aire) */
int msi_heightmap(const unsigned char *blocks, unsigned char *out, int layout){
	if(layout != 0 && layout != 1){
		return 1;
	}
	for(int x = 0; x < 16; ++x){
		for(int z = 0; z < 16; ++z){
			int y;
			for(y = 127; y >= 0; --y){
				if(blocks[layout == 0 ? msi_anvil_idx(x, y, z) : msi_flat_idx(x, y, z)] != 0){
					break;
				}
			}
			out[(z << 4) + x] = (unsigned char) (y >= 0 ? y : 0);
		}
	}
	return 0;
}

/* ------------------------------------------------------------------ */
/* FastNoise 3D/2D con muestreo + interpolacion                        */
/* ------------------------------------------------------------------ */

int msi_fast_noise3d(const int *perm,
                     int octaves, double persistence, double expansion,
                     double offx, double offy, double offz,
                     int xs, int ys, int zs, int rx, int ry, int rz,
                     int x, int y, int z, double *out){
	if(xs < 1 || ys < 1 || zs < 1 || rx < 1 || ry < 1 || rz < 1){
		return 1;
	}
	int xspan = xs + 1, zspan = zs + 1, yspan = ys + 1;

	/* muestreo en la reticula, orden [xx][zz][yy], normalizado (true) */
	for(int xx = 0; xx < xspan; xx += rx){
		for(int zz = 0; zz < zspan; zz += rz){
			for(int yy = 0; yy < yspan; yy += ry){
				out[(xx * zspan + zz) * yspan + yy] =
					msi_octave_noise3d(perm, octaves, persistence, expansion,
						offx, offy, offz,
						(double) (x + xx), (double) (y + yy), (double) (z + zz), 1);
			}
		}
	}

	/* interpolacion trilineal identica a Generator::getFastNoise3D */
	for(int xx = 0; xx < xs; ++xx){
		for(int zz = 0; zz < zs; ++zz){
			for(int yy = 0; yy < ys; ++yy){
				if((xx % rx) != 0 || (zz % rz) != 0 || (yy % ry) != 0){
					int nx = (xx / rx) * rx;
					int ny = (yy / ry) * ry;
					int nz = (zz / rz) * rz;
					int nnx = nx + rx;
					int nny = ny + ry;
					int nnz = nz + rz;

					double dx1 = (nnx - xx) / (double) (nnx - nx);
					double dx2 = (xx - nx) / (double) (nnx - nx);
					double dy1 = (nny - yy) / (double) (nny - ny);
					double dy2 = (yy - ny) / (double) (nny - ny);

					const double *q000 = out + (nx * zspan + nz) * yspan + ny;
					const double *q100 = out + (nnx * zspan + nz) * yspan + ny;
					const double *q010 = out + (nx * zspan + nnz) * yspan + ny;
					const double *q110 = out + (nnx * zspan + nnz) * yspan + ny;
					const double *q001 = out + (nx * zspan + nz) * yspan + nny;
					const double *q101 = out + (nnx * zspan + nz) * yspan + nny;
					const double *q011 = out + (nx * zspan + nnz) * yspan + nny;
					const double *q111 = out + (nnx * zspan + nnz) * yspan + nny;

					out[(xx * zspan + zz) * yspan + yy] =
						((nnz - zz) / (double) (nnz - nz)) * (
							dy1 * (dx1 * *q000 + dx2 * *q100) +
							dy2 * (dx1 * *q001 + dx2 * *q101)
						) + ((zz - nz) / (double) (nnz - nz)) * (
							dy1 * (dx1 * *q010 + dx2 * *q110) +
							dy2 * (dx1 * *q011 + dx2 * *q111)
						);
				}
			}
		}
	}
	return 0;
}

int msi_fast_noise2d(const int *perm,
                     int octaves, double persistence, double expansion,
                     double offx, double offy, double offz,
                     int xs, int zs, int rate,
                     int x, int y, int z, double *out){
	if(xs < 1 || zs < 1 || rate < 1){
		return 1;
	}
	int zspan = zs + 1;

	/* muestreo en la reticula, sin normalizar (noise3D con y fija) */
	for(int xx = 0; xx <= xs; xx += rate){
		for(int zz = 0; zz <= zs; zz += rate){
			out[xx * zspan + zz] =
				msi_octave_noise3d(perm, octaves, persistence, expansion,
					offx, offy, offz,
					(double) (x + xx), (double) y, (double) (z + zz), 0);
		}
	}

	/* interpolacion bilineal identica a Generator::getFastNoise2D */
	for(int xx = 0; xx < xs; ++xx){
		for(int zz = 0; zz < zs; ++zz){
			if((xx % rate) != 0 || (zz % rate) != 0){
				int nx = (xx / rate) * rate;
				int nz = (zz / rate) * rate;
				int nnx = nx + rate;
				int nnz = nz + rate;

				double dx1 = (nnx - xx) / (double) (nnx - nx);
				double dx2 = (xx - nx) / (double) (nnx - nx);

				double q00 = out[nx * zspan + nz];
				double q01 = out[nx * zspan + nnz];
				double q10 = out[nnx * zspan + nz];
				double q11 = out[nnx * zspan + nnz];

				out[xx * zspan + zz] =
					((nnz - zz) / (double) (nnz - nz)) * (dx1 * q00 + dx2 * q10) +
					((zz - nz) / (double) (nnz - nz)) * (dx1 * q01 + dx2 * q11);
			}
		}
	}
	return 0;
}

/* ------------------------------------------------------------------ */
/* Empaquetado                                                         */
/* ------------------------------------------------------------------ */

int msi_pack_nibbles(const unsigned char *in, size_t len, unsigned char *out){
	for(size_t i = 0; i < len; i += 2){
		unsigned char hi = (i + 1 < len) ? (unsigned char) (in[i + 1] & 0x0F) : 0;
		out[i >> 1] = (unsigned char) ((in[i] & 0x0F) | (hi << 4));
	}
	return 0;
}

int msi_unpack_nibbles(const unsigned char *in, size_t len, unsigned char *out){
	for(size_t i = 0; i < len; ++i){
		out[i] = (unsigned char) ((i & 1) ? ((in[i >> 1] >> 4) & 0x0F) : (in[i >> 1] & 0x0F));
	}
	return 0;
}

int msi_pack_heightmap(const int64_t *hm, unsigned char *out){
	for(int i = 0; i < 256; ++i){
		out[i] = (unsigned char) (hm[i] & 0xFF);
	}
	return 0;
}

int msi_pack_biomecolors(const int *colors, unsigned char *out){
	for(int i = 0; i < 256; ++i){
		uint32_t c = (uint32_t) colors[i];
		int b = i << 2;
		out[b]     = (unsigned char) ((c >> 24) & 0xFF);
		out[b + 1] = (unsigned char) ((c >> 16) & 0xFF);
		out[b + 2] = (unsigned char) ((c >> 8) & 0xFF);
		out[b + 3] = (unsigned char) (c & 0xFF);
	}
	return 0;
}
