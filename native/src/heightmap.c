/* Chunk heightmap recalculation (getHighestBlockAt) */
#include "memis.h"

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
