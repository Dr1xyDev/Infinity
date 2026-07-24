<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

interface AttachableLogger extends \Logger{

    /**
     * @param LoggerAttachment $attachment
     */
    public function addAttachment(\LoggerAttachment $attachment);

    /**
     * @param LoggerAttachment $attachment
     */
    public function removeAttachment(\LoggerAttachment $attachment);

    public function removeAttachments();

    /**
     * @return \LoggerAttachment[]
     */
    public function getAttachments();
}