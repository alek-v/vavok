<?php 
// (c) vavok.net
include("../include/strtup.php");

if (!$users->is_reg()) {
    redirect_to("../pages/error.php?isset=nologin");
} 

$mediaLikeButton = 'off'; // dont show like buttons

$action = check($_GET["action"]);
$ajax = isset($_GET["ajax"]) ? $ajax = check($_GET["ajax"]) : $ajax = '';
$pmtext = isset($_POST["pmtext"]) ? $pmtext = check($_POST["pmtext"]) : $pmtext = '';
$pmtext = no_br($pmtext, '[br]');
$who = isset($_GET["who"]) ? $who = check($_GET["who"]) : $who = '';
$timex = time();
$system_id = $users->getidfromnick('System');
$config["floodTime"] = 1;

if ($who == $system_id) {
    redirect_to("inbox.php");
} 

$my_title = $lang_home['inbox'];
if (empty($ajax)) {
    include("../themes/$config_themes/index.php");
} 

if ($action == "sendpm") {
    $inbox_notif = $db->get_data('notif', "uid='{$user_id}' AND type='inbox'", 'active');

    $whonick = $users->getnickfromid($who);
    $byuid = $user_id;
    $tm = time();

    $stmt = $db->query("SELECT MAX(timesent) FROM inbox WHERE byuid='{$byuid}'");
    $lastpm = (integer) $stmt->fetch(PDO::FETCH_COLUMN);
    $stmt->closeCursor();

    $pmfl = $lastpm + $config["floodTime"]; 
    // if ($byuid == 1)$pmfl = 0;
    if ($pmfl < $tm) {
        if (!isignored($byuid, $who)) {

            $users->send_pm($pmtext, $user_id, $who);

            if (empty($ajax)) {

                echo "<img src=\"../images/img/open.gif\" alt=\"O\"/>";
                echo $lang_page['msgsentto'] . " $whonick<br /><br />";
                echo $users->parsepm($pmtext);

            } else {
                header("Location: inbox.php?action=dialog&who=" . $who);
                exit;
            }

        } else {

            echo "<img src=\"../images/img/close.gif\" alt=\"X\"/> ";
            echo $lang_page['msgnotsent'] . "<br /><br />";
        } 
    } else {

        $rema = $pmfl - $tm;
        echo "<img src=\"../images/img/close.gif\" alt=\"X\"/> ";
        echo "Flood control: $rema Seconds<br /><br />";

    }
    
    echo '<br /><br /><img src="../images/img/mail.gif" alt=""> <a href="inbox.php?action=main">' . $lang_home['inbox'] . '</a><br />';
} 

if ($action == "sendto") {
    $inbox_notif = $db->select('notif', "uid='" . $user_id . "' AND type='inbox'", '', 'active');

    $pmtou = check($_POST["who"]);
    $who = $users->getidfromnick($pmtou);
    if ($who == 0) {
        echo "<img src=\"../images/img/close.gif\" alt=\"X\"/> " . $lang_home['usrnoexist'] . "<br />";
    } else {
        $whonick = $users->getnickfromid($who);
        $byuid = $user_id;
        $tm = time();

        $stmt = $db->query("SELECT MAX(timesent) FROM inbox WHERE byuid='" . $byuid . "'");
        $lastpm = (integer) $stmt->fetch(PDO::FETCH_COLUMN);
        $stmt->closeCursor();

        $pmfl = $lastpm + $config["floodTime"];

        if ($pmfl < $tm) {
            if ((!isignored($byuid, $who))) {

                $users->send_pm($pmtext, $byuid, $who);

                echo "<img src=\"../images/img/open.gif\" alt=\"O\"/> ";
                echo $lang_page['msgsentto'] . " " . $whonick . "<br /><br />";
                echo $pmtext;

                $user_profile = $db->select('vavok_profil', "uid='" . $who . "'", '', 'lastvst');
                $last_notif = $db->select('notif', "uid='" . $who . "' AND type='inbox'", '', 'lstinb, type'); 
                // no data in database, insert data
                if (empty($last_notif['lstinb']) && empty($last_notif['type'])) {
                    $db->insert_data('notif', array('uid' => $who, 'lstinb' => $timex, 'type' => 'inbox'));
                } 
                $notif_expired = $last_notif['lstinb'] + 864000;

                if (($user_profile['lastvst'] + 3600) < $timex && $timex > $notif_expired && ($inbox_notif['active'] == 1 || empty($inbox_notif['active']))) {
                    $user_mail = $db->select('vavok_about', "uid='" . $who . "'", '', 'email');

                    sendmail($user_mail['email'], "Message on " . $config["homeUrl"] . "", "Hello " . getnickfromid($who) . "\r\n\r\nYou have new message on site " . $config["homeUrl"] . ""); // update lang
                    
                    $db->update('notif', 'lstinb', $timex, "uid='" . $who . "' AND type='inbox'");
                } 
            } else {
                echo "<img src=\"../images/img/close.gif\" alt=\"X\"/> ";
                echo $lang_page['msgnotsent'] . "<br /><br />";
            } 
        } else {
            $rema = $pmfl - $tm;
            echo "<img src=\"../images/img/close.gif\" alt=\"X\"/> ";
            echo $lang_page['fcwaitmore'] . " " . $rema . " " . $lang_page['seconds'] . "<br /><br />";
        } 
    } 

    echo '<br /><br /><img src="../images/img/mail.gif" alt=""> <a href="inbox.php?action=main">' . $lang_home['inbox'] . '</a><br />';
} 

