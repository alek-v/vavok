<?php 
// modified: 02.08.2020. 3:03:43
// (c) vavok.net
require_once"../include/startup.php";

$current_page->page_title = "CHMOD";
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

if ($users->is_administrator() == true && $_SESSION['permissions'] == 101) {
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

    echo'<p><a href="../index.php">Home page</a></p>';
} else {
    header ("Location: index.php");
} 

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>