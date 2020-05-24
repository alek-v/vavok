<?php 
// (c) vavok.net
require_once"../include/strtup.php";

$mediaLikeButton = 'off'; // dont show like buttons

$my_title = $lang_page['ignorlist'];
include_once"../themes/$config_themes/index.php";
// if is set message
if (isset($_GET['isset'])) {
    $isset = check($_GET['isset']);
    echo '<div align="center"><b><font color="#FF0000">';
    echo get_isset();
    echo '</font></b></div>';
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
if (!empty($_POST['uz'])) { $uz = check($_POST['uz']); }

if ($users->is_reg()) {

    if (empty($action)) {

        $num_items = $db->count_row('`ignore`', "name='{$user_id}'");
        $items_per_page = 10;

        $navigation = new Navigation($items_per_page, $num_items, $page, 'ignor.php?'); // start navigation

        $limit_start = $navigation->start()['start']; // starting point

        $sql = "SELECT target FROM `ignore` WHERE name='" . $user_id . "' LIMIT $limit_start, $items_per_page";

        if ($num_items > 0) {
            foreach ($db->query($sql) as $item) {
                $tnick = getnickfromid($item['target']);
                /*
            if (isonline($item[0])) {
                $iml = "<img src=\"images/onl.gif\" alt=\"+\"/>";
            } else {
                $iml = "<img src=\"images/ofl.gif\" alt=\"-\"/>";
            }
            */
                $lnk = '<a href="../pages/user.php?uz=' . $item['target'] . '" class="btn btn-outline-primary sitelink">' . $tnick . '</a>';
                echo "$lnk: ";
                echo "<img src=\"../images/img/close.gif\" alt=\"\"> <a href=\"ignor.php?action=ign&amp;who=" . $item['target'] . "&amp;todo=del\" class=\"sitelink\">" . $lang_home['delete'] . "</a><br>";
            } 
        } else {
            echo '<img src="../images/img/reload.gif" alt=""> ' . $lang_page['ignorempty'] . '<br><br>';
        } 

        echo $navigation->get_navigation();

    } elseif ($action == "ign") {
        $todo = $_GET["todo"];
        $who = $_GET["who"]; 
        // $uid = getuid_sid($sid);
        $tnick = getnickfromid($who);
        if ($todo == "add") {
            if ($users->ignoreres($user_id, $who) == 1) {
                $db->insert_data('`ignore`', array('name' => $user_id, 'target' => $who));

                echo "<img src=\"../images/img/open.gif\" alt=\"o\"/> " . $lang_home['user'] . " $tnick " . $lang_page['sucadded'] . "<br>";
            } else {
                echo "<img src=\"../images/img/close.gif\" alt=\"x\"/> " . $lang_page['cantadd'] . " " . $tnick . " " . $lang_page['inignor'] . "<br>";
            } 
        } elseif ($todo = "del") {
            if ($users->ignoreres($user_id, $who) == 2) {
                $db->delete('`ignore`', "name='" . $user_id . "' AND target='" . $who . "'");

                echo "<img src=\"../images/img/open.gif\" alt=\"o\"/> $tnick " . $lang_page['deltdfrmignor'] . "<br>";
            } else {
                echo "<img src=\"../images/img/close.gif\" alt=\"x\"/> $tnick " . $lang_page['notinignor'] . "<br>";
            } 
        } 

        echo '<br><a href="ignor.php" class="btn btn-outline-primary sitelink">' . $lang_page['ignorlist'] . '</a><br>';
    } 
} else {
    echo $lang_home['notloged'] . '<br><br>';
} 

echo '<a href="inbox.php" class="btn btn-outline-primary sitelink">' . $lang_home['inbox'] . '</a><br>';
echo '<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a>';

include_once"../themes/" . $config_themes . "/foot.php";

?>