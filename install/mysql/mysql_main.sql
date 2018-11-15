CREATE TABLE IF NOT EXISTS `vavok_users` (
  `id` int(100) NOT NULL auto_increment,             -- member id
  `name` varchar(40) NOT NULL,                       -- nick
  `pass` varchar(120) NOT NULL,						           -- pass
  `perm` int(4) NOT NULL default '0',                -- permissions (accessr)
  `skin` varchar(30) NOT NULL default 'default',     -- skin
  `browsers` varchar(50) NOT NULL default '',        -- browser
  `ipadd` varchar(30) NOT NULL default '',           -- ip address
  `timezone` varchar(10) NOT NULL default '0',       -- time zone
  `banned` char(1) NOT NULL default '0',             -- is banned?
  `newmsg` int(30) NOT NULL default '0',             -- new messages in inbox
  `lang` varchar(30) NOT NULL default '',            -- language
  `mskin` varchar(30) NOT NULL default '',           -- mobile skin
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `vavok_users` (`id`, `name`, `pass`, `perm`, `skin`, `browsers`, `ipadd`, `timezone`, `banned`, `newmsg`, `lang`) VALUES
(0, 'System', '0', 999, 'default', 'Mozilla/5.0', '127.0.0.1', '0', '0', 0, 'english');

CREATE TABLE IF NOT EXISTS `vavok_profil` (
  `id` int(100) NOT NULL auto_increment,
  `uid` int(100) NOT NULL default '0',               -- user unique id from vavok_users table
  `opentem` int(100) NOT NULL default '0',           -- forum topics
  `forummes` int(100) NOT NULL default '0',          -- forum posts
  `chat` int(100) NOT NULL default '0',              -- chat posts
  `commadd` int(100) NOT NULL default '0',           -- comments
  `subscri` char(1) NOT NULL default '0',            -- subscribed to site news status
  `newscod` varchar(100) NOT NULL default '',        -- unsubscription code
  `perstat` varchar(50) NOT NULL default '',         -- personal status
  `regdate` varchar(30) NOT NULL default '',         -- reg. date
  `regche` varchar(2) NOT NULL default '0',          -- reg. activated?
  `regkey` varchar(100) NOT NULL default '',         -- reg. key
  `bantime` varchar(20) NOT NULL default '',         -- ban time
  `bandesc` varchar(120) NOT NULL default '',        -- ban reason
  `lastban` varchar(20) NOT NULL default '',         -- last ban time
  `allban` varchar(20) DEFAULT NULL,                 -- no. of bans
  `lastvst` varchar(30) NOT NULL default '',         -- last visit
 PRIMARY KEY  (`id`),
 UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



CREATE TABLE IF NOT EXISTS `vavok_about` (
  `id` int(100) NOT NULL auto_increment,
  `uid` int(100) NOT NULL default '0',
  `birthday` varchar(40) NOT NULL default '',		-- birthday
  `sex` char(1) NOT NULL default 'n',				    -- sex
  `email` varchar(80) NOT NULL default '',			-- email
  `site` varchar(50) NOT NULL default '',			  -- site
  `city` varchar(100) NOT NULL default '',			-- location
  `about` tinytext NOT NULL default '',					-- about yourself
  `rname` varchar(150) NOT NULL default '',			-- real name
  `surname` varchar(150) NOT NULL default '',		-- surname
  `photo` varchar(30) NOT NULL default '',			-- photo
  `address` varchar(100) NOT NULL default '',		-- street address
  `zip` varchar(20) NOT NULL default '',			  -- postal address
  `country` varchar(75) NOT NULL default '',
  `phone` varchar(30) NOT NULL default '',
 PRIMARY KEY  (`id`),
 UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



CREATE TABLE IF NOT EXISTS `page_setting` (
  `id` int(100) NOT NULL auto_increment,
  `uid` int(100) NOT NULL default '0',
  `newsmes` int(3) NOT NULL default '5',             -- posts on page in site news
  `forummes` int(3) NOT NULL default '5',            -- posts on page in forum
  `forumtem` int(3) NOT NULL default '10',           -- topics on page in forum
  `privmes` int(3) NOT NULL default '5',             -- messages in inbox per page
 PRIMARY KEY  (`id`),
 UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- inbox
CREATE TABLE IF NOT EXISTS `inbox` (
  `id` int(100) NOT NULL auto_increment,
  `text` MEDIUMTEXT NOT NULL,
  `byuid` int(100) NOT NULL default '0',
  `touid` int(100) NOT NULL default '0',
  `unread` char(1) NOT NULL default '1',
  `timesent` int(100) NOT NULL default '0',
  `starred` char(1) NOT NULL default '0',
  `reported` char(1) NOT NULL default '0',
  `deleted` int(11) NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



CREATE TABLE IF NOT EXISTS `ignore` (
  `id` int(10) NOT NULL auto_increment,
  `name` int(99) NOT NULL default '0',
  `target` int(99) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `buddy` (
  `id` int(10) NOT NULL auto_increment,
  `name` int(99) NOT NULL default '0',
  `target` int(99) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- Moder log
CREATE TABLE IF NOT EXISTS `mlog` (
  `id` int(100) NOT NULL auto_increment,
  `action` varchar(10) NOT NULL default '',
  `details` TEXT NOT NULL,
  `actdt` int(100) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `subs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `user_mail` varchar(80) DEFAULT NULL,
  `user_pass` varchar(80) DEFAULT NULL,
  `date_subscribed` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subscription_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


-- users online
CREATE TABLE IF NOT EXISTS `online` (
  `b_id` int unsigned primary key NOT NULL auto_increment,
  `date` int NOT NULL,
  `ip` varchar(40) NOT NULL,
  `page` varchar(200) NOT NULL,
  `user` int(20),
  `usr_chck` varchar(60),
  `bot` text COMMENT 'bot'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- site pages
CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tname` varchar(120) NULL COMMENT 'page title',
  `pname` varchar(120) NOT NULL COMMENT 'page name',
  `lang` varchar(120) NULL COMMENT 'language',
  `created` int(50) NULL COMMENT 'date created',
  `lastupd` int(50) NULL COMMENT 'last update',
  `lstupdby` int(50) NULL COMMENT 'last update by',
  `file` varchar(120) NULL COMMENT 'file name',
  `crtdby` int(50) NULL COMMENT 'created by',
  `headt` text,
  `published` int(11) NULL,
  `pubdate` int(11) NULL,
  `content` longtext NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `pages` (`id`, `tname`, `pname`, `lang`, `created`, `lastupd`, `lstupdby`, `file`, `crtdby`, `headt`, `published`, `pubdate`, `content`) VALUES
(1, 'My new website', 'index', '', 0, 0, 0, 'index.php', 0, NULL, 2, 0, '<div style="text-align:center;">Welcome to my Web site!</div>'),
(2, 'BB Codes', 'bb-codes', '', 1452540347, 1452540352, 0, 'bb-codes.php', 0, NULL, 2, 0, '<p><img src="/images/img/partners.gif" alt="" /> <b>BB codes</b></p><br />\r<br />\r<span style="font-weight:bold;">Text</span> - &#91;b&#93;Text&#91;/b&#93;<br />\r<span style="font-style:italic;">Text</span> - &#91;i&#93;Text&#91;/i&#93;<br />\r<span style="text-decoration:underline;">Text</span> - &#91;u&#93;Text&#91;/u&#93;<br />\r<span style="font-size:0.8em;">Text</span> - &#91;small&#93;Text&#91;/small&#93;<br />\r<br />\r<span style="color:red">Text</span> - &#91;red&#93;Text&#91;/red&#93;<br />\r<span style="color:green">Text</span> - &#91;green&#93;Text&#91;/green&#93;<br />\r<span style="color:blue">Text</span> - &#91;blue&#93;Text&#91;/blue&#93;<br />\r<br />\r<a href="http://vavok.net/">link</a> - &#91;url=http://vavok.net&#93;link&#91;/url&#93;');


-- notifications
CREATE TABLE IF NOT EXISTS `notif` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(100) NOT NULL DEFAULT '0',
  `type` varchar(20) NOT NULL COMMENT 'notification department',
  `active` int(2) NULL,
  `lstinb` varchar(120) NOT NULL DEFAULT '' COMMENT 'last notification of received message',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- special permitions list
CREATE TABLE IF NOT EXISTS `splist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permid` varchar(120) NOT NULL COMMENT 'permission id',
  `permacc` varchar(120) NOT NULL COMMENT 'defined permissions (view, edit, delete)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- special permitions
CREATE TABLE IF NOT EXISTS `specperm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(100) NOT NULL default '0',
  `permname` varchar(120) NOT NULL COMMENT 'permission name',
  `permacc` varchar(120) NOT NULL COMMENT 'defined permissions (view, edit, delete)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- uploaded files
CREATE TABLE IF NOT EXISTS `uplfiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(170) NOT NULL COMMENT 'file name',
  `date` int(30) NOT NULL COMMENT 'upload date',
  `ext` varchar(5) NOT NULL COMMENT 'extension',
  `fulldir` varchar(200) NOT NULL COMMENT 'full file address',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `languages` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `lngeng` varchar(30) NOT NULL,
  `iso-2` varchar(35) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=137 ;


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
  `day` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `visits_today` int(11) NOT NULL,
  `visits_total` int(11) NOT NULL,
  `clicks_today` int(11) NOT NULL,
  `clicks_total` int(11) NOT NULL,
  UNIQUE KEY `day` (`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `counter` (`day`, `month`, `visits_today`, `visits_total`, `clicks_today`, `clicks_total`) VALUES
(0, 0, 0, 0, 0, 0);


CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address` varchar(45) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `username` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
