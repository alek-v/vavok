<?php 
// (c) vavok.net
require_once"../include/startup.php";

if (isset($_POST['newpar'])) {$newpar = $vavok->check($_POST['newpar']);}
if (isset($_POST['newpar2'])) {$newpar2 = $vavok->check($_POST['newpar2']);}
if (isset($_POST['oldpar'])) {$oldpar = $vavok->check($_POST['oldpar']);}



if ($users->is_reg()) {
    
    $check_pass = $db->get_data('vavok_users', "id='{$users->user_id}'", 'pass');
		
    $newpar = $vavok->check($newpar);
    $oldpar = $vavok->check($oldpar);

    if ($newpar == $newpar2) {

        if ($users->password_check($oldpar, $check_pass['pass'])) {

            // write changes
            $newpass = $users->password_encrypt($newpar);

            $db->update('vavok_users', 'pass', $newpass, "id='{$users->user_id}'");

            setcookie('cookpar', '');
            setcookie('cooklog', '');
            session_unset();
            setcookie(session_name(), '');
            session_destroy();

            $vavok->redirect_to(website_home_address() . "/pages/input.php?log=" . $users->getnickfromid($users->user_id) . "&pass=" . $newpar . "&isset=editpass");

        } else {
            $vavok->redirect_to("profile.php?isset=nopass");
        } 
    } else {
        $vavok->redirect_to("profile.php?isset=nonewpass");
    } 
} else {
    $vavok->redirect_to("../index.php?isset=inputoff");
} 

?>