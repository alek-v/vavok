<?php 
// modified: 11.9.2012 0:16:26
// (c) vavok.net
require_once"../include/strtup.php";

$my_title = "CHMOD";
include_once"../themes/$config_themes/index.php";

if (isadmin() == true && $accessr == 101) {
    $mode = 777;
    echo "<br><div class='b'>Change CHMOD:</div>";


    @chmod("used", 0777);
    $odir = opendir("../used");

    echo '<div><b>Folders:</b><br>';

    while ($fid = readdir($odir)) {
        if ((($fid != ".") && ($fid != "..")) && is_dir("../used/$fid")) {
            if (@chmod("../used/$fid", octdec($mode)) == true) {
                echo "CHMOD (" . $mode . "), fajl - <b>" . $fid . "</b> - OK!<br>";
            } else {
                echo "<font color='red'>CHMOD (" . $mode . "), fajl - <b>" . $fid . "</b> - ERROR!!!</font><br>";
            } 
        } 
    } 

    $odir = opendir("../used");

    echo '</div><br><div><b>Files:</b><br>';

    while ($fid = readdir($odir)) {
        if (is_file("../used/$fid") && preg_match('.dat', $fid)) {
            if (@chmod("../used/$fid", octdec($mode)) == true) {
                echo "CHMOD (" . $mode . "), file - <b>" . $fid . "</b> - OK!<br>";
            } else {
                echo "<font color='red'>CHMOD (" . $mode . "), fajl - <b>" . $fid . "</b> - ERROR!!!</font><br>";
            } 
        } 
    } 

    echo "</div>";

    echo'<br><br><img src="../images/img/homepage.gif" alt=""> <a href="../index.php?' . SID . '">Home page</a><br>';
} else {
    header ("Location: index.php");
} 

include_once"../themes/$config_themes/foot.php";

?>