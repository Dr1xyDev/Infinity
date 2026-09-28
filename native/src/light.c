/* Chunk sky light fill, ported 1:1 from the PHP fallback */
#include <string.h>
#include "memis.h"

static int msi_solid_at(const unsigned char *blocks, int layout, int x, int y, int z, const unsigned char *solid){
	return solid[layout == 0 ? blocks[msi_anvil_idx(x, y, z)] : blocks[msi_flat_idx(x, y, z)]];
}

static void msi_sky_set(unsigned char *sky, int layout, int x, int y, int z, unsigned char v){
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

/* vertical fill per column, identical to the PHP fallback:
 * above heightmap = 15, down to first solid block = 15, rest keeps skyIn */
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

/* per-section anvil sky light (2048 bytes per section) */
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
