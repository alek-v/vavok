<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if ($vavok->get_configuration('showCounter') == 6 && !$vavok->go('users')->is_administrator()) { $vavok->redirect_to("../"); }

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('statistics');
$vavok->require_header();

if (!empty($_GET['action'])) {
    $action = $vavok->check($_GET["action"]);
} else {
    $action = '';
}

$hour = (int)date("H", time());
$hday = date("j", time())-1;

if (empty($action)) {
    $pcounter_guest = $vavok->go('db')->count_row(DB_PREFIX . 'online', "user='0'");

    $pcounter_online = $vavok->go('db')->count_row(DB_PREFIX . 'online');

    $pcounter_reg = $pcounter_online - $pcounter_guest;

    $counts = $vavok->go('db')->get_data(DB_PREFIX . 'counter');

    $clicks_today = $counts['clicks_today'];
    $total_clicks = $counts['clicks_total'];
    $visits_today = $counts['visits_today']; // visits today
    $total_visits = $counts['visits_total']; // total visits

    echo $vavok->go('localization')->string('temponline') . ': ';
    if ($vavok->get_configuration('showOnline') == 1 || $vavok->go('users')->is_administrator()) {
        echo '<a href="online.php">' . (int)$pcounter_online . '</a><br />';
    } else {
        echo '<b>' . (int)$pcounter_online . '</b><br />';
    }

    echo $vavok->go('localization')->string('registered') . ': <b>' . (int)$pcounter_reg . '</b><br />';
    echo $vavok->go('localization')->string('guests') . ': <b>' . (int)$pcounter_guest . '</b><br /><br />';

    echo $vavok->go('localization')->string('vststoday') . ': <b>' . (int)$visits_today . '</b><br />';
    echo $vavok->go('localization')->string('vstpagestoday') . ': <b>' . (int)$clicks_today . '</b><br />';
    echo $vavok->go('localization')->string('totvisits') . ': <b>' . (int)$total_visits . '</b><br />';
    echo $vavok->go('localization')->string('totopenpages') . ': <b>' . (int)$total_clicks . '</b><br /><br />';

    //echo $vavok->go('localization')->string('vstinhour') . ': <b>' . (int)$pcounter_hourhost . '</b><br />';
    //echo $vavok->go('localization')->string('vstpagesinhour') . ': <b>' . (int)$pcounter_hourhits . '</b><br /><br />';

}

// last 24 hours
if ($action == "count24") {
    exit;
    echo'<img src="../images/img/partners.gif" alt="" /> <b>' . $vavok->go('localization')->string('statbyhour') . '</b><br /><br />';

    $p24_hits = $vavok->get_data_file('datacounter/24_hits.dat');
    $p24_hits = explode("|", $p24_hits[0]);

    $p24_host = $vavok->get_data_file('datacounter/24_host.dat');
    $p24_host = explode("|", $p24_host[0]);

    echo $vavok->go('localization')->string('vstin24hgraph') . '<br />';
    echo '<img src="' . BASEDIR . 'gallery/count24.php" alt="" /><br /><br />';

    if ($hour > 0) {
        echo '<b>' . $vavok->go('localization')->string('vstdpages') . '</b><br />';

        for ($i = 0;$i < $hour;$i++) {
            $p24_hitshour = explode("-", $p24_hits[$i]);

            $tekhour = (date("H:i", $p24_hitshour[0]));
            $tekhour2 = (date("H:i", $p24_hitshour[0]-3600));

            if ($tekhour != "" && $tekhour2 != "") {
                echo $tekhour2 . '-' . $tekhour . ' - <b>' . (int)$p24_hitshour[1] . '</b> ' . $vavok->go('localization')->string('views') . '<br />';
            }
        }

        echo '<br /><b>' . $vavok->go('localization')->string('uniquevsts') . '</b><br />';

        for($i = 0;$i < $hour;$i++) {
            $p24_hosthour = explode("-", $p24_host[$i]);
            $tekhour = (date("H:i", $p24_hosthour[0]));
            $tekhour2 = (date("H:i", $p24_hosthour[0]-3600));

            if ($tekhour != "" && $tekhour2 != "") {
                echo $tekhour2 . '-' . $tekhour . ' - <b>' . (int)$p24_hosthour[1] . '</b> ' . $vavok->go('localization')->string('visitss') . '<br />';
            }
        }
    } else {
        echo $vavok->go('localization')->string('statnotformed') . '<br />';
    }

    echo '<br /><a href="counter.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a>';
}

// statistics for a month
if ($action == "count31") {
    exit;
    echo '<img src="../images/img/partners.gif" alt=""> <b>' . $vavok->go('localization')->string('statbyday') . '</b><br /><br />';

    $p31_hits = $vavok->get_data_file('datacounter/31_hits.dat');
    $p31_hits = explode("|", $p31_hits[0]);

    $p31_host = $vavok->get_data_file('datacounter/31_host.dat');
    $p31_host = explode("|", $p31_host[0]);

    echo 'This month<br />';
    echo '<img src="' . BASEDIR . 'gallery/count31.php" alt="" /><br /><br />';

    if ($hday > 0) {
        echo '<b>' . $vavok->go('localization')->string('vstdpages') . '</b><br />';

        for($i = 0;$i < $hday;$i++) {
            $p31_hitshour = explode("-", $p31_hits[$i]);

            $tekhour = (date("d.m", $p31_hitshour[0]));
            $tekhour2 = (date("d.m", $p31_hitshour[0]-86400));

            if ($tekhour != "" && $tekhour2 != "") {
                echo $tekhour2 . '-' . $tekhour . ' - <b>' . (int)$p31_hitshour[1] . '</b> ' . $vavok->go('localization')->string('views') . '<br />';
            }
        }

        echo '<p><b>' . $vavok->go('localization')->string('uniquevsts') . '</b></p>';

        for($i = 0;$i < $hday;$i++) {
            $p31_hosthour = explode("-", $p31_host[$i]);

            $tekhour = (date("d.m", $p31_hosthour[0]));
            $tekhour2 = (date("d.m", $p31_hosthour[0]-86400));

            if ($tekhour != "" && $tekhour2 != "") {
                echo $tekhour2 . '-' . $tekhour . ' - <b>' . (int)$p31_hosthour[1] . '</b> ' . $vavok->go('localization')->string('visitss') . '<br />';
            }
        }
    } else {
        echo '<p>' . $vavok->go('localization')->string('statnotformed') . '</p>';
    }

    echo $vavok->sitelink('counter.php', $vavok->go('localization')->string('back'), '<p>', '</p>');
}

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>