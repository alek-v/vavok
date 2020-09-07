<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

define('START_TIME', microtime(true));
define('VERSION', '1.5.6.beta7');

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
    $counter = new Counter();
    new Manageip();
    new Referer();
}


?>