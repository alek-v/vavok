<?php 
// (c) vavok.net
require_once"../include/strtup.php";

if ($config["showOnline"] == 0 && (!is_reg() && !isadmin())) {
    header("Location: ../");
    exit;
} 

$mediaLikeButton = 'off'; // dont show like buttons

$my_title = 'Online';
include_once"../themes/$config_themes/index.php";
if (isset($_GET['isset'])) {
    $isset = check($_GET['isset']);
    echo '<div align="center"><b><font color="#FF0000">';
    echo get_isset();
    echo '</font></b></div>';
} 

echo '<img src="../images/img/online.gif" alt=""> <b>' . $lang_page['whoisonline'] . '</b><br /><br />';

$total = $db->count_row('online');
$totalreg = $db->count_row('online', "user > 0");

if (!empty($_GET['list'])) {
    $list = check($_GET['list']);
} else {
    if ($totalreg > 0) {
        $list = 'reg';
    } else {
        $list = 'full';
    } 
} 
if ($list != 'full' && $list != 'reg') {
    $list = 'full';
} 
if (isset($_GET['page'])) {
    $page = check($_GET['page']);
} 
if (isset($_GET['start'])) {
    $start = check($_GET['start']);
} 

echo $lang_page['totonsite'] . ': <b>' . (int)$total . '</b><br />' . $lang_page['registered'] . ':  <b>' . (int)$totalreg . '</b><br /><hr>';

if ($list == "full") {
    if (empty($page) || $page < 1) {
        $page = 1;
    } 

    $start = $config["dataOnPage"] * ($page - 1);
    if ($start < 0) {
        $start = 0;
    } 

    $full_query = "SELECT * FROM online ORDER BY date DESC LIMIT $start, " . $config["dataOnPage"];

    foreach ($db->query($full_query) as $item) {
        $time = date_fixed($item['date'], 'H:i');

        if (($item['user'] == "0" || empty($item['user'])) && empty($item['bot'])) {
            echo '<b>' . $lang_home['guest'] . '</b> (' . $lang_home['time'] . ': ' . $time . ')<br />';
            if (ismod() || isadmin()) {
                echo '<small><font color="#CC00CC">(<a href="../' . $config["mPanel"] . '/ip-informations.php?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
            } 
            echo '<hr />';
        } elseif (!empty($item['bot']) && ($item['user'] == "0" || empty($item['user']))) {
            echo '<b>' . $item['bot'] . '</b> (' . $lang_home['time'] . ': ' . $time . ')<br />';
            if (ismod() || isadmin()) {
                echo '<small><font color="#CC00CC">(<a href="../' . $config["mPanel"] . '/ip-informations.php?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
            } 
            echo '<hr />';
        } else {
            echo '<b><a href="../pages/user.php?uz=' . $item['user'] . '">' . getnickfromid($item['user']) . '</a></b> (' . $lang_home['time'] . ': ' . $time . ')<br />';
            if (ismod() || isadmin()) {
                echo '<small><font color="#CC00CC">(<a href="../' . $config["mPanel"] . '/ip-informations.php?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
            } 
            echo '<hr />';
        } 
    } 
} else {
    $total = $totalreg;

    if ($total < 1) {
        echo '<br /><img src="../images/img/reload.gif" alt=""> <b>' . $lang_page['noregd'] . '!</b><br />';
    } 

    if (empty($page) || $page < 1) {
        $page = 1;
    } 

    $start = $config["dataOnPage"] * ($page - 1);
    if ($start < 0) {
    $start = 0;
    } 

    $full_query = "SELECT * FROM online WHERE user > 0 ORDER BY date DESC LIMIT $start, " . $config["dataOnPage"];

    foreach ($db->query($full_query) as $item) {
        $time = date_fixed($item['date'], 'H:i');

        echo '<b><a href="../pages/user.php?uz=' . $item['user'] . '">' . getnickfromid($item['user']) . '</a></b> (' . $lang_home['time'] . ': ' . $time . ')<br />';
        if (ismod() || isadmin()) {
            echo '<small><font color="#CC00CC">(<a href="../' . $config["mPanel"] . '/ip-informations.php?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
        } 
        echo '<hr />';
    } 
} 

page_navigation('online.php?list=' . $list . '&amp;', $config["dataOnPage"], $page, $total);
page_numbnavig('online.php?list=' . $list . '&amp;', $config["dataOnPage"], $page, $total);

if ($list != "full") {
    echo'<p><a href="online.php?list=full" class="sitelink">' . $lang_page['showguest'] . '</a></p>';
} else {
    echo'<p><a href="online.php?list=reg" class="sitelink">' . $lang_page['hideguest'] . '</a></p>';
} 

echo '<p><a href="../" class="homepage">' . $lang_home['home'] . '</a></p>';

include_once "../themes/" . $config_themes . "/foot.php";

?>