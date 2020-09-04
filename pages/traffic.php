<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('traffic');
$vavok->require_header();

$timeon = $vavok->maketime(round(time() - $_SESSION['currs']));

echo $vavok->go('localization')->string('visitedpages') . ': <b>' . ($_SESSION['counton'] + 1) . '</b><br>';
echo $vavok->go('localization')->string('timeonsite') . ': <b>' . $timeon . '</b><br><br>';

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>


