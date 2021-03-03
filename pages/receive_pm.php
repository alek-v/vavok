<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   03.03.2021. 20:36:09
 */
require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg()) $vavok->redirect_to("../pages/login.php");

$who = isset($_GET["who"]) ? $vavok->check($_GET["who"]) : '';
$last_id = isset($_GET["lastid"]) ? $vavok->check($_GET["lastid"]) : '';

// if there is last message id set
if (isset($last_id) && !empty($last_id)) {

	$sql = "SELECT * FROM inbox WHERE id > {$last_id} AND ((byuid = {$who} OR touid = {$vavok->go('users')->user_id}) or (byuid = {$vavok->go('users')->user_id} OR touid = {$who})) ORDER BY id DESC LIMIT 1";

} else {

	// no last id, load unread message
	$sql = "SELECT * FROM inbox WHERE ((byuid = {$who} OR touid = {$vavok->go('users')->user_id}) or (byuid = {$vavok->go('users')->user_id} OR touid = {$who})) ORDER BY id DESC LIMIT 1";

	}

foreach($vavok->go('db')->query($sql) as $item) {

	echo $vavok->go('users')->getnickfromid($item['byuid']) . ':|:' . $vavok->go('users')->parsepm($item['text']) . ':|:' . $item['id'] . ':|:' . $item['byuid'] . ':|:' . date("d m y - h:i:s", $item['timesent']);

	// update read status
	if ($vavok->go('users')->user_id == $item['touid']) {
		$vavok->go('db')->update('inbox', 'unread', 0, "id = {$item['id']} LIMIT 1");
	}

}

?>