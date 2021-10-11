<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->check_permissions(basename(__FILE__))) $vavok->redirect_to('../?auth_error');

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('modlist');
$vavok->require_header();

echo '<p><img src="../themes/images/img/user.gif" alt=""> <b>' . $vavok->go('localization')->string('adminlistl') . '</b></p>'; 

$num_items = $vavok->go('users')->total_admins();
$items_per_page = 10;

$navigation = new Navigation($items_per_page, $num_items, $vavok->post_and_get('page'), 'adminlist.php?'); // start navigation

$limit_start = $navigation->start()['start']; // starting point
$end = $navigation->start()['end']; // ending point

if ($num_items > 0) {
    foreach ($vavok->go('db')->query("SELECT id, name, perm FROM " . DB_PREFIX . "vavok_users WHERE perm='101' OR perm='102' OR perm='103' OR perm='105' OR perm='106' ORDER BY perm LIMIT $limit_start, $items_per_page") as $item) {
        if ($item['perm'] == 101 or $item['perm'] == 102 or $item['perm'] == 103 or $item['perm'] == 105 or $item['perm'] == 106) {
            $lnk = '<div class="a">' . $vavok->sitelink('../pages/user.php?uz=' . $item['id'], $item['name']) . ' - ' . $vavok->go('users')->user_status($item['perm']) . '</div>';
            echo $lnk . '<br>';
        }
    }
}

echo $navigation->get_navigation();

echo '<p>' . $vavok->sitelink('./', $vavok->go('localization')->string('admpanel')) . '<br>';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();

?>
