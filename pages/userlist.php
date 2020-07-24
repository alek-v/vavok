<?php 
// (c) vavok.net
require_once"../include/startup.php";
 
if (!empty($_GET['action'])) {
    $action = check($_GET["action"]);
} else {
    $action = '';
} 
if (!empty($_GET['page'])) {
    $page = check($_GET["page"]);
} else {
    $page = '';
} 
if (!empty($_GET['view'])) {
    $view = check($_GET["view"]);
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

$my_title = $lang_page['userlist'];
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

if ($num_items > 0) {

    foreach ($db->query($sql) as $item) {

        if ($item['id'] == 0 || $item['name'] == 'System') {
            continue;
        }
        
        $profile = $db->get_data('vavok_profil', "uid='{$item['id']}'");

        echo '<div class="a">';
        echo '<a href="../pages/user.php?uz=' . $item['id'] . '">' . $item['name'] . '</a> - joined: ' . date_fixed($profile["regdate"], 'd.m.Y.'); // update lang
        echo '</div>';

    }

} 

echo '<p>';

echo $navigation->get_navigation();

echo '</p>';

// echo '<br>Total users: <b>' . (int)$num_items . '</b><br>';
echo '<img src="../images/img/homepage.gif" alt="' . $lang_home['home'] . '" /> <a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>

