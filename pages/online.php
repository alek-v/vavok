<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   29.08.2020. 1:33:47
 */

require_once"../include/startup.php";

if ($vavok->get_configuration('showOnline') == 0 && (!$users->is_reg() && !$users->is_administrator())) $vavok->redirect_to("../");

// page settings
$data_on_page = 10; // online users per page

$current_page->page_title = 'Online';
$vavok->require_header();

echo '<p><img src="../images/img/online.gif" alt=""> <b>' . $localization->string('whoisonline') . '</b></p>';

$total = $db->count_row(DB_PREFIX . 'online');
$totalreg = $db->count_row(DB_PREFIX . 'online', "user > 0");

if (!empty($_GET['list'])) {
    $list = $vavok->check($_GET['list']);
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

$page = isset($_GET['page']) ? $vavok->check($_GET['page']) : 1;

if (isset($_GET['start'])) {
    $start = $vavok->check($_GET['start']);
} 

echo $localization->string('totonsite') . ': <b>' . (int)$total . '</b><br />' . $localization->string('registered') . ':  <b>' . (int)$totalreg . '</b><br /><hr>';

if ($list == "full") {

    $navigation = new Navigation($data_on_page, $total, $page, 'online.php?'); // start navigation

    $start = $navigation->start()['start']; // starting point 

    $full_query = "SELECT * FROM " . DB_PREFIX . "online ORDER BY date DESC LIMIT $start, " . $data_on_page;

    foreach ($db->query($full_query) as $item) {
        $time = $vavok->date_fixed($item['date'], 'H:i');

        if (($item['user'] == "0" || empty($item['user'])) && empty($item['bot'])) {
            echo '<b>' . $localization->string('guest') . '</b> (' . $localization->string('time') . ': ' . $time . ')<br />';
            if ($users->is_moderator() || $users->is_administrator()) {
                echo '<small><font color="#CC00CC">(<a href="../' . $vavok->get_configuration('mPanel') . '/ip-informations.php?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
            } 
            echo '<hr />';
        } elseif (!empty($item['bot']) && ($item['user'] == "0" || empty($item['user']))) {
            echo '<b>' . $item['bot'] . '</b> (' . $localization->string('time') . ': ' . $time . ')<br />';
            if ($users->is_moderator() || $users->is_administrator()) {
                echo '<small><font color="#CC00CC">(<a href="../' . $vavok->get_configuration('mPanel') . '/ip-informations.php?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
            } 
            echo '<hr />';
        } else {
            echo '<b><a href="../pages/user.php?uz=' . $item['user'] . '">' . $users->getnickfromid($item['user']) . '</a></b> (' . $localization->string('time') . ': ' . $time . ')<br />';
            if ($users->is_moderator() || $users->is_administrator()) {
                echo '<small><font color="#CC00CC">(<a href="../' . $vavok->get_configuration('mPanel') . '/ip-informations.php?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
            }
            echo '<hr />';
        }
    }
} else {
    $total = $totalreg;

    if ($total < 1) {
        echo '<br /><img src="../images/img/reload.gif" alt=""> <b>' . $localization->string('noregd') . '!</b><br />';
    }

    $navigation = new Navigation($data_on_page, $total, $page, 'online.php?'); // start navigation

    $start = $navigation->start()['start']; // starting point

    $full_query = "SELECT * FROM " . DB_PREFIX . "online WHERE user > 0 ORDER BY date DESC LIMIT $start, " . $data_on_page;

    foreach ($db->query($full_query) as $item) {
        $time = $vavok->date_fixed($item['date'], 'H:i');

        echo '<b><a href="../pages/user.php?uz=' . $item['user'] . '">' . $users->getnickfromid($item['user']) . '</a></b> (' . $localization->string('time') . ': ' . $time . ')<br />';
        if ($users->is_moderator() || $users->is_administrator()) {
            echo '<small><font color="#CC00CC">(<a href="../' . $vavok->get_configuration('mPanel') . '/ip-informations.php?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
        }
        echo '<hr />';
    }
}

echo $navigation->get_navigation();

if ($list != 'full') {
    echo $vavok->sitelink(HOMEDIR . 'pages/online.php?list=full', $localization->string('showguest'), '<p>', '</p>');
} else {
    echo $vavok->sitelink(HOMEDIR . 'pages/online.php?list=reg', $localization->string('hideguest'), '<p>', '</p>');
}

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>