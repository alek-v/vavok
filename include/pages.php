<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   01.08.2020. 19:21:08
*/

if (!empty($_GET['pg'])) {
	// Page class
	$current_page = new Page($_GET['pg'], $users->get_prefered_language($users->get_user_language(), 'short'));

	// Load page
	$page_data = $current_page->load_page();

	// Update language
	if (!empty($page_data['lang']) && strtolower($page_data['lang']) != $users->get_prefered_language($_SESSION['lang'], 'short')) { $users->change_language(strtolower($page_data['lang'])); }
	  
}

// Load main page
elseif (isset($_GET['ln']) || $_SERVER['PHP_SELF'] == '/index.php') {
	// Requested page language
	$requested_language = isset($_GET['ln']) ? $vavok->check($_GET['ln']) : '';

	// Load user notice from URL
	$url_isset = isset($_GET['isset']) ? '?isset=' . $vavok->check($_GET['isset']) : '';

	// Load main page
	$current_page = new Page('index', $requested_language);

	// Load page with language requested in URL if exists
	$page_data = $current_page->load_page();

	// Update language
	if (!empty($page_data['lang']) && strtolower($page_data['lang']) != $users->get_prefered_language($_SESSION['lang'], 'short')) { $users->change_language(strtolower($page_data['lang'])); }

	// Redirect to root dir if visitor is using site default language and this language is requested in url
	// Example: default website language is english and user is opening www.example.com/en/
	if ($vavok->get_configuration('siteDefaultLang') == $users->get_prefered_language($requested_language) && !empty($requested_language) && file_exists(BASEDIR . "include/lang/" . $requested_language . "/index.php")) {
		$vavok->redirect_to("/" . $url_isset);
	}

	/*
	  Redirect if user's language is not website default language,
	  language is not in URL (www.example.com/en/)
	  and page with users's language exists
	*/
	if ($vavok->get_configuration('siteDefaultLang') != $users->get_user_language() && empty($requested_language)) {
		$vavok->redirect_to("/" .  $users->get_prefered_language($users->get_user_language(), 'short') . "/" . $url_isset);
	}

} else {
    // Start class for pages that are not dynamic
    $current_page = new Page();
}

// Load language files
if (!file_exists(BASEDIR . "include/lang/" . $users->get_user_language() . "/index.php")) { $_SESSION['lang'] = 'english'; }

include_once BASEDIR . "include/lang/" . $users->get_user_language() . "/index.php";

?>