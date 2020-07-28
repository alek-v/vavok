<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   28.07.2020. 11:43:11
*/

require_once"../include/startup.php";

$language = isset($_GET['lang']) ? check($_GET['lang']) : ''; // get language

if (empty($language) && !empty($_POST['lang'])) { $language = check($_POST['lang']); }

// page to load after changing language
$ptl = isset($_GET['ptl']) && !empty($_GET['ptl']) ? urldecode($_GET['ptl']) : ''; 

if (!file_exists(BASEDIR . "include/lang/" . $users->get_prefered_language($language) . "/index.php")) {
	redirect_to(HOMEDIR . "index.php?error=no_lang");
} else {
	include_once BASEDIR . 'include/lang/' . $users->get_prefered_language($language) . '/index.php';
}

if (!empty($language)) {
	// Set new language
	$users->change_language($language);

}

// ignore language url's, /index.php will do the work
if ($ptl == '/en/' || $ptl == '/sr/' || $ptl == '/fr/' || $ptl == '/de/' || $ptl == '/ru/' || $ptl == '/js/') {
	$ptl = '';
}

if (!empty($ptl)) {
	redirect_to($ptl);
} else {
	redirect_to("../");
}
?>
