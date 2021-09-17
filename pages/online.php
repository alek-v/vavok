<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if ($vavok->get_configuration('showOnline') == 0 && (!$vavok->go('users')->is_reg() && !$vavok->go('users')->is_administrator())) $vavok->redirect_to("../");

// page settings
$data_on_page = 10; // online users per page

$vavok->go('current_page')->page_title = 'Online';
$vavok->require_header();

echo '<p><img src="../images/img/online.gif" alt=""> <b>' . $vavok->go('localization')->string('whoisonline') . '</b></p>';

$total = $vavok->go('db')->count_row(DB_PREFIX . 'online');
$totalreg = $vavok->go('db')->count_row(DB_PREFIX . 'online', "user > 0");

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

echo $vavok->go('localization')->string('totonsite') . ': <b>' . (int)$total . '</b><br />' . $vavok->go('localization')->string('registered') . ':  <b>' . (int)$totalreg . '</b><br /><hr>';

if ($list == 'full') {
    $navigation = new Navigation($data_on_page, $total, $vavok->post_and_get('page'), 'online.php?list=full&'); // start navigation

    $start = $navigation->start()['start']; // starting point 

    $full_query = "SELECT * FROM " . DB_PREFIX . "online ORDER BY date DESC LIMIT $start, " . $data_on_page;

    foreach ($vavok->go('db')->query($full_query) as $item) {
        $time = $vavok->date_fixed($item['date'], 'H:i');

        if (($item['user'] == "0" || empty($item['user'])) && empty($item['bot'])) {
            echo '<b>' . $vavok->go('localization')->string('guest') . '</b> (' . $vavok->go('localization')->string('time') . ': ' . $time . ')<br />';
            if ($vavok->go('users')->is_moderator() || $vavok->go('users')->is_administrator()) {
                echo '<small><font color="#CC00CC">(<a href="../' . $vavok->get_configuration('mPanel') . '/ip-informations.php?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
            } 
            echo '<hr />';
        } elseif (!empty($item['bot']) && ($item['user'] == "0" || empty($item['user']))) {
            echo '<b>' . $item['bot'] . '</b> (' . $vavok->go('localization')->string('time') . ': ' . $time . ')<br />';
            if ($vavok->go('users')->is_moderator() || $vavok->go('users')->is_administrator()) {
                echo '<small><font color="#CC00CC">(<a href="../' . $vavok->get_configuration('mPanel') . '/ip-informations.php?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
            } 
            echo '<hr />';
        } else {
            echo '<b><a href="../pages/user.php?uz=' . $item['user'] . '">' . $vavok->go('users')->getnickfromid($item['user']) . '</a></b> (' . $vavok->go('localization')->string('time') . ': ' . $time . ')<br />';
            if ($vavok->go('users')->is_moderator() || $vavok->go('users')->is_administrator()) {
                echo '<small><font color="#CC00CC">(<a href="../' . $vavok->get_configuration('mPanel') . '/ip-informations.php?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
            }
            echo '<hr />';
        }
    }
} else {
    $total = $totalreg;

    if ($total < 1) {
        echo '<p><img src="../images/img/reload.gif" alt=""> <b>' . $vavok->go('localization')->string('noregd') . '!</b></p>';
    }

    $navigation = new Navigation($data_on_page, $total, $vavok->post_and_get('page'), 'online.php?'); // start navigation

    $start = $navigation->start()['start']; // starting point

    $full_query = "SELECT * FROM " . DB_PREFIX . "online WHERE user > 0 ORDER BY date DESC LIMIT $start, " . $data_on_page;

    foreach ($vavok->go('db')->query($full_query) as $item) {
        $time = $vavok->date_fixed($item['date'], 'H:i');

        echo '<b><a href="../pages/user.php?uz=' . $item['user'] . '">' . $vavok->go('users')->getnickfromid($item['user']) . '</a></b> (' . $vavok->go('localization')->string('time') . ': ' . $time . ')<br />';
        if ($vavok->go('users')->is_moderator() || $vavok->go('users')->is_administrator()) {
            echo '<small><font color="#CC00CC">(<a href="../' . $vavok->get_configuration('mPanel') . '/ip-informations.php?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
        }
        echo '<hr />';
    }
}

echo $navigation->get_navigation();

if ($list != 'full') {
    echo $vavok->sitelink(HOMEDIR . 'pages/online.php?list=full', $vavok->go('localization')->string('showguest'), '<p>', '</p>');
} else {
    echo $vavok->sitelink(HOMEDIR . 'pages/online.php?list=reg', $vavok->go('localization')->string('hideguest'), '<p>', '</p>');
}

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>