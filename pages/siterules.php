<?php 
// modified: 26.07.2020. 1:44:42
require_once"../include/startup.php";

$my_title = $lang_siterules['siterules'];
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

echo $lang_siterules['mainrules'];

echo "<br><br><br>";
echo '<a href="../">' . $localization->string('home') . '</a>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>