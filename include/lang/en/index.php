<?php
// (c) vavok.net - Aleksandar Vranešević
// updated: 28.07.2020. 11:26:48

$language = 'english';

// check is language available
if (!file_exists(BASEDIR . "include/lang/english/index.php")) {
	redirect_to('../?error=lang');
}

$users->change_language($language);

$ln_loc = 'en';
?>
