<?php 
// (c) vavok.net
require_once"../include/startup.php";
include_once"../themes/$config_themes/index.php";

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

            $db->delete('subs', "user_pass='" . $code . "'");

            echo $lang_page['hello'] . ' ' . $uz_log . '!<br>' . $lang_page['delsubok'] . '!<br><br>';
        } else {
            echo $lang_page['unsubcodefail'] . '!<br><br>'; // code does not match
        } 
    } else {
        echo $lang_page['unsubcodefail'] . '!<br><br>'; // bad code
    } 
} else {
    echo $lang_page['unsubfail'] . '!<br><br>'; // code 
} 

echo '<a href="../" class="homepage">' . $lang_home['home'] . '</a>';

include_once"../themes/$config_themes/foot.php";

?>