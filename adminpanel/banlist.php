<?php 
// (c) vavok.net
require_once"../include/strtup.php";

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

if (!isset($config_banlist)) {
    $config_banlist = 10;
} 

$time = time();

if (!$users->is_reg() || (!$users->is_administrator() && !$users->is_moderator('', '103'))) {
    header ("Location: ../index.php?error");
    exit;
} 

$my_title = $lang_admin['banlist'];
include_once"../themes/$config_themes/index.php";
if (isset($_GET['isset'])) {
    $isset = check($_GET['isset']);
    echo '<div align="center"><b><font color="#FF0000">';
    echo get_isset();
    echo '</font></b></div>';
} 

echo '<img src="../images/img/partners.gif" alt=""> <b>' . $lang_admin['banlist'] . '</b><br><br>'; 

if ($page == "" || $page <= 0)$page = 1;
$noi = $db->count_row('vavok_users', "banned='1' OR banned='2'");
$num_items = $noi; //changable
$items_per_page = 10;
$num_pages = ceil($num_items / $items_per_page);
if (($page > $num_pages) && $page != 1)$page = $num_pages;
$limit_start = ($page-1) * $items_per_page;
if ($limit_start < 0) {
    $limit_start = 0;
} 

$sql = "SELECT id, name, banned FROM vavok_users WHERE banned='1' OR banned='2' ORDER BY banned LIMIT $limit_start, $items_per_page";

if ($noi > 0) {
    foreach ($db->query($sql) as $item) {
        if ($item['banned'] == '1') {
            $banned_profil = $db->select('vavok_profil', "id='" . $item['id'] . "'", '', 'bantime, bandesc');

            $lnk = "<div class=\"a\"><a href=\"../pages/user.php?uz=" . $item['name'] . "\" class=\"sitelink\">" . $item['name'] . "</a> <small>" . $lang_admin['banduration'] . ": " . date_fixed($banned_profil['bantime'], "d.m.y.") . " | " . $lang_admin['bandesc'] . ": " . $banned_profil['bandesc'] . "</small></div>";
            echo $lnk . "<br>";
        } 
    } 
} else {
    echo '<img src="../images/img/reload.gif" alt="" /> ' . $lang_admin['noentry'] . '!<br><br>';
} 

page_navigation('banlist.php?view=' . $view . '&amp;', $items_per_page, $page, $num_items);
page_numbnavig('banlist.php?view=' . $view . '&amp;', $items_per_page, $page, $num_items);

echo '<p><a href="index.php" class="btn btn-outline-primary sitelink">' . $lang_home['admpanel'] . '</a><br>';
echo '<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';

include_once"../themes/$config_themes/foot.php";

?>
