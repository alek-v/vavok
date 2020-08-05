<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   05.08.2020. 21:50:17
*/

define('START_TIME', microtime(true));
define('VERSION', '1.5.5.beta6');

/**
 * Root dir for including system files
 */
if (!defined('BASEDIR')) {
    $folder_level = "";
    while (!file_exists($folder_level . "robots.txt")) {
        $folder_level .= "../";
    } 
    define("BASEDIR", $folder_level);
}

/**
 * Autoload classes
 */
spl_autoload_register(function ($class) {
    include BASEDIR . "include/classes/{$class}.class.php";
});

$vavok = new Vavok();
$db = new Db();
$users = new Users();

/**
 * We don't need this data if this is system request
 */
if (!strstr($_SERVER['PHP_SELF'], '/cronjob/')) {
	$current_page = new Page();
	$localization = new Localization();
    new Manageip();
    new Counter();
    new Referer();
}


?>