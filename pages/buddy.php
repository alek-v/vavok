<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

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
$uz = isset($_POST['uz']) ? $vavok->check($_POST['uz']) : '';

if (!isset($config_kontaktlist)) {
    $config_kontaktlist = '10';
}

if ($vavok->go('users')->is_reg()) {
    if ($action == 'ign') {
        $todo = $vavok->check($_GET["todo"]);
        $who = $vavok->check($_GET["who"]); 
        $tnick = $vavok->go('users')->getnickfromid($who);

        if ($todo == "add") {
            if ($vavok->go('users')->ignoreres($vavok->go('users')->user_id, $who) == 1 && !$vavok->go('users')->isbuddy($who, $vavok->go('users')->user_id)) {
                $vavok->go('db')->insert_data('buddy', array('name' => $vavok->go('users')->user_id, 'target' => $who));

                header ("Location: buddy.php?isset=kontakt_add");
                exit;
            } else {
                header ("Location: buddy.php?start=$start&isset=kontakt_noadd");
                exit;
            }
        } elseif ($todo = "del") {
            $vavok->go('db')->delete('buddy', "name='{$vavok->go('users')->user_id}' AND target='" . $who . "'");

            header ("Location: buddy.php?start=$start&isset=kontakt_del");
            exit;
        }
    }

    if (empty($action)) {
        $vavok->go('current_page')->page_title = $vavok->go('localization')->string('contacts');
        $vavok->require_header();

        if (empty($page) || $page <= 0) $page = 1;

        $num_items = $vavok->go('db')->count_row('buddy', "name='{$vavok->go('users')->user_id}'");
        $items_per_page = 10;

        $navigation = new Navigation($items_per_page, $num_items, $page, 'buddy.php?'); // start navigation

        $limit_start = $navigation->start()['start']; // starting point

        $sql = "SELECT target FROM buddy WHERE name='{$vavok->go('users')->user_id}' LIMIT $limit_start, $items_per_page";

        if ($num_items > 0) {
            foreach ($vavok->go('db')->query($sql) as $item) {
                $tnick = $vavok->go('users')->getnickfromid($item['target']);

                $lnk = "<a href=\"../pages/user.php?uz=" . $item['target'] . "\"  class=\"sitelink\">" . $tnick . "</a>";
                echo $vavok->go('users')->user_online($tnick) . " " . $lnk . ": ";
                echo "<img src=\"../images/img/close.gif\" alt=\"\"> <a href=\"buddy.php?action=ign&amp;who=" . $item['target'] . "&amp;todo=del\" class=\"sitelink\">" . $vavok->go('localization')->string('delete') . "</a><br>";
            }

        } else {
            echo '<p><img src="../images/img/reload.gif" alt=""> ' . $vavok->go('localization')->string('nobuddy') . '</p>';
        }

        echo $navigation->get_navigation();
    }
} else {
    echo '<p>' . $vavok->go('localization')->string('notloged') . '</p>';
}

echo '<p>' . $vavok->sitelink('inbox.php', $vavok->go('localization')->string('inbox')) . '<br />';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();

?>