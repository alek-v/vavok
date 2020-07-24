<?php 
// (c) vavok.net
require_once"../include/startup.php";

if (!$users->is_reg()) {
    redirect_to("../pages/login.php?ptl=pages/inbox.php");
} 

$last_notif = $db->count_row('notif', "uid='{$user_id}' AND type='inbox'");
// update notification data
if ($last_notif > 0) {
    $db->update('notif', 'lstinb', 0, "uid='{$user_id}' AND type='inbox'");
} 

$genHeadTag = '<meta name="robots" content="noindex">
<script src="/js/inbox.js"></script>
<script src="/js/ajax.js"></script>
'; // header data

$my_title = $lang_home['inbox'];
require_once BASEDIR . "themes/" . MY_THEME. "/index.php";

$action = isset($_GET['action']) ? check($_GET["action"]) : '';
$page = isset($_GET['page']) ? check($_GET["page"]) : '';
$who = isset($_GET['who']) ? check($_GET["who"]) : '';
$pmid = isset($_GET['pmid']) ? check($_GET["pmid"]) : '';


if ($action == "main" or empty($action)) {

    $num_items = $users->getpmcount($user_id);
    $items_per_page = 10;

    // navigation
    $navigation = new Navigation($items_per_page, $i, $page, 'inbox.php?action=main&amp;');
	$limit_start = $navigation->start()['start']; // starting point

    if ($num_items > 0) {

    $sql = "SELECT
    a.name, b.id, b.byuid, b.unread, b.starred FROM vavok_users a
    JOIN inbox b ON a.id = b.byuid
    WHERE b.touid='{$user_id}' AND (deleted IS NULL OR deleted <> '{$user_id}')
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
        echo '<img src="../images/img/reload.gif" alt=""> ' . $lang_page['nopmsgs'] . '<br /><br />';
    } 

    echo '<a href="inbox.php?action=sendto" class="btn btn-primary sitelink">' . $lang_page['sendmsg'] . '</a><br />';

} else if ($action == "dialog") {

    $pms = $db->count_row('inbox', "(byuid=$user_id AND touid=$who) OR (byuid=$who AND touid=$user_id) AND (deleted IS NULL OR deleted = $who) ORDER BY timesent");

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

    if ($num_items > 0) {

        $db->update('inbox', 'unread', 0, "byuid='{$who}' AND touid='{$user_id}'");


        echo '<form id="message-form" method="post" action="send_pm.php?who=' . $who . '">';
        echo '<div class="form-group">';
        echo '<label for="chatbarText"></label>';
        echo '<input name="pmtext" class="send_pm form-control" id="chatbarText" placeholder="' . $lang_home['message'] . '..." ' . $read_only . ' />';
        echo '</div>';
        echo '<input type="hidden" name="who" id="who" value="' . $who . '" class="send_pm" />';

        echo '<input type="hidden" name="lastid" id="lastid" value="' . $who . '" />';
        echo '<button type="submit" class="btn btn-primary" onclick="send_message(); return false;">' . $lang_home['send'] . '</button>';
        echo '</form><br />'; // update lang


        echo '<div id="message_box" class="message_box" style="overflow-y: scroll; height:400px;overflow-x: hidden;">';


        echo '<p id="outputList" class="outputList"></p>'; // ajax messages


        $pms = "SELECT * FROM inbox WHERE (byuid = '" . $user_id . "' AND touid = '" . $who . "') OR (byuid='" . $who . "' AND touid = '" . $user_id . "') AND (deleted IS NULL OR deleted = '" . $who . "') ORDER BY timesent DESC LIMIT $limit_start, $items_per_page";
        foreach ($db->query($pms) as $pm) {

            $bylnk = "<a href=\"../pages/user.php?uz=" . $pm['byuid'] . "\" class=\"sitelink\">" . $users->getnickfromid($pm['byuid']) . "</a> ";
            echo $bylnk;
            $tmopm = date("d m y - h:i:s", $pm['timesent']);
            echo "$tmopm<br />";

            echo $users->parsepm($pm['text']);

            echo '<hr />';

        } 
          

        echo '</div>'; // end of #message-box


    } else {

        echo '<p><img src="../images/img/reload.gif" alt="Inbox is empty" /> Inbox is empty!</p>'; // update lang

    } 

    echo '<br /><br /><a href="inbox.php?action=main" class="btn btn-outline-primary sitelink">' . $lang_home['inbox'] . '</a><br />';

}

echo '<p><a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>