<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg()) $vavok->redirect_to("../pages/login.php?ptl=pages/inbox.php");

// Update notification data
if ($vavok->go('db')->count_row('notif', "uid='{$vavok->go('users')->user_id}' AND type='inbox'") > 0) $vavok->go('db')->update('notif', 'lstinb', 0, "uid='{$vavok->go('users')->user_id}' AND type='inbox'");

// Header data
$vavok->go('current_page')->append_head_tags('<meta name="robots" content="noindex">
<script src="' . HOMEDIR . 'include/js/inbox.js"></script>
<script src="' . HOMEDIR . 'include/js/ajax.js"></script>
');

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('inbox');
$vavok->require_header();

$action = isset($_GET['action']) ? $vavok->check($_GET["action"]) : '';
$page = isset($_GET['page']) ? $vavok->check($_GET["page"]) : '';
$pmid = isset($_GET['pmid']) ? $vavok->check($_GET["pmid"]) : '';
$who = isset($_GET['who']) ? $vavok->check($_GET["who"]) : '';
if (empty($who) && isset($_POST['who'])) $who = $vavok->go('users')->getidfromnick($vavok->check($_POST['who']));

if (empty($action)) {

    $num_items = $vavok->go('users')->getpmcount($vavok->go('users')->user_id);
    $items_per_page = 10;

    // navigation
    $navigation = new Navigation($items_per_page, $num_items, $page, 'inbox.php?');
	$limit_start = $navigation->start()['start']; // starting point

    if ($num_items > 0) {

    $sql = "SELECT
    a.name, b.id, b.byuid, b.unread, b.starred FROM vavok_users a
    JOIN inbox b ON a.id = b.byuid
    WHERE b.touid='{$vavok->go('users')->user_id}' AND (deleted IS NULL OR deleted <> '{$vavok->go('users')->user_id}')
    ORDER BY b.timesent DESC
    LIMIT $limit_start, $items_per_page";


    $senders = array();
    $i = 0;
    foreach ($vavok->go('db')->query($sql) as $item) {

        // don't list user twice
        if (!in_array($item['name'], $senders)) {

            $i = $i++;

            // add user to list
            array_push($senders, $item['name']);

            if ($item['unread'] == "1") {
                $iml = '<img src="../images/img/new.gif" alt="New message" />';
            } else { $iml = ''; }

            $lnk = '<a href="inbox.php?action=dialog&amp;who=' . $item['byuid'] . '" class="btn btn-outline-primary sitelink">' . $iml . ' ' . $item['name'] . '</a>';
            echo $lnk . "<br />";
        }
    }

    echo '<br /><br/>';

    // navigation    
    echo $navigation->get_navigation();

    } else {
        echo '<img src="../images/img/reload.gif" alt=""> ' . $vavok->go('localization')->string('nopmsgs') . '<br /><br />';
    }

    echo $vavok->sitelink('inbox.php?action=sendto', $vavok->go('localization')->string('sendmsg')) . '<br />';

} else if ($action == "dialog") {

    if (empty($who) || empty($vavok->go('users')->getnickfromid($who))) { $vavok->show_error('User does not exist'); $vavok->require_footer(); exit; }

    $pms = $vavok->go('db')->count_row('inbox', "(byuid='" . $vavok->go('users')->user_id . "' AND touid='" . $who . "') OR (byuid='" . $who . "' AND touid='" . $vavok->go('users')->user_id . "') AND (deleted IS NULL OR deleted = '" . $who . "') ORDER BY timesent");

    $num_items = $pms; //changable
    $items_per_page = 50;
    $limit_start = $num_items - $items_per_page;
    if ($limit_start < 0) {
        $limit_start = 0;
    }

    $read_only = '';
    if ($who == 1) {
        $read_only = 'readonly';
    }

    $vavok->go('db')->update('inbox', 'unread', 0, "byuid='" . $who . "' AND touid='" . $vavok->go('users')->user_id . "'");

    echo '<form id="message-form" method="post" action="send_pm.php?who=' . $who . '">';
    echo '<div class="form-group">';
    echo '<label for="chatbarText"></label>';
    echo '<input name="pmtext" class="send_pm form-control" id="chatbarText" placeholder="' . $vavok->go('localization')->string('message') . '..." ' . $read_only . ' />';
    echo '</div>';
    echo '<input type="hidden" name="who" id="who" value="' . $who . '" class="send_pm" />';

    echo '<input type="hidden" name="lastid" id="lastid" value="' . $who . '" />';
    echo '<button type="submit" class="btn btn-primary" onclick="send_message(); return false;">' . $vavok->go('localization')->string('send') . '</button>';
    echo '</form><br />'; // update lang

    echo '<div id="message_box" class="message_box" style="overflow-y: scroll; height:400px;overflow-x: hidden;">';

    echo '<p id="outputList" class="outputList"></p>'; // ajax messages

    $pms = "SELECT * FROM inbox WHERE (byuid = '" . $vavok->go('users')->user_id . "' AND touid = '" . $who . "') OR (byuid='" . $who . "' AND touid = '" . $vavok->go('users')->user_id . "') AND (deleted IS NULL OR deleted = '" . $who . "') ORDER BY timesent DESC LIMIT $limit_start, $items_per_page";
    foreach ($vavok->go('db')->query($pms) as $pm) {

        $bylnk = "<a href=\"../pages/user.php?uz=" . $pm['byuid'] . "\" class=\"sitelink\">" . $vavok->go('users')->getnickfromid($pm['byuid']) . "</a> ";
        echo $bylnk;
        $tmopm = date("d m y - h:i:s", $pm['timesent']);
        echo "$tmopm<br />";

        echo $vavok->go('users')->parsepm($pm['text']);

        echo '<hr />';

    } 

    echo '</div>'; // end of #message-box

}

else if ($action == "sendto") {

    echo '<form method="post" action="inbox.php?action=dialog">';
    echo '<div class="form-group">';
    echo '<label for="who">' . $vavok->go('localization')->string('sendpmto') . ':</label>';
    echo '<input type="text" name="who" id="who" class="form-control" />';
    echo '</div>';
    echo '<button type="submit" class="btn btn-primary">' . $vavok->go('localization')->string('confirm') . '</button>
    </form>
    <hr>';

}

if (!empty($action)) echo $vavok->sitelink('inbox.php', $vavok->go('localization')->string('inbox'), '<p>', '</p>');

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>