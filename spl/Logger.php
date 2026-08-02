<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

interface Logger{

    
    public function emergency($message);

    
    public function alert($message);

    
    public function critical($message);

    
    public function error($message);

    
    public function warning($message);

    
    public function notice($message);

    
    public function info($message);

    
    public function debug($message);

    
    public function log($level, $message);

    
    public function logException(\Throwable $e, $trace = null);
}