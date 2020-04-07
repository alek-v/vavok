<?php 
// (c) vavok.net
require_once"include/strtup.php";

if (!empty($_GET['action'])) { $action = check($_GET["action"]); } else { $action = ''; }

if (isset($_POST['log'])) {
    $log = check($_POST['log']);
} else if (isset($_GET['log'])) {
    $log = check($_GET['log']);
} else {
	$log = '';
}

if (isset($_POST['pass'])) {
    $pass = check($_POST['pass']);
} else if (isset($_GET['pass'])) {
    $pass = check($_GET['pass']);
} else {
	$pass = '';
}

if (isset($_POST['cookietrue'])) {
    $cookietrue = check($_POST['cookietrue']);
} else if (isset($_GET['cookietrue'])) {
    $cookietrue = check($_GET['cookietrue']);
} else {
	$cookietrue = '';
}

if (isset($_POST['ptl'])) {
    $pagetoload = check($_POST['ptl']);
} else if (isset($_GET['ptl'])) {
    $pagetoload = check($_GET['ptl']);
} else {
	$pagetoload = '';
}

$xtime = time();

if ($log == 'System') {
	// cannot login as a System
    unset($log);
}

// check login attempts
$max_time_in_seconds = 600;
$max_attempts = 5;

if (login_attempt_count($max_time_in_seconds, $log, $userip, $db) > $max_attempts) {
    include "themes/" . $config_themes . "/index.php";

    echo "<p>I'm sorry, you've made too many attempts to log in too quickly.<br>
    Try again in " . explode(':', maketime($max_time_in_seconds))[0] . " minutes.</p>"; // update lang

    include "themes/" . $config_themes . "/foot.php";
    exit;
}

// login
if (empty($action) && !empty($log)) {
    if (isValidEmail($log)) {
        $userx_about = $db->select('vavok_about', "email='" . $log . "'", '', 'uid');
        $userx_id = $userx_about['uid'];
        $log = getnickfromid($userx_id);
    } else {
        $userx_id = $users->getidfromnick($log);
    } 

    $show_userx = $db->select('vavok_users', "id='" . $userx_id . "'", '', 'name, pass, banned, perm');
    $user_profil = $db->select('vavok_profil', "uid='" . $userx_id . "'", '', 'regche');
    if (!empty($log) && !empty($pass) && md5($pass) == $show_userx['pass'] && $log == $show_userx['name']) {
        if ($cookietrue == 1) {
            $cookiePass = xoft_encode($pass, $config["keypass"]);
            $cookieUsername = xoft_encode($show_userx['name'], $config["keypass"]);

            SetCookie("cookpass", $cookiePass, $xtime + 3600 * 24 * 365);
            SetCookie("cooklog", $cookieUsername, $xtime + 3600 * 24 * 365);
        } 

        $log = $show_userx['name'];

        $ip = preg_replace("/[^0-9.]/", "", $ip);
        $pr_ip = explode(".", $ip);
        $my_ip = $pr_ip[0] . $pr_ip[1] . $pr_ip[2];

        $_SESSION['log'] = $log;
        $_SESSION['pass'] = $pass;
        $_SESSION['permissions'] = $show_userx['perm'];
        $_SESSION['my_ip'] = $my_ip;
        $_SESSION['my_brow'] = $brow;
        unset($_SESSION['lang']); // use language settings from profile

        // get new session id to prevent session fixation
        session_regenerate_id();

        // update data in profile
        $db->update('vavok_users', 'ipadd', check($ip), "id='" . $userx_id . "'");

        if ($user_profil['regche'] == "1") {
            header ("Location: " . BASEDIR . "pages/key.php?log=$log");
            exit;
        } 
        if ($show_userx['banned'] == '1') {
            header ("Location: " . BASEDIR . "pages/ban.php?log=$log");
            exit;
        } 
        if (!empty($pagetoload)) {
            header ("Location: " . $pagetoload);
            exit;
        } else {
            header ("Location: ./");
            exit;
        } 
    }
} 
// logout
if (is_reg() && $action == "exit") {
    $db->delete('online', "user = '" . $user_id . "'");

    setcookie('cookpass', "", time() - 3600);
    setcookie('cooklog', "", time() - 3600);
    setcookie(session_name(), "", time() - 3600);
    session_destroy();

    header ("Location: ./?isset=exit");
    exit;
}

header ("Location: ./?isset=inputoff");
exit;
?>