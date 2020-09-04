<?php
// (c) vavok.net - Aleksandar Vranešević

require_once '../include/startup.php';

// meta tag for this page
$vavok->go('current_page')->append_head_tags('<meta name="robots" content="noindex">');
$vavok->go('current_page')->append_head_tags('<link rel="stylesheet" href="../themes/templates/pages/login/login.css">');

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('login');
$vavok->require_header();

$cookName = isset($_COOKIE['cookname']) ? $cookName = $_COOKIE['cookname'] : $cookName = '';

// page template
$current_page = new PageGen('pages/login/login.tpl');

if (!empty($cookName)) {
	$vavok->go('current_page')->set('username', $vavok->check($cookName));
}

if (!empty($_GET['ptl'])) {
	$vavok->go('current_page')->set('page_to_load', $vavok->check($_GET['ptl']));
}

// Show page
echo $vavok->go('current_page')->output();

$vavok->require_footer();
?>