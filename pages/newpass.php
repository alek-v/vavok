<?php 
// (c) vavok.net
require_once"../include/strtup.php";

if (isset($_POST['newpar'])) {$newpar = check($_POST['newpar']);}
if (isset($_POST['newpar2'])) {$newpar2 = check($_POST['newpar2']);}
if (isset($_POST['oldpar'])) {$oldpar = check($_POST['oldpar']);}

$mediaLikeButton = 'off'; // dont show like buttons

if ($users->is_reg()) {
    
    $check_pass = $db->select('vavok_users', "id='" . $user_id . "'", '', 'pass');
		
    $newpar = check($newpar);
    $oldpar = check($oldpar);

    if ($newpar == $newpar2) {

        if ($users->password_check($oldpar, $check_pass['pass'])) {

            // write changes
            $newpass = $users->password_encrypt($newpar);

            $db->update('vavok_users', 'pass', $newpass, "id='" . $user_id . "'");

            setcookie('cookpar', '');
            setcookie('cooklog', '');
            session_unset();
            setcookie(session_name(), '');
            session_destroy();

            redirect_to(website_home_address() . "/pages/input.php?log=" . $users->getnickfromid($user_id) . "&pass=" . $newpar . "&isset=editpass");

        } else {
            redirect_to("profile.php?isset=nopass");
        } 
    } else {
        redirect_to("profile.php?isset=nonewpass");
    } 
} else {
    redirect_to("../index.php?isset=inputoff");
} 

?>