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
// todo: fix error reporting
// header("Status: 404 Not Found");
header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");

$vavok->require_header();
?>

<p>Error 404 - Page not found<br /></p>
<div class="break"></div>
<?php echo $vavok->homelink('<p>', '</p>'); ?>
<div class="break"></div>

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
	echo '<p>Requested page is not published.</p>'; // update lang
	echo '<p><br /><br />';
	echo $vavok->homelink() . '</p>';

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