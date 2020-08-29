<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   28.08.2020. 16:57:26
 */

require_once "include/startup.php";

/**
 * Page header
 */
$vavok->require_header();

/**
 * Page content
 */
$current_page->show_page();

/**
 * Page footer
 */
$vavok->require_footer();

?>