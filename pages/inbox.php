<?php 
// (c) vavok.net
require_once"../include/strtup.php";

if (!$users->is_reg()) {
    redirect_to("Location: ../");
} 

$mediaLikeButton = 'off'; // dont show like buttons

$last_notif = $db->count_row('notif', "uid='" . $user_id . "' AND type='inbox'");
// update notification data
if ($last_notif > 0) {
    $db->update('notif', 'lstinb', 0, "uid='" . $user_id . "' AND type='inbox'");
} 

$genHeadTag = '<meta name="robots" content="noindex">
<script src="/js/inbox.js"></script>
<script src="/js/ajax.js"></script>
'; // dont index this

$my_title = $lang_home['inbox'];
require_once"../themes/$config_themes/index.php";

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
if (!empty($_GET['who'])) {
    $who = check($_GET["who"]);
} else {
    $who = '';
} 
if (!empty($_GET['pmid'])) {
    $pmid = check($_GET["pmid"]);
} else {
    $pmid = '';
}
$iml = '';

if ($action == "sendpm") {
    $whonick = $users->getnickfromid($who);
    echo '<img src="../images/img/mail.gif" alt=""> Send PM to ' . $whonick . '<br /><br />'; // update lang
    echo'<form method="post" action="inbxproc.php?action=sendpm&amp;who=' . $who . '">';
    echo'<textarea cols="25" rows="3" name="pmtext"></textarea><br />';
    echo'<input value="Send" name="do" type="submit" /></form><hr>'; // update lang
    
    echo '<br /><br /><a href="inbox.php?action=main" class="btn btn-outline-primary sitelink">Inbox</a><br />';
} elseif ($action == "sendto") {
    // $whonick = getnickfromid($who);
    echo $lang_page['sendpmto'] . ':<br /><br />';
    echo '<form method="post" action="inbxproc.php?action=sendto">';
    echo $lang_home['username'] . ':<br /><input type="text" name="who" maxlength="20" /><br />';
    echo $lang_home['message'] . '<br /><textarea cols="25" rows="3" name="pmtext"></textarea><br />';
    echo '<input value="' . $lang_home['send'] . '" name="do" type="submit" /></form><hr><br />';

    echo '<br /><br /><a href="inbox.php?action=main" class="btn btn-outline-primary sitelink">' . $lang_home['inbox'] . '</a><br />';
} elseif ($action == "main" or empty($action)) {

    $page_set = $db->get_data('page_setting', "uid='" . $user_id . "'");

    if ($page == "" || $page <= 0) $page = 1;

    $num_items = $users->getpmcount($user_id); //changable
    $items_per_page = $page_set['privmes'];
    if ($users->user_device() == 'phone' && $items_per_page > 6) {
        $items_per_page = 6;
    } 
    $num_pages = ceil($num_items / $items_per_page);
    if ($page > $num_pages) $page = $num_pages;
    $limit_start = ($page-1) * $items_per_page;
    if ($limit_start < 0) {
        $limit_start = 0;
    } 

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

            array_push($senders, $item['name']);

            if ($item['unread'] == "1") {
                $iml = '<img src="../images/img/new.gif" alt="New message" />';
            }

            $lnk = '<a href="inbox.php?action=dialog&amp;who=' . $item['byuid'] . '" class="btn btn-outline-primary sitelink">' . $iml . ' ' . $item['name'] . '</a>';
            echo $lnk . "<br />";
        }
    }

        echo '<br /><br/>';

        echo Navigation::numbNavigation('inbox.php?action=main&amp;', $items_per_page, $page, $i);

    } else {
        echo '<img src="../images/img/reload.gif" alt=""> ' . $lang_page['nopmsgs'] . '<br /><br />';
    } 
    // //// UNTILL HERE >>
    echo '<a href="inbox.php?action=sendto" class="btn btn-primary sitelink">' . $lang_page['sendmsg'] . '</a><br />';
} else if ($action == "readpm") {

    $pminfo = $db->get_data('inbox', "id='{$pmid}'", 'text, byuid, timesent, touid, reported, deleted');
    $system_id = $users->getidfromnick('System');

    if ($user_id == $pminfo['touid']) {
        $db->update('inbox', 'unread', 0, "id='" . $pmid . "'");
    } 

    if ($pminfo['deleted'] != $user_id && (($pminfo['touid'] == $user_id) || ($pminfo['byuid'] == $user_id))) {
        if ($user_id == $pminfo['touid']) {
            $ptxt = $lang_page['msgfrom'] . ": ";
            if ($pminfo['byuid'] == $system_id) {
                $bylnk = 'System';
            } else {
                $bylnk = "<a href=\"../pages/user.php?uz=" . $pminfo['byuid'] . "\" class=\"sitelink\">" . $iml . "" . getnickfromid($pminfo['byuid']) . "</a>";
            } 
        } else {
            $ptxt = $lang_page['msgfor'] . ": ";

            $bylnk = "<a href=\"../pages/user.php?uz=" . $pminfo['touid'] . "\" class=\"sitelink\">" . $iml . "" . getnickfromid($pminfo['touid']) . "</a>";
        } 

        echo "$ptxt $bylnk<br />";
        $tmstamp = $pminfo['timesent'];
        $tmdt = date_fixed($tmstamp, "d.m.Y. - H:i:s");
        echo "$tmdt<br /><br />";
        $pmtext = $users->parsepm($pminfo['text']);

        echo $pmtext;

        echo '<br /><br /><form method="post" action="inbxproc.php?action=proc">';
        echo $lang_page['choose'] . ": <select name=\"pmact\">";
        if ($pminfo['byuid'] != $system_id) {
            echo "<option value=\"rep-$pmid\">" . $lang_page['replymsg'] . "</option>";
        } 
        echo "<option value=\"del-$pmid\">" . $lang_home['delete'] . "</option>";
        if (isstarred($pmid)) {
            echo "<option value=\"ust-$pmid\">" . $lang_page['unarchive'] . "</option>";
        } else {
            echo "<option value=\"str-$pmid\">" . $lang_page['archive'] . "</option>";
        } 
        echo "<option value=\"rpt-$pmid\">" . $lang_page['report'] . "</option>"; 
        // echo "<option value=\"frd-$pmid\">Email To</option>";
        // echo "<option value=\"dnl-$pmid\">Download</option>";
        echo "</select>";
        echo '<input value="[' . $lang_home['confirm'] . ']" type="submit" /></form><br />';
        if ((int)$pminfo['byuid'] == (int)$user_id) {
            $whouser = $pminfo['touid'];
        } else {
            $whouser = $pminfo['byuid'];
        } 
        echo '<a href="inbox.php?action=dialog&amp;who=' . $whouser . '" class="btn btn-outline-primary sitelink">Dialog</a>'; // update lang
    } else {
        echo "<img src=\"../images/img/close.gif\" alt=\"X\"/>This PM ain't yours";
    } 
    echo '<br /><br /><a href="inbox.php?action=main" class="btn btn-outline-primary sitelink">' . $lang_home['inbox'] . '</a><br />';
} else if ($action == "dialog") {

    if (empty($page) || $page <= 0) {
        $page = 1;
    } 

    $pms = $db->count_row('inbox', "(byuid=$user_id AND touid=$who) OR (byuid=$who AND touid=$user_id) AND (deleted IS NULL OR deleted = $who) ORDER BY timesent");

    $num_items = $pms; //changable
    $items_per_page = 50;
    $limit_start = $num_items - $items_per_page;
    if ($limit_start < 0) {
        $limit_start = 0;
    }

    if ($num_items > 0) {
        $db->update('inbox', 'unread', 0, "byuid='" . $who . "' AND touid='" . $user_id . "'");


        echo '<form id="message-form" method="post" action="send_pm.php?who=' . $who . '">';
        echo '<div class="form-group">';
        echo '<label for="chatbarText"></label>';
        echo '<input name="pmtext" class="send_pm form-control" id="chatbarText" placeholder="' . $lang_home['message'] . '..." />';
        echo '</div>';
        echo '<input type="hidden" name="who" id="who" value="' . $who . '" class="send_pm" />';

        echo '<input type="hidden" name="lastid" id="lastid" value="' . $who . '" />';
        echo '<button type="submit" class="btn btn-primary" onclick="send_message(); return false;">' . $lang_home['send'] . '</button>';
        echo '</form><br />'; // update lang


        echo '<div id="message_box" class="message_box" style="overflow-y: scroll; height:400px;overflow-x: hidden;">';


        echo '<p id="outputList" class="outputList"></p>'; // ajax messages


        $pms = "SELECT * FROM inbox WHERE (byuid = '" . $user_id . "' AND touid = '" . $who . "') OR (byuid='" . $who . "' AND touid = '" . $user_id . "') AND (deleted IS NULL OR deleted = '" . $who . "') ORDER BY timesent LIMIT $limit_start, $items_per_page";
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
        echo '<img src="../images/img/reload.gif" alt="" /> Inbox is empty!'; // update lang
    } 
    // echo "<br /><br /><a href=\"rwdpm.php?action=dlg&amp;sid=$sid&amp;who=$who\">Download</a><br /><small>only first 50 messages</small><br />";
    echo '<br /><br /><a href="inbox.php?action=main" class="btn btn-outline-primary sitelink">' . $lang_home['inbox'] . '</a><br />';
} else {
    echo "I don't know how you got into here, but there's nothing to show<br /><br />";
} 

echo '<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a>';

require_once"../themes/" . $config_themes . "/foot.php";

?>