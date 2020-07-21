<?php 
// (c) vavok.net - Aleksandar Vranesevic
// modified: 02.04.2020. 20:16:28

require_once"../include/startup.php";

if (!$users->is_reg() || !$users->check_permissions(basename(__FILE__))) {
    redirect_to("../pages/input.php?action=exit");
}

if (!empty($_GET['action'])) {
    $action = check($_GET["action"]);
} else {
    $action = '';
} 

$rand = rand(100, 999);
$dates = date("d.m.y");
$times = date("H:i");

$my_title = $lang_admin['adminchat'];

include_once"../themes/$config_themes/index.php";

echo '<img src="../images/img/menu.gif" alt=""> <b>' . $lang_admin['adminchat'] . '</b><br><br>';

if (empty($action)) {
    echo '<a href="#down"><img src="../images/img/downs.gif" alt=""></a> ';
    echo '<a href="adminchat.php?r=' . $rand . '" class="btn btn-outline-primary sitelink">' . $lang_home['refresh'] . '</a><br>';

    echo'<hr><form action="process.php?action=acadd" method="post"><b>' . $lang_home['message'] . '</b><br>';
    echo'<textarea cols="80" rows="5" name="msg"></textarea><br>';

    echo'<input type="submit" value="' . $lang_home['save'] . '" /></form><hr>';

    $file = file("../used/adminchat.dat");
    $file = array_reverse($file);
    $total = count($file);
    if ($total < 1) {
        echo'<br><img src="../images/img/reload.gif" alt=""> <b>' . $lang_home['nomsgs'] . '</b><br>';
    } 
    if (empty($_GET['start'])) $start = 0;
    else $start = $_GET['start'];
    if ($total < $start + 10) {
        $end = $total;
    } else {
        $end = $start + 10;
    } 
    for ($i = $start; $i < $end; $i++) {
        $data = explode("|", $file[$i]); 
        // ////////////////////////////////////////////////////////////
        $statwho = $users->user_online($data[1]); 
        // /////////////////////////////////////////////////////////////
        $data_text = getbbcode($data[0]);

        echo'<div class=b><b><a href="../pages/user.php?uz=' . $data[1] . '" class="btn btn-outline-primary sitelink"> ' . $data[1] . ' </a></b> ' . $statwho;

        if ($dates == $data[2]) {
            $data[2] = '<font color="#FF0000">' . $lang_home['today'] . '</font>';
        } 

        echo'<small> (' . $data[2] . ' / ' . $data[3] . ')</small></div>' . $data_text . '<br><small><font color="#CC00CC">[' . $data[4] . ', ' . $data[5] . ']</font></small>';
        echo'<br>';
    } 

    echo'<hr>';
    if ($start != 0) {
        echo '<a href="adminchat.php?start=' . ($start - 10) . '" class="btn btn-outline-primary sitelink">&lt; ' . $lang_home['back'] . '</a> ';
    } else {
        echo'&lt; ' . $lang_home['back'] . '';
    } 
    echo'|';
    if ($total > $start + 10) {
        echo ' <a href="adminchat.php?start=' . ($start + 10) . '" class="btn btn-outline-primary sitelink">' . $lang_home['forw'] . ' &gt;</a>';
    } else {
        echo'' . $lang_home['forw'] . ' &gt;';
    } 

    if ($total > 0) {
        $ba = ceil($total / 10);
        $ba2 = $ba * 10 - 10;

        echo '<br><hr>Page:';
        $asd = $start - (10 * 3);
        $asd2 = $start + (10 * 4);

        if ($asd < $total && $asd > 0) {
            echo ' <a href="adminchat.php?start=0" class="btn btn-outline-primary sitelink">1</a> ... ';
        } 

        for($i = $asd; $i < $asd2;) {
            if ($i < $total && $i >= 0) {
                $ii = floor(1 + $i / 10);

                if ($start == $i) {
                    echo ' <b>(' . $ii . ')</b>';
                } else {
                    echo ' <a href="adminchat.php?start=' . $i . '" class="btn btn-outline-primary sitelink">' . $ii . '</a>';
                } 
            } 

            $i = $i + 10;
        } 
        if ($asd2 < $total) {
            echo ' ... <a href="adminchat.php?start=' . $ba2 . '" class="btn btn-outline-primary sitelink">' . $ba . '</a>';
        } 
    } 

    echo '<br><br>';

    echo '<a href="../pages/smiles.php" class="btn btn-outline-primary sitelink">' . $lang_home['smile'] . '</a>';
} 

if ($action == "prodel") {
    echo '<br>' . $lang_admin['delacmsgs'] . '?<br>';
    echo '<b><a href="process.php?action=acdel" class="btn btn-outline-primary sitelink">' . $lang_admin['yessure'] . '!</a></b><br>';

    echo '<br><a href="adminchat.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a>';
} 

if ($total > 0 && ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102)) {
    echo '<br><a href="adminchat.php?action=prodel" class="btn btn-outline-primary sitelink">' . $lang_admin['cleanchat'] . '</a>';
} 

echo'<br><a href="./" class="btn btn-outline-primary sitelink">' . $lang_home['admpanel'] . '</a><br>';
echo'<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a><br>';



include_once"../themes/$config_themes/foot.php";

?>