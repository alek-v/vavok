<?php 
// (c) vavok.net
require_once '../include/startup.php';

if ($vavok->go('users')->is_reg()) {
    $check_pass = $vavok->go('db')->get_data('vavok_users', "id='{$vavok->go('users')->user_id}'", 'pass');

    if ($vavok->post_and_get('newpar') == $vavok->post_and_get('newpar2')) {
        if ($vavok->go('users')->password_check($vavok->post_and_get('oldpar'), $check_pass['pass'])) {
            // write changes
            $newpass = $vavok->go('users')->password_encrypt($vavok->post_and_get('newpar'));

            $vavok->go('db')->update(DB_PREFIX . 'vavok_users', 'pass', $newpass, "id='{$vavok->go('users')->user_id}'");

            $vavok->go('users')->logout();

            $vavok->redirect_to($vavok->website_home_address() . "/pages/input.php?log=" . $vavok->go('users')->getnickfromid($vavok->go('users')->user_id) . "&pass=" . $vavok->post_and_get('newpar') . "&isset=editpass");
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