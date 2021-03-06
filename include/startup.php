<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   10.03.2021. 23:01:53
 */

define('START_TIME', microtime(true));
define('VERSION', '1.5.7');

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
new Db();
new Users();

/**
 * We don't need this data if this is system request
 */
if (!strstr($_SERVER['PHP_SELF'], '/cronjob/')) {
	new Page();
	new Localization();
    new Counter();
    new Manageip();
    new Referer();
}


?>