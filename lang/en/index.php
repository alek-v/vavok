<?php
// (c) vavok.net - Aleksandar Vranešević
// updated: 05.04.2020. 17:56:29

$language = 'english';

// check is language available
if (!file_exists(BASEDIR . "lang/english/index.php")) {
	redirect_to('../?error=lang');
}


unset($_SESSION['lang']);
$_SESSION['lang'] = $language;


$ln_loc = 'en';
$config["language"] = $language;
?>
