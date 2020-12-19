<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!empty($_GET['action'])) { $action = $vavok->check($_GET["action"]); } else { $action = ''; }

if (isset($_POST['log'])) {
    $log = $vavok->check($_POST['log']);
} else if (isset($_GET['log'])) {
    $log = $vavok->check($_GET['log']);
} else {
	$log = '';
}

if (isset($_POST['pass'])) {
    $pass = $vavok->check($_POST['pass']);
} else if (isset($_GET['pass'])) {
    $pass = $vavok->check($_GET['pass']);
} else {
	$pass = '';
}

if (isset($_POST['cookietrue'])) {
    $cookietrue = $vavok->check($_POST['cookietrue']);
} else if (isset($_GET['cookietrue'])) {
    $cookietrue = $vavok->check($_GET['cookietrue']);
} else {
	$cookietrue = '';
}

if (isset($_POST['ptl'])) {
    $pagetoload = $vavok->check($_POST['ptl']);
} else if (isset($_GET['ptl'])) {
    $pagetoload = $vavok->check($_GET['ptl']);
} else {
	$pagetoload = '';
}

// meta tag for this page
$vavok->go('current_page')->append_head_tags('<meta name="robots" content="noindex">');

// check login attempts
$max_time_in_seconds = 600;
$max_attempts = 5;

// login
if (empty($action) && !empty($log) && $log != 'System') {
	if ($vavok->go('users')->login_attempt_count($max_time_in_seconds, $log, $vavok->go('users')->find_ip()) > $max_attempts) {
	    $vavok->require_header();

	    echo "<p>I'm sorry, you've made too many attempts to log in too quickly.<br>
	    Try again in " . explode(':', $vavok->maketime($max_time_in_seconds))[0] . " minutes.</p>"; // update lang

	    $vavok->require_footer();
	    exit;
	}

    // user is logging in with email
    if ($vavok->go('users')->validate_email($log)) {
        $userx_about = $vavok->go('db')->get_data('vavok_about', "email='{$log}'", 'uid');
        $userx_id = $userx_about['uid'];
        $log = $vavok->go('users')->getnickfromid($userx_id);
    } else {
        // user is logging in with username
        $userx_id = $vavok->go('users')->getidfromnick($log);
    }

    $show_userx = $vavok->go('db')->get_data('vavok_users', "id='{$userx_id}'", 'name, pass, banned, perm');
    $user_profil = $vavok->go('db')->get_data('vavok_profil', "uid='{$userx_id}'", 'regche');

    // compare sent data and data from database
    if ($vavok->go('users')->password_check($pass, $show_userx['pass']) && $log == $show_userx['name']) {
        // user want to remember login
        if ($cookietrue == 1) {
            // encrypt data to save in cookie
            $cookiePass = $vavok->xoft_encode($pass, $vavok->get_configuration('keypass'));
            $cookieUsername = $vavok->xoft_encode($show_userx['name'], $vavok->get_configuration('keypass'));

            /**
             * With '.' session is accessible from all subdomains
             */
            $rootDomain = '.' . $vavok->clean_domain();

            // save cookie
            SetCookie("cookpass", $cookiePass, time() + 3600 * 24 * 365, "/", $rootDomain); // one year
            SetCookie("cooklog", $cookieUsername, time() + 3600 * 24 * 365, "/", $rootDomain); // one year
        }

        $_SESSION['log'] = $log;
        $_SESSION['permissions'] = $show_userx['perm'];
        $_SESSION['uid'] = $userx_id;

        unset($_SESSION['lang']); // use language settings from profile

        /**
         * Get new session id to prevent session fixation
         */
        session_regenerate_id();

        // update data in profile
        $vavok->go('db')->update('vavok_users', 'ipadd', $vavok->go('users')->find_ip(), "id='{$userx_id}'");

        if ($user_profil['regche'] == 1) {
            $vavok->redirect_to(HOMEDIR . "pages/key.php?log=$log");
        }

        if ($show_userx['banned'] == 1) {
            $vavok->redirect_to(HOMEDIR . "pages/ban.php?log=$log");
        }

        if (!empty($pagetoload)) {
            $vavok->redirect_to(HOMEDIR . $pagetoload);
        } else {
            $vavok->redirect_to(HOMEDIR);
        }
    }
}

// logout
if ($vavok->go('users')->is_reg() && $action == "exit") {
    // log out
    $vavok->go('users')->logout($vavok->go('users')->user_id);

    $vavok->redirect_to("../?isset=exit");
}

$vavok->redirect_to("../?isset=inputoff");
?>