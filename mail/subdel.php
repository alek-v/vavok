<?php 
// (c) vavok.net
require_once"../include/strtup.php";
include_once"../themes/$config_themes/index.php";

$mediaLikeButton = 'off'; // dont show like buttons

$code = !empty($_GET['subdel']) ? check($_GET['subdel']) : '';
$subscriptionName = !empty($_GET['sn']) ? check($_GET['sn']) : '';

if (!empty($code)) {
    if (preg_match("/[^a-z0-9]/", $code)) {
        $email_check = $db->get_data('subs', "user_pass='" . $code . "'");
        if ($code == $email_check['user_pass'] && (empty($email_check['subscripton_name']) || $email_check['subscripton_name'] == $subscriptionName)) {
            if ($email_check['user_id'] > 0 && ($subscriptionName == 'sitenews' || empty($subscriptionName))) {
                $uz_log = getnickfromid($email_check['user_id']);
                $db->update('vavok_profil', array('subscri', 'newscod'), array(0, ''), "uid='" . $email_check['user_id'] . "'");
            } 

            $db->delete('subs', "user_pass='" . $code . "'");

            echo '<p>' . $lang_mail['hello'] . ' ' . $uz_log . '!</p>
            <p>' . $lang_mail['delsubok'] . '!</p>';
        } else {
            echo '<p>' . $lang_mail['unsubcodefail'] . '!</p>'; // code does not match
        } 
    } else {
        echo '<p>' . $lang_mail['unsubcodefail'] . '!</p>'; // bad code
    } 
} else {
    echo '<p>' . $lang_mail['unsubfail'] . '!</p>'; // code 
} 

echo '<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a>';

include_once"../themes/$config_themes/foot.php";

?>