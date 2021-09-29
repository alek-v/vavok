<?php 
// (c) vavok.net

require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg()) {
    $vavok->redirect_to(BASEDIR . "pages/error.php?isset=nologin");
}

// add to admin chat
if ($vavok->post_and_get('action') == 'acadd') {
    if (!$vavok->go('users')->is_reg() || !$vavok->go('users')->check_permissions('adminchat')) $vavok->redirect_to(BASEDIR . "pages/input.php?action=exit");

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

if ($vavok->post_and_get('action') == 'delmail' && $_SESSION['permissions'] == 101) {
    $users_id = $vavok->check($vavok->post_and_get('users'));

    if (!empty($users_id)) {
            $fields = array('subscri', 'newscod');
            $values = array('', '');
            $vavok->go('users')->update_user($fields, $values, $users_id);

            $vavok->go('db')->delete('subs', "user_id='" . $users_id . "'");

        header ("Location: subscribe.php?start=$start&isset=mp_delsubmail");
        exit;
    } else {
        header ("Location: subscribe.php?start=$start&isset=mp_nodelsubmail");
        exit;
    }
}

if ($vavok->post_and_get('action') == 'delallsub' && $_SESSION['permissions'] == 101) {
    $sql = "TRUNCATE TABLE subs";
    $vavok->go('db')->query($sql);
    header ("Location: subscribe.php?isset=mp_delsuball");
    exit;
}

if ($vavok->post_and_get('action') == "zaban" && ($_SESSION['permissions'] == 101 or $_SESSION['permissions'] == 102)) {
	$ips = $vavok->check($vavok->post_and_get('ips'));

    if (!empty($ips) && substr_count($ips, '.') == 3) {
        $vavok->write_data_file('ban.dat', "|$ips|" . PHP_EOL, 1);
    }

    $vavok->redirect_to("ban.php");
}

if ($vavok->post_and_get('action') == "razban" && ($_SESSION['permissions'] == 101 or $_SESSION['permissions'] == 102)) {
	if (!empty($vavok->post_and_get('id'))) $id = $vavok->post_and_get('id');

    if (!empty($id)) {
        $file = $vavok->get_data_file('ban.dat');
        $fp = fopen("../used/ban.dat", "w");
        flock ($fp, LOCK_EX);
        for ($i = 0;$i < sizeof($file);$i++) {
            if ($i == $id) {
                unset($file[$i]);
            }
        }
        fputs($fp, implode("", $file));
        flock ($fp, LOCK_UN);
        fclose($fp);
    }

    $vavok->redirect_to('ban.php');
}

if ($vavok->post_and_get('action') == "delallip" && ($_SESSION['permissions'] == 101 or $_SESSION['permissions'] == 102)) {
    $vavok->clear_files("../used/ban.dat");

    $vavok->redirect_to("ban.php");
}

if ($vavok->post_and_get('action') == 'delbw' && $_SESSION['permissions'] == 101) {
	$stroka = $vavok->post_and_get('stroka');
    $file = $vavok->get_data_file('antiword.dat');
    $filestr = explode("|", $file[0]);
    unset($filestr[$stroka]);
    $str = implode("|", $filestr);
    $fp = fopen('../used/antiword.dat', 'w');
    fputs($fp, $str);
    fclose($fp);
    header ("Location: antiword.php?isset=delok");
    exit;
}

if ($vavok->post_and_get('action') == 'addbw' && $_SESSION['permissions'] == 101 && !empty($vavok->post_and_get('slovo'))) {
	$slovo = $vavok->check($vavok->post_and_get('slovo'));
    $fp = fopen(BASEDIR . "used/antiword.dat", "a+");
    $text = preg_replace ("|[\r\n]+|si", "", $slovo);
    fputs($fp, $text . '|');
    fclose($fp);
    $vavok->redirect_to('antiword.php?isset=ok');
}

if ($vavok->post_and_get('action') == "delerlog" && ($_SESSION['permissions'] == 101 or $_SESSION['permissions'] == 102)) {
    $vavok->clear_files("../used/datalog/error401.dat");
    $vavok->clear_files("../used/datalog/error402.dat");
    $vavok->clear_files("../used/datalog/error403.dat");
    $vavok->clear_files("../used/datalog/error404.dat");
    $vavok->clear_files("../used/datalog/error406.dat");
    $vavok->clear_files("../used/datalog/error500.dat");
    $vavok->clear_files("../used/datalog/error502.dat");
    $vavok->clear_files("../used/datalog/dberror.dat");
    $vavok->clear_files("../used/datalog/error.dat");
    $vavok->clear_files("../used/datalog/ban.dat");

    $vavok->redirect_to("logfiles.php?isset=mp_dellogs");
}

if ($vavok->post_and_get('action') == 'delerid' && !empty($vavok->post_and_get('err')) && ($_SESSION['permissions'] == 101 or $_SESSION['permissions'] == 102)) {
	$err = $vavok->check($vavok->post_and_get('err'));
    $vavok->clear_files("../used/datalog/" . $err . ".dat");

    header ("Location: logfiles.php?isset=mp_dellogs");
    exit;
}

?>