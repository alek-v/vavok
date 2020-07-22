<?php


if (!empty($_GET['pg'])) {
	// Page class
	$current_page = new Page();

	$page_data = $current_page->select_page_name(check($_GET['pg']), $fields = 'lang, content, tname');

	// Update language
	if (!empty($page_data['lang']) && strtolower($page_data['lang']) != $users->get_prefered_language($_SESSION['lang'], 'short')) { $users->change_language(strtolower($page_data['lang'])); }
	  
}

// Load main page
elseif (isset($_GET['ln']) || $_SERVER['PHP_SELF'] == '/index.php') {
	// Page class
	$current_page = new Page();

	$requested_language = isset($_GET['ln']) ? check($_GET['ln']) : '';

	// Load user notice from URL
	$url_isset = isset($_GET['isset']) ? '?isset=' . check($_GET['isset']) : '';

	// Load page with language requested in URL if exists
	$page_data = $current_page->load_main_page($requested_language);

	// Update language
	if (!empty($page_data['lang']) && strtolower($page_data['lang']) != $users->get_prefered_language($_SESSION['lang'], 'short')) { $users->change_language(strtolower($page_data['lang'])); }

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

} else {
    // Start class for pages that are not dynamic
    $current_page = new Page();
}

// Load language files
if (!file_exists(BASEDIR . "lang/" . $users->get_user_language() . "/index.php")) {
        $config["language"] = 'english';
}
include_once BASEDIR . "lang/" . $users->get_user_language() . "/index.php";

// language settings
// use language from session
if (!empty($_SESSION['lang'])) { $config["language"] = $_SESSION['lang']; } 
?>