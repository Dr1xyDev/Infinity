<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\block;

class TripwireHook extends Solid {

    protected $id = self::TRIPWIRE_HOOK;

    public function __construct($meta = 0){
        $this->meta = (int) $meta;
    }

    public function getName() :string {
        return "Tripwire Hook";
    }

    public function getHardness() {
        return 0;
    }

    public function getResistance(){
        return 0;
    }
    
}
