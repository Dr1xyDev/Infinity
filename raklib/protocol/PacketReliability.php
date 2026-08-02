<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace raklib\protocol;

class PacketReliability {
	
	
	const UNRELIABLE = 0;

	
	
	const UNRELIABLE_SEQUENCED = 1;

	
	
	const RELIABLE = 2;

	
	
	const RELIABLE_ORDERED = 3;

	
	const RELIABLE_SEQUENCED = 4;

	const UNRELIABLE_WITH_ACK_RECEIPT = 5;
	const RELIABLE_WITH_ACK_RECEIPT = 6;

	
	const RELIABLE_ORDERED_WITH_ACK_RECEIPT = 7;
}
