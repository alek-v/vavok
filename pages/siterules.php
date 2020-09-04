<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('siterules');
$vavok->require_header();

echo '<p>' . $vavok->go('localization')->string('mainrules') . '</p>';;

echo '<p><a href="../">' . $vavok->go('localization')->string('home') . '</a></p>';

$vavok->require_footer();

?>