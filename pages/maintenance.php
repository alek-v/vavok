 <?php
// (c) vavok.net

require_once"../include/startup.php";

if ($config["siteOff"] != 1) {
    header("Location: ../");
    exit;
} 



$my_title = "Maintenance";
include_once"../themes/$config_themes/index.php";

echo $lang_page['maintenance'] . '!<br /><br />';

echo '<p><a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';


include_once"../themes/$config_themes/foot.php";
?>