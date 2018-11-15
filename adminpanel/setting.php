<?php 
// (c) vavok.net
require_once"../include/strtup.php";
$my_title = "Settings";

if (!is_reg() || !is_administrator(101)) {
    header ("Location: ../pages/error.php?error=401");
    exit;
}

if (!empty($_GET['action'])) {
    $action = check($_GET['action']);
} else {
    $action = '';
} 

include_once"../themes/$config_themes/index.php";
if (isset($_GET['isset'])) {
    $isset = check($_GET['isset']);
    echo '<div align="center"><b><font color="#FF0000">';
    echo get_isset();
    echo '</font></b></div>';
} 

if (empty($action)) {
    echo '<a href="setting.php?action=setone" class="sitelink">' . $lang_apsetting['mainset'] . '</a><br />';
    echo '<a href="setting.php?action=setnine" class="sitelink">' . $lang_apsetting['mainset'] . ' -> ' . $lang_apsetting['database'] . '</a><br />';
    echo '<a href="setting.php?action=settwo" class="sitelink">' . $lang_apsetting['shwinfo'] . '</a><br />';
    echo '<a href="setting.php?action=setthree" class="sitelink">' . $lang_apsetting['bookchatnews'] . '</a><br />';
    echo '<a href="setting.php?action=setfour" class="sitelink">' . $lang_apsetting['forumgallery'] . '</a><br />';
    echo '<a href="setting.php?action=setfive" class="sitelink">' . $lang_home['inbox'] . '</a><br />';
    // echo '<a href="setting.php?action=setsix" class="sitelink">' . $lang_apsetting['advert'] . '</a><br />';
    echo '<a href="setting.php?action=setseven" class="sitelink">' . $lang_apsetting['pagemanage'] . '</a><br />';
    echo '<a href="setting.php?action=seteight" class="sitelink">' . $lang_apsetting['other'] . '</a><br />';
} 

