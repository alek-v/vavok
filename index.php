<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   01.08.2020. 2:10:31
*/

require_once "include/startup.php";

/**
 * Page header
 */
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

/**
 * Page content
 */
$current_page->show_page();

/**
 * Page footer
 */
require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>