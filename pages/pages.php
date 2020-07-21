<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       http://vavok.net
* Updated:   21.06.2020. 12:50:31
*/

include_once"../include/startup.php";

// page name
$pg = isset($_GET['pg']) ? check($_GET['pg']) : '';

// redirect if it is index page or page is not set
if ($pg == 'index' || empty($pg)) {
	redirect_to(BASEDIR . "/");
}

// redirect to url_rewrite url
// when user is redirected from http:// to https:// page
// it redirect to non-rewriten url
if (stristr($_SERVER['REQUEST_URI'], 'pages.php?pg=')) {
	redirect_to(BASEDIR . 'page/' . $pg . '/');
}

// load page with language specified
$loadPage = new Page;

// check of there is current page with user's language
if ($loadPage->page_exists('', "pname='{$pg}' AND lang='{$ln_loc}'")) {

	$page_data = $loadPage->select_page($loadPage->page_exists('', "pname='{$pg}' AND lang='{$ln_loc}'"));

} elseif ($loadPage->page_exists('', "pname='{$pg}'")) { // get page if there is no user's language page
	
	$page_data = $loadPage->select_page($loadPage->page_exists('', "pname='{$pg}'"));

} else {
// error 404
// todo: fix error reporting
// header("Status: 404 Not Found");
header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");

include"../themes/$config_themes/index.php";

?>

<p>Error 404 - Page not found<br /></p>
<div class="break"></div>
<p><a href="<?php echo website_home_address(); ?>" class="btn btn-primary homepage"><?php echo $lang_home['home']; ?></a></p>
<div class="break"></div>

<?php

include"../themes/$config_themes/foot.php";
exit;
}

// page exist in database, show it

// load template
$this_page = new PageGen('pages/page/page.tpl');

// get page title
if (!empty($page_data['tname'])) {
    $my_title = $page_data['tname'];
}

// Show page language
// <html lang="(lang)">
if (!empty($page_data['lang'])) {
	define("PAGE_LANGUAGE", ' lang="' . $page_data['lang'] . '"');
}

// load theme
include"../themes/" . $config_themes . "/index.php";

// check is it published
if ($page_data['published'] == 1 && !$users->is_administrator()) {
	echo '<p>Requested page is not published.</p>'; // update lang
	echo '<p><br /><br />';
	echo '<a href="' . website_home_address() . '" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';

	include"../themes/$config_themes/foot.php";
	exit;
}

// page content
$this_page->set('content', $page_data['content']);

// facebook comments
if ($config["pgFbComm"] == 1) {
	$this_page->set('facebook_comments', $this_page->facebook_comments($config_srvhost, $clean_requri));
}

// homepage address
$this_page->set('homepage_url', website_home_address()); // homepage url

// show page
echo $this_page->output();

// load footer
include"../themes/$config_themes/foot.php";

?>