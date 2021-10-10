<?php

require_once '../include/startup.php';

// Redirect if token does not exist
if ($vavok->go('db')->count_row(DB_PREFIX . 'tokens', "type = 'email' AND token = '{$vavok->post_and_get('token')}'") < 1) $vavok->redirect_to(HOMEDIR . 'pages/profile.php?isset=notoken');

// Get token data
$data = $vavok->go('db')->get_data(DB_PREFIX . 'tokens', "type = 'email' AND token = '{$vavok->post_and_get('token')}'");

// Update email
$vavok->go('users')->update_user('email', $data['content'], $data['uid']);

// Remove token
$vavok->go('db')->delete(DB_PREFIX . 'tokens', "type = 'email' AND token = '{$vavok->post_and_get('token')}'");

$vavok->redirect_to(HOMEDIR . 'pages/profile.php?isset=editprofile');

?>