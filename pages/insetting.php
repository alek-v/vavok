<?php 
// (c) vavok.net
require_once"../include/strtup.php";

$skins = check($_POST['skins']);
$mskin = check($_POST['mskin']);
$lang = check($_POST['lang']);
$news = check($_POST['news']);
$forumpost = check($_POST['forumpost']);
$forumtem = check($_POST['forumtem']);
$prrivs = check($_POST['prrivs']);
$sdvig = check($_POST['timezone']);
$subnews = no_br(check($_POST['subnews']));
$inbox_notification = no_br(check($_POST['inbnotif']));

$mediaLikeButton = 'off'; // dont show like buttons

if (!$users->is_reg()) {
	header("Location: ../index.php?isset=inputoff");
    exit;
}
	
$getinfo = $db->select('vavok_about', "uid='" . $user_id . "'", '', 'email');
$notif = $db->select('notif', "uid='" . $user_id . "' AND type='inbox'", '', 'email');
$email = $getinfo['email'];

if (preg_match("/[^a-zA-Z0-9_+-]/", $skins) || empty($skins)) {
    header ("Location: settings.php?isset=incorrect");
    exit;
} 
if (empty($lang)) {
    header ("Location: settings.php?isset=incorrect");
    exit;
} 
if (preg_match("/[^0-9]/", $news) || $news > 50 || empty($news)) {
    header ("Location: settings.php?isset=incorrect");
    exit;
} 
if (preg_match("/[^0-9]/", $forumpost) || $forumpost > 50 || empty($forumpost)) {
    header ("Location: settings.php?isset=incorrect");
    exit;
} 
if (preg_match("/[^0-9]/", $forumtem) || $forumtem > 50 || empty($forumtem)) {
    header ("Location: settings.php?isset=incorrect");
    exit;
}
if (preg_match("/[^0-9]/", $prrivs) || $prrivs > 50 || empty($prrivs)) {
    header ("Location: settings.php?isset=incorrect");
    exit;
} 
if (!isset($sdvig) or $sdvig == "") {
    $sdvig = '0';
} 
if (preg_match("/[^0-9+-]/", $sdvig)) {
    header ("Location: settings.php?isset=incorrect");
    exit;
} 
if (!file_exists(BASEDIR . "themes/$skins/index.php")) {
    $skins = "default";
} 

$_SESSION['my_themes'] = "";
unset($_SESSION['my_themes']); 


// site news
if ($subnews == "yes") {
    $email_check = $db->select('subs', "user_mail='" . $getinfo['email'] . "'", '', 'user_mail');
    

    if (!empty($email_check['user_mail'])) {
        $result = 'error2'; // Error! Email already exist in database!
        
        $subnewss = "1";
        $randkey = generate_password();
    } 


    if ($result == "") {
        if ($email == "") {
            $email = $getinfo['email'];
        }

        $randkey = generate_password();
        
        $db->insert_data('subs', array('user_id' => $user_id, 'user_mail' => $email, 'user_pass' => $randkey));

        $result = 'ok'; // sucessfully subscribed to site news!
        $subnewss = "1";
    } 
}
if ($subnews == "no") {
    $email_check = $db->select('subs', "user_id='" . $user_id . "'", '', 'user_mail');


    if ($email_check['user_mail'] == "") {
        $result = 'error';
        $subnewss = 0;
        $randkey = "";
    } else {
    	// unsub
        $db->delete('subs', "user_id='" . $user_id . "'");
    	
        $result = 'no';
        $subnewss = 0;
        $randkey = "";
    } 
} 
if (empty($subnews) || $subnews == '') {
	$subnewss = $get_profilx['subscri'];
	$randkey = generate_password();
}


// update changes
$fields[] = 'skin';
$fields[] = 'ipadd';
$fields[] = 'timezone';
$fields[] = 'lang';
$fields[] = 'mskin';
 
$values[] = $skins;
$values[] = $ip;
$values[] = $sdvig;
$values[] = $lang;
$values[] = $mskin;
 
$db->update('vavok_users', $fields, $values, "id='" . $user_id . "'");
unset($fields, $values);

$fields[] = 'newsmes';
$fields[] = 'forummes';
$fields[] = 'forumtem';
$fields[] = 'privmes';
 
$values[] = $news;
$values[] = $forumpost;
$values[] = $forumtem;
$values[] = $prrivs;
 
$db->update('page_setting', $fields, $values, "uid='" . $user_id . "'");
unset($fields, $values);

// update email notificatoins
$fields[] = 'subscri';
$fields[] = 'newscod';
$fields[] = 'lastvst';
 
$values[] = $subnewss;
$values[] = $randkey;
$values[] = time();
 
$db->update('vavok_profil', $fields, $values, "uid='" . $user_id . "'");
unset($fields, $values);

// notification settings
if (!isset($inbox_notification)) {
	$inbox_notification = 1;
}

$check_inb = $db->count_row('notif', "uid='" . $user_id . "' AND type='inbox'");
if ($check_inb > 0) {
    $db->update('notif', 'active', $inbox_notification, "uid='" . $user_id . "' AND type='inbox'");
} else {
	$db->insert_data('notif', array('active' => $inbox_notification, 'uid' => $user_id, 'type' => 'inbox'));
}

// redirect
header("Location: ./settings.php?isset=editsetting");
exit;
?>