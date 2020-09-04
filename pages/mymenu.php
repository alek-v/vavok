<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   04.09.2020. 23:12:21
 */

require_once '../include/startup.php';

$vavok->require_header();

if ($vavok->go('users')->is_reg()) {
	echo '
	<a href="' . BASEDIR . 'pages/inbox.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('inbox') . ' (' . $vavok->go('users')->user_mail($vavok->go('users')->user_id) . ')</a><br>
	<a href="' . BASEDIR . 'pages/ignor.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('ignorlist') . '</a><br>
	<a href="' . BASEDIR . 'pages/buddy.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('contacts') . '</a><br>
	<a href="' . BASEDIR . 'pages/profile.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('updprof') . '</a><br>
	<a href="' . BASEDIR . 'pages/settings.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('settings') . '</a><br> 
	<a href="' . BASEDIR . 'pages/input.php?action=exit" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('logout') . '</a><br>
	';
} else {
    echo '<p>' . $vavok->go('localization')->string('notloged') . '</p>';
} 

$vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>