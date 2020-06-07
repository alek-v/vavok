<?php
// (c) vavok.net - Aleksandar Vranešević

require_once"../include/strtup.php";

// meta tag for this page
$genHeadTag = '<meta name="robots" content="noindex">';
$genHeadTag .= '<link rel="stylesheet" href="../themes/templates/pages/login/login.css">';

$mediaLikeButton = 'off'; // dont show like buttons

$my_title = $lang_home['login'];
include_once"../themes/$config_themes/index.php";

$cookName = isset($_COOKIE['cookname']) ? $cookName = $_COOKIE['cookname'] : $cookName = '';

// page template
$current_page = new PageGen('pages/login/login.tpl');

if (!empty($cookName)) {
	$current_page->set('username', check($cookName));
}

if (!empty($_GET['ptl'])) {
	$current_page->set('page_to_load', check($_GET['ptl']));
}

// show page
echo $current_page->output();


include_once"../themes/$config_themes/foot.php";
?>