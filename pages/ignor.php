<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('ignorlist');
$vavok->require_header(); 

if ($vavok->go('users')->is_reg()) {
    if (empty($vavok->post_and_get('action'))) {
        $num_items = $vavok->go('db')->count_row('`ignore`', "name='{$vavok->go('users')->user_id}'");
        $items_per_page = 10;

        $navigation = new Navigation($items_per_page, $num_items, $vavok->post_and_get('page'), 'ignor.php?'); // start navigation

        $limit_start = $navigation->start()['start']; // starting point

        $sql = "SELECT target FROM `ignore` WHERE name='{$vavok->go('users')->user_id}' LIMIT $limit_start, $items_per_page";

        if ($num_items > 0) {
            foreach ($vavok->go('db')->query($sql) as $item) {
                $tnick = $vavok->go('users')->getnickfromid($item['target']);

                $lnk = '<a href="../pages/user.php?uz=' . $item['target'] . '" class="btn btn-outline-primary sitelink">' . $tnick . '</a>';
                echo "$lnk: ";
                echo "<img src=\"../images/img/close.gif\" alt=\"\"> <a href=\"ignor.php?action=ign&amp;who=" . $item['target'] . "&amp;todo=del\" class=\"sitelink\">" . $vavok->go('localization')->string('delete') . "</a><br>";
            }
        } else {
            echo '<img src="../images/img/reload.gif" alt=""> ' . $vavok->go('localization')->string('ignorempty') . '<br><br>';
        }

        echo $navigation->get_navigation();

    } elseif ($vavok->post_and_get('action') == 'ign') {
        $tnick = $vavok->go('users')->getnickfromid($vavok->post_and_get('who'));

        if ($vavok->post_and_get('todo') == 'add') {
            if ($vavok->go('users')->ignoreres($vavok->go('users')->user_id, $who) == 1) {
                $vavok->go('db')->insert_data('`ignore`', array('name' => $vavok->go('users')->user_id, 'target' => $vavok->post_and_get('who')));

                echo "<img src=\"../images/img/open.gif\" alt=\"o\"/> " . $vavok->go('localization')->string('user') . " $tnick " . $vavok->go('localization')->string('sucadded') . "<br>";
            } else {
                echo "<img src=\"../images/img/close.gif\" alt=\"x\"/> " . $vavok->go('localization')->string('cantadd') . " " . $tnick . " " . $vavok->go('localization')->string('inignor') . "<br>";
            }
        } elseif ($vavok->post_and_get('todo') = 'del') {
            if ($vavok->go('users')->ignoreres($vavok->go('users')->user_id, $vavok->post_and_get('who')) == 2) {
                $vavok->go('db')->delete('`ignore`', "name='{$vavok->go('users')->user_id}' AND target='" . $vavok->post_and_get('who') . "'");

                echo "<img src=\"../images/img/open.gif\" alt=\"o\"/> $tnick " . $vavok->go('localization')->string('deltdfrmignor') . "<br>";
            } else {
                echo "<img src=\"../images/img/close.gif\" alt=\"x\"/> $tnick " . $vavok->go('localization')->string('notinignor') . "<br>";
            }
        }

        echo $vavok->sitelink(HOMEDIR . 'pages/ignor.php', $vavok->go('localization')->string('ignorlist'), '<p>', '</p>');
    }
} else {
    echo '<p>' . $vavok->go('localization')->string('notloged') . '</p>';
}

echo '<p>' . $vavok->sitelink(HOMEDIR . 'pages/inbox.php', $vavok->go('localization')->string('inbox')) . '<br />';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();

?>