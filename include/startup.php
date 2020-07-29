<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   29.07.2020. 14:15:18
*/

// Time when execution of the script has started
$start_time = microtime(true);

// Root dir for including system files
if (!defined('BASEDIR')) {
    $folder_level = "";
    while (!file_exists($folder_level . "robots.txt")) {
        $folder_level .= "../";
    } 
    define("BASEDIR", $folder_level);
}

// Autoload classes
spl_autoload_register(function ($class) {
    include BASEDIR . "include/classes/{$class}.class.php";
});

// Functions
require_once BASEDIR . "include/functions.php";

// Main class
$vavok = new Vavok();

// Connect to database
if (!strstr(REQUEST_URI, 'error=db') && !strstr($_SERVER['PHP_SELF'], 'install/index.php')) {
    // Connection to database
    $db = new Db(DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD);

    // Website configuration
    require_once BASEDIR . "include/config.php";

    // We are connected to database and we can load Users.class.php
    $users = new Users();
}

/**
 * We don't need this data if this is system request or we are installing cms
 */
if (!strstr($_SERVER['PHP_SELF'], '/cronjob/') && !strstr($_SERVER['PHP_SELF'], 'install/index.php')) {

    require_once BASEDIR . "include/cookies.php";
    require_once BASEDIR . "include/pages.php";
    require_once BASEDIR . "include/antidos.php";
    require_once BASEDIR . "include/counters.php";
    require_once BASEDIR . "include/referer.php";


    /**
     * Website configuration
     */
    if ($vavok->get_configuration('siteOff') == 1 && !strstr($_SERVER['PHP_SELF'], 'pages/maintenance.php') && !strstr($_SERVER['PHP_SELF'], 'pages/input.php') && !$users->is_administrator() && !strstr($_SERVER['PHP_SELF'], 'pages/login.php')) {
        redirect_to(website_home_address() . "/pages/maintenance.php");
    }
}


?>