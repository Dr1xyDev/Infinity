/*
 * Infinity lib for aceleration gen the worlds
 */

#include <stdint.h>
#include <string.h>

#define CH_X 17
#define CH_Z 17
#define CH_Y 129
#define CH_SIZE (CH_X * CH_Z * CH_Y)

static _Thread_local double noise_buf[CH_SIZE];
static _Thread_local uint8_t blocks_buf[32768];
static _Thread_local uint32_t biome_color_buf[256];

static const double G3 = 1.0 / 6.0;

static const double grad3[12][3] = {
	{1,1,0},{-1,1,0},{1,-1,0},{-1,-1,0},
	{1,0,1},{-1,0,1},{1,0,-1},{-1,0,-1},
	{0,1,1},{0,-1,1},{0,1,-1},{0,-1,-1}
};

 // simplex3D
static double simplex3(const uint8_t *perm, double x, double y, double z){
	const double F3 = 1.0 / 3.0;
	double s = (x + y + z) * F3;
	int i = (int)(x + s);
	int j = (int)(y + s);
	int k = (int)(z + s);
	double t = (i + j + k) * G3;

	double x0 = x - (i - t);
	double y0 = y - (j - t);
	double z0 = z - (k - t);

	int i1, j1, k1, i2, j2, k2;
	if(x0 >= y0){
		if(y0 >= z0){ i1 = 1; j1 = 0; k1 = 0; i2 = 1; j2 = 1; k2 = 0; }
		else if(x0 >= z0){ i1 = 1; j1 = 0; k1 = 0; i2 = 1; j2 = 0; k2 = 1; }
		else{ i1 = 0; j1 = 0; k1 = 1; i2 = 1; j2 = 0; k2 = 1; }
	}else{
		if(y0 < z0){ i1 = 0; j1 = 0; k1 = 1; i2 = 0; j2 = 1; k2 = 1; }
		else if(x0 < z0){ i1 = 0; j1 = 1; k1 = 0; i2 = 0; j2 = 1; k2 = 1; }
		else{ i1 = 0; j1 = 1; k1 = 0; i2 = 1; j2 = 1; k2 = 0; }
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
		const double *g = grad3[perm[ii + perm[jj + perm[kk]]] % 12];
		n += t0 * t0 * t0 * t0 * (g[0] * x0 + g[1] * y0 + g[2] * z0);
	}

	double t1 = 0.6 - x1 * x1 - y1 * y1 - z1 * z1;
	if(t1 > 0){
		const double *g = grad3[perm[ii + i1 + perm[jj + j1 + perm[kk + k1]]] % 12];
		n += t1 * t1 * t1 * t1 * (g[0] * x1 + g[1] * y1 + g[2] * z1);
	}

	double t2 = 0.6 - x2 * x2 - y2 * y2 - z2 * z2;
	if(t2 > 0){
		const double *g = grad3[perm[ii + i2 + perm[jj + j2 + perm[kk + k2]]] % 12];
		n += t2 * t2 * t2 * t2 * (g[0] * x2 + g[1] * y2 + g[2] * z2);
	}

	double t3 = 0.6 - x3 * x3 - y3 * y3 - z3 * z3;
	if(t3 > 0){
		const double *g = grad3[perm[ii + 1 + perm[jj + 1 + perm[kk + 1]]] % 12];
		n += t3 * t3 * t3 * t3 * (g[0] * x3 + g[1] * y3 + g[2] * z3);
	}

	return 32.0 * n;
}

 //Noise 3d
double utilsram_noise3d(const uint8_t *perm, int octaves, double persistence,
                        double expansion, double ox, double oy, double oz,
                        double x, double y, double z){
	x *= expansion;
	y *= expansion;
	z *= expansion;

	double freq = 1.0, amp = 1.0, max = 0.0, result = 0.0;
	for(int i = 0; i < octaves; i++){
		result += simplex3(perm, x * freq + ox, y * freq + oy, z * freq + oz) * amp;
		max += amp;
		freq *= 2.0;
		amp *= persistence;
	}
	return result / max;
}

 //noise field
