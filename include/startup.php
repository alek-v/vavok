<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   25.07.2020. 11:41:43
*/

// time when execution of the script has started
$start_time = microtime(true);

// session
session_name("sid");
session_start();

// vavok cms settings
$config_debug = 1;

// error reporting
if ($config_debug == 0) {
    error_reporting(0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

$config_srvhost = $_SERVER['HTTP_HOST'];
$config_requri = urldecode($_SERVER['REQUEST_URI']);
$subself = substr($_SERVER['PHP_SELF'], 1);

// clean URL (REQUEST_URI)
$clean_requri = preg_replace(array('#(\?|&)' . session_name() . '=([^=&\s]*)#', '#(&|\?)+$#'), '', $config_requri);
$clean_requri = explode('&fb_action_ids', $clean_requri);
$clean_requri = $clean_requri[0];
$clean_requri = explode('?fb_action_ids', $clean_requri);
$clean_requri = $clean_requri[0];
$clean_requri = explode('?isset', $clean_requri);
$clean_requri = $clean_requri[0];

// root dir for including system files
if (!defined('BASEDIR')) {
    $folder_level = "";
    while (!file_exists($folder_level . "robots.txt")) {
        $folder_level .= "../";
    } 
    define("BASEDIR", $folder_level);
}

// for links, images and other mod rewriten directories
if (!defined('HOMEDIR')) {
    $path = $_SERVER['HTTP_HOST'] . $clean_requri;
    $patharray = explode("/", $path);
    $pathindex = "";

    for ($i = count($patharray); $i > 2; $i--) {
        $pathindex .= '../';
    } 

    define("HOMEDIR", $pathindex);
}

 // Load website configuration
require_once BASEDIR . "include/config.php";

// Autoload classes
spl_autoload_register(function ($class) {
    include BASEDIR . "include/classes/" . $class . ".class.php";
});

// Default time zone
date_default_timezone_set('UTC');

@ini_set("url_rewriter.tags", "");
@ini_set('session.use_trans_sid', false);

require_once BASEDIR . "include/functions.php";

// connect to database
if (!strstr($config_requri, 'error=db') && !empty($config["dbhost"])) {

    // and this will be PDO connection to base
    $db = new Db($config["dbhost"], $config["dbname"], $config["dbuser"], $config["dbpass"]);

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

// this functions are not in functions.php because they require language files
function user_status($message) {
    global $lang_home;
    $message = str_replace('101', $lang_home['access101'], $message);
    $message = str_replace('102', $lang_home['access102'], $message);
    $message = str_replace('103', $lang_home['access103'], $message);
    $message = str_replace('105', $lang_home['access105'], $message);
    $message = str_replace('106', $lang_home['access106'], $message);
    $message = str_replace('107', $lang_home['access107'], $message);
    return $message;
}

// format time into days and minutes
function formattime($file_time) {
    global $lang_home;
    if ($file_time >= 86400) {
        $file_time = round((($file_time / 60) / 60) / 24, 1) . ' ' . $lang_home['days'];
    } elseif ($file_time >= 3600) {
        $file_time = round(($file_time / 60) / 60, 1) . ' ' . $lang_home['hours'];
    } elseif ($file_time >= 60) {
        $file_time = round($file_time / 60) . ' ' . $lang_home['minutes'];
    } else {
        $file_time = round($file_time) . ' ' . $lang_home['secs'];
    } 
    return $file_time;
}

// show page generation time
function show_gentime() {
    global $config, $start_time, $lang_home;
    if ($config["pageGenTime"] == '1') {
        $end_time = microtime(true);
        $gen_time = $end_time - $start_time;
        $pagegen = $lang_home['pggen'] . ' ' . round($gen_time, 4) . ' s.<br />';
        return $pagegen;
    } 
}

/*
Website configuration
*/

if (get_configuration('noCache') == "0") {
    header("Expires: Sat, 25 Jul 1997 05:00:00 GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
} 

// Website maintenance
if (get_configuration('siteOff') == 1 && !strstr($_SERVER['PHP_SELF'], 'pages/maintenance.php') && !strstr($_SERVER['PHP_SELF'], 'input.php') && !$users->is_administrator() && !strstr($_SERVER['PHP_SELF'], 'pages/login.php')) {
    redirect_to(website_home_address() . "/pages/maintenance.php");
} 

?>