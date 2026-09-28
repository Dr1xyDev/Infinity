/*
 * memis - native acceleration for the Infinity core (PHP FFI)
 *
 * Single public header: every exported msi_* symbol is declared here.
 * License: LGPL-3.0-or-later (same as the core).
 *
 * @author Infinity Team
 * @link https://github.com/Dr1xyDev
 */

#ifndef MEMIS_H
#define MEMIS_H

#include <stdint.h>
#include <stddef.h>

#define MEMIS_API_VERSION 5

/* Simplex gradient table */
extern const int8_t MSI_GRAD3[12][3];

/* Simplex core (noise/simplex.c) */
double msi_simplex3d(const int *perm, double sx, double sy, double sz,
                     double ox, double oy, double oz);
double msi_octave_noise3d(const int *perm, int octaves, double persistence,
                          double expansion, double ox, double oy, double oz,
                          double x, double y, double z, int normalized);

/* Noise sampled fields with interpolation (noise/field.c) */
void msi_noise_field3d(const int *perm,
                       int octaves, double persistence, double expansion,
                       double offx, double offy, double offz,
                       int xs, int ys, int zs, int rx, int ry, int rz,
                       int x, int y, int z, double *out);
void msi_noise_field2d(const int *perm,
                       int octaves, double persistence, double expansion,
                       double offx, double offy, double offz,
                       int xs, int zs, int rate,
                       int x, int y, int z, double *out);
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

/* Block index layouts (0 = anvil y-dominant, 1 = mcregion/leveldb x-dominant) */
int msi_anvil_idx(int x, int y, int z);
int msi_flat_idx(int x, int y, int z);

/* version.c */
int msi_version(void);

/* light.c */
int msi_sky_light(const unsigned char *blocks, const unsigned char *solid, const int *hm,
                  const unsigned char *sky_in, unsigned char *sky_out, int layout);
int msi_sky_light_sections(const unsigned char *blocks, const unsigned char *solid, const int *hm,
                           const unsigned char *sky_in, unsigned char *sky_out, int sectionY);

/* heightmap.c */
int msi_heightmap(const unsigned char *blocks, unsigned char *out, int layout);

/* generator.c */
int msi_generate_normal(const int *perm,
                        int octaves, double persistence, double expansion,
                        double offx, double offy, double offz,
                        int chunkX, int chunkZ,
                        const double *minSum, const double *maxSum,
                        int waterHeight, int layout, unsigned char *out);
int msi_vanilla_terrain(const int *perm,
                        int octaves, double persistence, double expansion,
                        double offx, double offy, double offz,
                        int chunkX, int chunkZ,
                        const double *envA, const double *envR, const double *envM,
                        const double *envD, const double *riverDepth,
                        int waterHeight, int layout, unsigned char *out);

/* packing.c */
int msi_pack_nibbles(const unsigned char *in, size_t len, unsigned char *out);
int msi_unpack_nibbles(const unsigned char *in, size_t len, unsigned char *out);
int msi_pack_heightmap(const int64_t *hm, unsigned char *out);
int msi_unpack_heightmap(const unsigned char *hm, int *out);
int msi_pack_biomecolors(const int *colors, unsigned char *out);
int msi_unpack_biomecolors(const unsigned char *colors, int64_t *out);

#endif /* MEMIS_H */
