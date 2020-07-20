<?php

include"../include/strtup.php";

if (!$users->is_reg()) { redirect_to("../pages/login.php"); }

$pmtext = isset($_POST["pmtext"]) ? $pmtext = check($_POST["pmtext"]) : $pmtext = '';
$who = isset($_POST["who"]) ? $who = check($_POST["who"]) : $who = '';

// dont send message to system
if ($who == 1) { redirect_to('inbox.php?who=1'); }

$inbox_notif = $db->get_data('notif', "uid='{$user_id}' AND type='inbox'", 'active');

$whonick = $users->getnickfromid($who);
$byuid = $user_id;

$stmt = $db->query("SELECT MAX(timesent) FROM inbox WHERE byuid='{$byuid}'");
$lastpm = (integer) $stmt->fetch(PDO::FETCH_COLUMN);
$stmt->closeCursor();

$pmfl = $lastpm + 0; // 0 is $config["floodTime"] // return in production

if ($pmfl < time()) {
    if (!$users->isignored($byuid, $who)) {

        $users->send_pm($pmtext, $user_id, $who);

        echo 'sent';

    } else {

        echo 'not_sent';

    } 
}

?>