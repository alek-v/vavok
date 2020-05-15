<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       http://vavok.net
* Updated:   10.05.2020. 18:37:31
*/


if (!empty($_SESSION['log'])) {

    $vavok_users = $db->get_data('vavok_users', "id='{$users->getidfromnick($_SESSION['log'])}'");

    $user_id = $vavok_users['id']; // user id
    $log = $_SESSION['log'];

    $user_profil = $db->get_data('vavok_profil', "uid='{$user_id}'", 'regche');

    $db->update('vavok_profil', 'lastvst', $time, "uid='{$user_id}'");

    if (!empty($vavok_users['mskin']) && $users->user_device() == 'phone') {

        $config_themes = $vavok_users['mskin'];

    } elseif (!empty($vavok_users['skin'])) { // skin

        $config_themes = $vavok_users['skin'];

    }

    if (!empty($vavok_users['timezone'])) { // time zone

        $config["timeZone"] = $vavok_users['timezone'];

    } 

    if (!empty($vavok_users['lang'])) { // language

        $config["language"] = $vavok_users['lang'];

    } 

    if ($vavok_users['banned'] == "1" && !strstr($phpself, 'pages/ban.php')) { // banned?

        redirect_to(BASEDIR . "pages/ban.php");

    }

    if ($user_profil['regche'] == 1 && !strstr($phpself, 'pages/key.php')) { // activate account

        setcookie('cookpass', '');
        setcookie('cooklog', '');
        setcookie(session_name(), '');
        unset($_SESSION['log']);
        session_destroy();

    }

    // check session life
    if ($config["sessionLife"] > 0) {

        if (($_SESSION['my_time'] + $config["sessionLife"]) < $time && $_SESSION['my_time'] > 0) {

            session_unset();
            setcookie(session_name(), '');
            session_destroy();

            redirect_to(BASEDIR . $request_uri);

        } 
    }

} else {
    // if subdomain is www
    if (substr($_SERVER['HTTP_HOST'], 0, 3) == 'www') {
        $config_themes = $config["webtheme"];
    } 
    // if subdomain is mobile
    elseif (substr($_SERVER['HTTP_HOST'], 0, 2) == 'm.') {
        $config_themes = $config["mTheme"];
    } 
    // else
    else {
        if ($users->user_device() == 'phone' && $config["redbrow"] == 1) {
            redirect_to(transfer_protocol() . "m." . $config["homeBase"] . $request_uri); 
            // header("Location: http://m.".$config["homeBase"]."".$request_uri."", TRUE, 301); // 301 Moved Permanently
        } elseif ($users->user_device() == 'computer' && $config["redbrow"] == 1) {
            redirect_to(transfer_protocol() . "www." . $config["homeBase"] . $request_uri); 
            // header("Location: http://www.".$config["homeBase"]."".$request_uri."", TRUE, 301); // 301 Moved Permanently
        } elseif ($users->user_device() == 'phone') {
            $config_themes = $config["mTheme"];
        } else {
            $config_themes = $config["webtheme"];
        } 
    } 
}

// if skin not found
if (!file_exists(BASEDIR . "themes/" . $config_themes . "/index.php")) {
    $config_themes = 'default';
}

// current theme
define("MY_THEME", $config_themes);

// language settings
// use language from session
if (!empty($_SESSION['lang'])) { $config["language"] = $_SESSION['lang']; } 

// if there is no language chosen by user

if (empty($user_id)) {

	// use page language
	if (!empty($_GET['pg'])) {
		$this_page = new Page();
		$v_lang = $this_page->select_page_name($_GET['pg'], $fields = 'lang')['lang'];
	}

	elseif (!empty($_SESSION['lang'])) {
		$v_lang = $_SESSION['lang'];
	}

	elseif (!isset($v_lang) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) { // use browser language

        $v_lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

    } else { $v_lang = ''; }

    $v_lang = substr($v_lang, 0, 2);

    switch ($v_lang) {

        case "en": 

            // english

            if (file_exists(BASEDIR . 'lang/english/index.php')) {
                $_SESSION['lang'] = 'english';
                $config["language"] = 'english';
            } else {

                $_SESSION['lang'] = $config["language"];

            } 

            break;

        case "sr": 

            // serbian

            if (file_exists(BASEDIR . 'lang/serbian_cyrillic/index.php')) {

                $_SESSION['lang'] = 'serbian_cyrillic';
                $config["language"] = 'serbian_cyrillic';

            } elseif (file_exists(BASEDIR . 'lang/serbian_latin/index.php')) {

                $_SESSION['lang'] = 'serbian_latin';
                $config["language"] = 'serbian_latin';

            } else {

                $_SESSION['lang'] = $config["language"];

            } 

            break;

        default: 

            // include default language in all other cases of different lang detection
            $_SESSION['lang'] = $config["language"];

            break;
    
    } 
} 

if ($config["noCache"] == "0") {
    header("Expires: Sat, 25 Jul 1997 05:00:00 GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
} 

if ($config["siteOff"] == 1 && !strstr($phpself, 'pages/maintenance.php') && !strstr($phpself, 'input.php') && !$users->is_administrator() && !strstr($phpself, 'pages/login.php')) {
    redirect_to(website_home_address() . "/pages/maintenance.php");
} 

?>