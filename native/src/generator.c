/* Terrain fillers for Normal (classic + vanilla), single native pass */
#include <stdlib.h>
#include <string.h>
#include "memis.h"

/* Normal::generateChunk classic loop: bedrock y=0, stone where
 * noise+adjust > 0, still water below sea level. minSum/maxSum already
 * smoothed from PHP, indexed (z << 4) + x. out = 32768 bytes. */
int msi_generate_normal(const int *perm,
                        int octaves, double persistence, double expansion,
                        double offx, double offy, double offz,
                        int chunkX, int chunkZ,
                        const double *minSum, const double *maxSum,
                        int waterHeight, int layout, unsigned char *out){
	if(layout != 0 && layout != 1){
		return 1;
	}
	const int xs = 16, ys = 128, zs = 16;
	const int zspan = zs + 1, yspan = ys + 1;

	double *field = (double *) malloc(sizeof(double) * (xs + 1) * zspan * yspan);
	if(field == NULL){
		return 2;
	}
	msi_noise_field3d(perm, octaves, persistence, expansion, offx, offy, offz,
		xs, ys, zs, 4, 8, 4, chunkX * 16, 0, chunkZ * 16, field);

	memset(out, 0, 32768);

	for(int x = 0; x < 16; ++x){
		for(int z = 0; z < 16; ++z){
			double mn = minSum[(z << 4) + x];
			double mx = maxSum[(z << 4) + x];
			const double *col = field + (x * zspan + z) * yspan;
			double caveLevel = mn - 10.0;
			int solidLand = 0;

			for(int y = 127; y >= 1; --y){
				double na = 2.0 * ((mx - (double) y) / (mx - mn)) - 1.0;
				double d = (double) y - caveLevel;
				if(d < 0.0){
					d = 0.0;
				}
				double cap = 0.4 + d / 10.0;
				if(na > cap){
					na = cap;
				}
				double nv = col[y] + na;
				if(nv > 0.0){
					out[layout == 0 ? msi_anvil_idx(x, y, z) : msi_flat_idx(x, y, z)] = 1;
					solidLand = 1;
				}else if(y <= waterHeight && solidLand == 0){
					out[layout == 0 ? msi_anvil_idx(x, y, z) : msi_flat_idx(x, y, z)] = 9;
				}
			}

			out[layout == 0 ? msi_anvil_idx(x, 0, z) : msi_flat_idx(x, 0, z)] = 7;
		}
	}

	free(field);
	return 0;
}

/* Vanilla terrain of Normal in one native pass, byte-identical to the
 * PHP path. PHP supplies smoothed biome envelopes (256 per column,
 * indexed (z << 4) + x): envA base height, envR hill amplitude, envM
 * mountain amplitude, envD fine detail, riverDepth already computed.
 * Noise fields are computed HERE with getFastNoise2D formulas. */
int msi_vanilla_terrain(const int *perm,
                        int octaves, double persistence, double expansion,
                        double offx, double offy, double offz,
                        int chunkX, int chunkZ,
                        const double *envA, const double *envR, const double *envM,
                        const double *envD, const double *riverDepth,
                        int waterHeight, int layout, unsigned char *out){
	if(layout != 0 && layout != 1){
		return 1;
	}
	const int xs = 16, zs = 16;
	const int zspan = zs + 1;

	double *hills = (double *) malloc(sizeof(double) * (xs + 1) * zspan);
	double *detail = (double *) malloc(sizeof(double) * (xs + 1) * zspan);
	double *ridge = (double *) malloc(sizeof(double) * (xs + 1) * zspan);
	if(hills == NULL || detail == NULL || ridge == NULL){
		free(hills); free(detail); free(ridge);
		return 2;
	}
	msi_noise_field2d(perm, octaves, persistence, expansion, offx, offy, offz,
		xs, zs, 4, chunkX * 16, 64, chunkZ * 16, hills);
	msi_noise_field2d(perm, octaves, persistence, expansion, offx, offy, offz,
		xs, zs, 2, chunkX * 16, 96, chunkZ * 16, detail);
	msi_noise_field2d(perm, octaves, persistence, expansion, offx, offy, offz,
		xs, zs, 4, chunkX * 16, 160, chunkZ * 16, ridge);

	memset(out, 0, 32768);

	for(int x = 0; x < 16; ++x){
		for(int z = 0; z < 16; ++z){
			int i = (z << 4) + x;
			int fi = x * zspan + z;

			/* ridged mountains: (1 - |noise|)^2 */
			double r = 1.0 - ridge[fi];
			if(r < 0.0){
				r = 0.0;
			}
			r *= r;

			double height = envA[i]
				+ hills[fi] * envR[i]
				+ ridge[fi] * envM[i] * r
				+ detail[fi] * envD[i]
				- riverDepth[i];

			int hi = (int) height;
			if(hi > 127){
				hi = 127;
			}
			if(hi < 1){
				hi = 1;
			}

			/* solid stone interior; caves are carved by the Cave populator */
			for(int y = 1; y <= hi; ++y){
				out[layout == 0 ? msi_anvil_idx(x, y, z) : msi_flat_idx(x, y, z)] = 1;
			}
			out[layout == 0 ? msi_anvil_idx(x, 0, z) : msi_flat_idx(x, 0, z)] = 7;

			if(hi < waterHeight){
				for(int y = hi + 1; y <= waterHeight; ++y){
					out[layout == 0 ? msi_anvil_idx(x, y, z) : msi_flat_idx(x, y, z)] = 9;
				}
			}
		}
	}

	free(hills); free(detail); free(ridge);
	return 0;
}
