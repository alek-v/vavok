<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       http://vavok.net
* Updated:   01.08.2020. 19:21:54
*/

include_once"../include/startup.php";

// Page name
$pg = isset($_GET['pg']) ? $vavok->check($_GET['pg']) : '';

/*
    redirect to url_rewrite url
    when user is redirected from http:// to https:// page
    it redirect to non-rewriten url
*/
if (stristr($_SERVER['REQUEST_URI'], 'pages.php?pg=')) {
	$vavok->redirect_to(BASEDIR . 'page/' . $pg . '/');
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
<p><a href="<?php echo $vavok->website_home_address(); ?>" class="btn btn-primary homepage"><?php echo $localization->string('home'); ?></a></p>
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
	echo '<a href="' . $vavok->website_home_address() . '" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

	require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";
	exit;
}

// page content
$this_page->set('content', $current_page->page_content);

// facebook comments
if ($vavok->get_configuration('pgFbComm') == 1) $this_page->set('facebook_comments', $this_page->facebook_comments());

// homepage address
$this_page->set('homepage_url', $vavok->website_home_address()); // homepage url

// show page
echo $this_page->output();

// load footer
require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>