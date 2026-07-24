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

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\generator\normal\object\mushroom\BigMushroom;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\Random;

class RedMushroom extends Flowable{

        protected $id = self::RED_MUSHROOM;

        public function __construct(){

        }

        public function getName() : string{
                return "Red Mushroom";
        }

        public function canBeActivated() : bool{
                return true;
        }

        /**
         * Handles bonemeal interaction: grows a big red mushroom when
         * bone meal (Dye with damage 15) is applied to a small red mushroom.
         * This matches vanilla Minecraft behavior where bone meal on a red
         * mushroom on mycelium/dirt/grass causes it to grow into a huge mushroom.
         * Uses BigMushroom(RED) by MaruselPlay which handles metadata correctly.
         */
        public function onActivate(Item $item, Player $player = null){
                // Check if the item is bone meal (Dye, damage 0x0F = 15)
                if($item->getId() === Item::DYE && $item->getDamage() === 0x0F){
                        $mushroom = new BigMushroom(BigMushroom::RED);
                        $result = $mushroom->generate(
                                $this->getLevel(),
                                new Random(mt_rand()),
                                new Vector3($this->x, $this->y, $this->z)
                        );

                        if($result){
                                // Remove the small mushroom block (replaced by the huge one)
                                $this->getLevel()->setBlock($this, Block::get(Block::AIR), true, false);

                                // Consume one bone meal if player is not in creative mode
                                if($player !== null && ($player->gamemode & 0x01) === 0){
                                        $item->count--;
                                }
                                return true;
                        }
                }

                return false;
        }

        public function onUpdate($type){
                if($type === Level::BLOCK_UPDATE_NORMAL){
                        if($this->getSide(0)->isTransparent() === true){
                                $this->getLevel()->useBreakOn($this);

                                return Level::BLOCK_UPDATE_NORMAL;
                        }
                }

                return false;
        }

        public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
                $down = $this->getSide(0);
                if($down->isTransparent() === false){
                        $this->getLevel()->setBlock($block, $this, true, true);

                        return true;
                }

                return false;
        }
}
