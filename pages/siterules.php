<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   02.08.2020. 2:59:51
*/

require_once"../include/startup.php";

$current_page->page_title = $localization->string('siterules');
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

echo '<p>' . $localization->string('mainrules') . '</p>';;

echo '<p><a href="../">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>