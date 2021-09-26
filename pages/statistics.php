<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';
include_once BASEDIR . 'include/lang/' . $vavok->go('users')->get_user_language() . '/pagescounter.php';

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('statistics');
$vavok->require_header();

$pcount = $vavok->get_data_file('datacounter/host.dat');
$pcount = explode("|", $pcount[0]);
$pcounter_host = $pcount[1];
$pcounter_all = $pcount[2];
$pcounter_hourhost = $pcount[4];

$phcount = $vavok->get_data_file('datacounter/hits.dat');
$phcount = explode("|", $phcount[0]);
$pcounter_hits = $phcount[1];
$pcounter_allhits = $phcount[2];
$pcounter_hourhits = $phcount[4];

echo $vavok->go('localization')->string('vststoday') . ': <b>' . (int)$pcounter_host . '</b><br />';
echo $vavok->go('localization')->string('vstpagestoday') . ': <b>' . (int)$pcounter_hits . '</b><br />';
echo $vavok->go('localization')->string('totvisits') . ': <b>' . (int)$pcounter_all . '</b><br />';
echo $vavok->go('localization')->string('totopenpages') . ': <b>' . (int)$pcounter_allhits . '</b><br />';

if ($vavok->get_configuration('forumAccess') == 1) {
    $notc = $vavok->go('db')->count_row('vk_topics');
    $nops = $vavok->go('db')->count_row('vk_posts');

    echo $vavok->go('localization')->string('comminforum') . ': <b>' . $nops[0] . '</b><br />';
    echo $vavok->go('localization')->string('topicinforum') . ': <b>' . $notc[0] . '</b><br />';
}

echo $vavok->go('localization')->string('bannedip') . ': <b>' . counter_string(BASEDIR . 'used/ban.dat') . '</b><br />';
echo $vavok->go('localization')->string('regusers') . ': <b>' . $vavok->go('users')->regmemcount() . '</b><br />';

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>