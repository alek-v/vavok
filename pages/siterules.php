<?php 
// modified: 19.6.2014 7:18:43
require_once"../include/strtup.php";

$mediaLikeButton = 'off'; // dont show like buttons

$my_title = $lang_siterules['siterules'];
include_once"../themes/$config_themes/index.php";


echo $lang_siterules['mainrules'];


echo "<br><br><br>";
echo '<img src="../images/img/homepage.gif" alt=""> <a href="../index.php?' . SID . '">' . $lang_home['home'] . '</a>';
include_once"../themes/$config_themes/foot.php";

?>