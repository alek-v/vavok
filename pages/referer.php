<?php 
// (c) vavok.net
require_once"../include/strtup.php";

$mediaLikeButton = 'off'; // dont show like buttons

$my_title = "Referer";
include_once"../themes/$config_themes/index.php";

if (empty($_GET['page']) || $_GET['page'] < 1) {
    $page = 1;
} else {
    $page = check($_GET['page']);
} 

$start = $config["dataOnPage"] * ($page - 1);

if ($config["showRefPage"] == "1" || isadmin()) {
    $file = file("../used/referer.dat");
    $file = array_reverse($file);
    $total = count($file);
    if ($start == "") {
        $start = 0;
    } 
    if ($total < $start + $config["dataOnPage"]) {
        $end = $total;
    } else {
        $end = $start + $config["dataOnPage"];
    } 
    for ($i = $start; $i < $end; $i++) {
        $data = explode("|", $file[$i]);
        $datime = date("H:i:s", $data[1]);
        echo '<b><a href="' transfer_protocol() . $data[0] . '">' . $data[0] . '</a></b> (' . $datime . ')<br />' . $lang_page['visits'] . ': ' . $data[3] . '<br /><hr />';
    } 

    page_navigation('referer.php?', $config["dataOnPage"], $page, $total);
    page_numbnavig('referer.php?', $config["dataOnPage"], $page, $total);
} else {
    echo '<p><img src="../images/img/reload.gif" alt="" /> ' . $lang_page['pgviewoff'] . '<br></p>';
} 

echo '<p><br><a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';

include_once"../themes/" . $config_themes . "/foot.php";

?>