<?php
// (c) vavok.net - Aleksandar Vranešević
// modified: 01.05.2020. 23:14:09

// get browser preferred language
$locale = isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2) : '';

$language = 'serbian_latin';

// if browser user serbian it is probabably cyrillic
if ($locale == 'sr') {
	$language = 'serbian_cyrillic';
}

// check is language available
if ($language == 'serbian_latin' && file_exists(BASEDIR . "lang/serbian_latin/index.php")) {
	$language = 'serbian_latin';
} elseif (file_exists(BASEDIR . "lang/serbian_cyrillic/index.php")) { // check if cyrillic scrypt is installed
	$language = 'serbian_cyrillic';
} else {
	$language = 'serbian_latin'; // cyrillic script not installed, use latin
}

if (!empty($language) && isset($_SESSION['lang'])) {
	unset($_SESSION['lang']);
	$_SESSION['lang'] = $language;
}

$ln_loc = 'sr';
$config["language"] = $language;
?>
