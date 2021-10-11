<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->check_permissions(basename(__FILE__))) $vavok->redirect_to('../?auth_error');

// add to admin chat
if ($vavok->post_and_get('action') == 'acadd') {
    $brow = $vavok->check($vavok->go('users')->user_browser());
    $msg = $vavok->check(wordwrap($vavok->post_and_get('msg'), 150, ' ', 1));
    $msg = substr($msg, 0, 1200);
    $msg = $vavok->check($msg);

    $msg = $vavok->antiword($msg);
    $msg = $vavok->smiles($msg);
    $msg = $vavok->no_br($msg, '<br />');

    $text = $msg . '|' . $vavok->go('users')->show_username() . '|' . $vavok->date_fixed(time(), "d.m.y") . '|' . $vavok->date_fixed(time(), "H:i") . '|' . $brow . '|' . $vavok->go('users')->find_ip() . '|';
    $text = $vavok->no_br($text);

    $vavok->write_data_file('adminchat.dat', $text . PHP_EOL, 1);

    $file = $vavok->get_data_file('adminchat.dat');
    $i = count($file);
    if ($i >= 300) {
        $fp = fopen("../used/adminchat.dat", "w");
        flock ($fp, LOCK_EX);
        unset($file[0]);
        unset($file[1]);
        fputs($fp, implode("", $file));
        flock ($fp, LOCK_UN);
        fclose($fp);
    } 
    header("Location: adminchat.php?isset=addon");
    exit;
}

// empty admin chat
if ($vavok->post_and_get('action') == "acdel") {
    if ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102) {
        $vavok->clear_files("../used/adminchat.dat");

        header ("Location: adminchat.php?isset=mp_admindelchat");
        exit;
    }
}

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('adminchat');
$vavok->require_header();

echo '<img src="../themes/images/img/menu.gif" alt=""> <b>' . $vavok->go('localization')->string('adminchat') . '</b><br><br>';

if (empty($vavok->post_and_get('action'))) {
    echo '<a href="#down"><img src="../themes/images/img/downs.gif" alt=""></a> ';
    echo $vavok->sitelink('adminchat.php?r=' . rand(100, 999), $vavok->go('localization')->string('refresh')) . '<br>';

    echo'<hr><form action="adminchat.php?action=acadd" method="post"><b>' . $vavok->go('localization')->string('message') . '</b><br>';
    echo'<textarea cols="80" rows="5" name="msg"></textarea><br>';

    echo'<input type="submit" value="' . $vavok->go('localization')->string('save') . '" /></form><hr>';

    $file = $vavok->get_data_file('adminchat.dat');
    $file = array_reverse($file);
    $total = count($file);

    if ($total < 1) {
        echo'<br><img src="../themes/images/img/reload.gif" alt=""> <b>' . $vavok->go('localization')->string('nomsgs') . '</b><br>';
    }

    $navigation = new Navigation(10, $total, $vavok->post_and_get('page'), 'adminchat.php?'); // start navigation

    $limit_start = $navigation->start()['start']; // starting point

    if ($total < $limit_start + 10) {
        $end = $total;
    } else {
        $end = $limit_start + 10;
    }

    for ($i = $limit_start; $i < $end; $i++) {
        $data = explode("|", $file[$i]); 

        $statwho = $vavok->go('users')->user_online($data[1]); 

        $data_text = $vavok->getbbcode($data[0]);

        echo '<div class=b><b>' . $vavok->sitelink('../pages/user.php?uz=' . $data[1], $data[1]) . '</b> ' . $statwho;

        if (date('d.m.y') == $data[2]) {
            $data[2] = '<font color="#FF0000">' . $vavok->go('localization')->string('today') . '</font>';
        }

        echo'<small> (' . $data[2] . ' / ' . $data[3] . ')</small></div>' . $data_text . '<br><small><font color="#CC00CC">[' . $data[4] . ', ' . $data[5] . ']</font></small>';
        echo'<br>';
    }

    echo '<hr>';
    echo $navigation->get_navigation();

    echo '<br><br>';

    echo $vavok->sitelink('../pages/smiles.php', $vavok->go('localization')->string('smile'));
}

if ($vavok->post_and_get('action') == 'prodel') {
    echo '<br>' . $vavok->go('localization')->string('delacmsgs') . '?<br>';
    echo '<b>' . $vavok->sitelink('adminchat.php?action=acdel', $vavok->go('localization')->string('yessure') . '!') . '</b><br>';

    echo '<br>' . $vavok->sitelink('adminchat.php', $vavok->go('localization')->string('back'));
} 

if (isset($total) && $total > 0 && ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102)) {
    echo '<br>' . $vavok->sitelink('adminchat.php?action=prodel', $vavok->go('localization')->string('cleanchat'));
}

echo '<p>' . $vavok->sitelink('./', $vavok->go('localization')->string('admpanel')) . '<br>';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();

?>