/* Sampled noise fields with trilinear/bilinear interpolation
 * (identical to Generator::getFastNoise3D / getFastNoise2D) */
#include <stdlib.h>
#include "memis.h"

int msi_fast_noise3d(const int *perm,
                     int octaves, double persistence, double expansion,
                     double offx, double offy, double offz,
                     int xs, int ys, int zs, int rx, int ry, int rz,
                     int x, int y, int z, double *out){
	if(xs < 1 || ys < 1 || zs < 1 || rx < 1 || ry < 1 || rz < 1){
		return 1;
	}
	msi_noise_field3d(perm, octaves, persistence, expansion, offx, offy, offz,
		xs, ys, zs, rx, ry, rz, x, y, z, out);
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
	msi_noise_field2d(perm, octaves, persistence, expansion, offx, offy, offz,
		xs, zs, rate, x, y, z, out);
	return 0;
}

void msi_noise_field3d(const int *perm,
                       int octaves, double persistence, double expansion,
                       double offx, double offy, double offz,
                       int xs, int ys, int zs, int rx, int ry, int rz,
                       int x, int y, int z, double *out){
	int xspan = xs + 1, zspan = zs + 1, yspan = ys + 1;

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
}

void msi_noise_field2d(const int *perm,
                       int octaves, double persistence, double expansion,
                       double offx, double offy, double offz,
                       int xs, int zs, int rate,
                       int x, int y, int z, double *out){
	int zspan = zs + 1;

	for(int xx = 0; xx <= xs; xx += rate){
		for(int zz = 0; zz <= zs; zz += rate){
			out[xx * zspan + zz] =
				msi_octave_noise3d(perm, octaves, persistence, expansion,
					offx, offy, offz,
					(double) (x + xx), (double) y, (double) (z + zz), 0);
		}
	}

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
}
