<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   22.07.2020. 2:33:21
*/

require_once("include/startup.php");

// Page header
include_once("themes/" . $config_themes . "/index.php");

// Show page content
echo $current_page->page_content;

// Load website footer
include_once"themes/" . $config_themes . "/foot.php";

?>