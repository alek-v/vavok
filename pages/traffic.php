<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   29.08.2020. 1:31:26
 */

require_once"../include/startup.php";

$current_page->page_title = $localization->string('traffic');
$vavok->require_header();

$timeon = $vavok->maketime(round(time() - $_SESSION['currs']));

echo $localization->string('visitedpages') . ': <b>' . ($_SESSION['counton'] + 1) . '</b><br>';
echo $localization->string('timeonsite') . ': <b>' . $timeon . '</b><br><br>';

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>


