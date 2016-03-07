MailQueue
=========
MailQueue gives you the opportunity to send bulk emails with respect to outgoing mail limits. Instead of sending an email directly, simply add it to the MailQueue. You can then periodically let the MailQueue class process the emails. In the config file, you can set the limit you want to use.

Installation
------------
Copy the following files to your web server:
* MailQueue.class.php
* MailQueue.config.php
* PhpMailer.themedClass.php
* PHPMailer/class.phpmailer.php

Open MailQueue.config.php for editing. Add the details to connect to your database and change the mail limits if desired.

Settings
--------
The outgoing mail limits can be set as follows:
* mailLimitTimeUnit: 'year', 'month', 'day', 'hour' or 'minute'
* mailLimit: integer to set the number of allowed emails in the set timeUnit

Examples
--------
* Add emails to the queue: examples/addToQueue.php
* Process emails: examples/processQueue.php
* Send emails directly through the queue: examples/processQueuePreferredEmails.php