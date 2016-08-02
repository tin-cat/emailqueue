CREATE TABLE `blacklist` (
  `id` int(11) unsigned NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `date_blocked` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `emails` (
  `id` int(11) unsigned NOT NULL COMMENT 'Unique auto increment id column. Do not specify this field when manually inserting.',
  `foreign_id_a` int(11) unsigned DEFAULT NULL COMMENT 'Optional, an id number for your internal records. e.g. Your internal id of the user who has sent this email.',
  `foreign_id_b` int(11) DEFAULT NULL COMMENT 'Optional, a secondary id number for your internal records.',
  `priority` tinyint(2) unsigned DEFAULT '10' COMMENT 'The priority of this email in relation to others: The lower the priority, the sooner it will be sent. e.g. An email with priority 10 will be sent first even if one thousand emails with priority 11 have been injected before.',
  `is_inmediate` tinyint(1) unsigned DEFAULT '0' COMMENT 'Set it to true to send this email as soon as possible. (doesn''t overrides priority setting)',
  `is_sent` tinyint(1) unsigned DEFAULT NULL COMMENT 'Whether the message has been already delivered or not. Do not specify this field when manually inserting emails.',
  `is_cancelled` tinyint(1) unsigned DEFAULT NULL COMMENT 'Whether this email has been cancelled or not. Do not specify this field when manually inserting.',
  `is_blocked` tinyint(1) unsigned DEFAULT NULL COMMENT 'Whether this email has been blocked or not. Do not specify this field when manually inserting.',
  `is_sendingnow` tinyint(1) unsigned DEFAULT NULL COMMENT 'Whether this email is being sent right now or not. Do not specify this field when manually inserting.',
  `send_count` int(11) unsigned DEFAULT NULL COMMENT 'The number of times Emailqueue tried to send this email. Do not specify this field when manually inserting.',
  `error_count` int(11) DEFAULT NULL COMMENT 'The number of times Emailqueue has tried to send this email and failed. Do not specify this field when manually inserting.',
  `date_injected` datetime DEFAULT NULL COMMENT 'The date/time this email was injected on the database. Specify this field when manually inserting.',
  `date_queued` datetime DEFAULT NULL COMMENT 'The date/time when this email should be delivered. Specify this field when manually inserting if you want the message to be delivered in the future.',
  `date_sent` datetime DEFAULT NULL COMMENT 'The date/time this email was delivered. Do not specify this field when manually inserting.',
  `is_html` tinyint(1) unsigned DEFAULT NULL COMMENT 'Whether this email''s content is HTML.',
  `from` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The email address of the sender.',
  `from_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional. The name of the sender.',
  `to` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The email address of the recipient.',
  `replyto` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The email addess where replies to this message will be sent by default.',
  `replyto_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The name where this email will be replied by default.',
  `subject` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT 'The email''s subject',
  `content` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT 'The content of the email. If HTML, be sure to set the is_html field to 1',
  `content_nonhtml` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT 'Optional. A plain-text version of the email for old clients.',
  `list_unsubscribe_url` varchar(255) DEFAULT NULL COMMENT 'Optional. The URL where users can unsubscribe from the newsletter. Highly recommended.',
  `attachments` text CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT 'A serialized array of hash arrays specifying the files to be attached to this email. See example.php on how to build this array.',
  `is_embed_images` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Whether to automatically convert <IMG ... /> tags found on the email content to embedded images that are transferred along with the email itself instead of being referenced to external URLs. Can bring you some interesting benefits, but also hugely increases the data transfer.',
  `custom_headers` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'A serialized array with custom headers to be added when sending with PHPMailer.  NB! The value should not be an empty string, in spite of the fact that PHPMailer::addCustomHeader() allows it.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `incidences` (
  `id` int(11) unsigned NOT NULL,
  `email_id` int(11) unsigned DEFAULT NULL,
  `date_incidence` datetime DEFAULT NULL,
  `description` longtext CHARACTER SET utf8
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `blacklist`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `emails`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`), ADD KEY `listings` (`is_sent`,`is_cancelled`,`date_injected`,`date_sent`);

ALTER TABLE `incidences`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `blacklist`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `emails`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `incidences`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;