<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\permission;


interface ServerOperator{
	/**
	 * Checks if the current object has operator permissions
	 *
	 * @return bool
	 */
	public function isOp();

	/**
	 * Sets the operator permission for the current object
	 *
	 * @param bool $value
	 *
	 * @return void
	 */
	public function setOp($value);
}