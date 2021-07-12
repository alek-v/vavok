<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!isset($view)) {
    $view = "name";
}

$num_items = $vavok->go('users')->regmemcount() - 1; // no. reg. members minus system
$items_per_page = 10;

$navigation = new Navigation($items_per_page, $num_items, $vavok->post_and_get('page'), 'userlist.php?'); // start navigation

$limit_start = $navigation->start()['start']; // starting point

// changable sql
if ($vavok->post_and_get('view') == 'name') {
    $sql = "SELECT id, name FROM vavok_users ORDER BY name LIMIT $limit_start, $items_per_page";
} else {
    $sql = "SELECT id, name FROM vavok_users ORDER BY regdate DESC LIMIT $limit_start, $items_per_page";
}

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('userlist');
$vavok->require_header();

if ($num_items > 0) {
    foreach ($vavok->go('db')->query($sql) as $item) {
        if ($item['id'] == 0 || $item['name'] == 'System') {
            continue;
        }
        
        $profile = $vavok->go('db')->get_data('vavok_profil', "uid='{$item['id']}'");

        echo '<div class="a">';
        echo '<a href="../pages/user.php?uz=' . $item['id'] . '">' . $item['name'] . '</a> - joined: ' . $vavok->date_fixed($profile["regdate"], 'd.m.Y.'); // update lang
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

