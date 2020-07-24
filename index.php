<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   24.07.2020. 16:08:36
*/

require_once "include/startup.php";

// Page header
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

// Show page content
$current_page->show_page();

// Load website footer
require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>