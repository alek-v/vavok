<?php 
// (c) vavok.net
require_once"../include/strtup.php";

$mediaLikeButton = 'off'; // dont show like buttons

$my_title = $lang_page['userlist'];
include_once"../themes/$config_themes/index.php";
if (isset($_GET['isset'])) {
    $isset = check($_GET['isset']);
    echo '<div align="center"><b><font color="#FF0000">';
    echo get_isset();
    echo '</font></b></div>';
} 

$time = time();

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
} // changable
// echo "<a href=\"userlist.php?action=members&amp;view=name&amp;sid=$sid\">Order By Name</a><br>";
// echo "<a href=\"userlist.php?action=members&amp;view=date&amp;sid=$sid\">Order By Join Date</a><br>";
if (empty($page) || $page < 1) {
    $page = 1;
} 
$num_items = $users->regmemcount() - 1; // no. reg. members minus system
$items_per_page = 10;
$num_pages = ceil($num_items / $items_per_page);
if (($page > $num_pages) && $page != 1)$page = $num_pages;
$limit_start = ($page-1) * $items_per_page;
if ($limit_start < 0) {
    $limit_start = 0;
} 
// changable sql
if ($view == "name") {
    $sql = "SELECT id, name FROM vavok_users ORDER BY name LIMIT $limit_start, $items_per_page";
} else {
    $sql = "SELECT id, name FROM vavok_users ORDER BY regdate DESC LIMIT $limit_start, $items_per_page";
} 

if ($num_items > 0) {
    foreach ($db->query($sql) as $item) {
        if ($item['id'] == 0 || $item['name'] == 'System') {
            continue;
        }
        $profile = $db->select('vavok_profil', "uid='" . $item['id'] . "'", '', "*");
        echo '<div class="a">';
        echo '<a href="../pages/user.php?uz=' . $item['id'] . '">' . $item['name'] . '</a> - joined: ' . date_fixed($profile["regdate"], 'd.m.Y.'); // update lang
        echo '</div>';
    } 
} 

echo '<div class="break"></div>';

page_navigation("userlist.php?view=$view&amp;", $items_per_page, $page, $num_items);
page_numbnavig("userlist.php?view=$view&amp;", $items_per_page, $page, $num_items);

echo '<br /><div class="break"></div>';
// echo '<br>Total users: <b>' . (int)$num_items . '</b><br>';
echo '<img src="../images/img/homepage.gif" alt="" /> <a href="../" class="homepage">' . $lang_home['home'] . '</a>';

include_once"../themes/$config_themes/foot.php";

?>

