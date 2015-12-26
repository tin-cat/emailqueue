CREATE TABLE `blacklist` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `date_blocked` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `emails` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `foreign_id_a` int(11) unsigned DEFAULT NULL,
  `foreign_id_b` int(11) DEFAULT NULL,
  `priority` tinyint(2) unsigned DEFAULT '10',
  `is_inmediate` tinyint(1) unsigned DEFAULT '0',
  `is_sent` tinyint(1) unsigned DEFAULT NULL,
  `is_cancelled` tinyint(1) unsigned DEFAULT NULL,
  `is_blocked` tinyint(1) unsigned DEFAULT NULL,
  `is_sendingnow` tinyint(1) unsigned DEFAULT NULL,
  `send_count` int(11) unsigned DEFAULT NULL,
  `error_count` int(11) DEFAULT NULL,
  `date_injected` datetime DEFAULT NULL,
  `date_queued` datetime DEFAULT NULL,
  `date_sent` datetime DEFAULT NULL,
  `is_html` tinyint(1) unsigned DEFAULT NULL,
  `from` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `from_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `to` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `replyto` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `replyto_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `content` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `content_nonhtml` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `list_unsubscribe_url` varchar(255) DEFAULT NULL,
  `attachments` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `is_embed_images` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `listings` (`is_sent`,`is_cancelled`,`date_injected`,`date_sent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `incidences` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email_id` int(11) unsigned DEFAULT NULL,
  `date_incidence` datetime DEFAULT NULL,
  `description` longtext CHARACTER SET latin1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;