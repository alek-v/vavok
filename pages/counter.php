<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   29.07.2020. 0:02:41
*/

require_once"../include/startup.php";

if (get_configuration('showCounter') == 6 && !$users->is_administrator()) { redirect_to("../"); }

$my_title = $localization->string('statistics');
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

if (!empty($_GET['action'])) {
    $action = check($_GET["action"]);
} else {
    $action = '';
} 

$hour = (int)date("H", time());
$hday = date("j", time())-1;

if (empty($action)) {
    $pcounter_guest = $db->count_row(DB_PREFIX . 'online', "user='0'");

    $pcounter_online = $db->count_row(DB_PREFIX . 'online');

    $pcounter_reg = $pcounter_online - $pcounter_guest;

    $counts = $db->get_data(DB_PREFIX . 'counter');

    $clicks_today = $counts['clicks_today'];
    $total_clicks = $counts['clicks_total'];
    $visits_today = $counts['visits_today']; // visits today
    $total_visits = $counts['visits_total']; // total visits

    echo $localization->string('temponline') . ': ';
    if (get_configuration('showOnline') == 1 || $users->is_administrator()) {
        echo '<a href="online.php">' . (int)$pcounter_online . '</a><br />';
    } else {
        echo '<b>' . (int)$pcounter_online . '</b><br />';
    }

    echo $localization->string('registered') . ': <b>' . (int)$pcounter_reg . '</b><br />';
    echo $localization->string('guests') . ': <b>' . (int)$pcounter_guest . '</b><br /><br />';

    echo $localization->string('vststoday') . ': <b>' . (int)$visits_today . '</b><br />';
    echo $localization->string('vstpagestoday') . ': <b>' . (int)$clicks_today . '</b><br />';
    echo $localization->string('totvisits') . ': <b>' . (int)$total_visits . '</b><br />';
    echo $localization->string('totopenpages') . ': <b>' . (int)$total_clicks . '</b><br /><br />';

    //echo $localization->string('vstinhour') . ': <b>' . (int)$pcounter_hourhost . '</b><br />';
    //echo $localization->string('vstpagesinhour') . ': <b>' . (int)$pcounter_hourhits . '</b><br /><br />';

}

// last 24 hours
if ($action == "count24") {
    exit;
    echo'<img src="../images/img/partners.gif" alt="" /> <b>' . $localization->string('statbyhour') . '</b><br /><br />';

    $p24_hits = file(BASEDIR . "used/datacounter/24_hits.dat");
    $p24_hits = explode("|", $p24_hits[0]);

    $p24_host = file(BASEDIR . "used/datacounter/24_host.dat");
    $p24_host = explode("|", $p24_host[0]);

    echo $localization->string('vstin24hgraph') . '<br />';
    echo '<img src="' . BASEDIR . 'gallery/count24.php" alt="" /><br /><br />';

    if ($hour > 0) {
        echo '<b>' . $localization->string('vstdpages') . '</b><br />';

        for($i = 0;$i < $hour;$i++) {
            $p24_hitshour = explode("-", $p24_hits[$i]);

            $tekhour = (date("H:i", $p24_hitshour[0]));
            $tekhour2 = (date("H:i", $p24_hitshour[0]-3600));

            if ($tekhour != "" && $tekhour2 != "") {
                echo $tekhour2 . '-' . $tekhour . ' - <b>' . (int)$p24_hitshour[1] . '</b> ' . $localization->string('views') . '<br />';
            } 
        } 

        echo '<br /><b>' . $localization->string('uniquevsts') . '</b><br />';

        for($i = 0;$i < $hour;$i++) {
            $p24_hosthour = explode("-", $p24_host[$i]);
            $tekhour = (date("H:i", $p24_hosthour[0]));
            $tekhour2 = (date("H:i", $p24_hosthour[0]-3600));

            if ($tekhour != "" && $tekhour2 != "") {
                echo $tekhour2 . '-' . $tekhour . ' - <b>' . (int)$p24_hosthour[1] . '</b> ' . $localization->string('visitss') . '<br />';
            } 
        } 
    } else {
        echo $localization->string('statnotformed') . '<br />';
    } 

    echo'<br /><a href="counter.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a>';
} 
// statistics for a month
if ($action == "count31") {
    exit;
    echo '<img src="../images/img/partners.gif" alt=""> <b>' . $localization->string('statbyday') . '</b><br /><br />';

    $p31_hits = file(BASEDIR . "used/datacounter/31_hits.dat");
    $p31_hits = explode("|", $p31_hits[0]);

    $p31_host = file(BASEDIR . "used/datacounter/31_host.dat");
    $p31_host = explode("|", $p31_host[0]);

    echo 'This month<br />';
    echo '<img src="' . BASEDIR . 'gallery/count31.php" alt="" /><br /><br />';

    if ($hday > 0) {
        echo '<b>' . $localization->string('vstdpages') . '</b><br />';

        for($i = 0;$i < $hday;$i++) {
            $p31_hitshour = explode("-", $p31_hits[$i]);

            $tekhour = (date("d.m", $p31_hitshour[0]));
            $tekhour2 = (date("d.m", $p31_hitshour[0]-86400));

            if ($tekhour != "" && $tekhour2 != "") {
                echo $tekhour2 . '-' . $tekhour . ' - <b>' . (int)$p31_hitshour[1] . '</b> ' . $localization->string('views') . '<br />';
            } 
        } 

        echo '<br /><b>' . $localization->string('uniquevsts') . '</b><br />';

        for($i = 0;$i < $hday;$i++) {
            $p31_hosthour = explode("-", $p31_host[$i]);

            $tekhour = (date("d.m", $p31_hosthour[0]));
            $tekhour2 = (date("d.m", $p31_hosthour[0]-86400));

            if ($tekhour != "" && $tekhour2 != "") {
                echo $tekhour2 . '-' . $tekhour . ' - <b>' . (int)$p31_hosthour[1] . '</b> ' . $localization->string('visitss') . '<br />';
            } 
        } 
    } else {
        echo '<p>' . $localization->string('statnotformed') . '</p>';
    } 

    echo '<p><a href="counter.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a></p>';
} 

echo '<p><a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>