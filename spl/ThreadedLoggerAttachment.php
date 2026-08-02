<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

abstract class ThreadedLoggerAttachment extends \Threaded implements \LoggerAttachment{

    
    protected $attachment = null;

    
    public final function call($level, $message){
        $this->log($level, $message);
        if($this->attachment instanceof \ThreadedLoggerAttachment){
            $this->attachment->call($level, $message);
        }
    }

    
    public function addAttachment(\ThreadedLoggerAttachment $attachment){
        if($this->attachment instanceof \ThreadedLoggerAttachment){
            $this->attachment->addAttachment($attachment);
        }else{
            $this->attachment = $attachment;
        }
    }

    
    public function removeAttachment(\ThreadedLoggerAttachment $attachment){
        if($this->attachment instanceof \ThreadedLoggerAttachment){
            if($this->attachment === $attachment){
                $this->attachment = null;
                foreach($attachment->getAttachments() as $attachment){
                    $this->addAttachment($attachment);
                }
            }
        }
    }

    public function removeAttachments(){
        if($this->attachment instanceof \ThreadedLoggerAttachment){
            $this->attachment->removeAttachments();
            $this->attachment = null;
        }
    }

    
    public function getAttachments(){
        $attachments = [];
        if($this->attachment instanceof \ThreadedLoggerAttachment){
            $attachments[] = $this->attachment;
            $attachments += $this->attachment->getAttachments();
        }

        return $attachments;
    }
}