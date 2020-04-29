<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       http://vavok.net
* Updated:   29.04.2020. 9:16:06
*/

require_once"../include/strtup.php";

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

if ($log == 'System') { // cannot login as a System
    unset($log);
}

// meta tag for this page
$genHeadTag = '<meta name="robots" content="noindex">';

// check login attempts
$max_time_in_seconds = 600;
$max_attempts = 5;


// login
if (empty($action) && !empty($log)) {

	if (login_attempt_count($max_time_in_seconds, $log, $userip, $db) > $max_attempts) {
	    include "../themes/" . $config_themes . "/index.php";

	    echo "<p>I'm sorry, you've made too many attempts to log in too quickly.<br>
	    Try again in " . explode(':', maketime($max_time_in_seconds))[0] . " minutes.</p>"; // update lang

	    include "../themes/" . $config_themes . "/foot.php";
	    exit;
	}

    // user is logging in with email
    if ($users->validate_email($log)) {

        $userx_about = $db->get_data('vavok_about', "email='" . $log . "'", 'uid');
        $userx_id = $userx_about['uid'];
        $log = $users->getnickfromid($userx_id);

    } else {
        // user is logging in with username
        $userx_id = $users->getidfromnick($log);
    } 

    $show_userx = $db->select('vavok_users', "id='" . $userx_id . "'", '', 'name, pass, banned, perm');
    $user_profil = $db->select('vavok_profil', "uid='" . $userx_id . "'", '', 'regche');

    // compare sent data and data from database
    if ($users->password_check($pass, $show_userx['pass']) && $log == $show_userx['name']) {

        // user want to remember login
        if ($cookietrue == 1) {

            // encrypt data to save in cookie
            $cookiePass = xoft_encode($pass, $config["keypass"]);
            $cookieUsername = xoft_encode($show_userx['name'], $config["keypass"]);

            // save cookie
            SetCookie("cookpass", $cookiePass, time() + 3600 * 24 * 365, "/"); // one year
            SetCookie("cooklog", $cookieUsername, time() + 3600 * 24 * 365, "/"); // one year
        
        } 

        $ip = preg_replace("/[^0-9.]/", "", $ip);
        $pr_ip = explode(".", $ip);
        $my_ip = $pr_ip[0] . $pr_ip[1] . $pr_ip[2];

        $_SESSION['log'] = $log;
        $_SESSION['permissions'] = $show_userx['perm'];
        $_SESSION['my_ip'] = $my_ip;
        $_SESSION['my_brow'] = $users->user_browser();

        unset($_SESSION['lang']); // use language settings from profile
        
        session_regenerate_id(); // get new session id to prevent session fixation

        // update data in profile
        $db->update('vavok_users', 'ipadd', check($ip), "id='{$userx_id}'");

        if ($user_profil['regche'] == "1") {
            redirect_to(BASEDIR . "pages/key.php?log=$log");
        }

        if ($show_userx['banned'] == '1') {
            redirect_to(BASEDIR . "pages/ban.php?log=$log");
        }

        if (!empty($pagetoload)) {
            redirect_to(BASEDIR . $pagetoload);
        } else {
            redirect_to(BASEDIR);
        } 

    }

}
 
// logout
if ($users->is_reg() && $action == "exit") {

    // log out
    $users->logout($user_id);

    redirect_to("../?isset=exit");
}

redirect_to("../?isset=inputoff");
?>