<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   01.08.2020. 20:47:37
*/

require_once"../include/startup.php";

$my_title = $localization->string('traffic');
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

if ($vavok->get_configuration('gzip') == 1) {
    echo '<font color="#00FF00">' . $localization->string('gzipon') . '</font><br><br>';
} else {
    echo '<font color="#FF0000">' . $localization->string('gzipoff') . '</font><br><br>';
}

$timeon = maketime(round(time() - $_SESSION['currs']));

echo $localization->string('visitedpages') . ': <b>' . ($_SESSION['counton'] + 1) . '</b><br>';
echo $localization->string('timeonsite') . ': <b>' . $timeon . '</b><br><br>';

echo '<p><a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>