if ($accessr == 101 && isadmin() == true) {
    // main settings
    if ($action == "setone") {
        echo '<b>' . $lang_apsetting['mainset'] . '</b><br /><hr>';

        echo '<form method="post" action="procsets.php?action=editone">';

        echo '<p>' . $lang_apsetting['language'] . ':<br /><select name="conf_set47"><option value="' . $config['siteDefaultLang'] . '">' . $config['siteDefaultLang'] . '</option>';

        $dir = opendir ("../lang");
        while ($file = readdir($dir)) {
            if (!preg_match('/[^0-9A-Za-z.\_\-]/', $file) && $file != $config['siteDefaultLang'] && $file != '..' && $file != '.' && $file != "index.php" && $file != ".htaccess" && strlen($file) > 2) {
                echo '<option value="' . $file . '">' . $file . '</option>';
            } 
        } 
        echo '</select></p>';
        closedir($dir);

        $config_themes_show = str_replace("web_", "", $config['webtheme']);
        $config_themes_show = str_replace("wap_", "", $config_themes_show);
        $config_themes_show = ucfirst($config_themes_show);
        echo '<p>' . $lang_apsetting['webskin'] . ':<br /><select name="conf_set2"><option value="' . $config['webtheme'] . '">' . $config_themes_show . '</option>';

        $dir = opendir ("../themes");
        while ($file = readdir ($dir)) {
            if (!preg_match('/[^0-9A-Za-z.\_\-]/', $file) && $file != $config['webtheme'] && $file != '..' && $file != '.' && $file != "index.php" && $file != ".htaccess" && $file != "templates") {
                $nfile = str_replace("web_", "", $file);
                $nfile = str_replace("wap_", "", $nfile);
                $nfile = ucfirst($nfile);
                echo '<option value="' . $file . '">' . $nfile . '</option>';
            } 
        } 
        echo '</select></p>';
        closedir ($dir);

        // default mobile skin
        $config_themes_show = str_replace("web_", "", $config['mTheme']);
        $config_themes_show = str_replace("wap_", "", $config_themes_show);
        $config_themes_show = ucfirst($config_themes_show);
        echo '<p>Mobile skin:<br /><select name="conf_set12"><option value="' . $config['mTheme'] . '">' . $config_themes_show . '</option>';

        $dir = opendir ("../themes");
        while ($file = readdir($dir)) {
            if (!preg_match('/[^0-9A-Za-z.\_\-]/', $file) && $file != $config['mTheme'] && $file != '..' && $file != '.' && $file != "index.php" && $file != ".htaccess" && $file != "templates") {
                $nfile = str_replace("web_", "", $file);
                $nfile = str_replace("wap_", "", $nfile);
                $nfile = ucfirst($nfile);
                echo '<option value="' . $file . '">' . $nfile . '</option>';
            } 
        } 
        echo '</select></p>';
        closedir ($dir);

        // this will be admin username or system username
        echo '<p>' . $lang_apsetting['adminusername'] . ':<br /><input name="conf_set8" maxlength="20" value="' . $config['adminNick'] . '" /></p>';

        echo '<p>' . $lang_apsetting['adminemail'] . ':<br /><input name="conf_set9" maxlength="50" value="' . $config['adminEmail'] . '" /></p>';
        echo '<p>' . $lang_apsetting['timezone'] . ':<br /><input name="conf_set10" maxlength="3" value="' . $config['timeZone'] . '" /></p>';
        echo '<p>' . $lang_apsetting['pagetitle'] . ':<br /><input name="conf_set11" maxlength="100" value="' . $config['title'] . '" /></p>';
        echo '<p>' . $lang_apsetting['siteurl'] . ':<br /><input name="conf_set14" maxlength="50" value="' . $config['homeUrl'] . '" /></p>';
        echo '<p>' . $lang_apsetting['floodtime'] . ':<br /><input name="conf_set29" maxlength="3" value="' . $config['chatPost'] . '" /></p>';
        // echo $lang_apsetting['apfolder'] . ':<br /><input name="conf_set48" maxlength="30" value="' . $con_data[48] . '" /><br />';
        // echo 'Limit requests per IP:<br /><input name="conf_set57" maxlength="3" value="' . $con_data[57] . '" /><br />';
        echo '<p>' . $lang_apsetting['passkey'] . ':<br /><input name="conf_set1" maxlength="25" value="' . $config['keypass'] . '" /></p>';

        // quarantine time
        echo '<p>' . $lang_apsetting['quarantinetime'] . ':<br /><select name="conf_set3">';

        $quarantine = array(0 => "" . $lang_apsetting['disabled'] . "", 21600 => "6 " . $lang_apsetting['hours'] . "", 43200 => "12 " . $lang_apsetting['hours'] . "", 86400 => "24 " . $lang_apsetting['hours'] . "", 129600 => "36 " . $lang_apsetting['hours'] . "", 172800 => "48 " . $lang_apsetting['hours'] . "");

        echo '<option value="' . $config['quarantine'] . '">' . $quarantine[$config['quarantine']] . '</option>';

        foreach($quarantine as $k => $v) {
            if ($k != $config['quarantine']) {
                echo '<option value="' . $k . '">' . $v . '</option>';
            } 
        } 
        echo '</select></p>';


        // transfer protocol
        echo '<p>Transfer protocol:<br /><select name="conf_set21">';

        $tProtocol = array('HTTPS' => 'HTTPS', 'HTTP' => 'HTTP', 'auto' => 'auto');

        if (empty($config['transferProtocol'])) $config['transferProtocol'] = 'auto';
        
        echo '<option value="' . $config['transferProtocol'] . '">' . $tProtocol[$config['transferProtocol']] . '</option>';

        foreach($tProtocol as $k => $v) {
            if ($k != $config['transferProtocol']) {
                echo '<option value="' . $k . '">' . $v . '</option>';
            } 
        } 
        echo '</select></p>';


        // is registration opened
        echo '<p>' . $lang_apsetting['openreg'] . ': <br />' . $lang_apsetting['yes'] . '';
        if ($config['openReg'] == "1") {
            echo '<input name="conf_set61" type="radio" value="1" checked>';
        } else {
            echo '<input name="conf_set61" type="radio" value="1" />';
        } 
        echo ' &nbsp; &nbsp; ';
        if ($config['openReg'] == "0") {
            echo '<input name="conf_set61" type="radio" value="0" checked>';
        } else {
            echo '<input name="conf_set61" type="radio" value="0" />';
        } 
        echo $lang_apsetting['no'] . '</p>';

        // does user need to confirm registration
        echo '<p>' . $lang_apsetting['confregs'] . ': <br />' . $lang_apsetting['yes'] . '';
        if ($config['regConfirm'] == "1") {
            echo '<input name="conf_set62" type="radio" value="1" checked>';
        } else {
            echo '<input name="conf_set62" type="radio" value="1" />';
        } 
        echo ' &nbsp; &nbsp; ';
        if ($config['regConfirm'] == "0") {
            echo '<input name="conf_set62" type="radio" value="0" checked>';
        } else {
            echo '<input name="conf_set62" type="radio" value="0" />';
        } 
        echo $lang_apsetting['no'] . '</p>';

        // redirect browser to mobile or desktop theme
        echo '<p>Browser redirection: <br />' . $lang_apsetting['yes'] . '';
        if ($config['redbrow'] == "1") {
            echo '<input name="conf_set0" type="radio" value="1" checked>';
        } else {
            echo '<input name="conf_set0" type="radio" value="1" />';
        } 
        echo ' &nbsp; &nbsp; ';
        if ($config['redbrow'] == "0") {
            echo '<input name="conf_set0" type="radio" value="0" checked>';
        } else {
            echo '<input name="conf_set0" type="radio" value="0" />';
        } 
        echo $lang_apsetting['no'] . '</p>';

        // maintenance mode
        echo '<p>Maintenance: <br />' . $lang_apsetting['yes'] . ''; // update lang
        if ($config['siteOff'] == 1) {
            echo '<input name="conf_set63" type="radio" value="1" checked>';
        } else {
            echo '<input name="conf_set63" type="radio" value="1" />';
        } 
        echo ' &nbsp; &nbsp; ';
        if ($config['siteOff'] == 0) {
            echo '<input name="conf_set63" type="radio" value="0" checked>';
        } else {
            echo '<input name="conf_set63" type="radio" value="0" />';
        } 
        echo $lang_apsetting['no'] . '</p>';

        echo '<br /><input value="' . $lang_home['save'] . '" type="submit" /></form><hr>';
        echo '<br /><a href="setting.php" class="sitelink">' . $lang_home['back'] . '</a>';
    } 
} 
if ($action == "settwo") {
    echo '<b>' . $lang_apsetting['shwinfo'] . '</b><br /><hr>';

    echo '<form method="post" action="procsets.php?action=edittwo">';

    echo '<p>' . $lang_apsetting['showclock'] . ': <br />' . $lang_apsetting['yes'] . '';
    if ($config['showtime'] == "1") {
        echo '<input name="conf_set4" type="radio" value="1" checked>';
    } else {
        echo '<input name="conf_set4" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if ($config['showtime'] == "0") {
        echo '<input name="conf_set4" type="radio" value="0" checked>';
    } else {
        echo '<input name="conf_set4" type="radio" value="0" />';
    } 
    echo $lang_apsetting['no'] . '</p>';

    echo '<p>' . $lang_apsetting['pagegen'] . ': <br />' . $lang_apsetting['yes'] . '';
    if ($config['pageGenTime'] == "1") {
        echo '<input name="conf_set5" type="radio" value="1" checked>';
    } else {
        echo '<input name="conf_set5" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if ($config['pageGenTime'] == "0") {
        echo '<input name="conf_set5" type="radio" value="0" checked>';
    } else {
        echo '<input name="conf_set5" type="radio" value="0" />';
    } 
    echo $lang_apsetting['no'] . '</p>';

    echo '<p>' . $lang_apsetting['showonline'] . ': <br />' . $lang_apsetting['yes'] . '';
    if ($config['showOnline'] == "1") {
        echo '<input name="conf_set7" type="radio" value="1" checked>';
    } else {
        echo '<input name="conf_set7" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if ($config['showOnline'] == "0") {
        echo '<input name="conf_set7" type="radio" value="0" checked>';
    } else {
        echo '<input name="conf_set7" type="radio" value="0" />';
    } 
    echo $lang_apsetting['no'] . '</p>';

    echo '<p>' . $lang_apsetting['countlook'] . ':<br /><select name="conf_set74">';

    $incounters = array(6 => "" . $lang_apsetting['dontshow'] . "", 1 => "" . $lang_apsetting['vsttotalvst'] . "", 2 => "" . $lang_apsetting['clicktotalclick'] . "", 3 => "" . $lang_apsetting['clickvisits'] . "", 4 => "" . $lang_apsetting['totclicktotvst'] . "", 5 => "" . $lang_apsetting['graphic'] . "");

    echo '<option value="' . $config['showCounter'] . '">' . $incounters[$config['showCounter']] . '</option>';

    foreach($incounters as $k => $v) {
        if ($k != $config['showCounter']) {
            echo '<option value="' . $k . '">' . $v . '</option>';
        }
    } 
    echo '</select></p>';

    echo '<br /><input value="' . $lang_home['save'] . '" type="submit" /></form><hr>';
    echo '<br /><a href="setting.php" class="sitelink">' . $lang_home['back'] . '</a>';
} 
if ($action == "setthree") {
    echo '<b>' . $lang_apsetting['gbnewschatset'] . '</b><br /><hr>';

    echo '<form method="post" action="procsets.php?action=editthree">';

    echo '<p>' . $lang_apsetting['newsonpg'] . ':<br /><input name="conf_set17" maxlength="2" value="' . $config['postNews'] . '" /></p>';
    echo '<p>' . $lang_apsetting['newsonhome'] . ':<br /><input name="conf_set18" maxlength="2" value="' . $config['lastNews'] . '" /></p>';
    echo '<p>' . $lang_apsetting['msgsinbookonpg'] . ':<br /><input name="conf_set19" maxlength="2" value="' . $config['bookPost'] . '" /></p>';

    echo '<p>' . $lang_apsetting['allowguestingb'] . ': <br />' . $lang_apsetting['yes'];
    if ($config['bookGuestAdd'] == "1") {
        echo '<input name="conf_set20" type="radio" value="1" checked>';
    } else {
        echo '<input name="conf_set20" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if ($config['bookGuestAdd'] == "0") {
        echo '<input name="conf_set20" type="radio" value="0" checked>';
    } else {
        echo '<input name="conf_set20" type="radio" value="0" />';
    } 
    echo $lang_apsetting['no'] . '</p>';

    echo '<p>' . $lang_apsetting['maxinchat'] . ':<br /><input name="conf_set22" maxlength="4" value="' . $config['maxPostChat'] . '" /></p>';
    echo '<p>' . $lang_apsetting['msgsinchat'] . ':<br /><input name="conf_set23" maxlength="2" value="' . $config['chatPost'] . '" /></p>';
    echo '<p>' . $lang_apsetting['maxnews'] . ':<br /><input name="conf_set24" maxlength="5" value="' . $config['maxPostNews'] . '" /></p>';
    echo '<p>' . $lang_apsetting['maxgbmsgs'] . ':<br /><input name="conf_set25" maxlength="5" value="' . $config['maxPostBook'] . '" /></p>';
    echo '<p>' . $lang_apsetting['onepassmail'] . ':<br /><input name="conf_set56" maxlength="3" value="' . $config['subMailPacket'] . '" /></p>';

    echo '<br /><input value="' . $lang_home['save'] . '" type="submit" /></form><hr>';
    echo '<br /><a href="setting.php" class="sitelink">' . $lang_home['back'] . '</a>';
} 
if ($action == "setfour") {

    $kbs = $config['photoFileSize'] / 1024;

    // forum settings
    echo '<b>' . $lang_apsetting['forumandgalset'] . '</b><br /><hr>';

    echo '<form method="post" action="procsets.php?action=editfour">';

    echo '<br /><img src="../images/img/forums.gif" alt=""/> Forum settings<br /><br />';
    echo '<p>' . $lang_apsetting['forumon'] . ': <br />' . $lang_apsetting['yes'] . '';
    if ($config['forumAccess'] == "1") {
        echo '<input name="conf_set49" type="radio" value="1" checked>';
    } else {
        echo '<input name="conf_set49" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if ($config['forumAccess'] == "0") {
        echo '<input name="conf_set49" type="radio" value="0" checked>';
    } else {
        echo '<input name="conf_set49" type="radio" value="0" />';
    } 
    echo $lang_apsetting['no'] . '</p>';

    echo '<p>' . $lang_apsetting['fmmsgspp'] . ':<br /><input name="conf_set26" maxlength="2" value="' . $config['forumPost'] . '" /></p>';
    echo '<p>' . $lang_apsetting['fmtpcpp'] . ':<br /><input name="conf_set27" maxlength="2" value="' . $config['forumTopics'] . '" /></p>'; 
    // echo $lang_apsetting['fmfaxtopics'] . ':<br /><input name="conf_set28" maxlength="4" value="' . $con_data[28] . '" /><br />';
    
        echo '<p>Show language dropdown: <br />' . $lang_apsetting['yes'];
    if ($config['forumChLang'] == "1") {
        echo '<input name="conf_set68" type="radio" value="1" checked>';
    } else {
        echo '<input name="conf_set68" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if ($config['forumChLang'] == "0") {
        echo '<input name="conf_set68" type="radio" value="0" checked>';
    } else {
        echo '<input name="conf_set68" type="radio" value="0" />';
    } 
    echo $lang_apsetting['no'] . '</p>';


    // gallery settings
    $gallery_config = file(BASEDIR . "used/dataconfig/gallery.dat");
    if ($gallery_config) {
        $gallery_data = explode("|", $gallery_config[0]);
    } else {
        $gallery_data = explode("|", '|||||||||||||');
    }
    echo '<br /><img src="../images/img/forums.gif" alt=""/> Gallery settings<br /><br />';
    echo '<p>' . $lang_apsetting['photosperpg'] . ':<br /><input name="gallery_set8" maxlength="2" value="' . $gallery_data[8] . '" /></p>';
    echo '<p>Maximum width in gallery:<br /><input name="screen_width" maxlength="5" value="' . $gallery_data[5] . '" /></p>';
    echo '<p>Maximum height in gallery:<br /><input name="screen_height" maxlength="5" value="' . $gallery_data[6] . '" /></p>';
    echo '<p>Social media like buttons in gallery <br />' . $lang_apsetting['yes']; // update lang
    if ($gallery_data[7] == "1") {
        echo '<input name="media_buttons" type="radio" value="1" checked>';
    } else {
        echo '<input name="media_buttons" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if ($gallery_data[7] == "0") {
        echo '<input name="media_buttons" type="radio" value="0" checked>';
    } else {
        echo '<input name="media_buttons" type="radio" value="0" />';
    } 
    echo $lang_apsetting['no'] . '</p>';


    echo '<br /><img src="../images/img/forums.gif" alt=""/> Uploading in gallery<br /><br />';

    echo '<p>' . $lang_apsetting['photomaxkb'] . ':<br /><input name="conf_set38" maxlength="8" value="' . (int)$kbs . '" /></p>';
    echo '<p>' . $lang_apsetting['photopx'] . ':<br /><input name="conf_set39" maxlength="4" value="' . $config['maxPhotoPixels'] . '" /></p>';
    echo '<p>Users can upload? <br />' . $lang_apsetting['yes'] . '';
    if ($gallery_data[0] == "1") {
        echo '<input name="gallery_set0" type="radio" value="1" checked>';
    } else {
        echo '<input name="gallery_set0" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if ($gallery_data[0] == "0") {
        echo '<input name="gallery_set0" type="radio" value="0" checked>';
    } else {
        echo '<input name="gallery_set0" type="radio" value="0" />';
    } 
    echo $lang_apsetting['no'] . '</p>';

    echo '<br /><input value="' . $lang_home['save'] . '" type="submit" /></form><hr>';
    echo '<br /><a href="setting.php" class="sitelink">' . $lang_home['back'] . '</a>';
} 
// 30,32,33,40,41,42,46,66
if ($action == "setfive") {
    echo '<b>' . $lang_apsetting['downandinbxsets'] . '</b><br /><hr>';

    echo '<form method="post" action="procsets.php?action=editfive">';

    echo '<p>' . $lang_apsetting['maxinbxmsgs'] . ':<br /><input name="conf_set30" maxlength="5" value="' . $config['pvtLimit'] . '" /></p>';
    echo '<br /><input value="' . $lang_home['save'] . '" type="submit" /></form><hr>';
    echo '<br /><a href="setting.php" class="sitelink">' . $lang_home['back'] . '</a>';
}
if ($action == "setseven") {
    echo '<b>' . $lang_apsetting['pagessets'] . '</b><br /><hr>';

    echo '<form method="post" action="procsets.php?action=editseven">';

    echo '<p>' . $lang_apsetting['dataonpage'] . ':<br /><input name="conf_set31" maxlength="2" value="' . $config['dataOnPage'] . '" /></p>'; 
    echo '<p>' . $lang_apsetting['maxrefererdata'] . ':<br /><input name="conf_set51" maxlength="3" value="' . $config['refererLog'] . '" /></p>'; 

    echo '<p>' . $lang_apsetting['showrefpage'] . ': <br />' . $lang_apsetting['yes'] . '';
    if ($config['showRefPage'] == "1") {
        echo '<input name="conf_set70" type="radio" value="1" checked>';
    } else {
        echo '<input name="conf_set70" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if ($config['showRefPage'] == "0") {
        echo '<input name="conf_set70" type="radio" value="0" checked>';
    } else {
        echo '<input name="conf_set70" type="radio" value="0" />';
    } 
    echo $lang_apsetting['no'] . '</p>';

    echo '<p>Facebook comments on pages: <br />' . $lang_apsetting['yes'] . ''; // update lang
    if ($config['pgFbComm'] == "1") {
        echo '<input name="conf_set6" type="radio" value="1" checked>';
    } else {
        echo '<input name="conf_set6" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if ($config['pgFbComm'] == "0") {
        echo '<input name="conf_set6" type="radio" value="0" checked>';
    } else {
        echo '<input name="conf_set6" type="radio" value="0" />';
    } 
    echo $lang_apsetting['no'] . '</p>';

    echo '<br /><input value="' . $lang_home['save'] . '" type="submit" /></form><hr>';
    echo '<br /><a href="setting.php" class="sitelink">' . $lang_home['back'] . '</a>';
} 
if ($action == "seteight") {
    echo '<b>' . $lang_apsetting['other'] . '</b><br /><hr>';

    echo '<form method="post" action="procsets.php?action=editeight">';

    echo '<p>' . $lang_apsetting['maxlogfile'] . ':<br /><input name="conf_set58" maxlength="3" value="' . $config['maxLogData'] . '" /></p>';
    echo '<p>' . $lang_apsetting['maxbantime'] . ':<br /><input name="conf_set76" maxlength="3" value="' . round($config['maxBanTime'] / 1440) . '" /></p>';

    echo '<br /><input value="' . $lang_home['save'] . '" type="submit" /></form><hr>';
    echo '<br /><a href="setting.php" class="sitelink">' . $lang_home['back'] . '</a>';
} 
// database settings
if ($action == "setnine") {
    echo '<b>Database settings</b><br /><hr>';

    echo '<form method="post" action="procsets.php?action=editnine">';

    echo '<p>Database host:<br /><input name="conf_set77" maxlength="40" value="' . $config['dbhost'] . '" /></p>';
    echo '<p>' . $lang_apsetting['username'] . ':<br /><input name="conf_set78" maxlength="40" value="' . $config['dbuser'] . '" /></p>';
    echo '<p>' . $lang_apsetting['password'] . ':<br /><input name="conf_set79" maxlength="40" value="' . $config['dbpass'] . '" /></p>';
    echo '<p>' . $lang_apsetting['dbname'] . ':<br /><input name="conf_set80" maxlength="40" value="' . $config['dbname'] . '" /></p>';

    echo '<br /><input value="' . $lang_home['save'] . '" type="submit" /></form><hr>';
    echo '<br /><a href="setting.php" class="sitelink">' . $lang_home['back'] . '</a>';
} 

echo'<br /><a href="index.php" class="sitelink">' . $lang_home['admpanel'] . '</a><br />';
echo'<a href="../" class="homepage">' . $lang_home['home'] . '</a><br />';

include_once"../themes/" . $config_themes . "/foot.php";
?>
