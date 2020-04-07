<?php 
// (c) vavok.net - Aleksandar Vranešević
include_once"include/strtup.php";

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
	redirect_to('page/' . $pg . '/');
}

// load page with language specified
$loadPage = new Page;

// check of there is current page with user's language
if ($loadPage->page_exists('', "pname='" . $pg . "' AND lang='" . $ln_loc . "'")) {
	$page_data = $db->get_data('pages', "pname='" . $pg . "' AND lang='" . $ln_loc . "'", '*');
} elseif ($loadPage->page_exists('', "pname='" . $pg . "'")) {
	// get page if there is no user's language page
	$page_data = $db->get_data('pages', "pname='" . $pg . "'", '*');
} else {
// error 404
// todo: fix error reporting
// header("Status: 404 Not Found");
header(check($_SERVER["SERVER_PROTOCOL"]) . " 404 Not Found");

include"themes/$config_themes/index.php";

?>

<p>Error 404 - Page not found<br /></p>
<div class="break"></div>
<p><a href="/" class="homepage">Home page</a></p>
<div class="break"></div>

<?php

include"themes/$config_themes/foot.php";
exit;
}

// get page title
if (!empty($page_data['tname'])) {
    $my_title = $page_data['tname'];
}

// load theme
include"themes/" . $config_themes . "/index.php";

// check is it published
if ($page_data['published'] == 1 && !$users->is_administrator()) {
	echo '<p>Requested page is not published.</p>'; // update lang
	echo '<p><br /><br />';
	echo '<a href="' . $connectionProtocol . $config_srvhost . '" class="homepage">' . $lang_home['home'] . '</a></p>';

include"themes/$config_themes/foot.php";
exit;
}

// page content
echo $page_data['content'];

// facebook comments
if ($config["pgFbComm"] == 1) {
    echo '<div class="fb-comments" data-href="' . $media_page_url . '" data-width="470" data-num-posts="10"></div>';
}

echo '<p><a href="' . $connectionProtocol . $config_srvhost . '" class="homepage">' . $lang_home['home'] . '</a></p>';

// load footer
include"themes/$config_themes/foot.php";

?>