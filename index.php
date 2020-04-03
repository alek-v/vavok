<?php
// (c) vavok.net - Aleksandar Vranesevic
require_once("include/strtup.php");

if (!empty($_GET['ln'])) { $ln = check($_GET['ln']); } else { $ln = ''; }

// get error code if it is in url
$urlIsset = isset($_GET['isset']) == true ? '?isset=' . check($_GET['isset']) : '';

// check is language set in url
$isUrlLangSet = strlen($ln);
if ($isUrlLangSet == 2) {
	$isUrlLangSet = 1;
} else {
	$isUrlLangSet = 0;
}

// redirect if user language is not site default language
if ($config["siteDefaultLang"] != $config["language"] && $isUrlLangSet == 0) {
	redirect_to("/" . $ln_loc . "/" . $urlIsset);
}

// redirect if language is set in url and it is not current lang
elseif ($ln != $ln_loc && $isUrlLangSet == 1) {
	$check_lang = "lang/" . $ln . "/index.php";

	if (file_exists($check_lang)) {
		// get $language varialble
		require $check_lang;
		// redirect
		redirect_to("/pages/chlng.php?lang=" . $language);
	} else {
		fatal_error('Language does not exist!');
	}
}

// redirect to root dir if visitor is using site default language and language is set in url
elseif ($config["siteDefaultLang"] == $config["language"] && $isUrlLangSet == 1) {
	redirect_to("/" . $urlIsset);
}

// get page title
$my_title = $db->get_data('pages', "pname='index' AND lang='" . $ln_loc . "'", 'tname');
if (empty($my_title['tname'])) {
	$my_title = $db->get_data('pages', "pname='index'", 'tname');
}
$my_title = $my_title['tname'];
if (empty($my_title)) { $my_title = ''; }

// load theme
include_once("themes/" . $config_themes . "/index.php");

// show "isset" message
if (isset($_GET['isset'])) {
	$isset = check($_GET['isset']);
	echo '<div align="center"><b><font color="#FF0000">';
	echo get_isset();
	echo '</font></b></div>';
}

// load main page
// check for chached page
if (file_exists(BASEDIR . "used/datamain/index!." . $ln_loc . "!.php")) {
	include BASEDIR . "used/datamain/index!." . $ln_loc . "!.php";
} elseif (file_exists(BASEDIR . "used/datamain/index.php")) {
	include("used/datamain/index.php");
} else {
	// load from database if there is no cached page
	$open_page_lng = $db->get_data('pages', "pname='index' AND lang='" . $ln_loc . "'", 'content');

	if (!empty($open_page_lng)) {
		echo $open_page_lng['content'];
	} else {
		$open_page = $db->select('pages', "pname='index'", '', '*');

		echo $open_page['content'];
	}
}

include_once"themes/" . $config_themes . "/foot.php";

?>