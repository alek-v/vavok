<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   01.08.2020. 19:21:21
*/

require_once"../include/startup.php";

$language = isset($_GET['lang']) ? $vavok->check($_GET['lang']) : ''; // get language

if (empty($language) && !empty($_POST['lang'])) { $language = $vavok->check($_POST['lang']); }

// page to load after changing language
$ptl = isset($_GET['ptl']) && !empty($_GET['ptl']) ? urldecode($_GET['ptl']) : ''; 

if (!file_exists(BASEDIR . "include/lang/" . $users->get_prefered_language($language) . "/index.php")) {
	$vavok->redirect_to(HOMEDIR . "index.php?error=no_lang");
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
	$vavok->redirect_to($ptl);
} else {
	$vavok->redirect_to("../");
}
?>
