<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
*            Website configuration
* Updated:   29.07.2020. 17:53:20
*/

$config = $vavok->get_configuration('', true);

/**
 * Deprecated in Vavok v1.5.4
 * Insert data in database if doesnt exist
 */
if (empty($config) || $config == false) {
	$con_text = file_get_contents(BASEDIR . "used/config.dat");
	$con_data = explode("|", $con_text);

	$config = array(
	"name" => 'system',
	"keypass" => $con_data[1], // key for decrypting passwords
	"webtheme" => $con_data[2], // default web skin
	"quarantine" => $con_data[3], // quarantine for new users
	"showtime" => (int)$con_data[4], // show clock and date on home page
	"pageGenTime" => (int)$con_data[5], // show page generation time. 0 = off
	"pgFbComm" => (int)$con_data[6], // facebook comments on pages
	"showOnline" => (int)$con_data[7], // show online users. 0 = off
	"adminNick" => $con_data[8], // your nick on site
	"adminEmail" => $con_data[9], // your email
	"timeZone" => $con_data[10], // time zone
	"title" => $con_data[11], // page title
	"homeUrl" => $con_data[14], // home page
	"bookGuestAdd" => (int)$con_data[20], // allow guests to write in gb
	"transferProtocol" => $con_data[21], // force HTTPS, HTTP or auto
	"maxPostChat" => (int)$con_data[22], // how much comments save in log
	"maxPostNews" => (int)$con_data[24], // how much news to save
	"customPages" => $con_data[28], // url of custom pages
	"floodTime" => (int)$con_data[29], // antiflood time in seconds
	"pvtLimit" => (int)$con_data[30], // max no of msgs in inbox, 0 => unlimited
	"cookieConsent" => (int)$con_data[32], // cookie consent
	"photoList" => (int)$con_data[37], // no. of photos per page in gallery
	"photoFileSize" => (int)$con_data[38], // maximum photo filesize in bytes
	"maxPhotoPixels" => (int)$con_data[39], // maximum photo size in pixels
	"siteDefaultLang" => $con_data[47], // website default language
	"mPanel" => $con_data[48], // folder with admin panel
	"forumAccess" => (int)$con_data[49], // is forum on? 1" => online, 0" => offline
	"refererLog" => (int)$con_data[51], // no. of loged referers
	"subMailPacket" => (int)$con_data[56], // emails in one package to subscribed users
	"dosLimit" => (int)$con_data[57], // max. no. of requests per IP in 1 min.
	"maxLogData" => (int)$con_data[58], // max log data
	"openReg" => (int)$con_data[61], // open or closed registration
	"regConfirm" => (int)$con_data[62], // turn on registration confirmation
	"siteOff" => (int)$con_data[63], // maintenance mode
	"forumChLang" => (int)$con_data[68], // choose language in forum
	"showRefPage" => (int)$con_data[70], // show referer page
	"showCounter" => (int)$con_data[74], // show counter on page in which mode (visits, clicks, total or graphic)
	"maxBanTime" => (int)$con_data[76] // maximum ban time
	);

	// Insert data to database
	$config_count = count($config);

	$fields = array(); $values = array();
	foreach ($config as $key => $value) {
		$fields[] = $key;
		$values[] = $value;
	}

	$db->insert_data(DB_PREFIX . 'settings', $config);

}

// advanced manual settings
$config["rssIcon"] = 0; // RSS icon
$config["timeZone"] = empty($config["timeZone"]) ? $config["timeZone"] = 0 : $config["timeZone"]; // check is there timezone number set
$config["siteTime"] = time() + ($config["timeZone"] * 3600); 
$config["homeBase"] = str_replace("http://", "", $config["homeUrl"]);
$config["homeBase"] = str_replace("https://", "", $config["homeBase"]);

// no. of fields in config file
$config["configKeys"] = "100";

?>