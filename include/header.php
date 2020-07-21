<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   21.07.2020. 3:20:03
*/

// Update language if page is using different language then current one
// Use page language
if (!empty($_GET['pg'])) {
	$this_page = new Page();
	$v_lang = $this_page->select_page_name($_GET['pg'], $fields = 'lang')['lang'];

	$users->change_language($v_lang);
}

// Load main page
elseif (isset($_GET['ln']) || $_SERVER['PHP_SELF'] == '/index.php') {
	$requested_language = isset($_GET['ln']) ? check($_GET['ln']) : '';

	// Load user notice from URL
	$url_isset = isset($_GET['isset']) ? '?isset=' . check($_GET['isset']) : '';

	// Page class
	$page = new Page();

	// Load page with language requested in URL if exists
	$page_data = $page->load_main_page($requested_language);

	// Update language
	if (!empty($page_data['lang'])) { $users->change_language($page_data['lang']); }

	// Redirect to root dir if visitor is using site default language and this language is requested in url
	// Example: default website language is english and user is opening www.example.com/en/
	if (get_configuration('siteDefaultLang') == $users->get_prefered_language($requested_language) && !empty($requested_language) && file_exists("lang/" . $requested_language . "/index.php")) {
		redirect_to("/" . $url_isset);
	}

	/*
	  Redirect if user's language is not website default language,
	  language is not in URL (www.example.com/en/)
	  and page with users's language exists
	*/
	if (get_configuration('siteDefaultLang') != $users->get_user_language() && empty($requested_language)) {
		redirect_to("/" .  $users->get_prefered_language($users->get_user_language(), 'short') . "/" . $url_isset);
	}

}

if (!empty($_SESSION['log'])) {

    $vavok_users = $db->get_data('vavok_users', "id='" . $users->getidfromnick($_SESSION['log']) . "'");

    $user_id = $vavok_users['id']; // user id

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

        if (($_SESSION['my_time'] + $config["sessionLife"]) < $time && $_SESSION['my_time'] > 0) {

            session_unset();
            setcookie(session_name(), '');
            session_destroy();

            redirect_to(BASEDIR . $request_uri);

        } 
    }

} else {

	// User's site theme
    $config_themes = $config["webtheme"];

}

// if skin not found
if (!file_exists(BASEDIR . "themes/" . $config_themes . "/index.php")) {
    $config_themes = 'default';
}

// Current theme
define("MY_THEME", $config_themes);

// Load language files
if (!file_exists(BASEDIR . "lang/" . $users->get_user_language() . "/index.php")) {
        $config["language"] = 'english';
}
include_once BASEDIR . "lang/" . $users->get_user_language() . "/index.php";

// language settings
// use language from session
if (!empty($_SESSION['lang'])) { $config["language"] = $_SESSION['lang']; } 

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