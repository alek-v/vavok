<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg()) $vavok->redirect_to('../pages/login.php');

$pmtext = !empty($vavok->post_and_get('pmtext')) ? $vavok->post_and_get('pmtext') : '';
$who = !empty($vavok->post_and_get('who')) ? $vavok->post_and_get('who') : '';

// dont send message to system
if ($who == 0 || empty($who)) exit;

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