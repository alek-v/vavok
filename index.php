<?php
// (c) vavok.net - Aleksandar Vranesevic
require_once("include/strtup.php");

// Page language requested in URL
$requested_language = isset($_GET['ln']) ? check($_GET['ln']) : '';

// Load user notice from URL
$url_isset = isset($_GET['isset']) ? '?isset=' . check($_GET['isset']) : '';

// Page class
$page = new Page();

// Load page with language requested in URL if exists
$page_data = $page->load_main_page($requested_language);

/*
   Language is set in url and it is not current user's lang
   We need to load language data and update user's language
   Example: www.example.com/en/ is requested, but current user's language is not english
*/
if ($requested_language != $ln_loc && !empty($requested_language)) {

	// Load language data
	if (file_exists("lang/" . $requested_language . "/index.php")) {

		// Load requested language
		include "lang/" . $requested_language . "/index.php"; // get $language variable
		include "lang/" . $language . "/index.php";

		// Update language
		$users->change_language($language);

	}

}

// Redirect to root dir if visitor is using site default language and this language is requested in url
// Example: default website language is english and user is opening www.example.com/en/
if ($config["siteDefaultLang"] == $config["language"] && !empty($requested_language) && file_exists("lang/" . $requested_language . "/index.php")) {
	redirect_to("/" . $url_isset);
}

/*
  Redirect if user's language is not website default language,
  language is not in URL (www.example.com/en/)
  and page with users's language exists
*/
if (get_configuration("siteDefaultLang") != $config["language"] && empty($requested_language) && $page->load_main_page($ln_loc)) {
	redirect_to("/" . $ln_loc . "/" . $urlIsset);
}

// Page title
$my_title = !empty($page_data['tname']) ? $page_data['tname'] : '';

// Page header
include_once("themes/" . $config_themes . "/index.php");

// Show page content
echo $page_data['content'];

// Load website footer
include_once"themes/" . $config_themes . "/foot.php";

?>