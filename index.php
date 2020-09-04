<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once 'include/startup.php';

$vavok->require_header();

$vavok->go('current_page')->show_page();

$vavok->require_footer();

?>