<?php 
// (c) vavok.net
require_once"../include/strtup.php";

if ($config["showOnline"] == 0 && (!$users->is_reg() && !$users->is_administrator())) {
    redirect_to("../");
} 

// page settings
$data_on_page = 10; // online users per page
$mediaLikeButton = 'off'; // dont show like buttons

$my_title = 'Online';
include_once"../themes/$config_themes/index.php";

 

echo '<img src="../images/img/online.gif" alt=""> <b>' . $lang_page['whoisonline'] . '</b><br /><br />';

$total = $db->count_row(get_configuration('tablePrefix') . 'online');
$totalreg = $db->count_row(get_configuration('tablePrefix') . 'online', "user > 0");

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

$page = isset($_GET['page']) ? check($_GET['page']) : 1;

if (isset($_GET['start'])) {
    $start = check($_GET['start']);
} 

echo $lang_page['totonsite'] . ': <b>' . (int)$total . '</b><br />' . $lang_page['registered'] . ':  <b>' . (int)$totalreg . '</b><br /><hr>';

if ($list == "full") {

    $navigation = new Navigation($data_on_page, $total, $page, 'online.php?'); // start navigation

    $start = $navigation->start()['start']; // starting point 

    $full_query = "SELECT * FROM " . get_configuration('tablePrefix') . "online ORDER BY date DESC LIMIT $start, " . $data_on_page;

    foreach ($db->query($full_query) as $item) {
        $time = date_fixed($item['date'], 'H:i');

        if (($item['user'] == "0" || empty($item['user'])) && empty($item['bot'])) {
            echo '<b>' . $lang_home['guest'] . '</b> (' . $lang_home['time'] . ': ' . $time . ')<br />';
            if ($users->is_moderator() || $users->is_administrator()) {
                echo '<small><font color="#CC00CC">(<a href="../' . $config["mPanel"] . '/ip-informations.php?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
            } 
            echo '<hr />';
        } elseif (!empty($item['bot']) && ($item['user'] == "0" || empty($item['user']))) {
            echo '<b>' . $item['bot'] . '</b> (' . $lang_home['time'] . ': ' . $time . ')<br />';
            if ($users->is_moderator() || $users->is_administrator()) {
                echo '<small><font color="#CC00CC">(<a href="../' . $config["mPanel"] . '/ip-informations.php?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
            } 
            echo '<hr />';
        } else {
            echo '<b><a href="../pages/user.php?uz=' . $item['user'] . '">' . getnickfromid($item['user']) . '</a></b> (' . $lang_home['time'] . ': ' . $time . ')<br />';
            if ($users->is_moderator() || $users->is_administrator()) {
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

    $navigation = new Navigation($data_on_page, $total, $page, 'online.php?'); // start navigation

    $start = $navigation->start()['start']; // starting point  

    $full_query = "SELECT * FROM " . get_configuration('tablePrefix') . "online WHERE user > 0 ORDER BY date DESC LIMIT $start, " . $data_on_page;

    foreach ($db->query($full_query) as $item) {
        $time = date_fixed($item['date'], 'H:i');

        echo '<b><a href="../pages/user.php?uz=' . $item['user'] . '">' . getnickfromid($item['user']) . '</a></b> (' . $lang_home['time'] . ': ' . $time . ')<br />';
        if ($users->is_moderator() || $users->is_administrator()) {
            echo '<small><font color="#CC00CC">(<a href="../' . $config["mPanel"] . '/ip-informations.php?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
        } 
        echo '<hr />';
    } 
} 

echo $navigation->get_navigation();

if ($list != "full") {
    echo'<p><a href="online.php?list=full" class="btn btn-outline-primary sitelink">' . $lang_page['showguest'] . '</a></p>';
} else {
    echo'<p><a href="online.php?list=reg" class="btn btn-outline-primary sitelink">' . $lang_page['hideguest'] . '</a></p>';
} 

echo '<p><a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';

include_once "../themes/" . $config_themes . "/foot.php";

?>