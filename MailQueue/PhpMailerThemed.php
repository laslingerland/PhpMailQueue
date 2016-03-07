<?
    // Subclass of PHPMailer. Adds a layout to the sent emails
    
    require("PHPMailer/class.phpmailer.php");
    
    class themedMailer extends PHPMailer {
        
        function __construct() {
            $this->CharSet = "utf-8";
        }
        
        function Send(){
            // HTML to preceed the message
            $bodyPre =  "<!DOCTYPE html>".
                        "<html>".
                            "<head>".
                                "<title></title>".
                            "</head>".
                            "<body style='font-family:arial,sans-serif;font-size:10pt;'>".
                                "<div style='margin: 10px;'>";
                                        
            // HTML to close the message
            $bodyPost =         "</div>".
                            "</body>".
                        "</html>";

            $this->msgHTML($bodyPre.$this->Body.$bodyPost);

            return parent::Send();
        }
    }
?>