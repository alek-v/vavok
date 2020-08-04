<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   03.08.2020. 12:50:03
*/

require_once"../include/startup.php";

$current_page->page_title = $localization->string('traffic');
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

$timeon = $vavok->maketime(round(time() - $_SESSION['currs']));

echo $localization->string('visitedpages') . ': <b>' . ($_SESSION['counton'] + 1) . '</b><br>';
echo $localization->string('timeonsite') . ': <b>' . $timeon . '</b><br><br>';

echo '<p><a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>


