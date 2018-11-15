<?php 
// (c) vavok.net
require_once"../include/strtup.php";

if (!is_reg() || !checkPermissions(basename(__FILE__))) {
    header ("Location: ../input.php?action=exit");
    exit;
}

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

$my_title = $lang_admin['modlist'];

include_once"../themes/$config_themes/index.php";
if (isset($_GET['isset'])) {
    $isset = check($_GET['isset']);
    echo '<div align="center"><b><font color="#FF0000">';
    echo get_isset();
    echo '</font></b></div>';
} 

if (empty($action)) {
    echo '<img src="../images/img/user.gif" alt=""> <b>' . $lang_admin['adminlistl'] . '</b><br><br>'; 
    // /////////////
    if ($page == "" || $page <= 0)$page = 1;
    $num_items = $db->count_row('vavok_users', "perm='101' OR perm='102' OR perm='103' OR perm='105'");
    $items_per_page = 10;
    $num_pages = ceil($num_items / $items_per_page);
    if (($page > $num_pages) && $page != 1)$page = $num_pages;
    $limit_start = ($page-1) * $items_per_page;
    if ($limit_start < 0) {
        $limit_start = 0;
    } 

    if ($num_items > 0) {
        foreach ($db->query("SELECT id, name, perm FROM vavok_users WHERE perm='101' OR perm='102' OR perm='103' OR perm='105' OR perm='106' ORDER BY perm LIMIT $limit_start, $items_per_page") as $item) {
            if ($item['perm'] == '101' or $item['perm'] == '102' or $item['perm'] == '103' or $item['perm'] == '105' or $item['perm'] == '106') {
                $lnk = "<div class=\"a\"><a href=\"../pages/user.php?uz=" . $item['id'] . "\" class=\"sitelink\">" . $item['name'] . "</a> - " . user_status($item['perm']) . "</div>";
                echo $lnk . "<br>";
            } 
        } 
    } 

    page_navigation('adminlist.php?view=' . $view . '&amp;', $items_per_page, $page, $num_items);
    page_numbnavig('adminlist.php?view=' . $view . '&amp;', $items_per_page, $page, $num_items);
} 
echo '<p><a href="index.php" class="sitelink">' . $lang_home['admpanel'] . '</a><br>';
echo '<a href="../" class="homepage">' . $lang_home['home'] . '</a></p>';

include_once"../themes/$config_themes/foot.php";

?>
