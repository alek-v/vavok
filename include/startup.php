<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   28.07.2020. 12:27:18
*/

// Time when execution of the script has started
$start_time = microtime(true);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

/*
Constants
*/

// Root dir for including system files
if (!defined('BASEDIR')) {
    $folder_level = "";
    while (!file_exists($folder_level . "robots.txt")) {
        $folder_level .= "../";
    } 
    define("BASEDIR", $folder_level);
}

define('REQUEST_URI', urldecode($_SERVER['REQUEST_URI']));
$config_requri = urldecode($_SERVER['REQUEST_URI']); // deprecated 25.07.2020. 13:04:27
define('SUB_SELF', substr($_SERVER['PHP_SELF'], 1));

// Clean URL (REQUEST_URI)
$clean_requri = explode('&fb_action_ids', REQUEST_URI)[0]; // facebook
$clean_requri = explode('?fb_action_ids', $clean_requri)[0]; // facebook
$clean_requri = explode('?isset', $clean_requri)[0];
define('CLEAN_REQUEST_URI', $clean_requri);

// For links, images and other mod rewriten directories
if (!defined('HOMEDIR')) {
    $path = $_SERVER['HTTP_HOST'] . CLEAN_REQUEST_URI;
    $patharray = explode("/", $path);
    $pathindex = "";

    for ($i = count($patharray); $i > 2; $i--) {
        $pathindex .= '../';
    } 

    define("HOMEDIR", $pathindex);
}

 // Website configuration
require_once BASEDIR . "include/config.php";

// Autoload classes
spl_autoload_register(function ($class) {
    include BASEDIR . "include/classes/{$class}.class.php";
});

require_once BASEDIR . "include/functions.php";

// Connect to database
if (!strstr(REQUEST_URI, 'error=db') && !empty(get_configuration('dbhost'))) {

    // and this will be PDO connection to base
    $db = new Db(get_configuration('dbhost'), get_configuration('dbname'), get_configuration('dbuser'), get_configuration('dbpass'));

    // We are connected to database and we can load Users class
    $users = new Users();

    // We don't need this data if this is system request or we are installing cms
    if (!strstr($_SERVER['PHP_SELF'], '/cronjob/') && !strstr($_SERVER['PHP_SELF'], '/install/finish.php')) {

        require_once BASEDIR . "include/cookies.php";
        require_once BASEDIR . "include/pages.php";
        require_once BASEDIR . "include/antidos.php";
        require_once BASEDIR . "include/counters.php";
        require_once BASEDIR . "include/referer.php";

    }
}

/*
Website configuration
*/

// Website maintenance
if (get_configuration('siteOff') == 1 && !strstr($_SERVER['PHP_SELF'], 'pages/maintenance.php') && !strstr($_SERVER['PHP_SELF'], 'input.php') && !$users->is_administrator() && !strstr($_SERVER['PHP_SELF'], 'pages/login.php')) {
    redirect_to(website_home_address() . "/pages/maintenance.php");
} 

?>