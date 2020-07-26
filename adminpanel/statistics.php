<?php 
// (c) vavok.net
require_once"../include/startup.php";

if (!$users->is_reg() || (!$users->is_administrator(101) && !$users->is_administrator(102))) {
    header ("Location: ../?errorAuth");
    exit;
} 

$my_title = $lang_admin['sitestats'];
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

echo '<p><img src="../images/img/webstats.png" alt="">  ' . $lang_admin['sitestats'] . '<br /><br /></p>';

echo '<p><a href="../pages/counter.php" class="btn btn-outline-primary sitelink"> ' . $lang_admin['visitstats'] . '</a><br />';
echo '<a href="../pages/online.php" class="btn btn-outline-primary sitelink"> ' . $lang_admin['usronline'] . '</a></p>';

echo '<p><br /><br />
<a href="./" class="btn btn-outline-primary sitelink">' . $localization->string('admpanel') . '</a><br>';
echo '<a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>
