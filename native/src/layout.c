/* Block index layouts for flat chunk buffers */
#include "memis.h"

/* anvil: section (y >> 4), inner (y & 0xF) << 8 + (z << 4) + x */
int msi_anvil_idx(int x, int y, int z){
	return ((y & 0xF0) << 8) + (y & 0x0F) * 256 + (z << 4) + x;
}

/* mcregion/leveldb: x-dominant */
int msi_flat_idx(int x, int y, int z){
	return (x << 11) | (z << 7) | y;
}