int utilsram_noise_field(const uint8_t *perm, int octaves, double persistence,
                         double expansion, double ox, double oy, double oz,
                         int startX, int startY, int startZ){
	for(int xx = 0; xx <= 16; xx += 4){
		for(int zz = 0; zz <= 16; zz += 4){
			for(int yy = 0; yy <= 128; yy += 8){
				noise_buf[((xx * CH_Z) + zz) * CH_Y + yy] =
					utilsram_noise3d(perm, octaves, persistence, expansion, ox, oy, oz,
						(double)(startX + xx), (double)(startY + yy), (double)(startZ + zz));
			}
		}
	}

	for(int xx = 0; xx < 16; xx++){
		for(int zz = 0; zz < 16; zz++){
			for(int yy = 0; yy < 128; yy++){
				if(xx % 4 == 0 && zz % 4 == 0 && yy % 8 == 0) continue;

				int nx = xx / 4, ny = yy / 8, nz = zz / 4;

				double dx1 = (double)(nx * 4 + 4 - xx) / 4.0;
				double dx2 = (double)(xx - nx * 4) / 4.0;
				double dy1 = (double)(ny * 8 + 8 - yy) / 8.0;
				double dy2 = (double)(yy - ny * 8) / 8.0;
				double dz1 = (double)(nz * 4 + 4 - zz) / 4.0;
				double dz2 = (double)(zz - nz * 4) / 4.0;

				int x0 = nx * 4, x1 = (nx + 1) * 4;
				int y0 = ny * 8, y1 = (ny + 1) * 8;
				int z0 = nz * 4, z1 = (nz + 1) * 4;
				double a = noise_buf[((x0 * CH_Z) + z0) * CH_Y + y0];
				double b = noise_buf[((x1 * CH_Z) + z0) * CH_Y + y0];
				double c = noise_buf[((x0 * CH_Z) + z0) * CH_Y + y1];
				double d = noise_buf[((x1 * CH_Z) + z0) * CH_Y + y1];
				double e = noise_buf[((x0 * CH_Z) + z1) * CH_Y + y0];
				double f = noise_buf[((x1 * CH_Z) + z1) * CH_Y + y0];
				double g = noise_buf[((x0 * CH_Z) + z1) * CH_Y + y1];
				double h = noise_buf[((x1 * CH_Z) + z1) * CH_Y + y1];

				noise_buf[((xx * CH_Z) + zz) * CH_Y + yy] =
					dz1 * (dy1 * (dx1 * a + dx2 * b) + dy2 * (dx1 * c + dx2 * d)) +
					dz2 * (dy1 * (dx1 * e + dx2 * f) + dy2 * (dx1 * g + dx2 * h));
			}
		}
	}

	return 0;
}

 // vanilla gen
const uint8_t* utilsram_generate_terrain(const uint8_t *biomeIds,
                                         const int32_t *minElev, const int32_t *maxElev,
                                         int waterHeight, uint8_t *heightOut){
	memset(blocks_buf, 0, sizeof(blocks_buf));

	for(int x = 0; x < 16; x++){
		for(int z = 0; z < 16; z++){
			int col = (z << 4) + x;
			uint8_t id = biomeIds[col];
			double minSum = (double)minElev[id];
			double maxSum = (double)maxElev[id];
			double denom = (maxSum > minSum) ? (maxSum - minSum) : 1.0;
			int solidLand = 0;
			int top = 0;
			int base = (x << 11) | (z << 7);

			for(int y = 127; y >= 0; y--){
				if(y == 0){
					blocks_buf[base] = 7; /* BEDROCK */
					continue;
				}

				double noiseAdjustment = 2.0 * ((maxSum - (double)y) / denom) - 1.0;
				double noiseValue = noise_buf[((x * CH_Z) + z) * CH_Y + y] + noiseAdjustment;

				if(noiseValue > 0.0){
					blocks_buf[base + y] = 1; /* STONE */
					solidLand = 1;
					if(top == 0) top = y;
				}else if(y <= waterHeight && !solidLand){
					blocks_buf[base + y] = 9; /* STILL_WATER */
				}
			}

			heightOut[col] = (uint8_t)top;
		}
	}

	return blocks_buf;
}

 // Ligh population
void utilsram_populate_sky_light(const uint8_t *blocks, uint8_t *skyLight,
                                 uint8_t *heightMap, const uint8_t *solid){
	for(int x = 0; x < 16; x++){
		for(int z = 0; z < 16; z++){
			int base = (x << 11) | (z << 7);
			int sbase = (x << 10) | (z << 6);
			int top = 0;

			for(int y = 127; y >= 0; y--){
				if(blocks[base + y] != 0){
					top = y;
					break;
				}
			}

			for(int y = 127; y > top; y--){
				int si = sbase + (y >> 1);
				if((y & 1) == 0){
					skyLight[si] = (uint8_t)((skyLight[si] & 0xF0) | 0x0F);
				}else{
					skyLight[si] = (uint8_t)((skyLight[si] & 0x0F) | 0xF0);
				}
			}

			for(int y = top; y >= 0; y--){
				if(solid[blocks[base + y]]){
					break;
				}
				int si = sbase + (y >> 1);
				if((y & 1) == 0){
					skyLight[si] = (uint8_t)((skyLight[si] & 0xF0) | 0x0F);
				}else{
					skyLight[si] = (uint8_t)((skyLight[si] & 0x0F) | 0xF0);
				}
			}

			heightMap[(z << 4) + x] = (uint8_t)top;
		}
	}
}

uint32_t utilsram_mem_usage(void){
	return (uint32_t)(sizeof(noise_buf) + sizeof(blocks_buf) + sizeof(biome_color_buf));
}

 // debug
double utilsram_noise_at(int xx, int zz, int yy){
	return noise_buf[((xx * CH_Z) + zz) * CH_Y + yy];
}
