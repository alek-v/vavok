<?php
// modified: 25.07.2020. 15:23:12
// (c) VAVOK .net

require_once"../include/startup.php";
include"../lang/" . $users->get_user_language() . "/pagescounter.php";

$my_title = $lang_page['statistic'];
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

$pcount = file(BASEDIR . "used/datacounter/host.dat");
$pcount = explode("|", $pcount[0]);
$pcounter_host = $pcount[1];
$pcounter_all = $pcount[2];
$pcounter_hourhost = $pcount[4];

$phcount = file(BASEDIR . "used/datacounter/hits.dat");
$phcount = explode("|", $phcount[0]);
$pcounter_hits = $phcount[1];
$pcounter_allhits = $phcount[2];
$pcounter_hourhits = $phcount[4];

echo $lang_count['vststoday'] . ': <b>' . (int)$pcounter_host . '</b><br>';
echo $lang_count['vstpagestoday'] . ': <b>' . (int)$pcounter_hits . '</b><br>';
echo $lang_count['totvisits'] . ': <b>' . (int)$pcounter_all . '</b><br>';
echo $lang_count['totopenpages'] . ': <b>' . (int)$pcounter_allhits . '</b><br>';

/*
if (table_exists(vk_topics, $db)) {
$vk_topic = "ok";
	$vk_topic_check = "ok";
}
*/

if (get_configuration('forumAccess') == 1) {
    $notc = $db->count_row('vk_topics');
    $nops = $db->count_row('vk_posts');

    echo $lang_page['comminforum'] . ': <b>' . $nops[0] . '</b><br>';
    echo $lang_page['topicinforum'] . ': <b>' . $notc[0] . '</b><br>';
} 

$f = @file("../used/local.dat");
$u = explode("|", $f[0]);

echo $lang_page['sitenews'] . ': <b>' . (int)$u[4] . '</b><br>';
echo $lang_page['sitenewscomm'] . ': <b>' . (int)$u[3] . '</b><br>';
//echo 'Poruka u knjizi gostiju: <b>' . (int)$u[0] . '</b><br>';
//echo 'Poruka na chatu: <b>' . (int)$u[1] . '</b><br>';
// echo 'Komentara u download-u: <b>'.(int)$u[5].'</b><br>';
echo $lang_page['bannedip'] . ': <b>' . counter_string(BASEDIR . "used/ban.dat") . '</b><br>';
$count_users = $db->count_row('vavok_users', "");
echo $lang_page['regusers'] . ': <b>' . $count_users . '</b><br>';
$tot = $u[0] + $u[1] + $u[2] + $u[3] + $u[4] + $u[5];
// $u[0]=knjiga
// $u[1]=chat
// $u[2]=postova u forumu
// $u[3]=komentara u novostima
// $u[4]=novosti
// $u[5]=komentara u downloadu
echo '<br><a href="../" class="btn btn-primary homepage"><img src="../images/img/homepage.gif" alt=""> ' . $lang_home['home'] . '</a><br>';


require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>