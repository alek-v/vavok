<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   23.07.2020. 0:10:32
*/

require_once("include/startup.php");

// Page header
include_once("themes/" . $config_themes . "/index.php");

// Show page content
$current_page->show_page();

// Load website footer
include_once"themes/" . $config_themes . "/foot.php";

?>