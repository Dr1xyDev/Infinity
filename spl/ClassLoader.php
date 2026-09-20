<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.2 Release
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

interface ClassLoader{

    
    public function __construct(?ClassLoader $parent = null);

    
    public function addPath($path, $prepend = false);

    
    public function removePath($path);

    
    public function getClasses();

    
    public function getParent();

    
    public function register($prepend = false);

    
    public function loadClass($name);

    
    public function findClass($name);
}