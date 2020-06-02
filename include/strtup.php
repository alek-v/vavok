<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URL:       http://vavok.net
* Updated:   31.05.2020. 17:26:44
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


$time = time(); // current time
$config_srvhost = $_SERVER['HTTP_HOST'];
$config_requri = urldecode($_SERVER['REQUEST_URI']);
$phpself = $_SERVER['PHP_SELF'];
$subself = substr($phpself, 1);

// clean URL (REQUEST_URI)
$clean_requri = preg_replace(array('#(\?|&)' . session_name() . '=([^=&\s]*)#', '#(&|\?)+$#'), '', $config_requri);
$clean_requri = explode('&fb_action_ids', $clean_requri);
$clean_requri = $clean_requri[0];
$clean_requri = explode('?fb_action_ids', $clean_requri);
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
    $path = $config_srvhost . $clean_requri;
    $patharray = explode("/", $path);
    $pathindex = "";

    for ($i = count($patharray); $i > 2; $i--) {
        $pathindex .= '../';
    } 

    define("HOMEDIR", $pathindex);
}

require_once BASEDIR . "include/config.php"; // load website configuration

// autoload classes
spl_autoload_register(function ($class) {
    include BASEDIR . "include/classes/" . $class . ".class.php";
});

// time zone
date_default_timezone_set('UTC');

@ini_set("url_rewriter.tags", "");
@ini_set('session.use_trans_sid', false);

// detect bots and spiders
$user_agents = '';
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $user_agents = $_SERVER['HTTP_USER_AGENT'];
}
if (stristr($user_agents, 'Yandex')) {
    $searchbot = 'Yandex';
} elseif (stristr($user_agents, 'Slurp')) {
    $searchbot = 'Yahoo! Slurp';
} elseif (stristr($user_agents, 'yahoo')) {
    $searchbot = 'Yahoo!';
} elseif (stristr($user_agents, 'mediapartners-google')) {
    $searchbot = 'Mediapartners-Google';
} elseif (stristr($user_agents, 'Googlebot-Image')) {
    $searchbot = 'Googlebot-Image';
} elseif (stristr($user_agents, 'google')) {
    $searchbot = 'Googlebot';
} elseif (stristr($user_agents, 'StackRambler')) {
    $searchbot = 'Rambler';
} elseif (stristr($user_agents, 'lycos')) {
    $searchbot = 'Lycos';
} elseif (stristr($user_agents, 'SurveyBot')) {
    $searchbot = 'Survey';
} elseif (stristr($user_agents, 'bingbot')) {
    $searchbot = 'Bing';
} elseif (stristr($user_agents, 'msnbot')) {
    $searchbot = 'msnbot';
} elseif (stristr($user_agents, 'Baiduspider')) {
    $searchbot = 'Baidu Spider';
} elseif (stristr($user_agents, 'Sosospider')) {
    $searchbot = 'Soso Spider';
} elseif (stristr($user_agents, 'ia_archiver')) {
    $searchbot = 'ia_archiver';
} elseif (stristr($user_agents, 'facebookexternalhit')) {
    $searchbot = 'Facebook External Hit';
}

require_once BASEDIR . "include/functions.php";

// connect to database
if (!strstr($config_requri, 'error=db') && !empty($config["dbhost"])) {

    // and this will be PDO connection to base
    $db = new Db($config["dbhost"], $config["dbname"], $config["dbuser"], $config["dbpass"]);


    // we are connected to database and we can load Users class
    $users = new Users;

    // we don't need this data if this is system request or we are installing cms
    if (!strstr($phpself, '/cronjob/') && !strstr($phpself, '/install/finish.php')) {

        require_once BASEDIR . "include/cookies.php";
        require_once BASEDIR . "include/header.php"; 
        require_once BASEDIR . "include/antidos.php";
        require_once BASEDIR . "include/counters.php";
        require_once BASEDIR . "include/referer.php";

    } 
}

if (!file_exists(BASEDIR . "lang/" . $config["language"] . "/index.php")) {
        $config["language"] = 'english';
}
include_once BASEDIR . "lang/" . $config["language"] . "/index.php";

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

// register user
function register($name, $pass, $regdate, $regkeys, $rkey, $theme, $brow, $ip, $mail) {
    global $lang_home, $config, $db, $users;
    
    $values = array(
        'name' => $name,
        'pass' => $users->password_encrypt($pass),
        'perm' => '107',
        'skin' => $theme,
        'browsers' => $brow,
        'ipadd' => $ip,
        'timezone' => 0,
        'banned' => 0,
        'newmsg' => 0,
        'lang' => $config["language"]
    );
    $db->insert_data('vavok_users', $values);

    $user_id = $db->get_data('vavok_users', "name='{$name}'", 'id')['id'];

    $db->insert_data('vavok_profil', array('uid' => $user_id, 'opentem' => 0, 'commadd' => 0, 'subscri' => 0, 'regdate' => $regdate, 'regche' => $regkeys, 'regkey' => $rkey, 'lastvst' => $regdate, 'forummes' => 0, 'chat' => 0));
    $db->insert_data('page_setting', array('uid' => $user_id, 'newsmes' => 5, 'forummes' => 5, 'forumtem' => 10, 'privmes' => 5));
    $db->insert_data('vavok_about', array('uid' => $user_id, 'sex' => 'N', 'email' => $mail));
    $db->insert_data('notif', array('uid' => $user_id, 'lstinb' => 0, 'type' => 'inbox'));

    // send private message
    $msg = $lang_home['autopmreg'];
    $users->autopm($msg, $user_id);

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

if (empty($_SESSION['currs'])) {
    $_SESSION['currs'] = $time;
}

if (empty($_SESSION['counton'])) {
    $_SESSION['counton'] = 0;
} 

$_SESSION['counton']++;

// pages visited at this session
$counton = $_SESSION['counton'];

// visitor's time on the site
$timeon = maketime(round($time - $_SESSION['currs']));

?>