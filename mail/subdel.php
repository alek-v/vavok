<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   26.07.2020. 17:13:30
*/

require_once"../include/startup.php";
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

$code = !empty($_GET['subdel']) ? check($_GET['subdel']) : '';
$subscriptionName = !empty($_GET['sn']) ? check($_GET['sn']) : '';

if (!empty($code)) {
    if (preg_match("/[^a-z0-9]/", $code)) {
        $email_check = $db->get_data('subs', "user_pass='" . $code . "'");
        if ($code == $email_check['user_pass'] && (empty($email_check['subscripton_name']) || $email_check['subscripton_name'] == $subscriptionName)) {
            if ($email_check['user_id'] > 0 && ($subscriptionName == 'sitenews' || empty($subscriptionName))) {
                $uz_log = $users->getnickfromid($email_check['user_id']);
                $db->update('vavok_profil', array('subscri', 'newscod'), array(0, ''), "uid='" . $email_check['user_id'] . "'");
            } 

            $db->delete('subs', "user_pass='{$code}'");

            echo '<p>' . $localization->string('hello') . ' ' . $uz_log . '!</p>
            <p>' . $localization->string('delsubok') . '!</p>';
        } else {
            echo '<p>' . $localization->string('unsubcodefail') . '!</p>'; // code does not match
        } 
    } else {
        echo '<p>' . $localization->string('unsubcodefail') . '!</p>'; // bad code
    } 
} else {
    echo '<p>' . $localization->string('unsubfail') . '!</p>'; // code 
} 

echo '<p><a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>