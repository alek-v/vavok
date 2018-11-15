<?php
// (c) vavok.net
$con_text = file(BASEDIR . "used/config.dat");
$con_data = explode("|", $con_text[0]);

$config = array(
"redbrow" => $con_data[0], // redirection to www. or m.
"keypass" => $con_data[1], // key for decrypting passwords
"webtheme" => $con_data[2], // default web skin
"quarantine" => $con_data[3], // quarantine for new users
"showtime" => $con_data[4], // show clock and date on home page
"pageGenTime" => $con_data[5], // show page generation time. 0 = off
"pgFbComm" => $con_data[6], // facebook comments on pages
"showOnline" => $con_data[7], // show online users. 0 = off
"adminNick" => $con_data[8], // your nick on site
"adminEmail" => $con_data[9], // your email
"timeZone" => $con_data[10], // time zone
"title" => $con_data[11], // page title
"mTheme" => $con_data[12], // default mobile theme
"siteCopy" => $con_data[13], // copyright - deprecated! 12.5.2017. 22:29:38
"homeUrl" => $con_data[14], // home page. Try to not use www. or m.
"postNews" => $con_data[17], // no. of news on page - deprecated! 09.04.2018. 1:04:28
"lastNews" => $con_data[18], // no. of news on home page - deprecated! 09.04.2018. 1:04:28
"bookPost" => $con_data[19], // no. of messages per page in guestbook - deprecated! 09.04.2018. 1:04:28
"bookGuestAdd" => $con_data[20], // allow guests to write in gb
"transferProtocol" => $con_data[21], // force HTTPS, HTTP or auto
"maxPostChat" => $con_data[22], // how much comments save in log
"chatPost" => $con_data[23], // no. of msgs per page in chat - deprecated! 09.04.2018. 1:04:28
"maxPostNews" => $con_data[24], // how much news to save
"maxPostBook" => $con_data[25], // no. of comments in gb to save
"forumPost" => $con_data[26], // posts per page in forum - deprecated! 09.04.2018. 1:04:28
"forumTopics" => $con_data[27], // threads per page in forum - deprecated! 09.04.2018. 1:04:28
"floodTime" => $con_data[29], // antiflood time in seconds
"pvtLimit" => $con_data[30], // max no of msgs in inbox, 0 => unlimited
"dataOnPage" => $con_data[31], // data on page (no. of users per page in online list etc)
"chatRew" => 30, // chat refresh time - deprecated! 12.5.2017. 22:31:21
"photoList" => $con_data[37], // no. of photos per page in gallery
"photoFileSize" => $con_data[38], // maximum photo filesize in bytes
"maxPhotoPixels" => $con_data[39], // maximum photo size in pixels
"language" => $con_data[47], // default site language - this value is overwritten with user settings
"siteDefaultLang" => $con_data[47], // website default language
"mPanel" => $con_data[48], // folder with admin panel
"forumAccess" => $con_data[49], // is forum on? 1" => online, 0" => offline
"refererLog" => $con_data[51], // no. of loged referers
"noCache" => $con_data[52], // allow caching
"subMailPacket" => $con_data[56], // emails in one package to subscribed users
"dosLimit" => $con_data[57], // max. no. of requests per IP in 1 min.
"maxLogData" => $con_data[58], // max log data
"openReg" => $con_data[61], // open or closed registration
"regConfirm" => $con_data[62], // turn on registration confirmation
"siteOff" => $con_data[63], // maintenance mode
"forumChLang" => $con_data[68], // choose language in forum
"gzip" => $con_data[69], // turn on gzip
"showRefPage" => $con_data[70], // show referer page
"showCounter" => $con_data[74], // show counter on page in which mode (visits, clicks, total or graphic)
"sessionLife" => $con_data[75], // session lifetime
"maxBanTime" => $con_data[76], // maximum ban time

// database settings
"dbhost" => $con_data[77], // database host
"dbuser" => $con_data[78], // database username
"dbpass" => $con_data[79], // database password
"dbname" => $con_data[80] // database name
);

// advanced manual settings
$config["rssIcon"] = 0; // RSS icon
$config["siteTime"] = time() + ($config["timeZone"] * 3600); 
$config["homeBase"] = str_replace("http://", "", $config["homeUrl"]);
$config["homeBase"] = str_replace("https://", "", $config["homeBase"]);
$config["tablePrefix"] = ''; // database prefix

// no. of fields in config file
$config["configKeys"] = "100";

?>