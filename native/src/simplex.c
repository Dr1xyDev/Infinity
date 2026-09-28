/* Simplex core ported 1:1 from the core PHP (noise/Simplex.php) */
#include "memis.h"

const int8_t MSI_GRAD3[12][3] = {
	{ 1,  1, 0}, {-1,  1, 0}, { 1, -1, 0}, {-1, -1, 0},
	{ 1,  0, 1}, {-1,  0, 1}, { 1,  0,-1}, {-1,  0,-1},
	{ 0,  1, 1}, { 0, -1, 1}, { 0,  1,-1}, { 0, -1,-1}
};

double msi_simplex3d(const int *perm, double sx, double sy, double sz,
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

/* Noise::noise3D base class (octaves) */
double msi_octave_noise3d(const int *perm, int octaves, double persistence,
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
