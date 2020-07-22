<?php 
// (c) vavok.net
// modified: 22.07.2020. 1:54:10

require_once"../include/startup.php";

$my_title = $lang_traffic['traffic'];
include_once"../themes/$config_themes/index.php";

if ($config["gzip"] == "1") {
    echo '<font color="#00FF00">' . $lang_traffic['gzipon'] . '</font><br><br>';
} else {
    echo '<font color="#FF0000">' . $lang_traffic['gzipoff'] . '</font><br><br>';
}

$timeon = maketime(round(time() - $_SESSION['currs']));

echo $lang_traffic['visitedpages'] . ': <b>' . ($_SESSION['counton'] + 1) . '</b><br>';
echo $lang_traffic['timeonsite'] . ': <b>' . $timeon . '</b><br><br>';


echo '<br><a href="../" class="btn btn-primary homepage"><img src="../images/img/homepage.gif" alt=""> ' . $lang_home['home'] . '</a>';

include_once"../themes/$config_themes/foot.php";

?>


