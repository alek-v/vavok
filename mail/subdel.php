<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';
$vavok->require_header();

if (!empty($vavok->post_and_get('subdel'))) {
    if (preg_match("/[^a-z0-9]/", $vavok->post_and_get('subdel'))) {
        $email_check = $vavok->go('db')->get_data('subs', "user_pass='" . $vavok->post_and_get('subdel') . "'");
        if ($vavok->post_and_get('subdel') == $email_check['user_pass'] && (empty($email_check['subscripton_name']) || $email_check['subscripton_name'] == $vavok->post_and_get('sn'))) {
            if ($email_check['user_id'] > 0 && ($vavok->post_and_get('sn') == 'sitenews' || empty($vavok->post_and_get('sn')))) {
                $uz_log = $vavok->go('users')->getnickfromid($email_check['user_id']);
                $vavok->go('users')->update_user(array('subscri', 'newscod'), array(0, ''), $email_check['user_id']);
            } 

            $vavok->go('db')->delete('subs', "user_pass='{$vavok->post_and_get('subdel')}'");

            echo '<p>' . $vavok->go('localization')->string('hello') . ' ' . $uz_log . '!</p>
            <p>' . $vavok->go('localization')->string('delsubok') . '!</p>';
        } else {
            echo '<p>' . $vavok->go('localization')->string('unsubcodefail') . '!</p>'; // code does not match
        }
    } else {
        echo '<p>' . $vavok->go('localization')->string('unsubcodefail') . '!</p>'; // bad code
    }
} else {
    echo '<p>' . $vavok->go('localization')->string('unsubfail') . '!</p>'; // code 
}

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>