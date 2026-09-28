/* Chunk packing helpers (heightmap, biome colors, nibbles) */
#include <string.h>
#include "memis.h"

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

/* heightmap: 256 bytes "C" -> int[256] */
int msi_unpack_heightmap(const unsigned char *hm, int *out){
	for(int i = 0; i < 256; ++i){
		out[i] = (int) hm[i];
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

/* biomeColors: 1024 bytes "N" -> int64[256], unsigned like PHP unpack("N*") */
int msi_unpack_biomecolors(const unsigned char *colors, int64_t *out){
	for(int i = 0; i < 256; ++i){
		const unsigned char *c = colors + (i << 2);
		out[i] = ((int64_t) c[0] << 24) | ((int64_t) c[1] << 16) | ((int64_t) c[2] << 8) | (int64_t) c[3];
	}
	return 0;
}
