<?php 
// (c) vavok.net
require_once"../include/strtup.php";
if (isset($_GET['action'])) {$action = check($_GET['action']);}

if (!is_reg()) {
    header ("Location: ../pages/error.php?isset=nologin");
    exit;
}

$time = time();
$rand = rand(100, 999);
$dates = date_fixed($time, "d.m.y");
$times = date_fixed($time, "H:i");

// add to admin chat
if ($action == "acadd") {
    if (!is_reg() || !checkPermissions('adminchat')) {
        header ("Location: ../input.php?action=exit");
        exit;
    }

    $brow = check($brow);
    $msg = check(wordwrap($_POST['msg'], 150, ' ', 1));
    $msg = substr($msg, 0, 1200);
    $msg = check($msg);
    $log = check($log);

    $msg = antiword($msg);
    $msg = smiles($msg);
    $msg = no_br($msg, '<br />');

    $text = $msg . '|' . $log . '|' . $dates . '|' . $times . '|' . $brow . '|' . $ip . '|';
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
    if ($accessr == 101 || $accessr == 102) {
        clear_files("../used/adminchat.dat");

        header ("Location: adminchat.php?isset=mp_admindelchat");
        exit;
    } 
} 

if ($action == "delmail" && $accessr == 101) {
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

if ($action == "delallsub" && $accessr == 101) {
    $sql = "TRUNCATE TABLE subs";
    $db->query($sql);
    header ("Location: subscribe.php?isset=mp_delsuball");
    exit;
} 


if ($action == "zaban" && ($accessr == 101 or $accessr == 102)) {
	$ips = check($_POST['ips']);
    if (!empty($ips)) {

        $fp = fopen("../used/ban.dat", "a+");
        flock ($fp, LOCK_EX);
        fputs($fp, "|$ips|\r\n");
        fflush ($fp);
        flock ($fp, LOCK_UN);
        fclose($fp);
    } 
    header ("Location: ban.php?start=$start");
    exit;
} 

if ($action == "razban" && ($accessr == 101 or $accessr == 102)) {
	if (isset($_POST['id'])) {$id = check($_POST['id']);} else {$id = check($_GET['id']);}

    if ($id != "") {
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
    header ("Location: ban.php?start=$start");
    exit;
} 

if ($action == "delallip" && ($accessr == 101 or $accessr == 102)) {
    $fp = fopen("../used/ban.dat", "a+");
    flock ($fp, LOCK_EX);
    ftruncate ($fp, 0);
    fflush ($fp);
    flock ($fp, LOCK_UN);
    header ("Location: ban.php?start=$start");
    exit;
} 

if ($action == "delbw" && $accessr == 101) {
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
if ($action == "addbw" && $accessr == 101 && $_POST['slovo'] != '') {
	$slovo = check($_POST['slovo']);
    $fp = fopen(BASEDIR . "used/antiword.dat", "a+");
    $text = preg_replace ("|[\r\n]+|si", "", $slovo);
    fputs($fp, $text . '|');
    fclose($fp);
    header ("Location: antiword.php?isset=ok");
    exit;
} 

if ($action == "delerlog" && ($accessr == 101 or $accessr == 102)) {
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

    header ("Location: logfiles.php?isset=mp_dellogs&" . SID);
    exit;
} 

if ($action == "delerid" && !empty($_GET['err']) && ($accessr == 101 or $accessr == 102)) {
	$err = check($_GET['err']);
    clear_files("../used/datalog/" . $err . ".dat");

    header ("Location: logfiles.php?isset=mp_dellogs");
    exit;
} 

?>