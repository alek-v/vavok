<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   03.08.2020. 8:07:52
*/

define('START_TIME', microtime(true));
define('VERSION', '1.5.5.beta4');

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

/**
 * Main class
 */
$vavok = new Vavok();

/**
 * Connect to database
 */
$db = new Db();

/**
 * Users
 */
$users = new Users();

/**
 * We don't need this data if this is system request
 */
if (!strstr($_SERVER['PHP_SELF'], '/cronjob/')) {
	$current_page = new Page();
    new Manageip();
    new Counter();
    new Referer();

    require_once BASEDIR . "include/pages.php";
}


?>