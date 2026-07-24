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
 * Backward compatibility alias file.
 *
 * The original "Object" class has been renamed to "TreeObject" and moved
 * to TreeObject.php because "Object" is a reserved class name in PHP 8.0+.
 *
 * This file exists solely to provide a class_alias so that any code still
 * referencing pocketmine\level\generator\object\Object will resolve to
 * the new TreeObject class. The alias uses string-based names to avoid
 * PHP 8+ reserved name issues with ::class syntax.
 *
 * The actual class definition is in TreeObject.php (same namespace).
 */
namespace pocketmine\level\generator\object;

// Backward compatibility alias: allows code that still references
// pocketmine\level\generator\object\Object to work correctly.
// Using string-based names to avoid PHP 8+ reserved name issues with ::class
class_alias('pocketmine\\level\\generator\\object\\TreeObject', 'pocketmine\\level\\generator\\object\\Object');
