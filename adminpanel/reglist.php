<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->check_permissions(basename(__FILE__))) $vavok->redirect_to('../?auth_error');

if ($vavok->post_and_get('action') == 'conf' && !empty($vavok->post_and_get('usr'))) {
    $fields = array('regche', 'regkey');
    $values = array('', '');
    $vavok->go('users')->update_user($fields, $values, $vavok->post_and_get('usr'));

    $vav_name = $vavok->go('users')->getnickfromid($vavok->post_and_get('usr'));

    $message = $vavok->go('localization')->string('hello') . " " . $vav_name . "!\r\n\r\n" . $vavok->go('localization')->string('sitemod') . " " . $vavok->get_configuration('homeBase') . " " . $vavok->go('localization')->string('confirmedreg') . ".\r\n" . $vavok->go('localization')->string('youcanlog') . ".\r\n\r\n" . $vavok->go('localization')->string('bye') . "!\r\n\r\n\r\n\r\n" . $vavok->go('users')->getnickfromid($vavok->go('users')->user_id) . "\r\n" . ucfirst($vavok->get_configuration('homeBase'));
    $newMail = new Mailer;
    $newMail->queue_email($vavok->go('users')->user_info('email', $vavok->post_and_get('usr')), $vavok->go('localization')->string('msgfrmst') . " " . $vavok->get_configuration('title'), $message, '', '', 'high');

    $vavok->redirect_to('reglist.php?isset=mp_ydelconf');
}

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('uncomfreg');

$vavok->require_header();

if (empty($vavok->post_and_get('action'))) {
    $noi = $vavok->go('users')->total_unconfirmed();
    $num_items = $noi;
    $items_per_page = 20;
    $num_pages = ceil($num_items / $items_per_page);

    if (($vavok->post_and_get('page') > $num_pages) && $vavok->post_and_get('page') != 1) $page = $num_pages;
    $limit_start = ($vavok->post_and_get('page')-1) * $items_per_page;
    if ($limit_start < 0) {
        $limit_start = 0;
    } 

    $sql = "SELECT uid, regche, regdate, lastvst FROM " . DB_PREFIX . "vavok_profil WHERE regche='1' OR regche='2' ORDER BY regdate LIMIT $limit_start, $items_per_page";

    if ($num_items > 0) {
        foreach ($vavok->go('db')->query($sql) as $item) {
            $lnk = '<a href="../pages/user.php?uz=' . $item['uid'] . '" class="sitelink">' . $vavok->go('users')->getnickfromid($item['uid']) . '</a> (' . $vavok->date_fixed($item['regdate'], 'd.m.Y. / H:i') . ')';
            if ($item['regche'] == 1) {
                $bt = $vavok->go('localization')->string('notconfirmed') . '!';
                $bym = '<a href="reglist.php?action=conf&usr=' . $item['uid'] . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('confirms') . '</a>';
            } else {
                $bt = 'Confirmed';
            }

            echo '<p>' . $lnk . ' IP: ' . $vavok->go('users')->user_info('ipadd', $item['uid']) . ' ' . $vavok->go('localization')->string('browser') . ': ' . $vavok->go('users')->user_info('browser', $item['uid']) . ' ' . $bym . '</p>';
        }
    } else {
        echo '<p><img src="../themes/images/img/reload.gif" alt="" /> ' . $vavok->go('localization')->string('emptyunconf') . '!</p>';
    }

    $navigation = new Navigation($items_per_page, $num_items, $vavok->post_and_get('page'), 'reglist.php?');

    echo '<div class="mt-5">';
        echo $navigation->get_navigation();
    echo '</div>';
}

echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('admpanel') . '</a><br />';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();
?>
