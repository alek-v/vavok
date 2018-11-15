<?php
// modified: 10.1.2016. 2:19:53
// (c) VAVOK .net
require_once"../include/strtup.php";
include"../lang/" . $config["language"] . "/pagescounter.php";

$mediaLikeButton = 'off'; // dont show like buttons

$my_title = $lang_page['statistic'];
include"../themes/$config_themes/index.php";


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

if ($config["forumAccess"] == '1') {
    $notc = $db->select('vk_topics', "", '', 'COUNT(*)');
    $nops = $db->select('vk_posts', "", '', 'COUNT(*)');

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
echo '<br><img src="../images/img/homepage.gif" alt=""> <a href="../" class="homepage">' . $lang_home['home'] . '</a><br>';


include"../themes/$config_themes/foot.php";

?>