<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\inventory;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\item\Item;
use pocketmine\Player;

class SimpleTransactionQueue implements TransactionQueue{

	
	protected $player = null;

	
	protected $transactionQueue;
	
	protected $transactionsToRetry;
	
	
	protected $inventories;

	
	protected $lastUpdate = -1;

	
	protected $transactionCount = 0;

	
	public function __construct(Player $player = null){
		$this->player = $player;
		$this->transactionQueue = new \SplQueue();
		$this->transactionsToRetry = new \SplQueue();
	}

	
	public function getPlayer(){
		return $this->player;
	}

	public function getInventories(){
		return $this->inventories;
	}

	public function getTransactions(){
		return $this->transactionQueue;
	}

	public function getTransactionCount(){
		return $this->transactionCount;
	}

	public function addTransaction(Transaction $transaction){
		$this->transactionQueue->enqueue($transaction);
		if($transaction->getInventory() instanceof Inventory){
			
			$this->inventories[spl_object_hash($transaction)] = $transaction->getInventory();
		}
		$this->lastUpdate = microtime(true);
		$this->transactionCount += 1;
	}

	public function execute(){
		
		$failed = [];

		while(!$this->transactionsToRetry->isEmpty()){
			
			$this->transactionQueue->enqueue($this->transactionsToRetry->dequeue());
		}

		if(!$this->transactionQueue->isEmpty()){
			$this->player->getServer()->getPluginManager()->callEvent($ev = new InventoryTransactionEvent($this));
		}else{
			return;
		}

		while(!$this->transactionQueue->isEmpty()){
			$transaction = $this->transactionQueue->dequeue();

			if($ev->isCancelled()){
				$this->transactionCount -= 1;
				$transaction->sendSlotUpdate($this->player); 
				unset($this->inventories[spl_object_hash($transaction)]);
				continue;
			}elseif(!$transaction->execute($this->player)){
				$transaction->addFailure();
				if($transaction->getFailures() >= self::DEFAULT_ALLOWED_RETRIES){
					
					$this->transactionCount -= 1;
					$failed[] = $transaction;
				}else{
					
					$this->transactionsToRetry->enqueue($transaction);
				}
				continue;
			}

			$this->transactionCount -= 1;
			$transaction->setSuccess();
			$transaction->sendSlotUpdate($this->player);
			unset($this->inventories[spl_object_hash($transaction)]);
		}

		foreach($failed as $f){
			$f->sendSlotUpdate($this->player);
			unset($this->inventories[spl_object_hash($f)]);
		}
	}
}