<?php
// (c) vavok.net - Aleksandar Vranešević
// modified: 05.04.2020. 17:52:07

require_once"../include/strtup.php";

$lang = $_GET['lang'];
if (empty($lang)) {
	$lang = $_POST['lang'];
}
if (isset($_GET['ptl']) && !empty($_GET['ptl'])) {
	$ptl = urldecode($_GET['ptl']);
}

$language = check($lang);

if (!file_exists("../lang/" . $language . "/index.php")) {
	redirect_to("../index.php?error=no_lang");
} else {
	include '../lang/' . $language . '/index.php';
}

if (!empty($language)) {
	$_SESSION['lang'] = "";
	unset($_SESSION['lang']);
	$_SESSION['lang'] = $language;
	}

// ignore language url's, /index.php will do the work
if ($ptl == '/en/' || $ptl == '/sr/' || $ptl == '/fr/' || $ptl == '/de/' || $ptl == '/ru/' || $ptl == '/js/' || $ptl == '/cms/') {
$ptl = '';
}

if (!empty($ptl)) {
header ("Location: " . $ptl);
exit;
} else {
header ("Location: ../");
exit;
}
?>
