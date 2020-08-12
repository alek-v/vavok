<?php 
/*
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   02.08.2020. 2:48:28
*/

require_once"../include/startup.php";

if (!$users->is_reg()) $vavok->redirect_to("../pages/login.php?ptl=pages/inbox.php");

// Update notification data
if ($db->count_row('notif', "uid='{$users->user_id}' AND type='inbox'") > 0) $db->update('notif', 'lstinb', 0, "uid='{$users->user_id}' AND type='inbox'");

// Header data
$current_page->append_head_tags('<meta name="robots" content="noindex">
<script src="' . HOMEDIR . 'include/js/inbox.js"></script>
<script src="' . HOMEDIR . 'include/js/ajax.js"></script>
');

$current_page->page_title = $localization->string('inbox');
require_once BASEDIR . "themes/" . MY_THEME. "/index.php";

$action = isset($_GET['action']) ? $vavok->check($_GET["action"]) : '';
$page = isset($_GET['page']) ? $vavok->check($_GET["page"]) : '';
$pmid = isset($_GET['pmid']) ? $vavok->check($_GET["pmid"]) : '';
$who = isset($_GET['who']) ? $vavok->check($_GET["who"]) : '';
if (empty($who) && isset($_POST['who'])) $who = $users->getidfromnick($vavok->check($_POST['who']));

if (empty($action)) {

    $num_items = $users->getpmcount($users->user_id);
    $items_per_page = 10;

    // navigation
    $navigation = new Navigation($items_per_page, $num_items, $page, 'inbox.php?');
	$limit_start = $navigation->start()['start']; // starting point

    if ($num_items > 0) {

    $sql = "SELECT
    a.name, b.id, b.byuid, b.unread, b.starred FROM vavok_users a
    JOIN inbox b ON a.id = b.byuid
    WHERE b.touid='{$users->user_id}' AND (deleted IS NULL OR deleted <> '{$users->user_id}')
    ORDER BY b.timesent DESC
    LIMIT $limit_start, $items_per_page";


    $senders = array();
    $i = 0;
    foreach ($db->query($sql) as $item) {

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
        echo '<img src="../images/img/reload.gif" alt=""> ' . $localization->string('nopmsgs') . '<br /><br />';
    } 

    echo '<a href="inbox.php?action=sendto" class="btn btn-primary sitelink">' . $localization->string('sendmsg') . '</a><br />';

} else if ($action == "dialog") {

    if (empty($who) || empty($users->getnickfromid($who))) { $vavok->show_error('User does not exist'); require_once BASEDIR . "themes/" . MY_THEME . "/foot.php"; exit; }

    $pms = $db->count_row('inbox', "(byuid='" . $users->user_id . "' AND touid='" . $who . "') OR (byuid='" . $who . "' AND touid='" . $users->user_id . "') AND (deleted IS NULL OR deleted = '" . $who . "') ORDER BY timesent");

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

    $db->update('inbox', 'unread', 0, "byuid='" . $who . "' AND touid='" . $users->user_id . "'");

    echo '<form id="message-form" method="post" action="send_pm.php?who=' . $who . '">';
    echo '<div class="form-group">';
    echo '<label for="chatbarText"></label>';
    echo '<input name="pmtext" class="send_pm form-control" id="chatbarText" placeholder="' . $localization->string('message') . '..." ' . $read_only . ' />';
    echo '</div>';
    echo '<input type="hidden" name="who" id="who" value="' . $who . '" class="send_pm" />';

    echo '<input type="hidden" name="lastid" id="lastid" value="' . $who . '" />';
    echo '<button type="submit" class="btn btn-primary" onclick="send_message(); return false;">' . $localization->string('send') . '</button>';
    echo '</form><br />'; // update lang

    echo '<div id="message_box" class="message_box" style="overflow-y: scroll; height:400px;overflow-x: hidden;">';

    echo '<p id="outputList" class="outputList"></p>'; // ajax messages

    $pms = "SELECT * FROM inbox WHERE (byuid = '" . $users->user_id . "' AND touid = '" . $who . "') OR (byuid='" . $who . "' AND touid = '" . $users->user_id . "') AND (deleted IS NULL OR deleted = '" . $who . "') ORDER BY timesent DESC LIMIT $limit_start, $items_per_page";
    foreach ($db->query($pms) as $pm) {

        $bylnk = "<a href=\"../pages/user.php?uz=" . $pm['byuid'] . "\" class=\"sitelink\">" . $users->getnickfromid($pm['byuid']) . "</a> ";
        echo $bylnk;
        $tmopm = date("d m y - h:i:s", $pm['timesent']);
        echo "$tmopm<br />";

        echo $users->parsepm($pm['text']);

        echo '<hr />';

    } 

    echo '</div>'; // end of #message-box

}

else if ($action == "sendto") {

    echo '<form method="post" action="inbox.php?action=dialog">';
    echo '<div class="form-group">';
    echo '<label for="who">' . $localization->string('sendpmto') . ':</label>';
    echo '<input type="text" name="who" id="who" class="form-control" />';
    echo '</div>';
    echo '<button type="submit" class="btn btn-primary">' . $localization->string('confirm') . '</button>
    </form>
    <hr>';

}

if (!empty($action)) echo '<p><a href="inbox.php" class="btn btn-outline-primary sitelink">' . $localization->string('inbox') . '</a></p>';

echo '<p><a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>