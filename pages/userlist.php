<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

$view = !empty($vavok->post_and_get('view')) ? $vavok->post_and_get('view') : 'name';

$num_items = $vavok->go('users')->regmemcount(); // no. reg. members
$items_per_page = 10;

$navigation = new Navigation($items_per_page, $num_items, $vavok->post_and_get('page'), 'userlist.php?'); // start navigation

$limit_start = $navigation->start()['start']; // starting point

// changable sql
if ($vavok->post_and_get('view') == 'name') {
    $sql = "SELECT id, name FROM vavok_users ORDER BY name LIMIT $limit_start, $items_per_page";
} else {
    $sql = "SELECT id, name FROM vavok_users ORDER BY id DESC LIMIT $limit_start, $items_per_page";
}

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('userlist');
$vavok->require_header();

if ($num_items > 0) {
    foreach ($vavok->go('db')->query($sql) as $item) {
        if ($item['id'] == 0 || $item['name'] == 'System') continue;

        echo '<div class="a">';
        echo '<a href="../pages/user.php?uz=' . $item['id'] . '">' . $item['name'] . '</a> - joined: ' . $vavok->date_fixed($vavok->go('users')->user_info('regdate', $item['id']), 'd.m.Y.'); // update lang
        echo '</div>';
    }
}

echo '<p>';
echo $navigation->get_navigation();
echo '</p>';

// echo '<br>Total users: <b>' . (int)$num_items . '</b><br>';
echo $vavok->homelink();

$vavok->require_footer();

?>

