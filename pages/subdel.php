<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   29.08.2020. 3:16:33
*/

require_once"../include/startup.php";
$vavok->require_header();

$code = !empty($_GET['subdel']) ? $vavok->check($_GET['subdel']) : '';
$subscriptionName = !empty($_GET['sn']) ? $vavok->check($_GET['sn']) : '';

if (!empty($code)) {
    if (preg_match("/[^a-z0-9]/", $code)) {
        $email_check = $db->get_data('subs', "user_pass='" . $code . "'");
        if ($code == $email_check['user_pass'] && (empty($email_check['subscripton_name']) || $email_check['subscripton_name'] == $subscriptionName)) {
            if ($email_check['user_id'] > 0 && ($subscriptionName == 'sitenews' || empty($subscriptionName))) {
                $uz_log = $users->getnickfromid($email_check['user_id']);
                $db->update('vavok_profil', array('subscri', 'newscod'), array(0, ''), "uid='" . $email_check['user_id'] . "'");
            }

            $db->delete('subs', "user_pass='{$code}'");

            echo $localization->string('hello') . ' ' . $uz_log . '!<br>' . $localization->string('delsubok') . '!<br><br>';
        } else {
            echo $localization->string('unsubcodefail') . '!<br><br>'; // code does not match
        }
    } else {
        echo $localization->string('unsubcodefail') . '!<br><br>'; // bad code
    }
} else {
    echo $localization->string('unsubfail') . '!<br><br>'; // code 
}

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>