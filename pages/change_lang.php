<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

 // Get language
$language = $vavok->post_and_get('lang');

// Page to load after changing language
$ptl = $vavok->post_and_get('ptl'); 

if (!file_exists(BASEDIR . "include/lang/" . $vavok->go('users')->get_prefered_language($language) . "/index.php")) {
	$vavok->redirect_to(HOMEDIR . "index.php?error=no_lang");
} else {
	include_once BASEDIR . 'include/lang/' . $vavok->go('users')->get_prefered_language($language) . '/index.php';
}

// Set new language
if (!empty($language)) $vavok->go('users')->change_language($language);

// ignore language url's, /index.php will do the work
if ($ptl == '/en/' || $ptl == '/sr/' || $ptl == '/fr/' || $ptl == '/de/' || $ptl == '/ru/' || $ptl == '/js/') $ptl = '';

if (!empty($ptl)) {
	$vavok->redirect_to($ptl);
} else {
	$vavok->redirect_to('../');
}
?>
