<?php

require_once '../include/startup.php';

$token = isset($_GET['token']) ? $vavok->check($_GET['token']) : '';

/**
 * Update profile if token exist
 */
if ($vavok->go('db')->count_row(DB_PREFIX . 'email_confirm', "type = 'email' AND token = '{$token}'") > 0) {
	/**
	 * Get token data
	 */
	$data = $vavok->go('db')->get_data(DB_PREFIX . 'email_confirm', "type = 'email' AND token = '{$token}'");

	/**
	 * Update email
	 */
	$vavok->go('db')->update(DB_PREFIX . 'vavok_about', 'email', $data['content'], "uid = '{$data['uid']}'");

	/**
	 * Remove token
	 */
	$vavok->go('db')->delete(DB_PREFIX . 'email_confirm', "type = 'email' AND token = '{$token}'");

	$vavok->redirect_to(HOMEDIR . "pages/profile.php?isset=editprofile");
} else {
	$vavok->redirect_to(HOMEDIR . "pages/profile.php?isset=notoken");
}


?>