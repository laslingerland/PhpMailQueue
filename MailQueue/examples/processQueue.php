<?php
    require_once('../PhpMailQueue.class.php');

    $try = 50;
    
    $queue = new PhpMailQueue();
    
    echo "Trying to send ".$try." emails.<br />";
    
    $counter = $queue->processQueue($try);
    
    echo "Sent ".$counter." emails.";
    
    if($counter < $try) {
        echo " Not all desired emails were sent, so the mail limit was probably reached. Please try again later.";
    }
?>