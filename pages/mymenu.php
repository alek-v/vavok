<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   29.08.2020. 1:34:19
 */

require_once"../include/startup.php";

$vavok->require_header();

if ($users->is_reg()) {
	echo '
	<a href="' . BASEDIR . 'pages/inbox.php" class="btn btn-outline-primary sitelink">' . $localization->string('inbox') . ' (' . $users->user_mail($users->user_id) . ')</a><br>
	<a href="' . BASEDIR . 'pages/ignor.php" class="btn btn-outline-primary sitelink">' . $localization->string('ignorlist') . '</a><br>
	<a href="' . BASEDIR . 'pages/buddy.php" class="btn btn-outline-primary sitelink">' . $localization->string('contacts') . '</a><br>
	<a href="' . BASEDIR . 'pages/profile.php" class="btn btn-outline-primary sitelink">' . $localization->string('updprof') . '</a><br>
	<a href="' . BASEDIR . 'pages/settings.php" class="btn btn-outline-primary sitelink">' . $localization->string('settings') . '</a><br> 
	<a href="' . BASEDIR . 'pages/input.php?action=exit" class="btn btn-outline-primary sitelink">' . $localization->string('logout') . '</a><br>
	';
} else {
    echo '<p>' . $localization->string('notloged') . '</p>';
} 

$vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>