<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg() || (!$vavok->go('users')->is_administrator(101) && !$vavok->go('users')->is_administrator(102))) $vavok->redirect_to("../?errorAuth");

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('sitestats');
$vavok->require_header();

echo '<p><img src="../themes/images/img/webstats.png" alt="">  ' . $vavok->go('localization')->string('sitestats') . '<br /><br /></p>';

echo '<p><a href="../pages/counter.php" class="btn btn-outline-primary sitelink"> ' . $vavok->go('localization')->string('visitstats') . '</a><br />';
echo '<a href="../pages/online.php" class="btn btn-outline-primary sitelink"> ' . $vavok->go('localization')->string('usronline') . '</a></p>';

echo '<p><br /><br />
<a href="./" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('admpanel') . '</a><br>';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();

?>
