 <?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   26.07.2020. 17:32:57
*/

require_once"../include/startup.php";

if (get_configuration('siteOff') != 1) { redirect_to("../"); }

$my_title = $localization->string('maintenance');
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

echo '<p>' . $localization->string('maintenance_msg') . '!</p>';

echo '<p><a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";
?>