<?php
// (c) vavok.net - Aleksandar Vranešević
// modified: 16.04.2020. 20:46:02

require_once"../include/strtup.php";

$language = isset($_GET['lang']) ? check($_GET['lang']) : ''; // get language

if (empty($language)) { $language = $_POST['lang']; }


// page to load after changing language
$ptl = isset($_GET['ptl']) && !empty($_GET['ptl']) ? urldecode($_GET['ptl']) : ''; 


if (!file_exists("../lang/" . $language . "/index.php")) {
	redirect_to("../index.php?error=no_lang");
} else {
	include '../lang/' . $language . '/index.php';
}

if (!empty($language)) {

	// unset current language
	$_SESSION['lang'] = "";
	unset($_SESSION['lang']);

	// set new language
	$_SESSION['lang'] = $language;

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
