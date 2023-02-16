<?php

self::$connection->query("ALTER TABLE vavok_users CHANGE COLUMN `perm` `access_permission` int(4) NOT NULL default '0'");

self::$connection->query("ALTER TABLE vavok_users CHANGE COLUMN `ipadd` `ip_address`  varchar(30) NULL");

self::$connection->query("ALTER TABLE vavok_users CHANGE COLUMN `lang` `localization` varchar(30) NULL");

self::$connection->query("ALTER TABLE vavok_users CHANGE COLUMN `pass` `password` varchar(255) NOT NULL");

self::$connection->query("ALTER TABLE `vavok_profil` DROP `chat`");

self::$connection->query("ALTER TABLE `vavok_profil` DROP `commadd`");

self::$connection->query("ALTER TABLE `vavok_profil` CHANGE COLUMN `subscri` `subscribed` int(1) NOT NULL default '0'");

self::$connection->query("ALTER TABLE `vavok_profil` CHANGE COLUMN `newscod` `subscription_code` varchar(100) NULL");

self::$connection->query("ALTER TABLE `vavok_profil` CHANGE COLUMN `perstat` `personal_status` varchar(50) NULL");

self::$connection->query("ALTER TABLE vavok_profil CHANGE COLUMN `regdate` `registration_date` varchar(30) NULL");

self::$connection->query("ALTER TABLE `vavok_profil` CHANGE COLUMN `regche` `registration_activated` varchar(2) NOT NULL default '0'");

self::$connection->query("ALTER TABLE `vavok_profil` CHANGE COLUMN `regkey` `registration_key` varchar(100) NULL");

self::$connection->query("ALTER TABLE `vavok_profil` CHANGE COLUMN `bantime` `ban_time` varchar(20) NULL");

self::$connection->query("ALTER TABLE `vavok_profil` CHANGE COLUMN `bandesc` `ban_description` varchar(120) NULL");

self::$connection->query("ALTER TABLE `vavok_profil` CHANGE COLUMN `lastban` `last_ban` varchar(20) NULL");

self::$connection->query("ALTER TABLE `vavok_profil` CHANGE COLUMN `allban` `all_bans` varchar(20) NULL");

self::$connection->query("ALTER TABLE `vavok_profil` CHANGE COLUMN `lastvst` `last_visit` varchar(30) NULL");

self::$connection->query("ALTER TABLE vavok_about CHANGE COLUMN `rname` `first_name` varchar(150) NULL");

self::$connection->query("ALTER TABLE vavok_about CHANGE COLUMN `surname` `last_name` varchar(150) NULL");
