<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   21.07.2020. 3:19:34
*/

require_once("include/startup.php");

// Page title
$my_title = !empty($page_data['tname']) ? $page_data['tname'] : '';

// Page header
include_once("themes/" . $config_themes . "/index.php");

// Show page content
echo $page_data['content'];

// Load website footer
include_once"themes/" . $config_themes . "/foot.php";

?>