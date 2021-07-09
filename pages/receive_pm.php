<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */
require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg()) $vavok->redirect_to("../pages/login.php");

// if there is last message id set
if (isset($vavok->post_and_get('lastid')) && !empty($vavok->post_and_get('lastid'))) {
	$sql = "SELECT * FROM inbox WHERE id > {$vavok->post_and_get('lastid')} AND ((byuid = {$vavok->post_and_get('who')} OR touid = {$vavok->go('users')->user_id}) or (byuid = {$vavok->go('users')->user_id} OR touid = {$vavok->post_and_get('who')})) ORDER BY id DESC LIMIT 1";
} else {
	// no last id, load unread message
	$sql = "SELECT * FROM inbox WHERE ((byuid = {$vavok->post_and_get('who')} OR touid = {$vavok->go('users')->user_id}) or (byuid = {$vavok->go('users')->user_id} OR touid = {$vavok->post_and_get('who')})) ORDER BY id DESC LIMIT 1";
}

foreach($vavok->go('db')->query($sql) as $item) {
	echo $vavok->go('users')->getnickfromid($item['byuid']) . ':|:' . $vavok->go('users')->parsepm($item['text']) . ':|:' . $item['id'] . ':|:' . $item['byuid'] . ':|:' . date("d m y - h:i:s", $item['timesent']);

	// update read status
	if ($vavok->go('users')->user_id == $item['touid']) {
		$vavok->go('db')->update('inbox', 'unread', 0, "id = {$item['id']} LIMIT 1");
	}
}

?>