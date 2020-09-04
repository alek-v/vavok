<?php 
// (c) vavok.net
require_once '../include/startup.php';

if (isset($_POST['newpar'])) {$newpar = $vavok->check($_POST['newpar']);}
if (isset($_POST['newpar2'])) {$newpar2 = $vavok->check($_POST['newpar2']);}
if (isset($_POST['oldpar'])) {$oldpar = $vavok->check($_POST['oldpar']);}

if ($vavok->go('users')->is_reg()) {
    $check_pass = $vavok->go('db')->get_data('vavok_users', "id='{$vavok->go('users')->user_id}'", 'pass');

    $newpar = $vavok->check($newpar);
    $oldpar = $vavok->check($oldpar);

    if ($newpar == $newpar2) {
        if ($vavok->go('users')->password_check($oldpar, $check_pass['pass'])) {
            // write changes
            $newpass = $vavok->go('users')->password_encrypt($newpar);

            $vavok->go('db')->update(DB_PREFIX . 'vavok_users', 'pass', $newpass, "id='{$vavok->go('users')->user_id}'");

            setcookie('cookpar', '');
            setcookie('cooklog', '');
            session_unset();
            setcookie(session_name(), '');
            session_destroy();

            $vavok->redirect_to($vavok->website_home_address() . "/pages/input.php?log=" . $vavok->go('users')->getnickfromid($vavok->go('users')->user_id) . "&pass=" . $newpar . "&isset=editpass");

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