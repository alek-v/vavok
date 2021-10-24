<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_administrator() && !$vavok->go('users')->is_moderator(103)) $vavok->redirect_to('../index.php?error');

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('banlist');
$vavok->require_header();

echo '<p><img src="../themes/images/img/partners.gif" alt=""> <b>' . $vavok->go('localization')->string('banlist') . '</b></p>'; 

// Number of banned users
$noi = $vavok->go('users')->total_banned();
$items_per_page = 10;

$navigation = new Navigation($items_per_page, $noi, $vavok->post_and_get('page'), 'banlist.php?'); // start navigation
$limit_start = $navigation->start()['start']; // starting point

$sql = "SELECT id, name, banned FROM vavok_users WHERE banned='1' OR banned='2' ORDER BY banned LIMIT $limit_start, $items_per_page";

if ($noi > 0) {
    foreach ($vavok->go('db')->query($sql) as $item) {
        if ($item['banned'] == 1) {
            echo '<div class="a"><p>' . $vavok->sitelink('../pages/user.php?uz=' . $item['name'], $item['name']) . ' <small>' . $vavok->go('localization')->string('banduration') . ': ' . $vavok->date_fixed($vavok->go('users')->user_info('bantime', $item['id']), 'd.m.y.') . ' | ' . $vavok->go('localization')->string('bandesc') . ': ' . $vavok->go('users')->user_info('bandesc', $item['id']) . '</small></p></div>';
        }
    }
} else {
    echo $vavok->show_notification('<img src="../themes/images/img/reload.gif" alt="" /> ' . $vavok->go('localization')->string('noentry'));
}

echo $navigation->get_navigation();

echo '<p>';
echo $vavok->sitelink('./', $vavok->go('localization')->string('admpanel')) . '<br />';
echo $vavok->homelink();
echo '</p>';

$vavok->require_footer();

?>
