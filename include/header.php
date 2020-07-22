<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   22.07.2020. 0:43:02
*/

if (!empty($_SESSION['log'])) {

    $vavok_users = $db->get_data('vavok_users', "id='" . $users->getidfromnick($_SESSION['log']) . "'");

    $user_id = $vavok_users['id']; // user id

    $user_profil = $db->get_data('vavok_profil', "uid='{$user_id}'", 'regche');

    $db->update('vavok_profil', 'lastvst', time(), "uid='{$user_id}'");

    if (!empty($vavok_users['mskin']) && $users->user_device() == 'phone') {

        $config_themes = $vavok_users['mskin'];

    } elseif (!empty($vavok_users['skin'])) { // skin

        $config_themes = $vavok_users['skin'];

    }

    if (!empty($vavok_users['timezone'])) { // time zone

        $config["timeZone"] = $vavok_users['timezone'];

    } 

    if (!empty($vavok_users['lang'])) { // language

        // Use language from profile
        $config['language'] = $vavok_users['lang'];

        // Update language in session if it is not language from prifile
        if (empty($_SESSION['lang']) || $_SESSION['lang'] != $vavok_users['lang']) { $_SESSION['lang'] = $vavok_users['lang']; }

    } 

    if ($vavok_users['banned'] == "1" && !strstr($phpself, 'pages/ban.php')) { // banned?

    	// Redirect to ban page
        redirect_to(BASEDIR . "pages/ban.php");

    }

    if ($user_profil['regche'] == 1 && !strstr($phpself, 'pages/key.php')) { // activate account

    	// Account need to be activated
        setcookie('cookpass', '');
        setcookie('cooklog', '');
        setcookie(session_name(), '');
        unset($_SESSION['log']);
        session_destroy();

    }

    // check session life
    if ($config["sessionLife"] > 0) {

        if (($_SESSION['my_time'] + $config["sessionLife"]) < time() && $_SESSION['my_time'] > 0) {

            session_unset();
            setcookie(session_name(), '');
            session_destroy();

            redirect_to(BASEDIR . $request_uri);

        } 
    }

} else {

	// User's site theme
    $config_themes = $config["webtheme"];

    if (empty($_SESSION['lang'])) { $users->change_language(); };

}

// if skin not found
if (!file_exists(BASEDIR . "themes/" . $config_themes . "/index.php")) {
    $config_themes = 'default';
}

// Current theme
define("MY_THEME", $config_themes);

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