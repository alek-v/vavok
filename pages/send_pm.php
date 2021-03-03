<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   03.03.2021. 20:37:08
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg()) { $vavok->redirect_to("../pages/login.php"); }

$pmtext = isset($_POST["pmtext"]) ? $pmtext = $vavok->check($_POST["pmtext"]) : $pmtext = '';
$who = isset($_POST["who"]) ? $who = $vavok->check($_POST["who"]) : $who = '';

// dont send message to system
if ($who == 1) { $vavok->redirect_to('inbox.php?who=1'); }

$inbox_notif = $vavok->go('db')->get_data('notif', "uid='{$vavok->go('users')->user_id}' AND type='inbox'", 'active');

$whonick = $vavok->go('users')->getnickfromid($who);
$byuid = $vavok->go('users')->user_id;

$stmt = $vavok->go('db')->query("SELECT MAX(timesent) FROM inbox WHERE byuid='{$byuid}'");
$lastpm = (integer) $stmt->fetch(PDO::FETCH_COLUMN);
$stmt->closeCursor();

$pmfl = $lastpm + 0; // 0 is $vavok->get_configuration("floodTime")

if ($pmfl < time()) {
    if (!$vavok->go('users')->isignored($byuid, $who)) {

        $vavok->go('users')->send_pm($pmtext, $vavok->go('users')->user_id, $who);

        echo 'sent';

    } else {

        echo 'not_sent';

    } 
}

?>