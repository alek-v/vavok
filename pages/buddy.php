<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if ($vavok->go('users')->is_reg()) {
    if ($vavok->post_and_get('action') == 'ign') {
        $tnick = $vavok->go('users')->getnickfromid($vavok->post_and_get('who'));

        if ($vavok->post_and_get('todo') == 'add') {
            if ($vavok->go('users')->ignoreres($vavok->go('users')->user_id, $vavok->post_and_get('who')) == 1 && !$vavok->go('users')->isbuddy($vavok->post_and_get('who'), $vavok->go('users')->user_id)) {
                $vavok->go('db')->insert_data('buddy', array('name' => $vavok->go('users')->user_id, 'target' => $vavok->post_and_get('who')));

                header ("Location: buddy.php?isset=kontakt_add");
                exit;
            } else {
                header ("Location: buddy.php?isset=kontakt_noadd");
                exit;
            }
        } elseif ($vavok->post_and_get('todo') = "del") {
            $vavok->go('db')->delete('buddy', "name='{$vavok->go('users')->user_id}' AND target='" . $vavok->post_and_get('who') . "'");

            header ("Location: buddy.php?isset=kontakt_del");
            exit;
        }
    }

    if (empty($vavok->post_and_get('action'))) {
        $vavok->go('current_page')->page_title = $vavok->go('localization')->string('contacts');
        $vavok->require_header();

        $num_items = $vavok->go('db')->count_row('buddy', "name='{$vavok->go('users')->user_id}'");
        $items_per_page = 10;

        $navigation = new Navigation($items_per_page, $num_items, $vavok->post_and_get('page'), 'buddy.php?'); // start navigation

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