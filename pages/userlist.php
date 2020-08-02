<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   02.08.2020. 2:58:20
*/

require_once"../include/startup.php";
 
if (!empty($_GET['action'])) {
    $action = $vavok->check($_GET["action"]);
} else {
    $action = '';
} 
if (!empty($_GET['page'])) {
    $page = $vavok->check($_GET["page"]);
} else {
    $page = '';
} 
if (!empty($_GET['view'])) {
    $view = $vavok->check($_GET["view"]);
} else {
    $view = '';
} 
if (empty($view)) {
    $view = "name";
}

$num_items = $users->regmemcount() - 1; // no. reg. members minus system
$items_per_page = 10;

$navigation = new Navigation($items_per_page, $num_items, $page, 'userlist.php?'); // start navigation

$limit_start = $navigation->start()['start']; // starting point

// changable sql
if ($view == "name") {
    $sql = "SELECT id, name FROM vavok_users ORDER BY name LIMIT $limit_start, $items_per_page";
} else {
    $sql = "SELECT id, name FROM vavok_users ORDER BY regdate DESC LIMIT $limit_start, $items_per_page";
}

$current_page->page_title = $localization->string('userlist');
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

if ($num_items > 0) {

    foreach ($db->query($sql) as $item) {

        if ($item['id'] == 0 || $item['name'] == 'System') {
            continue;
        }
        
        $profile = $db->get_data('vavok_profil', "uid='{$item['id']}'");

        echo '<div class="a">';
        echo '<a href="../pages/user.php?uz=' . $item['id'] . '">' . $item['name'] . '</a> - joined: ' . $vavok->date_fixed($profile["regdate"], 'd.m.Y.'); // update lang
        echo '</div>';

    }

} 

echo '<p>';

echo $navigation->get_navigation();

echo '</p>';

// echo '<br>Total users: <b>' . (int)$num_items . '</b><br>';
echo '<a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>

