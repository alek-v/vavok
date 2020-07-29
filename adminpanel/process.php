<?php 
// (c) vavok.net

require_once"../include/startup.php";

if (isset($_GET['action'])) {$action = check($_GET['action']);}

if (!$users->is_reg()) {
    redirect_to(BASEDIR . "pages/error.php?isset=nologin");
}

// add to admin chat
if ($action == "acadd") {

    if (!$users->is_reg() || !$users->check_permissions('adminchat')) {
        redirect_to(BASEDIR . "pages/input.php?action=exit");
    }

    $brow = check($users->user_browser());
    $msg = check(wordwrap($_POST['msg'], 150, ' ', 1));
    $msg = substr($msg, 0, 1200);
    $msg = check($msg);

    $msg = antiword($msg);
    $msg = smiles($msg);
    $msg = no_br($msg, '<br />');

    $text = $msg . '|' . $users->show_username() . '|' . date_fixed(time(), "d.m.y") . '|' . date_fixed(time(), "H:i") . '|' . $brow . '|' . $users->find_ip() . '|';
    $text = no_br($text);

    $fp = fopen("../used/adminchat.dat", "a+");
    flock ($fp, LOCK_EX);
    fputs($fp, "$text\r\n");
    flock ($fp, LOCK_UN);
    fclose($fp);

    $file = file("../used/adminchat.dat");
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
if ($action == "acdel") {
    if ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102) {
        clear_files("../used/adminchat.dat");

        header ("Location: adminchat.php?isset=mp_admindelchat");
        exit;
    } 
} 

if ($action == "delmail" && $_SESSION['permissions'] == 101) {
    $users_id = check($_GET['users']);
    //$users_id = $users->getidfromnick($users);
    if ($users_id != "") {

            $fields = array('subscri', 'newscod');
            $values = array('', '');
            $db->update('vavok_profil', $fields, $values, "uid='" . $users_id . "'");

            $db->delete('subs', "user_id='" . $users_id . "'");

        header ("Location: subscribe.php?start=$start&isset=mp_delsubmail");
        exit;
    } else {
        header ("Location: subscribe.php?start=$start&isset=mp_nodelsubmail");
        exit;
    } 
} 

if ($action == "delallsub" && $_SESSION['permissions'] == 101) {
    $sql = "TRUNCATE TABLE subs";
    $db->query($sql);
    header ("Location: subscribe.php?isset=mp_delsuball");
    exit;
} 


if ($action == "zaban" && ($_SESSION['permissions'] == 101 or $_SESSION['permissions'] == 102)) {
	$ips = check($_POST['ips']);

    if (!empty($ips) && substr_count($ips, '.') == 3) {
        $fp = fopen("../used/ban.dat", "a+");
        flock ($fp, LOCK_EX);
        fputs($fp, "|$ips|\r\n");
        fflush ($fp);
        flock ($fp, LOCK_UN);
        fclose($fp);
    }

    redirect_to("ban.php");
} 

if ($action == "razban" && ($_SESSION['permissions'] == 101 or $_SESSION['permissions'] == 102)) {

	if (isset($_POST['id'])) {$id = check($_POST['id']);} else {$id = check($_GET['id']);}

    if (isset($id)) {

        $file = file("../used/ban.dat");
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

    redirect_to("ban.php");

} 

if ($action == "delallip" && ($_SESSION['permissions'] == 101 or $_SESSION['permissions'] == 102)) {

    clear_files("../used/ban.dat");

    redirect_to("ban.php");

} 

if ($action == "delbw" && $_SESSION['permissions'] == 101) {
	$stroka = check($_GET['stroka']);
    $file = file('../used/antiword.dat');
    $filestr = explode("|", $file[0]);
    unset($filestr[$stroka]);
    $str = implode("|", $filestr);
    $fp = fopen('../used/antiword.dat', 'w');
    fputs($fp, $str);
    fclose($fp);
    header ("Location: antiword.php?isset=delok");
    exit;
} 
if ($action == "addbw" && $_SESSION['permissions'] == 101 && $_POST['slovo'] != '') {
	$slovo = check($_POST['slovo']);
    $fp = fopen(BASEDIR . "used/antiword.dat", "a+");
    $text = preg_replace ("|[\r\n]+|si", "", $slovo);
    fputs($fp, $text . '|');
    fclose($fp);
    header ("Location: antiword.php?isset=ok");
    exit;
} 

if ($action == "delerlog" && ($_SESSION['permissions'] == 101 or $_SESSION['permissions'] == 102)) {
    clear_files("../used/datalog/error401.dat");
    clear_files("../used/datalog/error402.dat");
    clear_files("../used/datalog/error403.dat");
    clear_files("../used/datalog/error404.dat");
    clear_files("../used/datalog/error406.dat");
    clear_files("../used/datalog/error500.dat");
    clear_files("../used/datalog/error502.dat");
    clear_files("../used/datalog/dberror.dat");
    clear_files("../used/datalog/error.dat");
    clear_files("../used/datalog/ban.dat");

    redirect_to("logfiles.php?isset=mp_dellogs");
} 

if ($action == "delerid" && !empty($_GET['err']) && ($_SESSION['permissions'] == 101 or $_SESSION['permissions'] == 102)) {

	$err = check($_GET['err']);
    clear_files("../used/datalog/" . $err . ".dat");

    header ("Location: logfiles.php?isset=mp_dellogs");
    exit;
} 

?>