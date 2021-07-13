<?php
// (c) vavok.net - Aleksandar Vranešević

require_once '../include/startup.php';

// meta tag for this page
$vavok->go('current_page')->append_head_tags('<meta name="robots" content="noindex">');
$vavok->go('current_page')->append_head_tags('<link rel="stylesheet" href="' . HOMEDIR . 'themes/templates/pages/login/login.css">');

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('login');
$vavok->require_header();

$cookName = isset($_COOKIE['cookname']) ? $cookName = $_COOKIE['cookname'] : $cookName = '';

// page template
$login = new PageGen('pages/login/login.tpl');

if (!empty($cookName)) $login->set('username', $vavok->check($cookName));

if (!empty($vavok->post_and_get('ptl'))) $login->set('page_to_load', $vavok->post_and_get('ptl'));

// Show page
echo $login->output();

$vavok->require_footer();
?>