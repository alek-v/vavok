<?php 
// (c) vavok.net
require_once"../include/strtup.php";
$my_title = 'Contact'; // update lang

$mediaLikeButton = 'off'; // dont show like buttons

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
$uz = isset($_POST['uz']) ? check($_POST['uz']) : '';

if (!isset($config_kontaktlist)) {
    $config_kontaktlist = '10';
} 

if ($users->is_reg()) {

    if ($action == "ign") {

        $todo = check($_GET["todo"]);
        $who = check($_GET["who"]); 
        $tnick = $users->getnickfromid($who);

        if ($todo == "add") {
            if ($users->ignoreres($user_id, $who) == 1 && !isbuddy($who, $user_id)) {
                $db->insert_data('buddy', array('name' => $user_id, 'target' => $who));

                header ("Location: buddy.php?isset=kontakt_add");
                exit;
            } else {
                header ("Location: buddy.php?start=$start&isset=kontakt_noadd");
                exit;
            } 
        } elseif ($todo = "del") {
            $db->delete('buddy', "name='" . $user_id . "' AND target='" . $who . "'");

            header ("Location: buddy.php?start=$start&isset=kontakt_del");
            exit;
        } 
    } 

    if (empty($action)) {

        $my_title = $lang_page['contacts'];
        include_once"../themes/$config_themes/index.php";

        if (isset($_GET['isset'])) {
            $isset = check($_GET['isset']);
            echo '<div align="center"><b><font color="#FF0000">';
            echo get_isset();
            echo '</font></b></div>';
        } 

        if ($page == "" || $page <= 0) {
            $page = 1;
        }

        $num_items = $db->count_row('buddy', "name='" . $user_id . "'");
        $items_per_page = 10;

        $navigation = new Navigation($items_per_page, $num_items, $page, 'buddy.php?'); // start navigation

        $limit_start = $navigation->start()['start']; // starting point

        $sql = "SELECT target FROM buddy WHERE name='" . $user_id . "' LIMIT $limit_start, $items_per_page";

        if ($num_items > 0) {

            foreach ($db->query($sql) as $item) {

                $tnick = $users->getnickfromid($item['target']);

                $lnk = "<a href=\"../pages/user.php?uz=" . $item['target'] . "\"  class=\"sitelink\">" . $tnick . "</a>";
                echo $users->user_online($tnick) . " " . $lnk . ": ";
                echo "<img src=\"../images/img/close.gif\" alt=\"\"> <a href=\"buddy.php?action=ign&amp;who=" . $item['target'] . "&amp;todo=del\" class=\"sitelink\">" . $lang_home['delete'] . "</a><br>";

            } 

        } else {
            echo '<img src="../images/img/reload.gif" alt=""> ' . $lang_page['nobuddy'] . '<br><br>';
        } 

        echo $navigation->get_navigation();

    } 
} else {
    echo $lang_home['notloged'] . '<br><br>';
} 

echo '<img src="../images/img/back.gif" alt=""> <a href="inbox.php" class="btn btn-outline-primary sitelink">' . $lang_home['inbox'] . '</a><br>';
echo '<a href="../" class="btn btn-primary homepage"><img src="../images/img/homepage.gif" alt=""> ' . $lang_home['home'] . '</a>';

include_once"../themes/" . $config_themes . "/foot.php";

?>