<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   22.07.2020. 0:42:50
*/

require_once("include/startup.php");

// Page header
include_once("themes/" . $config_themes . "/index.php");

// Show page content
echo $page_data['content'];

// Load website footer
include_once"themes/" . $config_themes . "/foot.php";

?>