if ($action == "proc") {
    $pmact = check($_POST["pmact"]);
    $pact = explode("-", $pmact);
    $pmid = $pact[1];
    $pact = $pact[0];

    $pminfo = $db->select('inbox', "id='" . $pmid . "'", '', 'text, byuid, touid, reported, deleted');
    if ($pact == "rep") {
        $whonick = getnickfromid($pminfo['byuid']);
        echo $lang_page['msgfor'] . " " . $whonick . "<br /><br />";
        echo '<form method="post" action="inbxproc.php?action=sendpm&amp;who=' . $pminfo['byuid'] . '">';
        echo '<textarea cols="25" rows="3" name="pmtext"></textarea><br />';
        echo '<input value="' . $lang_home['send'] . '" name="do" type="submit" /></form><hr>';
    } else if ($pact == "del") {
        if ($user_id == $pminfo['touid'] || $user_id == $pminfo['byuid']) {
            if ($pminfo['reported'] == "1") {
                echo "<img src=\"../images/img/close.gif\" alt=\"X\"/> " . $lang_page['msgreported'] . "";
            }
            // delete message from system
            elseif (empty($pminfo['deleted']) && $pminfo['byuid'] == '0') {
                $db->delete('inbox', "id='" . $pmid . "'");
            } elseif (empty($pminfo['deleted']) || $pminfo['deleted'] == $user_id) {
                $db->update('inbox', 'deleted', $user_id, "id='" . $pmid . "'");
            } else {
                $db->delete('inbox', "id='" . $pmid . "'");
            } 
            echo "<img src=\"../images/img/open.gif\" alt=\"O\"/> " . $lang_page['msgdelok'] . "";
        } else {
            echo "<img src=\"../images/img/close.gif\" alt=\"X\"/> This PM ain't yours";
        } 
    } else if ($pact == "str") {
        if (getidfromnick($log) == $pminfo['touid']) {
            $db->update('inbox', 'starred', 1, "id='" . $pmid . "'");

            echo "<img src=\"../images/img/open.gif\" alt=\"O\"/> " . $lang_page['msgarchok'] . "";
        } else {
            echo "<img src=\"../images/img/close.gif\" alt=\"X\"/> This PM ain't yours";
        } 
    } else if ($pact == "ust") {
        if (getidfromnick($log) == $pminfo['touid']) {
            $db->update('inbox', 'starred', 0, "id='" . $pmid . "'");

            echo "<img src=\"../images/img/open.gif\" alt=\"O\"/> " . $lang_page['msgdearchok'] . "";
        } else {
            echo "<img src=\"../images/img/close.gif\" alt=\"X\"/> This PM ain't yours";
        } 
    } else if ($pact == "rpt") {
        if (getidfromnick($log) == $pminfo['touid']) {
            if ($pminfo['reported'] == "0") {
                $db->update('inbox', 'reported', 1, "id='" . $pmid . "'");

                echo "<img src=\"../images/img/open.gif\" alt=\"O\"/> " . $lang_page['msgreportok'] . "";
            } else {
                echo "<img src=\"../images/img/close.gif\" alt=\"X\"/> " . $lang_page['msgalreareptd'] . "";
            } 
        } else {
            echo "<img src=\"../images/img/close.gif\" alt=\"X\"/> This PM ain't yours";
        } 
    } else if ($pact == "frd") {
        if ($user_id == $pminfo['touid'] || $user_id == $pminfo['byuid']) {
            echo "Forward to e-mail:<br /><br />";
            echo "<input name=\"email\" maxlength=\"250\"/><br />";
            echo "<anchor>Froward<go href=\"inbxproc.php?action=frdpm\" method=\"post\">";
            echo "<postfield name=\"email\" value=\"$(email)\"/>";
            echo "<postfield name=\"pmid\" value=\"$pmid\"/>";
            echo "</go></anchor>";
        } else {
            echo "<img src=\"../images/img/close.gif\" alt=\"X\"/>This PM ain't yours";
        } 
    } else if ($pact == "dnl") {
        if (getidfromnick($log) == $pminfo['touid'] || $users->getidfromnick($log) == $pminfo['byuid']) {
            echo "<img src=\"../images/img/open.gif\" alt=\"X\"/>request processed successfully<br /><br />";
            echo "<a href=\"rwdpm.php?action=dpm&amp;pmid=$pmid\">Download PM</a>";
        } else {
            echo "<img src=\"../images/img/close.gif\" alt=\"X\"/>This PM ain't yours";
        } 
    } 

    echo '<br /><br /><img src="../images/img/mail.gif" alt=""> <a href="inbox.php?action=main">' . $lang_home['inbox'] . '</a><br />';
} 

