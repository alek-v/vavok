<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   01.08.2020. 2:39:22
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

if (!$users->is_reg() || (!$users->is_administrator() && !$users->is_moderator(103))) {
    $vavok->redirect_to("../index.php?error");
} 

$my_title = $localization->string('banlist');
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

echo '<p><img src="../images/img/partners.gif" alt=""> <b>' . $localization->string('banlist') . '</b></p>'; 

$noi = $db->count_row('vavok_users', "banned='1' OR banned='2'");
$items_per_page = 10;

$navigation = new Navigation($items_per_page, $noi, $page, 'banlist.php?'); // start navigation

$limit_start = $navigation->start()['start']; // starting point

$sql = "SELECT id, name, banned FROM vavok_users WHERE banned='1' OR banned='2' ORDER BY banned LIMIT $limit_start, $items_per_page";

if ($noi > 0) {
    foreach ($db->query($sql) as $item) {
        if ($item['banned'] == '1') {
            $banned_profil = $db->get_data('vavok_profil', "id='" . $item['id'] . "'", 'bantime, bandesc');

            $lnk = "<div class=\"a\"><p><a href=\"../pages/user.php?uz=" . $item['name'] . "\" class=\"sitelink\">" . $item['name'] . "</a> <small>" . $localization->string('banduration') . ": " . $vavok->date_fixed($banned_profil['bantime'], "d.m.y.") . " | " . $localization->string('bandesc') . ": " . $banned_profil['bandesc'] . "</small></p></div>";
            echo $lnk . "<br>";
        } 
    } 
} else {
    echo '<p><img src="../images/img/reload.gif" alt="" /> ' . $localization->string('noentry') . '!</p>';
} 

echo $navigation->get_navigation();

echo '<p><a href="index.php" class="btn btn-outline-primary sitelink">' . $localization->string('admpanel') . '</a><br>';
echo '<a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>
