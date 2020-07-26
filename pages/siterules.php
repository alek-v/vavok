<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   26.07.2020. 17:40:33
*/

require_once"../include/startup.php";

$my_title = $localization->string('siterules');
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

echo '<p>' . $localization->string('mainrules') . '</p>';;

echo '<p><a href="../">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>