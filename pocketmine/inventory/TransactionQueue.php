<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\inventory;

interface TransactionQueue{

	const DEFAULT_ALLOWED_RETRIES = 5;

	/**
	 * @return Inventory
	 */
	function getInventories();

	/**
	 * @return \SplQueue
	 */
	function getTransactions();

	/**
	 * @return int
	 */
	function getTransactionCount();

	/**
	 * @param Transaction $transaction
	 *
	 * Adds a transaction to the queue
	 */
	function addTransaction(Transaction $transaction);

	/**
	 * Handles transaction queue execution
	 */
	function execute();

}