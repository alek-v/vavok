<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   29.08.2020. 1:31:42
 */

require_once"../include/startup.php";
include_once BASEDIR . "include/lang/" . $users->get_user_language() . "/pagescounter.php";

$current_page->page_title = $localization->string('statistics');
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

echo $localization->string('vststoday') . ': <b>' . (int)$pcounter_host . '</b><br />';
echo $localization->string('vstpagestoday') . ': <b>' . (int)$pcounter_hits . '</b><br />';
echo $localization->string('totvisits') . ': <b>' . (int)$pcounter_all . '</b><br />';
echo $localization->string('totopenpages') . ': <b>' . (int)$pcounter_allhits . '</b><br />';

if ($vavok->get_configuration('forumAccess') == 1) {
    $notc = $db->count_row('vk_topics');
    $nops = $db->count_row('vk_posts');

    echo $localization->string('comminforum') . ': <b>' . $nops[0] . '</b><br />';
    echo $localization->string('topicinforum') . ': <b>' . $notc[0] . '</b><br />';
}

echo $localization->string('bannedip') . ': <b>' . counter_string(BASEDIR . "used/ban.dat") . '</b><br />';
echo $localization->string('regusers') . ': <b>' . $db->count_row('vavok_users') - 1 . '</b><br />';

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>