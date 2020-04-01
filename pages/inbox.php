<?php 
// (c) vavok.net
require_once"../include/strtup.php";

if (!is_reg()) {
    header ("Location: ../");
    exit;
} 

$mediaLikeButton = 'off'; // dont show like buttons

$last_notif = $db->count_row('notif', "uid='" . $user_id . "' AND type='inbox'");
// update notification data
if ($last_notif > 0) {
    $db->update('notif', 'lstinb', 0, "uid='" . $user_id . "' AND type='inbox'");
} 

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
    $whonick = getnickfromid($who);
    echo '<img src="../images/img/mail.gif" alt=""> Send PM to ' . $whonick . '<br /><br />'; // update lang
    echo'<form method="post" action="inbxproc.php?action=sendpm&amp;who=' . $who . '">';
    echo'<textarea cols="25" rows="3" name="pmtext"></textarea><br />';
    echo'<input value="Send" name="do" type="submit" /></form><hr>'; // update lang
    
    echo '<br /><br /><a href="inbox.php?action=main" class="sitelink">Inbox</a><br />';
} elseif ($action == "sendto") {
    // $whonick = getnickfromid($who);
    echo $lang_page['sendpmto'] . ':<br /><br />';
    echo '<form method="post" action="inbxproc.php?action=sendto">';
    echo $lang_home['username'] . ':<br /><input type="text" name="who" maxlength="20" /><br />';
    echo $lang_home['message'] . '<br /><textarea cols="25" rows="3" name="pmtext"></textarea><br />';
    echo '<input value="' . $lang_home['send'] . '" name="do" type="submit" /></form><hr><br />';

    echo '<br /><br /><a href="inbox.php?action=main" class="sitelink">' . $lang_home['inbox'] . '</a><br />';
} elseif ($action == "main" or empty($action)) {
    echo'<form method="post" action="inbox.php?action=main">';
    echo $lang_page['view'] . ": <select name=\"view\">";
    echo "<option value=\"all\">" . $lang_page['all'] . "</option>";
    echo "<option value=\"snt\">Sent</option>"; // update lang
    echo "<option value=\"str\">" . $lang_page['archived'] . "</option>";
    echo "<option value=\"urd\">" . $lang_page['unread'] . "</option>";
    echo "</select>";
    echo ' <input value="[' . $lang_home['confirm'] . ']" type="submit" /></form><br />';

    if (isset($_GET['view'])) {
        $view = check($_GET["view"]);
    } else if (isset($_POST['view'])) {
        $view = check($_POST["view"]);
    } else {
        $view = '';
    }

    if (!empty($view)) {
        $show_view = str_replace("all", $lang_page['allreceivedm'], $view);
        $show_view = str_replace("snt", "Sent messages", $show_view); // update lang
        $show_view = str_replace("str", $lang_page['archived'], $show_view);
        $show_view = str_replace("urd", $lang_page['unread'], $show_view);
        echo $lang_page['view'] . ': ' . check($show_view) . '';
    } 
    echo '<br /><br />';

    $page_set = $db->select('page_setting', "uid='" . $user_id . "'", '', '*');

    if (empty($view)) {
        $view = "all";
    } 
    if ($page == "" || $page <= 0) $page = 1;
    $myid = $user_id;
    $doit = false;
    $num_items = $users->getpmcount($myid, $view); //changable
    $items_per_page = $page_set['privmes'];
    if ($userDevice == 'phone' && $items_per_page > 6) {
        $items_per_page = 6;
    } 
    $num_pages = ceil($num_items / $items_per_page);
    if ($page > $num_pages) $page = $num_pages;
    $limit_start = ($page-1) * $items_per_page;
    if ($limit_start < 0) {
        $limit_start = 0;
    } 

    if ($num_items > 0) {
        if ($doit) {
            $exp = "&amp;rwho=$myid";
        } else {
            $exp = "";
        } 
        // changable sql
        if ($view == "all") {
            $sql = "SELECT
            a.name, b.id, b.byuid, b.unread, b.starred FROM vavok_users a
            INNER JOIN inbox b ON a.id = b.byuid
            WHERE b.touid='" . $myid . "' AND (deleted IS NULL OR deleted <> '" . $user_id . "')
            ORDER BY b.timesent DESC
            LIMIT $limit_start, $items_per_page
    ";
        } else if ($view == "snt") {
            $sql = "SELECT
            a.name, b.id, b.touid, b.unread, b.starred FROM vavok_users a
            INNER JOIN inbox b ON a.id = b.touid
            WHERE b.byuid='" . $myid . "' AND (deleted IS NULL OR deleted <> '" . $user_id . "')
            ORDER BY b.timesent DESC
            LIMIT $limit_start, $items_per_page
    ";
        } else if ($view == "str") {
            $sql = "SELECT
            a.name, b.id, b.byuid, b.unread, b.starred FROM vavok_users a
            INNER JOIN inbox b ON a.id = b.byuid
            WHERE b.touid='" . $myid . "' AND b.starred='1'  AND (deleted IS NULL OR deleted <> '" . $user_id . "')
            ORDER BY b.timesent DESC
            LIMIT $limit_start, $items_per_page
    ";
        } else if ($view == "urd") {
            $sql = "SELECT
            a.name, b.id, b.byuid, b.unread, b.starred FROM vavok_users a
            INNER JOIN inbox b ON a.id = b.byuid
            WHERE b.touid='" . $myid . "' AND b.unread='1'
            ORDER BY b.timesent DESC
            LIMIT $limit_start, $items_per_page
    ";
        } 

        foreach ($db->query($sql) as $item) {
            if ($item['unread'] == "1" && $view !== 'snt') {
                $iml = "<img src=\"../images/img/new.gif\" alt=\"+\"/>";
            } else {
                if ($item['starred'] == "1") {
                    $iml = "<img src=\"../images/img/replies.gif\" alt=\"*\"/>";
                } else {
                    $iml = "<img src=\"../images/img/mail.gif\" alt=\"-\"/>";
                } 
            } 

            $lnk = "$iml <a href=\"inbox.php?action=readpm&amp;pmid=" . $item['id'] . "\" class=\"sitelink\">" . $item['name'] . "</a>";
            echo "$lnk<br />";
        } 

        echo '<br /><br/>';

        page_navigation("inbox.php?action=main&amp;view=$view$exp&amp;", $items_per_page, $page, $num_items);
        page_numbnavig("inbox.php?action=main&amp;view=$view$exp&amp;", $items_per_page, $page, $num_items);

        echo "<br />";

        echo '<form method="post" action="inbxproc.php?action=proall">';
        echo $lang_home['delete'] . ": <select name=\"pmact\">";
        echo "<option value=\"ust\">" . $lang_page['unarchived'] . "</option>";
        echo "<option value=\"red\">" . $lang_page['readed'] . "</option>";
        echo "<option value=\"all\">" . $lang_page['all'] . "</option>";
        echo "</select>";
        echo '<input value="[' . $lang_home['confirm'] . ']" type="submit" /></form><br /><br />';
    } else {
        echo '<img src="../images/img/reload.gif" alt=""> ' . $lang_page['nopmsgs'] . '<br /><br />';
    } 
    // //// UNTILL HERE >>
    echo '<a href="inbox.php?action=sendto" class="sitelink">' . $lang_page['sendmsg'] . '</a><br />';
} else if ($action == "readpm") {
    $pminfo = $db->select('inbox', "id='" . $pmid . "'", '', 'text, byuid, timesent, touid, reported, deleted');
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
        echo '<a href="inbox.php?action=dialog&amp;who=' . $whouser . '" class="sitelink">Dialog</a>'; // update lang
    } else {
        echo "<img src=\"../images/img/close.gif\" alt=\"X\"/>This PM ain't yours";
    } 
    echo '<br /><br /><a href="inbox.php?action=main" class="sitelink">' . $lang_home['inbox'] . '</a><br />';
} else if ($action == "dialog") {
    echo '
<script type="text/javascript">
window.onload=function () {
     var objDiv = document.getElementById("message-box");
     objDiv.scrollTop = objDiv.scrollHeight;
}
</script>
';
    if (empty($page) || $page <= 0) {
        $page = 1;
    } 
    $myid = $user_id;
    $pms = $db->count_row('inbox', "(byuid=$user_id AND touid=$who) OR (byuid=$who AND touid=$user_id) AND (deleted IS NULL OR deleted = $who) ORDER BY timesent", '', 'COUNT(*)');

    $num_items = $pms; //changable
    $items_per_page = 50;
    $limit_start = $num_items - $items_per_page;
    if ($limit_start < 0) {
        $limit_start = 0;
    } 
    if ($num_items > 0) {
        $db->update('inbox', 'unread', 0, "byuid='" . $who . "' AND touid='" . $user_id . "'");


        echo '<div id="message-box" style="overflow-y: scroll; height:400px;overflow-x: hidden;">';
        $pms = "SELECT byuid, text, timesent FROM inbox WHERE (byuid = '" . $user_id . "' AND touid = '" . $who . "') OR (byuid='" . $who . "' AND touid = '" . $user_id . "') AND (deleted IS NULL OR deleted = '" . $who . "') ORDER BY timesent LIMIT $limit_start, $items_per_page";
        foreach ($db->query($pms) as $pm) {
            $bylnk = "<a href=\"../pages/user.php?uz=" . $pm['byuid'] . "\" class=\"sitelink\">" . getnickfromid($pm['byuid']) . "</a> ";
            echo $bylnk;
            $tmopm = date("d m y - h:i:s", $pm['timesent']);
            echo "$tmopm<br />";

            echo $users->parsepm($pm['text']);

            echo '<hr />';
        } 

        echo '<div>&nbsp;</div>';
        echo '<form method="post" action="inbxproc.php?action=sendpm&amp;who=' . $who . '&amp;ajax=1">';
        echo '<textarea cols="25" rows="4" name="pmtext"></textarea><br />';
        echo '<input value="Send" type="submit" /></form>'; // update lang
        echo '</div>';
    } else {
        echo '<img src="../images/img/reload.gif" alt="" /> Inbox is empty!'; // update lang
    } 
    // echo "<br /><br /><a href=\"rwdpm.php?action=dlg&amp;sid=$sid&amp;who=$who\">Download</a><br /><small>only first 50 messages</small><br />";
    echo '<br /><br /><a href="inbox.php?action=main" class="sitelink">' . $lang_home['inbox'] . '</a><br />';
} else {
    echo "I don't know how you got into here, but there's nothing to show<br /><br />";
} 

echo '<a href="../" class="homepage">' . $lang_home['home'] . '</a>';

require_once"../themes/" . $config_themes . "/foot.php";

?>