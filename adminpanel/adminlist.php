<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg() || !$vavok->go('users')->check_permissions(basename(__FILE__))) $vavok->redirect_to("../pages/input.php?action=exit");

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

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('modlist');

$vavok->require_header();

 

if (empty($action)) {

    echo '<img src="../images/img/user.gif" alt=""> <b>' . $vavok->go('localization')->string('adminlistl') . '</b><br><br>'; 

    $num_items = $vavok->go('db')->count_row('vavok_users', "perm='101' OR perm='102' OR perm='103' OR perm='105'");
    $items_per_page = 10;

    $navigation = new Navigation($items_per_page, $num_items, $page, 'adminlist.php?'); // start navigation

    $limit_start = $navigation->start()['start']; // starting point
    $end = $navigation->start()['end']; // ending point

    if ($num_items > 0) {
        foreach ($vavok->go('db')->query("SELECT id, name, perm FROM vavok_users WHERE perm='101' OR perm='102' OR perm='103' OR perm='105' OR perm='106' ORDER BY perm LIMIT $limit_start, $items_per_page") as $item) {
            if ($item['perm'] == '101' or $item['perm'] == '102' or $item['perm'] == '103' or $item['perm'] == '105' or $item['perm'] == '106') {
                $lnk = "<div class=\"a\"><a href=\"../pages/user.php?uz=" . $item['id'] . "\" class=\"sitelink\">" . $item['name'] . "</a> - " . $vavok->go('users')->user_status($item['perm']) . "</div>";
                echo $lnk . "<br>";
            } 
        } 
    } 

    echo $navigation->get_navigation();

} 
echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('admpanel') . '</a><br>';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();

?>
