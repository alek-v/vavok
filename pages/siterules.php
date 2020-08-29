<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   28.08.2020. 17:13:18
 */

require_once"../include/startup.php";

$current_page->page_title = $localization->string('siterules');
$vavok->require_header();

echo '<p>' . $localization->string('mainrules') . '</p>';;

echo '<p><a href="../">' . $localization->string('home') . '</a></p>';

$vavok->require_footer();

?>