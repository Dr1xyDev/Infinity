<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\network\protocol;

use pocketmine\utils\Color;

class ClientboundMapItemDataPacket extends DataPacket {

    const NETWORK_ID = Info::CLIENTBOUND_MAP_ITEM_DATA_PACKET;

    const BITFLAG_TEXTURE_UPDATE = 0x02;
	const BITFLAG_DECORATION_UPDATE = 0x04;

    /** @var int */
	public $mapId;
	/** @var int */
	public $type;
	/** @var int */
	public $scale = 0;

	/** @var array */
	public $decorations = []; //TODO:

	/** @var int */
	public $width = 128;
	/** @var int */
	public $height = 128;
	/** @var int */
	public $xOffset = 0;
	/** @var int */
	public $yOffset = 0;

	/** @var Color[][]|string */
	public $colors;

	/** @var bool */
	public $isColorArray = true;

    public function decode() {
        //TODO:
    }

    public function encode() {
        $this->reset();
        $this->putLong($this->mapId);

		$type = 0x00;
		
		if(count($this->colors) > 0){
			$type |= self::BITFLAG_TEXTURE_UPDATE;
		}
		$this->putInt($type);

		if(($type & self::BITFLAG_TEXTURE_UPDATE) !== 0) {
			$this->putByte($this->scale);

			$this->putInt($this->width);
			$this->putInt($this->height);
			$this->putInt($this->xOffset);
			$this->putInt($this->yOffset);

			if($this->isColorArray) {
				for($y = 0; $y < $this->height; ++$y){
					for($x = 0; $x < $this->width; ++$x) {
						$color = $this->colors[$y][$x];
						
						$this->putByte($color->getR());
						$this->putByte($color->getG());
						$this->putByte($color->getB());
						$this->putByte($color->getA());
					}
				}
			} else {
				$this->put($this->colors);
			}
		}
    }

}