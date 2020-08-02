 <?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   02.08.2020. 3:05:52
*/

require_once"../include/startup.php";

if ($vavok->get_configuration('siteOff') != 1) { $vavok->redirect_to("../"); }

$current_page->page_title = $localization->string('maintenance');
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

echo '<p>' . $localization->string('maintenance_msg') . '!</p>';

echo '<p><a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";
?>