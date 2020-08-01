<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   26.07.2020. 17:25:09
*/

require_once"../include/startup.php";

$my_title = $localization->string('ignorlist');
require_once BASEDIR . "themes/" . MY_THEME . "/index.php"; 

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
if (!empty($_POST['uz'])) { $uz = $vavok->check($_POST['uz']); }

if ($users->is_reg()) {

    if (empty($action)) {

        $num_items = $db->count_row('`ignore`', "name='{$users->user_id}'");
        $items_per_page = 10;

        $navigation = new Navigation($items_per_page, $num_items, $page, 'ignor.php?'); // start navigation

        $limit_start = $navigation->start()['start']; // starting point

        $sql = "SELECT target FROM `ignore` WHERE name='{$users->user_id}' LIMIT $limit_start, $items_per_page";

        if ($num_items > 0) {
            foreach ($db->query($sql) as $item) {
                $tnick = $users->getnickfromid($item['target']);
                /*
            if (isonline($item[0])) {
                $iml = "<img src=\"images/onl.gif\" alt=\"+\"/>";
            } else {
                $iml = "<img src=\"images/ofl.gif\" alt=\"-\"/>";
            }
            */
                $lnk = '<a href="../pages/user.php?uz=' . $item['target'] . '" class="btn btn-outline-primary sitelink">' . $tnick . '</a>';
                echo "$lnk: ";
                echo "<img src=\"../images/img/close.gif\" alt=\"\"> <a href=\"ignor.php?action=ign&amp;who=" . $item['target'] . "&amp;todo=del\" class=\"sitelink\">" . $localization->string('delete') . "</a><br>";
            } 
        } else {
            echo '<img src="../images/img/reload.gif" alt=""> ' . $localization->string('ignorempty') . '<br><br>';
        } 

        echo $navigation->get_navigation();

    } elseif ($action == "ign") {
        $todo = $_GET["todo"];
        $who = $_GET["who"]; 
        // $uid = getuid_sid($sid);
        $tnick = $users->getnickfromid($who);
        if ($todo == "add") {
            if ($users->ignoreres($users->user_id, $who) == 1) {
                $db->insert_data('`ignore`', array('name' => $users->user_id, 'target' => $who));

                echo "<img src=\"../images/img/open.gif\" alt=\"o\"/> " . $localization->string('user') . " $tnick " . $localization->string('sucadded') . "<br>";
            } else {
                echo "<img src=\"../images/img/close.gif\" alt=\"x\"/> " . $localization->string('cantadd') . " " . $tnick . " " . $localization->string('inignor') . "<br>";
            } 
        } elseif ($todo = "del") {
            if ($users->ignoreres($users->user_id, $who) == 2) {
                $db->delete('`ignore`', "name='{$users->user_id}' AND target='" . $who . "'");

                echo "<img src=\"../images/img/open.gif\" alt=\"o\"/> $tnick " . $localization->string('deltdfrmignor') . "<br>";
            } else {
                echo "<img src=\"../images/img/close.gif\" alt=\"x\"/> $tnick " . $localization->string('notinignor') . "<br>";
            } 
        } 

        echo '<br><a href="ignor.php" class="btn btn-outline-primary sitelink">' . $localization->string('ignorlist') . '</a><br>';
    } 
} else {
    echo '<p>' . $localization->string('notloged') . '</p>';
} 

echo '<p><a href="inbox.php" class="btn btn-outline-primary sitelink">' . $localization->string('inbox') . '</a><br />';
echo '<a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>