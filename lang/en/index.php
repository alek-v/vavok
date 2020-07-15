<?php
// (c) vavok.net - Aleksandar Vranešević
// updated: 15.07.2020. 1:59:04

$language = 'english';

// check is language available
if (!file_exists(BASEDIR . "lang/english/index.php")) {
	redirect_to('../?error=lang');
}


$users->change_language($language);


$ln_loc = 'en';
$config["language"] = $language;
?>
