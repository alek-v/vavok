<?php
// (c) vavok.net

require_once"../include/startup.php";

if (!$users->is_reg()) {
	redirect_to("../index.php?isset=inputoff");
}

$action = isset($_GET['action']) ? check($_GET['action']) : '';

// Save settings
if ($action == 'save') {

	$skins = isset($_POST['skins']) ? check($_POST['skins']) : '';
	$mskin = isset($_POST['mskin']) ? check($_POST['mskin']) : '';
	$lang = isset($_POST['lang']) ? check($_POST['lang']) : '';
	$user_timezone = isset($_POST['timezone']) ? check($_POST['timezone']) : 0;
	$subnews = isset($_POST['subnews']) ? check($_POST['subnews']) : '';
	$inbox_notification = isset($_POST['inbnotif']) ? check($_POST['inbnotif']) : '';
		
	$getinfo = $db->get_data('vavok_about', "uid='{$user_id}'", 'email');
	$notif = $db->get_data('notif', "uid='{$user_id}' AND type='inbox'", 'email');
	$email = $getinfo['email'];

	if (empty($lang)) {
	    header ("Location: settings.php?isset=incorrect");
	    exit;
	} 

	if (!isset($user_timezone)) {
	    $user_timezone = '0';
	}
	if (preg_match("/[^0-9+-]/", $user_timezone)) {
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
	    $email_check = $db->get_data('subs', "user_mail='{$getinfo['email']}'", 'user_mail');
	    

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
	    $email_check = $db->get_data('subs', "user_id='{$user_id}'", 'user_mail');


	    if ($email_check['user_mail'] == "") {
	        $result = 'error';
	        $subnewss = 0;
	        $randkey = "";
	    } else {
	    	// unsub
	        $db->delete('subs', "user_id='{$user_id}'");
	    	
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
	$fields = array();
	$fields[] = 'skin';
	$fields[] = 'ipadd';
	$fields[] = 'timezone';
	$fields[] = 'mskin';

	$values = array();
	$values[] = $skins;
	$values[] = $users->find_ip();
	$values[] = $user_timezone;
	$values[] = $mskin;
	 
	$db->update('vavok_users', $fields, $values, "id='{$user_id}'");
	unset($fields, $values);

	// Update language
	$users->change_language($lang);

	// update email notificatoins
	$fields = array();
	$fields[] = 'subscri';
	$fields[] = 'newscod';
	$fields[] = 'lastvst';
	 
	$values = array();
	$values[] = $subnewss;
	$values[] = $randkey;
	$values[] = time();
	 
	$db->update('vavok_profil', $fields, $values, "uid='{$user_id}'");
	unset($fields, $values);

	// notification settings
	if (!isset($inbox_notification)) {
		$inbox_notification = 1;
	}

	$check_inb = $db->count_row('notif', "uid='{$user_id}' AND type='inbox'");
	if ($check_inb > 0) {
	    $db->update('notif', 'active', $inbox_notification, "uid='{$user_id}' AND type='inbox'");
	} else {
		$db->insert_data('notif', array('active' => $inbox_notification, 'uid' => $user_id, 'type' => 'inbox'));
	}

	// redirect
	redirect_to("./settings.php?isset=editsetting");

}


require_once"../lang/" . $config["language"] . "/pagesprofile.php"; // lang file



$my_title = $lang_setting['settings'];
include_once"../themes/$config_themes/index.php";

if ($users->is_reg()) {

	$show_user = $db->get_data('vavok_users', "id='" . $user_id . "'", 'lang, mskin, skin');
	$page_set = $db->get_data('page_setting', "uid='" . $user_id . "'");
	$user_profil = $db->get_data('vavok_profil', "uid='" . $user_id . "'", 'subscri');
	$inbox_notif = $db->get_data('notif', "uid='" . $user_id . "' AND type='inbox'", 'active');

	echo '<br><form method="post" action="settings.php?action=save">';
    
	echo $lang_home['lang'] . ':<br><select name="lang"><option value="' . $show_user['lang'] . '">' . $show_user['lang'] . '</option>';

    $dir = opendir ("../lang");
    while ($file = readdir ($dir)) {
        if (!preg_match("/[^a-z0-9_-]/", $file) && ($file != $show_user['lang']) && strlen($file) > 2) {
            echo '<option value="' . $file . '">' . $file . '</option>';
        } 
    } 
    echo '</select><br />';
    closedir($dir);
    
    
	$config_themes_show = str_replace("web_", "", $show_user['skin']);
	$config_themes_show = str_replace("wap_", "", $config_themes_show);
	$config_themes_show = ucfirst($config_themes_show);
		

	echo '<input name="skins" type="hidden" value="' . $show_user['skin'] . '" />';


    // echo 'Time zone (+1 -1):<br><input name="sdvig" value="'.$udata[30].'" /><br />';
    
    echo $lang_profil['subscribe'] . ': <br />Yes';
    if ($user_profil['subscri'] == "1") {
        echo '<input name="subnews" type="radio" value="yes" checked>';
    } else {
        echo '<input name="subnews" type="radio" value="yes" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if ($user_profil['subscri'] == "0" || empty($user_profil['subscri'])) {
        echo '<input name="subnews" type="radio" value="no" checked>';
    } else {
        echo '<input name="subnews" type="radio" value="no" />';
    } 
    echo 'No<br />';

    echo 'Receive new message notification: <br />Yes';
    if ($inbox_notif['active'] == 1 || empty($inbox_notif['active'])) {
        echo '<input name="inbnotif" type="radio" value="1" checked>';
    } else {
        echo '<input name="inbnotif" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if ($inbox_notif['active'] == 0) {
        echo '<input name="inbnotif" type="radio" value="0" checked>';
    } else {
        echo '<input name="inbnotif" type="radio" value="0" />';
    } 
    echo 'No<br />';

    echo '<br><br><input value="' . $lang_home['save'] . '" type="submit" /></form>';


} else {
    echo $lang_home['notloged'] . '<br>';
} 

echo '<p><a href="../" class="btn btn-primary homepage"><img src="../images/img/homepage.gif" alt=""> ' . $lang_home['home'] . '</a></p>';


include_once"../themes/" . $config_themes . "/foot.php";

?>