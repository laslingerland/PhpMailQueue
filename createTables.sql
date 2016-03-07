CREATE TABLE `mailqueue` (
    id bigint(20) NOT NULL,
    desiredSendTime datetime NOT NULL,
    actualSendTime datetime DEFAULT NULL,
    fromName varchar(255) NOT NULL,
    fromAddress varchar(255) NOT NULL,
    toName varchar(255) NOT NULL,
    toAddress varchar(255) NOT NULL,
    subject varchar(255) NOT NULL,
    bodyHtml text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `mailqueue`
    ADD PRIMARY KEY (`id`),
    ADD KEY `ix_desiredSendTime` (`desiredSendTime`),
    ADD KEY `ix_actualSendTime` (`actualSendTime`);

ALTER TABLE `mailqueue`
    MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;