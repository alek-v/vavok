<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if ($vavok->get_configuration('showCounter') == 6 && !$vavok->go('users')->is_administrator()) { $vavok->redirect_to("../"); }

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('statistics');
$vavok->require_header();

$hour = (int)date("H", time());
$hday = date("j", time())-1;

if (empty($vavok->post_and_get('action'))) {
    $pcounter_guest = $vavok->go('db')->count_row('online', "user='0'");

    $pcounter_online = $vavok->go('db')->count_row('online');

    $pcounter_reg = $pcounter_online - $pcounter_guest;

    $counts = $vavok->go('db')->get_data('counter');

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

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>