<?php
// (c) vavok.net
require_once"../include/strtup.php";
require_once"../lang/" . $config["language"] . "/pagesprofile.php"; // lang file

$mediaLikeButton = 'off'; // dont show like buttons

$my_title = $lang_setting['settings'];
include_once"../themes/$config_themes/index.php";

if (isset($_GET['isset'])) {
	$isset = check($_GET['isset']);
	echo '<div align="center"><b><font color="#FF0000">';
	echo get_isset();
	echo '</font></b></div>';
}

if ($users->is_reg()) {

	$show_user = $db->get_data('vavok_users', "id='" . $user_id . "'", 'lang, mskin, skin');
	$page_set = $db->get_data('page_setting', "uid='" . $user_id . "'");
	$user_profil = $db->get_data('vavok_profil', "uid='" . $user_id . "'", 'subscri');
	$inbox_notif = $db->get_data('notif', "uid='" . $user_id . "' AND type='inbox'", 'active');

	echo '<br><form method="post" action="insettings.php">';
    
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
		
		
    echo $lang_setting['siteskin'] . ':<br><select name="skins"><option value="' . $show_user['skin'] . '">' . $config_themes_show . '</option>';

    $dir = opendir ("../themes");
    while ($file = readdir ($dir)) {
        if (!preg_match("/[^a-zA-Z0-9_-]/", $file) && $file != "$config_themes" && $file != 'templates') {
        	$nfile = str_replace("web_", "", $file);
        	$nfile = str_replace("wap_", "", $nfile);
        	$nfile = ucfirst($nfile);
            echo'<option value="' . $file . '">' . $nfile . '</option>';
        } 
    } 
    echo'</select><br />';
    closedir ($dir); 


	$current_mtheme = $show_user['mskin'];
	
	if (empty($current_mtheme)) {
		$current_mtheme = $show_user['skin'];
	} // update lang
	
	$mobile_theme = str_replace("web_", "", $current_mtheme);
	$mobile_theme = str_replace("wap_", "", $mobile_theme);
	$mobile_theme = ucfirst($mobile_theme);

    echo 'Mobile skin:<br><select name="mskin"><option value="' . $current_mtheme . '">' . $mobile_theme . '</option>';
    $dir = opendir ("../themes");
    while ($file = readdir ($dir)) {
        if (!preg_match("/[^a-zA-Z0-9_-]/", $file) && $file != $current_mtheme && $file != 'templates') {
        	$nfile = str_replace("web_", "", $file);
        	$nfile = str_replace("wap_", "", $nfile);
        	$nfile = ucfirst($nfile);
            echo'<option value="' . $file . '">' . $nfile . '</option>';
        } 
    } 
    echo'</select><br />';
    closedir ($dir);

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