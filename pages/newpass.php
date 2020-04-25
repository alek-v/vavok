<?php 
// (c) vavok.net
require_once"../include/strtup.php";

if (isset($_POST['newpar'])) {$newpar = check($_POST['newpar']);}
if (isset($_POST['newpar2'])) {$newpar2 = check($_POST['newpar2']);}
if (isset($_POST['oldpar'])) {$oldpar = check($_POST['oldpar']);}

if (preg_match("/[^a-zA-Z0-9-]/", $newpar)) {
    header ("Location: profil.php?isset=inlogin");
    exit;
} 

$mediaLikeButton = 'off'; // dont show like buttons

if (is_reg()) {
    $check_pass = $db->select('vavok_users', "id='" . $user_id . "'", '', 'pass');
		
    $newpar = check($newpar);
    $oldpar = check($oldpar);
    if ($newpar == $newpar2) {
        if (md5($oldpar) == $check_pass['pass']) {
            // write changes
            $newpass = md5($newpar);

            $db->update('vavok_users', 'pass', $newpass, "id='" . $user_id . "'");

            setcookie('cookpar', '');
            setcookie('cooklog', '');
            session_unset();
            setcookie(session_name(), '');
            session_destroy();

            header("Location: " . transfer_protocol() . $config_srvhost . "/input.php?log=" . getnickfromid($user_id) . "&pass=" . $newpar . "&isset=editpass");
            exit;
        } else {
            header ("Location: profil.php?isset=nopass");
            exit;
        } 
    } else {
        header ("Location: profil.php?isset=nonewpass");
        exit;
    } 
} else {
    header ("Location: ../index.php?isset=inputoff");
    exit;
} 

?>