<?php
    require_once('PhpMailQueue.config.php');
    require_once('PhpMailerThemed.php');

    class PhpMailQueue {
        private $dbServer;
        private $dbName;
        private $dbUsername;
        private $dbPassword;
        private $mailLimit;
        private $mailLimitTimeUnit;
        private $db;
        
        function __construct() {
            global $CONFIG;
            
            // Set database connection variables
            $this->dbServer = $CONFIG['dbServer'];
            $this->dbName = $CONFIG['dbName'];
            $this->dbUsername = $CONFIG['dbUsername'];
            $this->dbPassword = $CONFIG['dbPassword'];
            
            // Set app settings
            $this->mailLimit = $CONFIG['mailLimit'];
            $this->mailLimitTimeUnit = $CONFIG['mailLimitTimeUnit'];
            
            // Connect to database
            $this->db = new mysqli($this->dbServer, $this->dbUsername, $this->dbPassword, $this->dbName);
            
            if($this->db->connect_error) {
                die("Error connecting to mailqueue.<br />".$this->db->connect_error);
            }
        }
        
        function add($data) {
            $sqls = "";
            
            foreach($data as $d) {
                $sqls .= "INSERT INTO mailqueue (".
                            "desiredSendTime, ".
                            "fromName, ".
                            "fromAddress, ".
                            "toName, ".
                            "toAddress, ".
                            "subject, ".
                            "bodyHtml ".
                        ") VALUES (".
                            "'".$d['sendAt']."',".
                            "'".$d['fromName']."',".
                            "'".$d['fromAddress']."',".
                            "'".$d['toName']."',".
                            "'".$d['toAddress']."',".
                            "'".$d['subject']."',".
                            "'".$d['body']."'".
                        ");";
            }
            
            $insertIds = Array();
            
            if(!$this->db->multi_query($sqls)){
                die("Error adding emails to mailqueue: (".$this->db->errno.") ".$this->db->error);
            }

            // fetch insert ids
            do {
                $insertIds[] = $this->db->insert_id;
            } while($this->db->more_results() && $this->db->next_result());
            
            return $insertIds;
        }
        
        function processQueue($numberOfMessages, $preferredEmailIds="") {
            // Get number of emails already sent in this timeframe
            
            // Do not check for the last timeUnit, but for the timeUnit we're currently in, e.g.
            // if timeUnit = 'hour', count the processed emails in de current hour, not in the last 60 minutes.
            switch($this->mailLimitTimeUnit) {
                case 'year':
                    $startOfCurrentTimeframe = date('Y-01-01 00:00:00');
                    break;
                case 'month':
                    $startOfCurrentTimeframe = date('Y-m-01 00:00:00');
                    break;
                case 'day':
                    $startOfCurrentTimeframe = date('Y-m-d 00:00:00');
                    break;
                case 'hour':
                    $startOfCurrentTimeframe = date('Y-m-d H:00:00');
                    break;
                case 'minute':
                    $startOfCurrentTimeframe = date('Y-m-d H:i:00');
                    break;
                default:
                    $startOfCurrentTimeframe = '';
            }
            
            if($startOfCurrentTimeframe != '') {
                $successCounter = 0;
                
                $sql =  "SELECT ".
                            "COUNT(*) AS currentCount ".
                        "FROM ".
                            "mailqueue ".
                        "WHERE ".
                            "actualSendTime BETWEEN '".$startOfCurrentTimeframe."' AND NOW()";
                
                if(!($stmt = $this->db->prepare($sql))) {
                    die("Prepare failed: (".$this->db->errno.") ".$this->db->error);
                }
                
                if(!($stmt->execute())) {
                    die("Execute failed: (".$stmt->errno.") ".$stmt->error);
                }
                
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $currentCount = $row['currentCount'];
                $spaceLeft = $this->mailLimit - $currentCount;
                
                // Don't exceed the mail limits!
                if($spaceLeft < $numberOfMessages){
                    $numberOfMessages = $spaceLeft;
                }
                
                // In case of preferred emails, add an explicit filter to accomplish this
                if($preferredEmailIds!=""){
                    $preferredEmailsOnly = "AND id IN (".implode(',', $preferredEmailIds).") ";
                } else {
                    $preferredEmailsOnly = "";
                }

                // Process new emails
                // Allow a gap of 60 seconds in case the time clocks of the DB server and PHP server differ,
                // which would be a problem on direct processings ($queue->process(30, $queue->add($data)))
                $sql =  "SELECT ".
                            "id, ".
                            "fromAddress, ".
                            "fromName, ".
                            "subject, ".
                            "bodyHtml, ".
                            "toAddress, ".
                            "toName ".
                        "FROM ".
                            "mailqueue ".
                        "WHERE ".
                            "desiredSendTime < DATE_ADD(NOW(), INTERVAL 60 second) ".
                            "AND actualSendTime IS NULL ".
                            $preferredEmailsOnly.
                        "ORDER BY ".
                            "desiredSendTime, ".
                            "id ".
                        "LIMIT ?";
                
                if(!($stmt = $this->db->prepare($sql))) {
                    die("Prepare failed: (".$this->db->errno.") ".$this->db->error);
                }
                
                if (!$stmt->bind_param("i", $numberOfMessages)){
                    die("Binding parameters failed: (".$stmt->errno.") ".$stmt->error);
                }
                
                if (!$stmt->execute()) {
                    die("Execute failed: (".$stmt->errno.") ".$stmt->error);
                }
                
                $result = $stmt->get_result();
                
                if($result->num_rows > 0) {
                    $successCounter = 0;
                    $sqls = "";
                    
                    while($row = $result->fetch_assoc()) {
                        $msg = new themedMailer();
                        
                        $msg->From = $row['fromAddress'];
                        $msg->FromName = $row['fromName'];
                        $msg->Subject = $row['subject'];
                        $msg->Body = $row['bodyHtml'];
                        
                        $msg->IsMail();
                        
                        $msg->AddAddress($row['toAddress'], $row['toName']);
                        
                        if($msg->Send()) {
                            $successCounter++;
                            
                            // Save the time of sending to the email in the database
                            $sqls .= "UPDATE mailqueue SET actualSendTime = NOW() WHERE id = ".$row['id'].";";
                        } else {
                            die("Sending mail failed: ".$this->ErrorInfo);
                        }
                    }
                    
                    // Update the sent emails
                    if(!$this->db->multi_query($sqls)){
                        die("Failed updating records for sent emails; these emails will be sent again because of this error.");
                    }
                }
                
                $stmt->close();
            }
            
            return $successCounter;
        }
    }
?>