<?php
// (c) vavok.net

$language = 'english';

if (!file_exists(BASEDIR . "lang/english/index.php")) {
	header ("Location: /?error=no_lang");
	exit;
}

if (!empty($language)) {
	unset($_SESSION['lang']);
	$_SESSION['lang'] = $language;
}

$ln_loc = 'en';
$config["language"] = $language;
?>
