<?php
    require_once('../PhpMailQueue.class.php');

    $queue = new PhpMailQueue();
    
    $currentTime = date('YmdHis');
    $try = 50;
    $data = Array();

    for($i=1; $i<=5; $i++) {
        $data[$i] = Array();
        $data[$i]['sendAt']      = date('Y-m-d H:i:s');
        $data[$i]['fromName']    = 'John Doe';
        $data[$i]['fromAddress'] = 'john@doe.com';
        $data[$i]['toName']      = 'Jane Doe';
        $data[$i]['toAddress']   = 'jane@doe.com';
        $data[$i]['subject']     = 'Test message PhpMailQueue '.$currentTime;
        $data[$i]['body']        = 'Test message PhpMailQueue '.$currentTime;
    }

    // Directly process the added messages
    echo "Trying to send ".$try." emails.<br />";
    
    $counter = $queue->processQueue($try, $queue->add($data));
    
    echo "Added 5 new emails to be sent directly. ".$counter." emails were actually sent.";
?>