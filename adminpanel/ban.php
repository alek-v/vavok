<?php 
// (c) vavok.net
require_once"../include/startup.php";
if (!empty($_GET['action'])) {
    $action = check($_GET["action"]);
} else {
    $action = '';
} 
if (isset($_GET['start'])) {
    $start = check($_GET['start']);
} 

if ($users->is_reg()) {
    if ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102) {
        $my_title = "IP ban";
        require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

        echo '<img src="../images/img/menu.gif" alt=""> <b>IP ban panel</b><br><br>';

        if (empty($action)) {
            $file = file("../used/ban.dat");
            $total = count($file);
            if (empty($_GET['start'])) $start = 0;
            else $start = $_GET['start'];
            if ($total < $start + 10) {
                $end = $total;
            } else {
                $end = $start + 10;
            } 
            for ($i = $start; $i < $end; $i++) {
                $file = file("../used/ban.dat");
                $file = array_reverse($file);
                $data = explode("|", $file[$i]);
                $i2 = round($i + 1);

                $num = $total - $i-1;

                echo $i2 . '. ' . $data[1] . ' <br><a href="process.php?action=razban&amp;start=' . $start . '&amp;id=' . $num . '" class="btn btn-outline-primary sitelink">' . $lang_admin['delban'] . '</a><hr>';
            } 

            if ($total < 1) {
                echo'<br><img src="../images/img/reload.gif" alt="" /> ' . $lang_admin['emptylist'] . '<br><br>';
            } 

            if ($start != 0) {
                echo '<a href="ban.php?start=' . ($start - 10) . '" class="btn btn-outline-primary sitelink">&lt; ' . $lang_home['back'] . '</a> ';
            } else {
                echo'&lt; ' . $lang_home['back'] . ' ';
            } 
            echo'|';
            if ($total > $start + 10) {
                echo ' <a href="ban.php?start=' . ($start + 10) . '" class="btn btn-outline-primary sitelink">' . $lang_home['forw'] . ' &gt;</a>';
            } else {
                echo' ' . $lang_home['forw'] . ' &gt;';
            } 

            echo '<hr><form method="post" action="process.php?action=zaban&amp;start=' . $start . '">';
            echo '' . $lang_admin['iptoblock'] . ':<br><input name="ips" /><br><br>';
            echo '<input value="' . $lang_home['confirm'] . '" type="submit" /></form>';

            echo '<hr>';
            echo '' . $lang_admin['ipbanexam'] . '<br><br>';
            echo '<br>' . $lang_admin['allbanips'] . ': ' . $total . '<br><br><br>';
            if ($total > 1) {
                echo'<br><a href="process.php?action=delallip" class="btn btn-outline-primary sitelink">' . $lang_admin['dellist'] . '</a>';
            } 
        } 

        echo'<br><a href="./" class="btn btn-outline-primary sitelink">' . $lang_home['admpanel'] . '</a><br>';
        echo'<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a><br>';
    } else {
        header ("Location: ../index.php?error");
        exit;
    } 
} else {
    header ("Location: ../index.php?error");
    exit;
} 

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>
