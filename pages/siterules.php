<?php 
// modified: 19.6.2014 7:18:43
require_once"../include/startup.php";



$my_title = $lang_siterules['siterules'];
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";


echo $lang_siterules['mainrules'];


echo "<br><br><br>";
echo '<img src="../images/img/homepage.gif" alt=""> <a href="../index.php?' . SID . '">' . $lang_home['home'] . '</a>';
require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>