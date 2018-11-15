<?php
// modified: 21.11.2014 3:02:13
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
header ("Location: ../index.php?error=no_lang");
exit;
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
