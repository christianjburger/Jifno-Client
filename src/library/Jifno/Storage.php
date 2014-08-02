<?php
namespace Jifno;

interface Storage
{
    public function persist(\Jifno\Email $email);
    
    public function delete($messageId);
    
    public function moveToSent($messageId, $reference);

    public function moveToFailed($messageId, $error);
    
    public function getQueue();
    
    public function purge($days);
}