<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   01.08.2020. 2:40:57
*/

require_once"../include/startup.php";

if (!$users->is_reg() || (!$users->is_administrator(101) && !$users->is_administrator(102))) $vavok->redirect_to("../?errorAuth");

$my_title = $localization->string('sitestats');
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

echo '<p><img src="../images/img/webstats.png" alt="">  ' . $localization->string('sitestats') . '<br /><br /></p>';

echo '<p><a href="../pages/counter.php" class="btn btn-outline-primary sitelink"> ' . $localization->string('visitstats') . '</a><br />';
echo '<a href="../pages/online.php" class="btn btn-outline-primary sitelink"> ' . $localization->string('usronline') . '</a></p>';

echo '<p><br /><br />
<a href="./" class="btn btn-outline-primary sitelink">' . $localization->string('admpanel') . '</a><br>';
echo '<a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>
