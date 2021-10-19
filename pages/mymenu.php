<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

$vavok->require_header();

if ($vavok->go('users')->is_reg()) {
	echo $vavok->sitelink(HOMEDIR . 'pages/inbox.php', $vavok->go('localization')->string('inbox') . ' (' . $vavok->go('users')->user_mail($vavok->go('users')->user_id) . ')', '', '<br />') . 
	$vavok->sitelink(HOMEDIR . 'pages/ignor.php', $vavok->go('localization')->string('ignorlist'), '', '<br />') .
	$vavok->sitelink(HOMEDIR . 'pages/buddy.php', $vavok->go('localization')->string('contacts'), '', '<br />') .
	$vavok->sitelink(HOMEDIR . 'pages/profile.php', $vavok->go('localization')->string('updprof'), '', '<br />') .
	$vavok->sitelink(HOMEDIR . 'pages/settings.php', $vavok->go('localization')->string('settings'), '', '<br />') .
	$vavok->sitelink(HOMEDIR . 'pages/input.php?action=exit', $vavok->go('localization')->string('logout'));
} else {
    echo $vavok->show_danger($vavok->go('localization')->string('notloged'));
}

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>