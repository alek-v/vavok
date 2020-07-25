 <?php
// (c) vavok.net

require_once"../include/startup.php";

if (get_configuration('siteOff') != 1) { redirect_to("../"); }

$my_title = "Maintenance";
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

echo '<p>' . $lang_page['maintenance'] . '!</p>';

echo '<p><a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";
?>