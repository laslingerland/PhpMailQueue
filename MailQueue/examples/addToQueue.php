<?php
    require_once('../PhpMailQueue.class.php');

    $queue = new PhpMailQueue();
    
    $currentTime = date('YmdHis');
    $data = Array();

    $num = 30;
    
    for($i=1; $i<=$num; $i++) {
        $data[$i] = Array();
        $data[$i]['sendAt']      = date('Y-m-d H:i:s');
        $data[$i]['fromName']    = 'John Doe '.$i;
        $data[$i]['fromAddress'] = 'john@doe.com';
        $data[$i]['toName']      = 'Jane Doe';
        $data[$i]['toAddress']   = 'jane@doe.com';
        $data[$i]['subject']     = 'Test message MailQueue '.$currentTime;
        $data[$i]['body']        = 'Test message MailQueue '.$currentTime;
    }

    $queue->add($data);
    
    echo "Added ".$num." new emails to the queue";
?>