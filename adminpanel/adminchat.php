<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg() || !$vavok->go('users')->check_permissions(basename(__FILE__))) {
    $vavok->redirect_to("../pages/input.php?action=exit");
}

if (!empty($_GET['action'])) {
    $action = $vavok->check($_GET["action"]);
} else {
    $action = '';
} 

$rand = rand(100, 999);
$dates = date("d.m.y");
$times = date("H:i");

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('adminchat');

$vavok->require_header();

echo '<img src="../images/img/menu.gif" alt=""> <b>' . $vavok->go('localization')->string('adminchat') . '</b><br><br>';

if (empty($action)) {
    echo '<a href="#down"><img src="../images/img/downs.gif" alt=""></a> ';
    echo '<a href="adminchat.php?r=' . $rand . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('refresh') . '</a><br>';

    echo'<hr><form action="process.php?action=acadd" method="post"><b>' . $vavok->go('localization')->string('message') . '</b><br>';
    echo'<textarea cols="80" rows="5" name="msg"></textarea><br>';

    echo'<input type="submit" value="' . $vavok->go('localization')->string('save') . '" /></form><hr>';

    $file = $vavok->get_data_file('adminchat.dat');
    $file = array_reverse($file);
    $total = count($file);
    if ($total < 1) {
        echo'<br><img src="../images/img/reload.gif" alt=""> <b>' . $vavok->go('localization')->string('nomsgs') . '</b><br>';
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
        $statwho = $vavok->go('users')->user_online($data[1]); 
        // /////////////////////////////////////////////////////////////
        $data_text = $vavok->getbbcode($data[0]);

        echo'<div class=b><b><a href="../pages/user.php?uz=' . $data[1] . '" class="btn btn-outline-primary sitelink"> ' . $data[1] . ' </a></b> ' . $statwho;

        if ($dates == $data[2]) {
            $data[2] = '<font color="#FF0000">' . $vavok->go('localization')->string('today') . '</font>';
        } 

        echo'<small> (' . $data[2] . ' / ' . $data[3] . ')</small></div>' . $data_text . '<br><small><font color="#CC00CC">[' . $data[4] . ', ' . $data[5] . ']</font></small>';
        echo'<br>';
    } 

    echo'<hr>';
    if ($start != 0) {
        echo '<a href="adminchat.php?start=' . ($start - 10) . '" class="btn btn-outline-primary sitelink">&lt; ' . $vavok->go('localization')->string('back') . '</a> ';
    } else {
        echo'&lt; ' . $vavok->go('localization')->string('back') . '';
    } 
    echo'|';
    if ($total > $start + 10) {
        echo ' <a href="adminchat.php?start=' . ($start + 10) . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('forw') . ' &gt;</a>';
    } else {
        echo'' . $vavok->go('localization')->string('forw') . ' &gt;';
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

    echo '<a href="../pages/smiles.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('smile') . '</a>';
} 

if ($action == "prodel") {
    echo '<br>' . $vavok->go('localization')->string('delacmsgs') . '?<br>';
    echo '<b><a href="process.php?action=acdel" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('yessure') . '!</a></b><br>';

    echo '<br><a href="adminchat.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a>';
} 

if (isset($total) && $total > 0 && ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102)) {
    echo '<br><a href="adminchat.php?action=prodel" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('cleanchat') . '</a>';
} 

echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('admpanel') . '</a><br>';
echo $vavok->homelink() . '</p>';



$vavok->require_footer();

?>