<?php
// (c) vavok.net - Aleksandar Vranesevic
require_once("include/strtup.php");


// get error code if it is in url
$urlIsset = isset($_GET['isset']) ? '?isset=' . check($_GET['isset']) : '';

// requested language
$ln = isset($_GET['ln']) ? check($_GET['ln']) : '';

// redirect if user language is not website default language
// and new language is not in URL like (www.example.com/sr/)
if ($config["siteDefaultLang"] != $config["language"] && empty($ln)) {
	redirect_to("/" . $ln_loc . "/" . $urlIsset);
}

// redirect if language is set in url and it is not current lang
// example www.example.com/en/ is requested, but current language is not english
elseif ($ln != $ln_loc && !empty($ln)) {

	// load new language data
	$check_lang = "lang/" . $ln . "/index.php";

	if (file_exists($check_lang)) {
		// redirect to requested language
		redirect_to("/pages/chlng.php?lang=" . $ln);
	} else {
		fatal_error('Language does not exist!');
	}

}

// redirect to root dir if visitor is using site default language and language is set in url
// when default website language is english it should be at www.example.com instead of www.example.com/en/
elseif ($config["siteDefaultLang"] == $config["language"] && !empty($ln)) {
	redirect_to("/" . $urlIsset);
}

// get page title
// first we check is there a page with language we use
$my_title = $db->get_data('pages', "pname='index' AND lang='" . $ln_loc . "'", 'tname');
if (empty($my_title['tname'])) {
	// load default index page title
	$my_title = $db->get_data('pages', "pname='index'", 'tname');
}

$my_title = !empty($my_title['tname']) ? $my_title['tname'] : '';

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
// first we check is there a page with language we use
if (file_exists(BASEDIR . "used/datamain/index!." . $ln_loc . "!.php")) {
	include BASEDIR . "used/datamain/index!." . $ln_loc . "!.php";
} elseif (file_exists(BASEDIR . "used/datamain/index.php")) {
	// load default index page title
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

// load website footer
include_once"themes/" . $config_themes . "/foot.php";

?>