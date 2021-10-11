<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_administrator()) $vavok->redirect_to('../?errorAuth');

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('sitestats');
$vavok->require_header();

echo '<p><img src="../themes/images/img/webstats.png" alt="">  ' . $vavok->go('localization')->string('sitestats') . '<br /><br /></p>';

echo '<p>' . $vavok->sitelink('../pages/counter.php', $vavok->go('localization')->string('visitstats')) . '<br />';
echo $vavok->sitelink('../pages/online.php', $vavok->go('localization')->string('usronline')) . '</p>';

echo '<p>' . $vavok->sitelink('./', $vavok->go('localization')->string('admpanel')) . '<br>';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();

?>
