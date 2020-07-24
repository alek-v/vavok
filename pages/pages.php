<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       http://vavok.net
* Updated:   24.07.2020. 14:03:16
*/

include_once"../include/startup.php";

// Page name
$pg = isset($_GET['pg']) ? check($_GET['pg']) : '';

/*
    redirect to url_rewrite url
    when user is redirected from http:// to https:// page
    it redirect to non-rewriten url
*/
if (stristr($_SERVER['REQUEST_URI'], 'pages.php?pg=')) {
	redirect_to(BASEDIR . 'page/' . $pg . '/');
}

/* Page not found */
if (empty($current_page->page_content)) {
// error 404
// todo: fix error reporting
// header("Status: 404 Not Found");
header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");

require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

?>

<p>Error 404 - Page not found<br /></p>
<div class="break"></div>
<p><a href="<?php echo website_home_address(); ?>" class="btn btn-primary homepage"><?php echo $lang_home['home']; ?></a></p>
<div class="break"></div>

<?php

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";
exit;
}

/* Show page */

// load template
$this_page = new PageGen('pages/page/page.tpl');

// load theme
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

// Check if page is published
if ($current_page->published == 1 && !$users->is_administrator()) {
	echo '<p>Requested page is not published.</p>'; // update lang
	echo '<p><br /><br />';
	echo '<a href="' . website_home_address() . '" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';

	require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";
	exit;
}

// page content
$this_page->set('content', $current_page->page_content);

// facebook comments
if ($config["pgFbComm"] == 1) {
	$this_page->set('facebook_comments', $this_page->facebook_comments($config_srvhost, $clean_requri));
}

// homepage address
$this_page->set('homepage_url', website_home_address()); // homepage url

// show page
echo $this_page->output();

// load footer
require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>