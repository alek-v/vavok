<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

$num_items = $vavok->go('users')->regmemcount(); // no. reg. members
$items_per_page = 10;

// Start navigation
$navigation = new Navigation($items_per_page, $num_items, $vavok->post_and_get('page'), 'userlist.php?');

// Starting point
$limit_start = $navigation->start()['start'];

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('userlist');
$vavok->require_header();

if ($num_items > 0) {
    foreach ($vavok->go('db')->query("SELECT id, name FROM vavok_users ORDER BY name LIMIT $limit_start, $items_per_page") as $item) {
        echo '<div class="a">';
        echo '<a href="../pages/user.php?uz=' . $item['id'] . '">' . $item['name'] . '</a> - joined: ' . $vavok->date_fixed($vavok->go('users')->user_info('regdate', $item['id']), 'd.m.Y.'); // update lang
        echo '</div>';
    }
}

echo $navigation->get_navigation();

// echo '<br>Total users: <b>' . (int)$num_items . '</b><br>';
echo $vavok->homelink();

$vavok->require_footer();

?>

