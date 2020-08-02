<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   02.08.2020. 18:00:00
*/

define('START_TIME', microtime(true));
define('VERSION', '1.5.5.beta2');

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
$db = new Db(DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD);

/**
 * Users
 */
$users = new Users();

/**
 * We don't need this data if this is system request
 * Pages manipulation
 * IP management - IP ban
 * Statistics
 * Referer informations
 */
if (!strstr($_SERVER['PHP_SELF'], '/cronjob/')) {
    require_once BASEDIR . "include/pages.php";
    require_once BASEDIR . "include/antidos.php";
    require_once BASEDIR . "include/counters.php";
    require_once BASEDIR . "include/referer.php";

    /**
     * Show website maintenance page
     */
    if ($vavok->get_configuration('siteOff') == 1 && !strstr($_SERVER['PHP_SELF'], 'pages/maintenance.php') && !strstr($_SERVER['PHP_SELF'], 'pages/input.php') && !$users->is_administrator() && !strstr($_SERVER['PHP_SELF'], 'pages/login.php')) {
        $vavok->redirect_to(website_home_address() . "/pages/maintenance.php");
    }
}


?>