if ($action == "proall") {
    $pact = check($_POST["pmact"]);

    $uid = $user_id;
    if ($pact == "ust") {
        $db->delete('inbox', "touid='" . $uid . "' AND reported !='1' AND starred='0' And unread='0'");

        echo "<img src=\"../images/img/open.gif\" alt=\"O\"/> " . $lang_page['allmsginarunr'] . "";
    } else if ($pact == "red") {
        $db->delete('inbox', "touid='" . $uid . "' AND reported !='1' and unread='0'");

        echo "<img src=\"../images/img/open.gif\" alt=\"O\"/> " . $lang_page['allmsgiunandard'] . "";
    } else if ($pact == "all") {
        $db->delete('inbox', "touid='" . $uid . "' AND reported !='1'");

        echo "<img src=\"../images/img/open.gif\" alt=\"O\"/> " . $lang_page['allmsgeunrarcd'] . "";
    } 

    echo '<br /><br /><img src="../images/img/mail.gif" alt=""> <a href="inbox.php?action=main">' . $lang_home['inbox'] . '</a><br />';
} 

if ($action == "frdpm") {
    exit; // temp
    $email = check($_POST["email"]);
    $pmid = check($_POST["pmid"]);

    $pminfo = $db->select('inbox', "id='" . $pmid . "'", '', 'text, byuid, timesent, touid, reported');

    if (($pminfo[3] == $users->getidfromnick($log)) || ($pminfo[1] == $users->getidfromnick($log))) {
        $from_head = "From: noreplay@system";
        $subject = "PM By " . getnickfromid($pminfo[1]) . " To " . getnickfromid($pminfo[3]) . " (www.vavok.net)";
        $content = "Date: " . date("l d/m/y H:i:s", $pminfo[2]) . "\n\n";
        $content .= $pminfo[0] . "\n------------------------\n";
        $content .= "Vavok.net";
        mail($email, $subject, $content, $from_head);
        echo "<img src=\"../images/img/open.gif\" alt=\"X\"/>PM forwarded to $email";
    } else {
        echo "<img src=\"../images/img/close.gif\" alt=\"X\"/>This PM ain't yours";
    } 
    echo '<br /><br /><img src="../images/img/mail.gif" alt=""> <a href="inbox.php?action=main">' . $lang_home['inbox'] . '</a><br />';
} 

if (empty($action)) {
    echo "I don't know how you got into here, but there's nothing to show <br /><br />";
} 

echo '<img src="../images/img/homepage.gif" alt=""> <a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a>';

include("../themes/$config_themes/foot.php");

?>