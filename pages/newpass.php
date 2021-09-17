<?php 
// (c) vavok.net
require_once '../include/startup.php';

if ($vavok->go('users')->is_reg()) {
    if ($vavok->post_and_get('newpar') == $vavok->post_and_get('newpar2')) {
        // Check if old password is correct
        if ($vavok->go('users')->password_check($vavok->post_and_get('oldpar'), $vavok->go('users')->get_user_info($vavok->go('users')->user_id, 'password'))) {
            // Update password
            $vavok->go('db')->update(DB_PREFIX . 'vavok_users', 'pass', $vavok->go('users')->password_encrypt($vavok->post_and_get('newpar')), "id='{$vavok->go('users')->user_id}'");

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