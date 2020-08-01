<?php
include"../include/startup.php";

if (!$users->is_reg()) $vavok->redirect_to("../pages/login.php");

$who = isset($_GET["who"]) ? $vavok->check($_GET["who"]) : '';
$last_id = isset($_GET["lastid"]) ? $vavok->check($_GET["lastid"]) : '';

// if there is last message id set
if (isset($last_id) && !empty($last_id)) {

	$sql = "SELECT * FROM inbox WHERE id > {$last_id} AND ((byuid = {$who} OR touid = {$users->user_id}) or (byuid = {$users->user_id} OR touid = {$who})) ORDER BY id DESC LIMIT 1";

} else {

	// no last id, load unread message
	$sql = "SELECT * FROM inbox WHERE ((byuid = {$who} OR touid = {$users->user_id}) or (byuid = {$users->user_id} OR touid = {$who})) ORDER BY id DESC LIMIT 1";

	}

foreach($db->query($sql) as $item) {

	echo $users->getnickfromid($item['byuid']) . ':|:' . $users->parsepm($item['text']) . ':|:' . $item['id'] . ':|:' . $item['byuid'] . ':|:' . date("d m y - h:i:s", $item['timesent']);

	// update read status
	if ($users->user_id == $item['touid']) {
		$db->update('inbox', 'unread', 0, "id = {$item['id']} LIMIT 1");
	}

}

?>