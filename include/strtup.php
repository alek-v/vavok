<?php 
// (c) vavok.net

// time when execution of the script has started
$start_time = microtime(true);

// current time
$time = time();

// vavok cms settings
$config_debug = 1;

// error reporting
if ($config_debug == 0) {
    error_reporting(0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// session
session_name("sid");
session_start();

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

// detect connection protocol HTTPS or HTTP
if (empty($config['transferProtocol']) || $config['transferProtocol'] == 'auto') {
    if (!empty($_SERVER['HTTPS'])) {
        $connectionProtocol = 'https://';
    } else {
        $connectionProtocol = 'http://';
    }
} elseif ($config['transferProtocol'] == 'HTTPS') {
    $connectionProtocol = 'https://';
} else {
    $connectionProtocol = 'http://';
}

// media page url (fb, g+, etc..) to avoid duplicated pages
// remove number of page (forums, news, etc..)
function mediaPageUrl($host, $request) {
$r = preg_replace('/&page=(\d+)/', '', $request);
$r = preg_replace('/page=(\d+)/', '', $r);
$r = str_replace('&page=last', '', $r);
$r = str_replace('page=last', '', $r);
// remove language dir from main page
$r = str_replace('/en/', '', $r);
$r = str_replace('/sr/', '', $r);
// remove index.php from urls to remove double content
$r = str_replace('/index.php', '/', $r);

// keep HTTP protocol to count old likes
$media_page_url = 'http://' . $host . $r;

return $media_page_url;
}

// keep HTTP protocol to count old likes
$media_page_url = mediaPageUrl($config_srvhost, $clean_requri);
$media_like_url = $media_page_url; // deprecated - 10.5.2017. 22:24:59

// autoload classes
spl_autoload_register(function ($class) {
    include BASEDIR . "include/classes/" . $class . ".class.php";
});

// visitor's browser
if(ini_get("browscap")) {
	$userBrowser = get_browser(null, true);
} else {
	$detectBrowser = new BrowserDetection();
	$userBrowser = rtrim($detectBrowser->detect()->getBrowser() . ' ' . $detectBrowser->getVersion());
}
if (empty($userBrowser)) { $userBrowser = 'Not detected'; }

$brow = $userBrowser; // deprecated! 14.5.2017. 15:57:31 $brow var is deprecated

// did visitor use computer or phone?
$userDevice = BrowserDetection::userDevice();

// deprecated! 14.5.2017. 16:39:13 use var $userDevice
function browser_vtype() {
    $user_agents = $_SERVER["HTTP_USER_AGENT"];
    if (stristr($user_agents, "symbian") == true || stristr($user_agents, "midp") == true || stristr($user_agents, "android") == true || stristr($user_agents, "mobi") == true) {
        return 'phone';
    } elseif (stristr($user_agents, "unix") == true || stristr($user_agents, "msie") == true || stristr($user_agents, "windows") == true || stristr($user_agents, "macintosh") == true || stristr($user_agents, "macos") == true || stristr($user_agents, "bsd") == true) {
        return 'computer';
    } elseif (stristr($user_agents, "mozilla") == true) {
        return 'computer';
    } else {
        return 'computer';
    } 
}

// time zone
date_default_timezone_set('UTC');

@ini_set("url_rewriter.tags", "");
@ini_set('session.use_trans_sid', false);

// detect bots and spiders
$user_agents = $_SERVER['HTTP_USER_AGENT'];
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
if (!stristr($config_requri, 'error=db') && !stristr($phpself, 'install/install.php')) {
    // and this will be PDO connection to base
    $db = new Db("mysql:host=" . $config["dbhost"] . ";dbname=" . $config["dbname"], $config["dbuser"], $config["dbpass"]);

    // we are connected to database and we can load Users class
    $users = new Users;

    if (!stristr($phpself, 'install/finish.php') && !stristr($phpself, '/cronjob/')) {

        require_once BASEDIR . "include/cookies.php";
        require_once BASEDIR . "include/header.php"; 
        // require_once BASEDIR . "include/antidos.php";
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
    global $lang_home, $config, $db;
    
    $values = array(
        'name' => $name,
        'pass' => $pass,
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

    $user_id = $db->select('vavok_users', "name='" . $name . "'", '', 'id');
    $user_id = $user_id['id'];

    $db->insert_data('vavok_profil', array('uid' => $user_id, 'opentem' => 0, 'commadd' => 0, 'subscri' => 0, 'regdate' => $regdate, 'regche' => $regkeys, 'regkey' => $rkey, 'lastvst' => $regdate, 'forummes' => 0, 'chat' => 0));
    $db->insert_data('page_setting', array('uid' => $user_id, 'newsmes' => 5, 'forummes' => 5, 'forumtem' => 10, 'privmes' => 5));
    $db->insert_data('vavok_about', array('uid' => $user_id, 'sex' => 'N', 'email' => $mail));
    $db->insert_data('notif', array('uid' => $user_id, 'lstinb' => 0, 'type' => 'inbox'));

    // send private message
    $msg = $lang_home['autopmreg'];
    autopm($msg, $user_id);
} 
// page navigation - old version (deprecated)
function page_navigation($link, $posts, $page, $total)
{
    global $lang_home;
    echo '<hr />';
    if ($page > 2) {
        echo '<a href="' . $link . 'page=' . ($page - 1) . '">&lt; ' . $lang_home['back'] . '</a> ';
    } elseif ($page == 2) {
        $linkx = rtrim($link, '&amp;');
        $linkx = rtrim($linkx, '?');
        echo '<a href="' . $linkx . '">&lt; ' . $lang_home['back'] . '</a> ';
    } else {
        echo '&lt; ' . $lang_home['back'] . '';
    } 
    echo ' | ';
    if ($total > ($posts * $page)) {
        echo '<a href="' . $link . 'page=' . ($page + 1) . '">' . $lang_home['forw'] . ' &gt;</a>';
    } else {
        echo $lang_home['forw'] . ' &gt;';
        echo '<br />';
    } 
} 
// page navigation (deprecated)
function pageNavigation($link, $posts, $page, $total) {
    global $lang_home;
    $navigation = '';

    if ($page > 2) {
        $navigation = '<a href="' . $link . 'page=' . ($page - 1) . '">&lt; ' . $lang_home['back'] . '</a> ';
    } elseif ($page == 2) {
        $linkx = rtrim($link, '&amp;');
        $linkx = rtrim($linkx, '?');
        $navigation .= '<a href="' . $linkx . '">&lt; ' . $lang_home['back'] . '</a> ';
    } else {
        $navigation = '&lt; ' . $lang_home['back'] . '';
    } 
    $navigation .= ' | ';
    if ($total > ($posts * $page)) {
        $navigation .= '<a href="' . $link . 'page=' . ($page + 1) . '">' . $lang_home['forw'] . ' &gt;</a>';
    } else {
        $navigation .= $lang_home['forw'] . ' &gt;';
    } 
    return $navigation;
} 
// numerical navigaton - old version (deprecated)
function page_numbnavig($link, $posts, $page, $total, $lnks = 3)
{
    global $lang_home;
    if ($total > 0) {
        $ba = ceil($total / $posts);

        echo '<hr />' . $lang_home['page'] . ': ';
        $start = $posts * ($page - 1);
        $min = $start - $posts * ($lnks - 1);
        $max = $start + $posts * $lnks;

        if ($min < $total && $min > 0) {
            if ($min - $posts > 0) {
                $linkx = rtrim($link, '&amp;');
                $linkx = rtrim($linkx, '?');
                echo '<a href="' . $linkx . '">1</a> ... ';
            } else {
                $linkx = rtrim($link, '&amp;');
                echo '<a href="' . $linkx . '">1</a> ';
            } 
        } 

        for($i = $min; $i < $max;) {
            if ($i < $total && $i >= 0) {
                $ii = floor(1 + $i / $posts);

                if ($start == $i) {
                    echo ' <b>(' . $ii . ')</b> ';
                } elseif ($ii == 1) {
                    $linkx = rtrim($link, '&amp;');
                    $linkx = rtrim($linkx, '?');
                    echo ' <a href="' . $linkx . '">' . $ii . '</a> ';
                } else {
                    echo ' <a href="' . $link . 'page=' . $ii . '">' . $ii . '</a> ';
                } 
            } 

            $i = $i + $posts;
        } 

        if ($max < $total) {
            if ($max + $posts < $total) {
                echo ' ... <a href="' . $link . 'page=' . $ba . '">' . $ba . '</a>';
            } else {
                echo ' <a href="' . $link . 'page=' . $ba . '">' . $ba . '</a>';
            } 
        } 
        echo '<br />';
    } 
}
// numerical navigaton (deprecated)
function numbNavigation($link, $posts, $page, $total, $lnks = 3) {
    global $lang_home;
    if ($total > 0) {
        $ba = ceil($total / $posts);

        $navigation = $lang_home['page'] . ': ';
        $start = $posts * ($page - 1);
        $min = $start - $posts * ($lnks - 1);
        $max = $start + $posts * $lnks;

        if ($min < $total && $min > 0) {
            if ($min - $posts > 0) {
                $linkx = rtrim($link, '&amp;');
                $linkx = rtrim($linkx, '?');
                $navigation .= '<a href="' . $linkx . '">1</a> ... ';
            } else {
                $linkx = rtrim($link, '&amp;');
                $navigation .= '<a href="' . $linkx . '">1</a> ';
            } 
        } 

        for($i = $min; $i < $max;) {
            if ($i < $total && $i >= 0) {
                $ii = floor(1 + $i / $posts);

                if ($start == $i) {
                    $navigation .= ' <b>(' . $ii . ')</b> ';
                } elseif ($ii == 1) {
                    $linkx = rtrim($link, '&amp;');
                    $linkx = rtrim($linkx, '?');
                    $navigation .= ' <a href="' . $linkx . '">' . $ii . '</a> ';
                } else {
                    $navigation .= ' <a href="' . $link . 'page=' . $ii . '">' . $ii . '</a> ';
                } 
            } 

            $i = $i + $posts;
        } 

        if ($max < $total) {
            if ($max + $posts < $total) {
                $navigation .= ' ... <a href="' . $link . 'page=' . $ba . '">' . $ba . '</a>';
            } else {
                $navigation .= ' <a href="' . $link . 'page=' . $ba . '">' . $ba . '</a>';
            } 
        } 
    } else {
        $navigation = '';
    }
    return $navigation;
} 
// page navigation combined - prev, next and page number (deprecated)
function siteNavigation($link, $posts, $page, $total, $lnks = 3) {
    global $lang_home;

    $navigation = '<div id="v_pagination">'; 
    // back link
    if ($page > 2) {
        $navigation .= '<a href="' . $link . 'page=' . ($page - 1) . '">' . $lang_home['prev'] . '</a>';
    } elseif ($page == 2) {
        $linkx = rtrim($link, '&amp;');
        $linkx = rtrim($linkx, '?');
        $navigation .= '<a href="' . $linkx . '">' . $lang_home['prev'] . '</a>';
    } else {
        $navigation .= '<span class="prev_v_pagination">' . $lang_home['prev'] . '</span>';
    } 
    // page number navigation
    if ($total > 0) {
        $ba = ceil($total / $posts);

        $start = $posts * ($page - 1);
        $min = $start - $posts * ($lnks - 1);
        $max = $start + $posts * $lnks;

        if ($min < $total && $min > 0) {
            if ($min - $posts > 0) {
                $linkx = rtrim($link, '&amp;');
                $linkx = rtrim($linkx, '?');
                $navigation .= '<a href="' . $linkx . '">1</a> <span class="prev_v_pagination">...</span>';
            } else {
                $linkx = rtrim($link, '&amp;');
                $navigation .= '<a href="' . $linkx . '">1</a> ';
            } 
        } 

        for($i = $min; $i < $max;) {
            if ($i < $total && $i >= 0) {
                $ii = floor(1 + $i / $posts);

                if ($start == $i) {
                    $navigation .= '<span class="prev_v_pagination">' . $ii . '</span>';
                } elseif ($ii == 1) {
                    $linkx = rtrim($link, '&amp;');
                    $linkx = rtrim($linkx, '?');
                    $navigation .= '<a href="' . $linkx . '">' . $ii . '</a>';
                } else {
                    $navigation .= '<a href="' . $link . 'page=' . $ii . '">' . $ii . '</a>';
                } 
            } 

            $i = $i + $posts;
        } 

        if ($max < $total) {
            if ($max + $posts < $total) {
                $navigation .= '<span class="prev_v_pagination">...</span> <a href="' . $link . 'page=' . $ba . '">' . $ba . '</a>';
            } else {
                $navigation .= '<a href="' . $link . 'page=' . $ba . '">' . $ba . '</a>';
            } 
        } 
    } 
    // forward link
    if ($total > ($posts * $page)) {
        $navigation .= '<a href="' . $link . 'page=' . ($page + 1) . '">' . $lang_home['next'] . '</a>';
    } else {
        $navigation .= '<span class="next_v_pagination">' . $lang_home['next'] . '</span>';
    } 

    $navigation .= '</div>';

    return $navigation;
} 
// format time into days and minutes
function formattime($file_time)
{
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
function show_gentime()
{
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