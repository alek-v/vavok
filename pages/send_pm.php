<?php

include"../include/startup.php";

if (!$users->is_reg()) { $vavok->redirect_to("../pages/login.php"); }

$pmtext = isset($_POST["pmtext"]) ? $pmtext = $vavok->check($_POST["pmtext"]) : $pmtext = '';
$who = isset($_POST["who"]) ? $who = $vavok->check($_POST["who"]) : $who = '';

// dont send message to system
if ($who == 1) { $vavok->redirect_to('inbox.php?who=1'); }

$inbox_notif = $db->get_data('notif', "uid='{$users->user_id}' AND type='inbox'", 'active');

$whonick = $users->getnickfromid($who);
$byuid = $users->user_id;

$stmt = $db->query("SELECT MAX(timesent) FROM inbox WHERE byuid='{$byuid}'");
$lastpm = (integer) $stmt->fetch(PDO::FETCH_COLUMN);
$stmt->closeCursor();

$pmfl = $lastpm + 0; // 0 is $vavok->get_configuration("floodTime")

if ($pmfl < time()) {
    if (!$users->isignored($byuid, $who)) {

        $users->send_pm($pmtext, $users->user_id, $who);

        echo 'sent';

    } else {

        echo 'not_sent';

    } 
}

?>