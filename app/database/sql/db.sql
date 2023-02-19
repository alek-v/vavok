CREATE TABLE IF NOT EXISTS `vavok_users` (
  `id` int(9) NOT NULL auto_increment,
  `name` varchar(40) NOT NULL,
  `password` varchar(255) NOT NULL,
  `access_permission` int(4) NOT NULL default '0',
  `skin` varchar(30) NOT NULL default 'default',
  `browsers` varchar(50) NULL,
  `ip_address` varchar(30) NULL,
  `timezone` varchar(10) NOT NULL default '0',
  `banned` int(1) NOT NULL default '0',
  `localization` varchar(30) NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;



CREATE TABLE IF NOT EXISTS `vavok_profil` (
  `id` int(9) NOT NULL auto_increment,
  `uid` int(9) NOT NULL default '0',
  `subscribed` int(1) NOT NULL default '0',
  `subscription_code` varchar(100) NULL,
  `personal_status` varchar(50) NULL,
  `registration_date` varchar(30) NULL,
  `registration_activated` varchar(2) NOT NULL default '0',
  `registration_key` varchar(100) NULL,
  `ban_time` varchar(20) NULL,
  `ban_description` varchar(120) NULL,
  `last_ban` varchar(20) NULL,
  `all_bans` varchar(20) NULL,
  `last_visit` varchar(30) NULL,
 PRIMARY KEY  (`id`),
 UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;



CREATE TABLE IF NOT EXISTS `vavok_about` (
  `id` int(9) NOT NULL auto_increment,
  `uid` int(9) NOT NULL default '0',
  `birthday` varchar(40) NULL,
  `sex` char(1) NOT NULL default 'n',
  `email` varchar(80) NULL,
  `site` varchar(50) NULL,
  `city` varchar(100) NULL,
  `about` tinytext NULL,
  `first_name` varchar(150) NULL,
  `last_name` varchar(150) NULL,
  `photo` varchar(30) NULL,
  `address` varchar(100) NULL,
  `zip` varchar(20) NULL,
  `country` varchar(75) NULL,
  `phone` varchar(30) NULL,
 PRIMARY KEY  (`id`),
 UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;



-- inbox
CREATE TABLE IF NOT EXISTS `inbox` (
  `id` int(9) NOT NULL auto_increment,
  `text` MEDIUMTEXT NOT NULL,
  `byuid` int(9) NOT NULL default '0',
  `touid` int(9) NOT NULL default '0',
  `unread` char(1) NOT NULL default '1',
  `timesent` int(10) NOT NULL default '0',
  `starred` char(1) NOT NULL default '0',
  `reported` char(1) NOT NULL default '0',
  `deleted` int(9) NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;



CREATE TABLE IF NOT EXISTS `blocklist` (
  `id` int(10) NOT NULL auto_increment,
  `name` int(99) NOT NULL default '0',
  `target` int(99) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;



CREATE TABLE IF NOT EXISTS `buddy` (
  `id` int(10) NOT NULL auto_increment,
  `name` int(99) NOT NULL default '0',
  `target` int(99) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;



-- Moder log
CREATE TABLE IF NOT EXISTS `mlog` (
  `id` int(9) NOT NULL auto_increment,
  `action` varchar(10) NULL,
  `details` TEXT NOT NULL,
  `actdt` int(9) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;



CREATE TABLE IF NOT EXISTS `subs` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `user_mail` varchar(80) DEFAULT NULL,
  `user_pass` varchar(80) DEFAULT NULL,
  `date_subscribed` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subscription_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- users online
CREATE TABLE IF NOT EXISTS `online` (
  `b_id` int unsigned primary key NOT NULL auto_increment,
  `date` int NOT NULL,
  `ip` varchar(40) NOT NULL,
  `page` varchar(200) NOT NULL,
  `user` int(20),
  `usr_chck` varchar(60),
  `bot` text COMMENT 'bot'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- site pages
CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `page_title` varchar(255) NULL,
  `slug` varchar(255) NOT NULL,
  `localization` varchar(120) NULL,
  `date_created` int(11) NULL,
  `date_updated` int(11) NULL,
  `updated_by` int(8) NULL,
  `file` varchar(120) NULL,
  `created_by` int(8) NULL,
  `head_tags` text,
  `published_status` int(1) NULL,
  `date_published` int(11) NULL,
  `content` longtext NULL,
  `type` varchar(20) DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `default_img` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

INSERT INTO `pages` (`id`, `tname`, `pname`, `lang`, `created`, `lastupd`, `lstupdby`, `file`, `crtdby`, `headt`, `published`, `pubdate`, `content`, `type`, `views`, `default_img`) VALUES
(1, 'Hello World', 'index', '', 0, 0, 2, 'index.php', 0, NULL, 2, 0, '<h1 style=\"text-align: center;\">Hello World!</h1>\r\n<h2 style=\"text-align: center;\">This is my first page</h2>\r\n<p>&nbsp;</p>\r\n<p><img style=\"display: block; margin-left: auto; margin-right: auto;\" src=\"/themes/images/img/hello-world.png\" alt=\"Hello World\" width=\"320\" height=\"320\" /></p>\r\n<p>&nbsp;</p>', NULL, 0, NULL);



-- notifications
CREATE TABLE IF NOT EXISTS `notif` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `uid` int(9) NOT NULL DEFAULT '0',
  `type` varchar(20) NOT NULL COMMENT 'notification department',
  `active` int(2) NULL,
  `lstinb` varchar(120) NOT NULL DEFAULT '' COMMENT 'last notification of received message',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;



-- special permissions list
CREATE TABLE IF NOT EXISTS `splist` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `permid` varchar(120) NOT NULL COMMENT 'permission id',
  `permacc` varchar(120) NOT NULL COMMENT 'defined permissions (view, edit, delete)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;



-- special permissions
CREATE TABLE IF NOT EXISTS `specperm` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `uid` int(9) NOT NULL default '0',
  `permname` varchar(120) NOT NULL COMMENT 'permission name',
  `permacc` varchar(120) NOT NULL COMMENT 'defined permissions (view, edit, delete)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;



-- uploaded files
CREATE TABLE IF NOT EXISTS `uplfiles` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(170) NOT NULL COMMENT 'file name',
  `date` int(30) NOT NULL COMMENT 'upload date',
  `ext` varchar(5) NOT NULL COMMENT 'extension',
  `fulldir` varchar(200) NOT NULL COMMENT 'full file address',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;



CREATE TABLE IF NOT EXISTS `languages` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `lngeng` varchar(30) NOT NULL,
  `iso-2` varchar(35) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;

INSERT INTO `languages` (`id`, `lngeng`, `iso-2`) VALUES
(1, 'Abkhazian', 'AB'),
(2, 'Afar', 'AA'),
(3, 'Afrikaans', 'AF'),
(4, 'Albanian', 'SQ'),
(5, 'Amharic', 'AM'),
(6, 'Arabic', 'AR'),
(7, 'Armenian', 'HY'),
(8, 'Assamese', 'AS'),
(9, 'Aymara', 'AY'),
(10, 'Azerbaijani', 'AZ'),
(11, 'Bashkir', 'BA'),
(12, 'Basque', 'EU'),
(13, 'Bengali, Bangla', 'BN'),
(14, 'Bhutani', 'DZ'),
(15, 'Bihari', 'BH'),
(16, 'Bislama', 'BI'),
(17, 'Breton', 'BR'),
(18, 'Bulgarian', 'BG'),
(19, 'Burmese', 'MY'),
(20, 'Byelorussian', 'BE'),
(21, 'Cambodian', 'KM'),
(22, 'Catalan', 'CA'),
(23, 'Chinese', 'ZH'),
(24, 'Corsican', 'CO'),
(25, 'Croatian', 'HR'),
(26, 'Czech', 'CS'),
(27, 'Danish', 'DA'),
(28, 'Dutch', 'NL'),
(29, 'English, American', 'EN'),
(30, 'Esperanto', 'EO'),
(31, 'Estonian', 'ET'),
(32, 'Faeroese', 'FO'),
(33, 'Fiji', 'FJ'),
(34, 'Finnish', 'FI'),
(35, 'French', 'FR'),
(36, 'Frisian', 'FY'),
(37, 'Gaelic (Scots Gaelic)', 'GD'),
(38, 'Galician', 'GL'),
(39, 'Georgian', 'KA'),
(40, 'German', 'DE'),
(41, 'Greek', 'EL'),
(42, 'Greenlandic', 'KL'),
(43, 'Guarani', 'GN'),
(44, 'Gujarati', 'GU'),
(45, 'Hausa', 'HA'),
(46, 'Hebrew', 'IW'),
(47, 'Hindi', 'HI'),
(48, 'Hungarian', 'HU'),
(49, 'Icelandic', 'IS'),
(50, 'Indonesian', 'IN'),
(51, 'Interlingua', 'IA'),
(52, 'Interlingue', 'IE'),
(53, 'Inupiak', 'IK'),
(54, 'Irish', 'GA'),
(55, 'Italian', 'IT'),
(56, 'Japanese', 'JA'),
(57, 'Javanese', 'JW'),
(58, 'Kannada', 'KN'),
(59, 'Kashmiri', 'KS'),
(60, 'Kazakh', 'KK'),
(61, 'Kinyarwanda', 'RW'),
(62, 'Kirghiz', 'KY'),
(63, 'Kirundi', 'RN'),
(64, 'Korean', 'KO'),
(65, 'Kurdish', 'KU'),
(66, 'Laothian', 'LO'),
(67, 'Latin', 'LA'),
(68, 'Latvian, Lettish', 'LV'),
(69, 'Lingala', 'LN'),
(70, 'Lithuanian', 'LT'),
(71, 'Macedonian', 'MK'),
(72, 'Malagasy', 'MG'),
(73, 'Malay', 'MS'),
(74, 'Malayalam', 'ML'),
(75, 'Maltese', 'MT'),
(76, 'Maori', 'MI'),
(77, 'Marathi', 'MR'),
(78, 'Moldavian', 'MO'),
(79, 'Mongolian', 'MN'),
(80, 'Nauru', 'NA'),
(81, 'Nepali', 'NE'),
(82, 'Norwegian', 'NO'),
(83, 'Occitan', 'OC'),
(84, 'Oriya', 'OR'),
(85, 'Oromo, Afan', 'OM'),
(86, 'Pashto, Pushto', 'PS'),
(87, 'Persian', 'FA'),
(88, 'Polish', 'PL'),
(89, 'Portuguese', 'PT'),
(90, 'Punjabi', 'PA'),
(91, 'Quechua', 'QU'),
(92, 'Rhaeto-Romance', 'RM'),
(93, 'Romanian', 'RO'),
(94, 'Russian', 'RU'),
(95, 'Samoan', 'SM'),
(96, 'Sangro', 'SG'),
(97, 'Sanskrit', 'SA'),
(98, 'Serbian', 'SR'),
(100, 'Sesotho', 'ST'),
(101, 'Setswana', 'TN'),
(102, 'Shona', 'SN'),
(103, 'Sindhi', 'SD'),
(104, 'Singhalese', 'SI'),
(105, 'Siswati', 'SS'),
(106, 'Slovak', 'SK'),
(107, 'Slovenian', 'SL'),
(108, 'Somali', 'SO'),
(109, 'Spanish', 'ES'),
(110, 'Sudanese', 'SU'),
(111, 'Swahili', 'SW'),
(112, 'Swedish', 'SV'),
(113, 'Tagalog', 'TL'),
(114, 'Tajik', 'TG'),
(115, 'Tamil', 'TA'),
(116, 'Tatar', 'TT'),
(117, 'Tegulu', 'TE'),
(118, 'Thai', 'TH'),
(119, 'Tibetan', 'BO'),
(120, 'Tigrinya', 'TI'),
(121, 'Tonga', 'TO'),
(122, 'Tsonga', 'TS'),
(123, 'Turkish', 'TR'),
(124, 'Turkmen', 'TK'),
(125, 'Twi', 'TW'),
(126, 'Ukrainian', 'UK'),
(127, 'Urdu', 'UR'),
(128, 'Uzbek', 'UZ'),
(129, 'Vietnamese', 'VI'),
(130, 'Volapuk', 'VO'),
(131, 'Welsh', 'CY'),
(132, 'Wolof', 'WO'),
(133, 'Xhosa', 'XH'),
(134, 'Yiddish', 'JI'),
(135, 'Yoruba', 'YO'),
(136, 'Zulu', 'ZU');



CREATE TABLE IF NOT EXISTS `counter` (
  `day` int(9) NOT NULL,
  `month` int(9) NOT NULL,
  `visits_today` int(9) NOT NULL,
  `visits_total` int(11) NOT NULL,
  `clicks_today` int(9) NOT NULL,
  `clicks_total` int(9) NOT NULL,
  UNIQUE KEY `day` (`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `counter` (`day`, `month`, `visits_today`, `visits_total`, `clicks_today`, `clicks_total`) VALUES
(0, 0, 0, 0, 0, 0);



CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `address` varchar(45) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `username` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE IF NOT EXISTS `email_queue` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uad` mediumint(9) NOT NULL COMMENT 'user added email to queue',
  `sender` varchar(255) NOT NULL,
  `sender_mail` varchar(255) DEFAULT NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `sent` tinyint(1) NOT NULL,
  `timesent` datetime DEFAULT NULL,
  `timeadded` datetime NOT NULL,
  `priority` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `uid` int(9) NOT NULL,
  `comment` text NOT NULL,
  `date` datetime NOT NULL,
  `pid` int(9) NOT NULL COMMENT 'page id where comment will be shown',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- Site settings
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `options` varchar(255) DEFAULT NULL,
  `setting_group` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `setting_name` (`setting_name`(250)),
  KEY `setting_group` (`setting_group`(250))
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`id`, `setting_name`, `value`, `options`, `setting_group`) VALUES
(1, 'keypass', NULL, NULL, 'system'),
(2, 'webtheme', NULL, NULL, 'system'),
(3, 'quarantine', '0', NULL, 'system'),
(4, 'showtime', '0', NULL, 'system'),
(5, 'pageGenTime', '0', NULL, 'system'),
(6, 'pgFbComm', '0', NULL, 'system'),
(7, 'showOnline', '0', NULL, 'system'),
(8, 'adminNick', NULL, NULL, 'system'),
(9, 'adminEmail', NULL, NULL, 'system'),
(10, 'timeZone', '0', NULL, 'system'),
(11, 'title', NULL, NULL, 'system'),
(12, 'homeUrl', NULL, NULL, 'system'),
(13, 'bookGuestAdd', '0', NULL, 'system'),
(14, 'transferProtocol', NULL, NULL, 'system'),
(15, 'maxPostChat', NULL, NULL, 'system'),
(16, 'maxPostNews', NULL, NULL, 'system'),
(18, 'floodTime', '0', NULL, 'system'),
(19, 'pvtLimit', '0', NULL, 'system'),
(20, 'cookieConsent', '0', NULL, 'system'),
(21, 'photoList', NULL, NULL, 'system'),
(22, 'photoFileSize', NULL, NULL, 'system'),
(23, 'maxPhotoPixels', NULL, NULL, 'system'),
(24, 'siteDefaultLang', 'english', NULL, 'system'),
(25, 'mPanel', NULL, NULL, 'system'),
(26, 'forumAccess', '0', NULL, 'system'),
(27, 'refererLog', NULL, NULL, 'system'),
(28, 'subMailPacket', '50', NULL, 'system'),
(29, 'dosLimit', '120', NULL, 'system'),
(30, 'maxLogData', '200', NULL, 'system'),
(31, 'openReg', '0', NULL, 'system'),
(32, 'regConfirm', '0', NULL, 'system'),
(33, 'siteOff', '0', NULL, 'system'),
(34, 'forumChLang', '0', NULL, 'system'),
(35, 'showRefPage', '0', NULL, 'system'),
(36, 'showCounter', '0', NULL, 'system'),
(37, 'maxBanTime', '43200', NULL, 'system'),
(38, 'recaptcha_sitekey', NULL, NULL, 'system'),
(39, 'recaptcha_secretkey', NULL, NULL, 'system');
COMMIT;



CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `page_id` int(9) NOT NULL,
  `tag_name` varchar(2255) NOT NULL,
  UNIQUE KEY `UNIQUE` (`id`),
  KEY `page_id` (`page_id`),
  KEY `tag_name` (`tag_name`(250))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE IF NOT EXISTS `tokens` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `uid` int(9) NOT NULL COMMENT 'user id',
  `type` varchar(255) NOT NULL COMMENT 'token type',
  `content` text DEFAULT NULL,
  `token` varchar(255) NOT NULL,
  `expiration_time` datetime NOT NULL COMMENT 'time until token is valid',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Reserved tokens: login, email



CREATE TABLE IF NOT EXISTS `group_members` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `uid` int(9) NOT NULL COMMENT 'user id',
  `group_name` varchar(500) NOT NULL,
  `date_joined` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;