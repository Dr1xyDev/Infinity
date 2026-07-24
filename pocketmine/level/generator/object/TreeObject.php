<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

/**
 * All the different object classes used in populators.
 *
 * NOTE: This class was originally named "Object" but was renamed to
 * "TreeObject" because "Object" is a reserved class name in PHP 8.0+
 * and cannot be used as a class declaration. This file is named
 * TreeObject.php to match the class name, so the PocketMine classloader
 * (BaseClassLoader) can find it properly by converting the FQCN to a
 * file path.
 *
 * A class_alias is provided in Object.php for backward compatibility
 * with any existing code that references the old "Object" name.
 */
namespace pocketmine\level\generator\object;


abstract class TreeObject{

}
