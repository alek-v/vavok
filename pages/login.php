<?php
// (c) vavok.net - Aleksandar Vranešević

require_once"../include/startup.php";

// meta tag for this page
$genHeadTag = '<meta name="robots" content="noindex">';
$genHeadTag .= '<link rel="stylesheet" href="../themes/templates/pages/login/login.css">';

$my_title = $lang_home['login'];
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

$cookName = isset($_COOKIE['cookname']) ? $cookName = $_COOKIE['cookname'] : $cookName = '';

// page template
$current_page = new PageGen('pages/login/login.tpl');

if (!empty($cookName)) {
	$current_page->set('username', check($cookName));
}

if (!empty($_GET['ptl'])) {
	$current_page->set('page_to_load', check($_GET['ptl']));
}

// Show page
echo $current_page->output();

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";
?>