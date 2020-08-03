<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   03.08.2020. 8:07:45
*/

/**
 * Load localization files
 */
if (!file_exists(BASEDIR . "include/lang/" . $users->get_user_language() . "/index.php")) { $_SESSION['lang'] = 'english'; }

include_once BASEDIR . "include/lang/" . $users->get_user_language() . "/index.php";

?>