<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

include_once '../include/startup.php';

/**
 * redirect to url_rewrite url
 * when user is redirected from http:// to https:// page
 * it redirect to non-rewriten url
 */
if (stristr($_SERVER['REQUEST_URI'], 'pages.php?pg=')) $vavok->redirect_to(BASEDIR . 'page/' . $vavok->post_and_get('pg') . '/');

/* Page not found */
if (empty($vavok->go('current_page')->page_content)) {
// error 404
// header("Status: 404 Not Found");
header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");

$vavok->require_header();
?>

<p class="mb-5">Error 404 - Page not found</p>
<?php echo $vavok->homelink('<p>', '</p>'); ?>

<?php

$vavok->require_footer();
exit;
}

/* Show page */

// load template
$this_page = new PageGen('pages/page/page.tpl');

// load theme
$vavok->require_header();

// Check if page is published
if ($vavok->go('current_page')->published == 1 && !$vavok->go('users')->is_administrator()) {
	$vavok->show_notification('Requested page is not published.'); // update lang
	echo $vavok->homelink();

	$vavok->require_footer();
	exit;
}

// page content
$this_page->set('content', $vavok->go('current_page')->page_content);

// facebook comments
if ($vavok->get_configuration('pgFbComm') == 1) $this_page->set('facebook_comments', $this_page->facebook_comments());

// homepage address
$this_page->set('homepage_url', $vavok->website_home_address()); // homepage url

// show page
echo $this_page->output();

// load footer
$vavok->require_footer();

?>