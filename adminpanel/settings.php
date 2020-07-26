<?php 
// (c) vavok.net
require_once"../include/startup.php";

if (!$users->is_reg() || !$users->is_administrator(101)) {
    redirect_to("../pages/error.php?error=401");
}

$action = isset($_GET['action']) ? check($_GET['action']) : '';

// main settings update
if ($action == "editone") {

	// Check fields
    if (!isset($_POST['conf_set1']) || !isset($_POST['conf_set2']) || !isset($_POST['conf_set3']) || !isset($_POST['conf_set8']) || !isset($_POST['conf_set9']) || !isset($_POST['conf_set10']) || !isset($_POST['conf_set11']) || !isset($_POST['conf_set14']) || !isset($_POST['conf_set21']) || !isset($_POST['conf_set29']) || !isset($_POST['conf_set61']) || !isset($_POST['conf_set62']) || !isset($_POST['conf_set63'])) {
        redirect_to("settings.php?action=setone&isset=mp_nosset");
    }

    $ufile = file_get_contents(BASEDIR . "used/config.dat");
    $udata = explode("|", $ufile);

    $udata[1] = check($_POST['conf_set1']);
    $udata[2] = check($_POST['conf_set2']);
    $udata[3] = check($_POST['conf_set3']);
    $udata[8] = check($_POST['conf_set8']);
    $udata[9] = htmlspecialchars(stripslashes(trim($_POST['conf_set9'])));
    $udata[10] = check($_POST['conf_set10']);
    $udata[11] = check($_POST['conf_set11']);
    $udata[14] = check($_POST['conf_set14']);
    $udata[21] = check($_POST['conf_set21']); // transfer protocol
    $udata[29] = (int)$_POST['conf_set29'];
    $udata[47] = check($_POST['conf_set47']);
    $udata[61] = (int)$_POST['conf_set61'];
    $udata[62] = (int)$_POST['conf_set62'];
    $udata[63] = (int)$_POST['conf_set63'];

    $utext = '';

    for ($u = 0; $u < get_configuration('configKeys'); $u++) {
        $utext .= $udata[$u] . '|';
    } 

    // update configuration file
    if (!empty($udata[8]) && !empty($udata[9])) {
        file_put_contents(BASEDIR . "used/config.dat", $utext);
    } 

    // update .htaccess file
    // dont force https
$htaccess_tp_nos = '# force https protocol
#RewriteCond %{HTTPS} !=on
#RewriteRule ^.*$ https://%{SERVER_NAME}%{REQUEST_URI} [R,L]';

        // force https
$htaccess_tp_s = '# force https protocol
RewriteCond %{HTTPS} !=on
RewriteRule ^.*$ https://%{SERVER_NAME}%{REQUEST_URI} [R,L]';

    if (get_configuration('transferProtocol') == 'HTTPS' && ($udata[21] == 'auto' || $udata[21] == 'HTTP')) {

        // Disable forcing HTTPS in .htaccess

        $file = file_get_contents('../.htaccess');

        $start = strpos($file, '# force https protocol');
        $strlen = mb_strlen($htaccess_tp_s); // find string length


        $file = substr_replace($file, $htaccess_tp_nos, $start, $strlen);

        file_put_contents('../.htaccess', $file);

    } elseif ($udata[21] == 'HTTPS' && (get_configuration('transferProtocol') == 'HTTP' || get_configuration('transferProtocol') == 'auto')) {
        
        // Enable forcing HTTPS in .htaccess

        $file = file_get_contents('../.htaccess');

        $start = strpos($file, '# force https protocol');
        $strlen = mb_strlen($htaccess_tp_nos); // find string length


        $file = substr_replace($file, $htaccess_tp_s, $start, $strlen);

        file_put_contents('../.htaccess', $file);
    }

    redirect_to("settings.php?isset=mp_yesset");

 
} 


if ($action == "edittwo") {

	if ($_POST['conf_set4'] != "" && $_POST['conf_set5'] != "" && $_POST['conf_set7'] != "" && isset($_POST['conf_set32']) && $_POST['conf_set74'] != "") {
	
	$ufile = file(BASEDIR . "used/config.dat");
	$udata = explode("|", $ufile[0]);

	$udata[4] = (int)$_POST['conf_set4'];
	$udata[5] = (int)$_POST['conf_set5'];
	$udata[7] = (int)$_POST['conf_set7'];
	$udata[32] = (int)$_POST['conf_set32']; // cookie consent
	$udata[74] = (int)$_POST['conf_set74'];

	for ($u = 0; $u < get_configuration('configKeys'); $u++) {
	    $utext .= $udata[$u] . '|';
	} 

	if (!empty($udata[8]) && !empty($udata[9])) {
        // Save data
        file_put_contents(BASEDIR . "used/config.dat", $utext);
	} 

	redirect_to ("settings.php?isset=mp_yesset");

	} else {
	header ("Location: settings.php?action=settwo&isset=mp_nosset");
	exit;
	} 
	
} 

if ($action == "editthree") {

    if ($_POST['conf_set20'] != "" && $_POST['conf_set22'] != "" && $_POST['conf_set24'] != "" && $_POST['conf_set25'] != "" && $_POST['conf_set56'] != "") {

    $ufile = file(BASEDIR . "used/config.dat");
    $udata = explode("|", $ufile[0]);

    $udata[20] = (int)$_POST['conf_set20'];
    $udata[22] = (int)$_POST['conf_set22'];
    $udata[24] = (int)$_POST['conf_set24'];
    $udata[25] = (int)$_POST['conf_set25'];
    $udata[56] = (int)$_POST['conf_set56'];
    $udata[63] = (int)$_POST['conf_set63'];
    $udata[64] = (int)$_POST['conf_set64'];
    $udata[65] = (int)$_POST['conf_set65'];

    for ($u = 0; $u < get_configuration('configKeys'); $u++) {
        $utext .= $udata[$u] . '|';
    } 

    file_put_contents(BASEDIR . "used/config.dat", $utext);

    header ("Location: settings.php?isset=mp_yesset");
    exit;
    } else {
    header ("Location: settings.php?action=setthree&isset=mp_nosset");
    exit;
    }
}

if ($action == "editfour") {

    if ($_POST['conf_set38'] != "" && $_POST['conf_set39'] != "" && $_POST['conf_set49'] != "") {

    // update main config
    $ufile = file(BASEDIR . "used/config.dat");
    $udata = explode("|", $ufile[0]);

    if (!empty($_POST['conf_set28'])) {
    $udata[28] = (int)$_POST['conf_set28'];
    }
    $udata[37] = (int)$_POST['conf_set37'];
    $udata[38] = (int)$_POST['conf_set38'];
    $udata[38] = $udata[38] * 1024;
    $udata[38] = (int)$udata[38];
    $udata[39] = (int)$_POST['conf_set39'];
    $udata[49] = (int)$_POST['conf_set49'];
    $udata[68] = (int)$_POST['conf_set68'];

    for ($u = 0; $u < get_configuration('configKeys'); $u++) {
        $utext .= $udata[$u] . '|';
    } 

    file_put_contents(BASEDIR . "used/config.dat", $utext);

    // update gallery settings
    $gallery_file = file(BASEDIR . "used/dataconfig/gallery.dat");
    if ($gallery_file) {
        $gallery_data = explode("|", $gallery_file[0]);

        $gallery_data[0] = (int)$_POST['gallery_set0'];
        $gallery_data[8] = (int)$_POST['gallery_set8']; // photos per page
        $gallery_data[5] = (int)$_POST['screen_width'];
        $gallery_data[6] = (int)$_POST['screen_height'];
        $gallery_data[7] = (int)$_POST['media_buttons'];


        for ($u = 0; $u < get_configuration('configKeys'); $u++) {
            $gallery_text .= $gallery_data[$u] . '|';
        } 

        if (isset($gallery_data[0])) {
            file_put_contents(BASEDIR . "used/dataconfig/gallery.dat", $gallery_text);
        }
    }

    redirect_to("settings.php?isset=mp_yesset");

    } else { redirect_to("settings.php?action=setfour&isset=mp_nosset"); }

} 

if ($action == "editfive") {

	if (!empty($_POST['conf_set30'])) {
	$ufile = file(BASEDIR . "used/config.dat");
	$udata = explode("|", $ufile[0]);

	$udata[30] = (int)$_POST['conf_set30'];

	for ($u = 0; $u < get_configuration('configKeys'); $u++) {
	    $utext .= $udata[$u] . '|';
	} 

	if (!empty($udata[8]) && !empty($udata[9])) {
	    file_put_contents(BASEDIR . "used/config.dat", $utext);
	}

	redirect_to("settings.php?isset=mp_yesset");

	} else { redirect_to("settings.php?action=setfive&isset=mp_nosset"); }

}

if ($action == "editseven") {

    if (!empty($_POST['conf_set6']) || !empty($_POST['conf_set51']) || !empty($_POST['conf_set70'])) {

        // url of custom pages
        $htaccess = file_get_contents('../.htaccess'); // load .htaccess file

        // replace custom link
        $chars = strlen('# website custom pages');
        $start = strpos($htaccess, '# website custom pages') + $chars;
        $end = strpos($htaccess, '# end of website custom pages');

        $replace = '';
        for ($i=$start; $i < $end; $i++) {
            $replace .= $htaccess[$i];
        }

        // do replacement
        if (!empty($_POST['conf_set28'])) {
            $_POST['conf_set28'] = str_replace(' ', '', $_POST['conf_set28']);

            $replacement = "\r\n" . 'RewriteRule ^' . $_POST['conf_set28'] . '\/([^\/]+)\/?$ pages/pages.php?pg=$1 [NC,L]' . "\r\n";
        } else { $replacement = "\r\n# custom_link - don't remove\r\n"; }

        $new_htaccess = str_replace($replace, $replacement, $htaccess);

        // save changes
        file_put_contents('../.htaccess', $new_htaccess);

        $data = array(
            6 => $_POST['conf_set6'],
            28 => $_POST['conf_set28'],
            51 => $_POST['conf_set51'],
            70 => $_POST['conf_set70']
        );

        $config_update = new Config();
        $config_update->update($data);

        redirect_to("settings.php?isset=mp_yesset");

    } else {
        redirect_to("settings.php?action=setseven&isset=mp_nosset");
    } 
    
} 

if ($action == "editeight") {

    if ($_POST['conf_set58'] != "" && $_POST['conf_set76'] != "") {
    $ufile = file(BASEDIR . "used/config.dat");
    $udata = explode("|", $ufile[0]);

    $udata[58] = (int)$_POST['conf_set58'];
    $udata[76] = round($_POST['conf_set76'] * 1440);

    for ($u = 0; $u < get_configuration('configKeys'); $u++) {
        $utext .= $udata[$u] . '|';
    } 

    if (!empty($udata[8]) && !empty($udata[9])) {
        file_put_contents(BASEDIR . "used/config.dat", $utext);
    }

    redirect_to("settings.php?isset=mp_yesset");

    } else {
    redirect_to("settings.php?action=seteight&isset=mp_nosset");
    } 

} 
// edit database settings
if ($action == "editnine") {

    if ($_POST['conf_set77'] != "" && $_POST['conf_set78'] != "" && $_POST['conf_set79'] != "" && $_POST['conf_set80'] != "") {

        // check for tables
        if (!$db->table_exists($_POST['conf_set71'] . 'pages')) { $db->copy_table('pages', $_POST['conf_set71']); } // pages for this site
        if (!$db->table_exists($_POST['conf_set71'] . 'online')) { $db->copy_table('online', $_POST['conf_set71']); } // visitor counter for this site
        if (!$db->table_exists($_POST['conf_set71'] . 'specperm')) { $db->copy_table('specperm', $_POST['conf_set71']); } // permittions for this site

        if (!$db->table_exists($_POST['conf_set71'] . 'counter')) {

            $db->copy_table('counter', $_POST['conf_set71']);

            // set default values
            $db->query("INSERT INTO " . $_POST['conf_set71'] . "counter (`day`, `month`, `visits_today`, `visits_total`, `clicks_today`, `clicks_total`) VALUES (0, 0, 0, 0, 0, 0)");

        } // visitor counter for this site



        $data = array(
            71 => $_POST['conf_set71'], // crossdomain table prefix 'tablePrefix'
            77 => $_POST['conf_set77'],
            78 => $_POST['conf_set78'],
            79 => $_POST['conf_set79'],
            80 => $_POST['conf_set80']
        );

        $config_update = new Config();
        $config_update->update($data);

        redirect_to("settings.php?isset=mp_yesset");

    } else {

    redirect_to("settings.php?action=setnine&isset=mp_nosset");

    }

}

$my_title = "Settings";

require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

if (empty($action)) {
    echo '<a href="settings.php?action=setone" class="btn btn-outline-primary sitelink">' . $lang_apsetting['mainset'] . '</a>';
    echo '<a href="settings.php?action=setnine" class="btn btn-outline-primary sitelink">' . $lang_apsetting['mainset'] . ' -> ' . $lang_apsetting['database'] . '</a>';
    echo '<a href="settings.php?action=settwo" class="btn btn-outline-primary sitelink">' . $lang_apsetting['shwinfo'] . '</a>';
    echo '<a href="settings.php?action=setthree" class="btn btn-outline-primary sitelink">' . $lang_apsetting['bookchatnews'] . '</a>';
    echo '<a href="settings.php?action=setfour" class="btn btn-outline-primary sitelink">' . $lang_apsetting['forumgallery'] . '</a>';
    echo '<a href="settings.php?action=setfive" class="btn btn-outline-primary sitelink">' . $localization->string('inbox') . '</a>';
    // echo '<a href="settings.php?action=setsix" class="btn btn-outline-primary sitelink">' . $lang_apsetting['advert'] . '</a><br />';
    echo '<a href="settings.php?action=setseven" class="btn btn-outline-primary sitelink">' . $lang_apsetting['pagemanage'] . '</a>';
    echo '<a href="settings.php?action=seteight" class="btn btn-outline-primary sitelink">' . $lang_apsetting['other'] . '</a>';
} 

if ($_SESSION['permissions'] == 101 && $users->is_administrator()) {
    // main settings
    if ($action == "setone") {
        echo '<h1>' . $lang_apsetting['mainset'] . '</h1>';

        echo '<form method="post" action="settings.php?action=editone">';

        echo '<p>' . $lang_apsetting['language'] . ':<br /><select name="conf_set47"><option value="' . get_configuration('siteDefaultLang') . '">' . get_configuration('siteDefaultLang') . '</option>';

        $dir = opendir ("../lang");
        while ($file = readdir($dir)) {
            if (!preg_match('/[^0-9A-Za-z.\_\-]/', $file) && $file != get_configuration('siteDefaultLang') && $file != '..' && $file != '.' && $file != "index.php" && $file != ".htaccess" && strlen($file) > 2) {
                echo '<option value="' . $file . '">' . $file . '</option>';
            } 
        } 
        echo '</select></p>';
        closedir($dir);

        $config_themes_show = str_replace("web_", "", get_configuration('webtheme'));
        $config_themes_show = str_replace("wap_", "", $config_themes_show);
        $config_themes_show = ucfirst($config_themes_show);
        echo '<p>' . $lang_apsetting['webskin'] . ':<br /><select name="conf_set2"><option value="' . get_configuration('webtheme') . '">' . $config_themes_show . '</option>';

        $dir = opendir ("../themes");
        while ($file = readdir ($dir)) {
            if (!preg_match('/[^0-9A-Za-z.\_\-]/', $file) && $file != get_configuration('webtheme') && $file != '..' && $file != '.' && $file != "index.php" && $file != ".htaccess" && $file != "templates") {
                $nfile = str_replace("web_", "", $file);
                $nfile = str_replace("wap_", "", $nfile);
                $nfile = ucfirst($nfile);
                echo '<option value="' . $file . '">' . $nfile . '</option>';
            } 
        } 
        echo '</select></p>';
        closedir ($dir);

        // this will be admin username or system username
        echo '<p>' . $lang_apsetting['adminusername'] . ':<br /><input name="conf_set8" maxlength="20" value="' . get_configuration('adminNick') . '" /></p>';

        echo '<p>' . $lang_apsetting['adminemail'] . ':<br /><input name="conf_set9" maxlength="50" value="' . get_configuration('adminEmail') . '" /></p>';
        echo '<p>' . $lang_apsetting['timezone'] . ':<br /><input name="conf_set10" maxlength="3" value="' . get_configuration('timeZone') . '" /></p>';
        echo '<p>' . $lang_apsetting['pagetitle'] . ':<br /><input name="conf_set11" maxlength="100" value="' . get_configuration('title') . '" /></p>';
        echo '<p>' . $lang_apsetting['siteurl'] . ':<br /><input name="conf_set14" maxlength="50" value="' . get_configuration('homeUrl') . '" /></p>';
        echo '<p>' . $lang_apsetting['floodtime'] . ':<br /><input name="conf_set29" maxlength="3" value="' . get_configuration('floodTime') . '" /></p>';
        echo '<p>' . $lang_apsetting['passkey'] . ':<br /><input name="conf_set1" maxlength="25" value="' . get_configuration('keypass') . '" /></p>';

        // quarantine time
        echo '<p>' . $lang_apsetting['quarantinetime'] . ':<br /><select name="conf_set3">';

        $quarantine = array(0 => "" . $lang_apsetting['disabled'] . "", 21600 => "6 " . $lang_apsetting['hours'] . "", 43200 => "12 " . $lang_apsetting['hours'] . "", 86400 => "24 " . $lang_apsetting['hours'] . "", 129600 => "36 " . $lang_apsetting['hours'] . "", 172800 => "48 " . $lang_apsetting['hours'] . "");

        echo '<option value="' . get_configuration('quarantine') . '">' . $quarantine[get_configuration('quarantine')] . '</option>';

        foreach($quarantine as $k => $v) {
            if ($k != get_configuration('quarantine')) {
                echo '<option value="' . $k . '">' . $v . '</option>';
            } 
        } 
        echo '</select></p>';

        // transfer protocol
        echo '<p>Transfer protocol:<br /><select name="conf_set21">';

        $tProtocol = array('HTTPS' => 'HTTPS', 'HTTP' => 'HTTP', 'auto' => 'auto');

        $transfer_protocol = get_configuration('transferProtocol');
        if (empty(get_configuration('transferProtocol'))) $transfer_protocol = 'auto';
        
        echo '<option value="' . $transfer_protocol . '">' . $tProtocol[$transfer_protocol] . '</option>';

        foreach($tProtocol as $k => $v) {
            if ($k != $transfer_protocol) {
                echo '<option value="' . $k . '">' . $v . '</option>';
            } 
        } 
        echo '</select></p>';

        // Registration opened or closed
        echo '<p>' . $lang_apsetting['openreg'] . ': <br />' . $lang_apsetting['yes'] . '';
        if (get_configuration('openReg') == 1) {
            echo '<input name="conf_set61" type="radio" value="1" checked>';
        } else {
            echo '<input name="conf_set61" type="radio" value="1" />';
        } 
        echo ' &nbsp; &nbsp; ';
        if (get_configuration('openReg') == 0) {
            echo '<input name="conf_set61" type="radio" value="0" checked>';
        } else {
            echo '<input name="conf_set61" type="radio" value="0" />';
        } 
        echo $lang_apsetting['no'] . '</p>';

        // Does user need to confirm registration
        echo '<p>' . $lang_apsetting['confregs'] . ': <br />' . $lang_apsetting['yes'] . '';
        if (get_configuration('regConfirm') == 1) {
            echo '<input name="conf_set62" type="radio" value="1" checked>';
        } else {
            echo '<input name="conf_set62" type="radio" value="1" />';
        } 
        echo ' &nbsp; &nbsp; ';
        if (get_configuration('regConfirm') == 0) {
            echo '<input name="conf_set62" type="radio" value="0" checked>';
        } else {
            echo '<input name="conf_set62" type="radio" value="0" />';
        } 
        echo $lang_apsetting['no'] . '</p>';

        // Maintenance mode
        echo '<p>Maintenance: <br />' . $lang_apsetting['yes'] . ''; // update lang
        if (get_configuration('siteOff') == 1) {
            echo '<input name="conf_set63" type="radio" value="1" checked>';
        } else {
            echo '<input name="conf_set63" type="radio" value="1" />';
        } 
        echo ' &nbsp; &nbsp; ';
        if (get_configuration('siteOff') == 0) {
            echo '<input name="conf_set63" type="radio" value="0" checked>';
        } else {
            echo '<input name="conf_set63" type="radio" value="0" />';
        } 
        echo $lang_apsetting['no'] . '</p>';

        echo '<br /><button class="btn btn-primary" type="submit" />' . $localization->string('save') . '</button></form><hr>';
        echo '<br /><a href="settings.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a>';
    } 
} 
if ($action == "settwo") {
    echo '<h1>' . $lang_apsetting['shwinfo'] . '</h1>';

    echo '<form method="post" action="settings.php?action=edittwo">';

    echo '<p>' . $lang_apsetting['showclock'] . ': <br />' . $lang_apsetting['yes'] . '';
    if (get_configuration('showtime') == 1) {
        echo '<input name="conf_set4" type="radio" value="1" checked>';
    } else {
        echo '<input name="conf_set4" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if (get_configuration('showtime') == 0) {
        echo '<input name="conf_set4" type="radio" value="0" checked>';
    } else {
        echo '<input name="conf_set4" type="radio" value="0" />';
    } 
    echo $lang_apsetting['no'] . '</p>';

    echo '<p>' . $lang_apsetting['pagegen'] . ': <br />' . $lang_apsetting['yes'] . '';
    if (get_configuration('pageGenTime') == 1) {
        echo '<input name="conf_set5" type="radio" value="1" checked>';
    } else {
        echo '<input name="conf_set5" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if (get_configuration('pageGenTime') == 0) {
        echo '<input name="conf_set5" type="radio" value="0" checked>';
    } else {
        echo '<input name="conf_set5" type="radio" value="0" />';
    } 
    echo $lang_apsetting['no'] . '</p>';


    echo '<p>' . $lang_apsetting['showonline'] . ': <br />' . $lang_apsetting['yes'] . '';
    if (get_configuration('showOnline') == 1) {
        echo '<input name="conf_set7" type="radio" value="1" checked>';
    } else {
        echo '<input name="conf_set7" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if (get_configuration('showOnline') == 0) {
        echo '<input name="conf_set7" type="radio" value="0" checked>';
    } else {
        echo '<input name="conf_set7" type="radio" value="0" />';
    } 
    echo $lang_apsetting['no'] . '</p>';

	// cookie consent
	echo '<p>Cookie consent: <br />' . $lang_apsetting['yes'] . '';
    if (get_configuration('cookieConsent') == 1) {
        echo '<input name="conf_set32" type="radio" value="1" checked>';
    } else {
        echo '<input name="conf_set32" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if (get_configuration('cookieConsent') == 0) {
        echo '<input name="conf_set32" type="radio" value="0" checked>';
    } else {
        echo '<input name="conf_set32" type="radio" value="0" />';
    } 
    echo $lang_apsetting['no'] . '</p>';


    echo '<p>' . $lang_apsetting['countlook'] . ':<br /><select name="conf_set74">';

    $incounters = array(6 => "" . $lang_apsetting['dontshow'] . "", 1 => "" . $lang_apsetting['vsttotalvst'] . "", 2 => "" . $lang_apsetting['clicktotalclick'] . "", 3 => "" . $lang_apsetting['clickvisits'] . "", 4 => "" . $lang_apsetting['totclicktotvst']);

    echo '<option value="' . get_configuration('showCounter') . '">' . $incounters[get_configuration('showCounter')] . '</option>';

    foreach($incounters as $k => $v) {
        if ($k != get_configuration('showCounter')) {
            echo '<option value="' . $k . '">' . $v . '</option>';
        }
    } 
    echo '</select></p>';

    echo '<br /><button class="btn btn-primary" type="submit" />' . $localization->string('save') . '</button></form><hr>';
    echo '<br /><a href="settings.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a>';
}

if ($action == "setthree") {
    echo '<h1>' . $lang_apsetting['gbnewschatset'] . '</h1>';

    echo '<form method="post" action="settings.php?action=editthree">';

    echo '<p>' . $lang_apsetting['allowguestingb'] . ': <br />' . $lang_apsetting['yes'];
    if (get_configuration('bookGuestAdd') == 1) {
        echo '<input name="conf_set20" type="radio" value="1" checked>';
    } else {
        echo '<input name="conf_set20" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if (get_configuration('bookGuestAdd') == 0) {
        echo '<input name="conf_set20" type="radio" value="0" checked>';
    } else {
        echo '<input name="conf_set20" type="radio" value="0" />';
    } 
    echo $lang_apsetting['no'] . '</p>';

    echo '<p>' . $lang_apsetting['maxinchat'] . ':<br /><input name="conf_set22" maxlength="4" value="' . get_configuration('maxPostChat') . '" /></p>';
    echo '<p>' . $lang_apsetting['maxnews'] . ':<br /><input name="conf_set24" maxlength="5" value="' . get_configuration('maxPostNews') . '" /></p>';
    echo '<p>' . $lang_apsetting['maxgbmsgs'] . ':<br /><input name="conf_set25" maxlength="5" value="' . get_configuration('maxPostBook') . '" /></p>';
    echo '<p>' . $lang_apsetting['onepassmail'] . ':<br /><input name="conf_set56" maxlength="3" value="' . get_configuration('subMailPacket') . '" /></p>';

    echo '<br /><button class="btn btn-primary" type="submit" />' . $localization->string('save') . '</button></form><hr>';
    echo '<br /><a href="settings.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a>';
}

if ($action == "setfour") {

    $kbs = get_configuration('photoFileSize') / 1024;

    // forum settings
    echo '<h1>' . $lang_apsetting['forumandgalset'] . '</h1>';

    echo '<form method="post" action="settings.php?action=editfour">';

    echo '<br /><img src="../images/img/forums.gif" alt=""/> Forum settings<br /><br />';
    echo '<p>' . $lang_apsetting['forumon'] . ': <br />' . $lang_apsetting['yes'] . '';
    if (get_configuration('forumAccess') == 1) {
        echo '<input name="conf_set49" type="radio" value="1" checked>';
    } else {
        echo '<input name="conf_set49" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if (get_configuration('forumAccess') == 0) {
        echo '<input name="conf_set49" type="radio" value="0" checked>';
    } else {
        echo '<input name="conf_set49" type="radio" value="0" />';
    } 
    echo $lang_apsetting['no'] . '</p>';
    
        echo '<p>Show language dropdown: <br />' . $lang_apsetting['yes'];
    if (get_configuration('forumChLang') == 1) {
        echo '<input name="conf_set68" type="radio" value="1" checked>';
    } else {
        echo '<input name="conf_set68" type="radio" value="1" />';
    } 
    echo ' &nbsp; &nbsp; ';
    if (get_configuration('forumChLang') == 0) {
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
    echo '<p>' . $lang_apsetting['photopx'] . ':<br /><input name="conf_set39" maxlength="4" value="' . get_configuration('maxPhotoPixels') . '" /></p>';
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

    echo '<br /><button class="btn btn-primary" type="submit" />' . $localization->string('save') . '</button></form><hr>';
    echo '<br /><a href="settings.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a>';
}

if ($action == "setfive") {
    echo '<h1>' . $lang_apsetting['downandinbxsets'] . '</h1>';

    echo '<form method="post" action="settings.php?action=editfive">';

    echo '<p>' . $lang_apsetting['maxinbxmsgs'] . ':<br /><input name="conf_set30" maxlength="5" value="' . get_configuration('pvtLimit') . '" /></p>';
    echo '<br /><button class="btn btn-primary" type="submit" />' . $localization->string('save') . '</button></form><hr>';
    echo '<br /><a href="settings.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a>';
}

if ($action == "setseven") {
    echo '<h1>' . $lang_apsetting['pagessets'] . '</h1>';

    echo '<form method="post" action="settings.php?action=editseven">';

    echo '<div class="form-group">';
        echo '<label for="custom-pages">' . $localization->string('customPageUrl') . '</label>';
        echo '<input class="form-control" name="conf_set28" id="custom-pages" value="' . get_configuration('customPages') . '" />';
    echo '</div>';

    echo '<div class="form-group">';
        echo '<label for="referals">' . $lang_apsetting['maxrefererdata'] . '</label>';
        echo '<input class="form-control" name="conf_set51" id="referals" maxlength="3" value="' . get_configuration('refererLog') . '" />';
    echo '</div>';

    echo '<p>' . $lang_apsetting['showrefpage'] . ': </p>';
    echo '<div class="form-group form-check form-check-inline">';

       if (get_configuration('showRefPage') == 1) {
            echo '<input class="form-check-input" id="referal-yes" name="conf_set70" type="radio" value="1" checked>';
        } else {
            echo '<input class="form-check-input" id="referal-yes" name="conf_set70" type="radio" value="1" />';
        } 
        echo '<label class="form-check-label" for="referal-yes">' . $lang_apsetting['yes'] . '</label>';

    echo '</div>';

    echo '<div class="form-check form-check-inline">';
        if (get_configuration('showRefPage') == 0) {
            echo '<input class="form-check-input" id="referal-no" name="conf_set70" type="radio" value="0" checked>';
        } else {
            echo '<input class="form-check-input" id="referal-no" name="conf_set70" type="radio" value="0" />';
        } 
        echo '<label class="form-check-label" for="referal-no">' . $lang_apsetting['no'] . '</label>';
    echo '</div>';

    echo '<p>Facebook comments on pages:</p>'; // update lang
    echo '<div class="form-group form-check form-check-inline">';

        if (get_configuration('pgFbComm') == 1) {
            echo '<input class="form-check-input" id="referal-yes" name="conf_set6" type="radio" value="1" checked>';
        } else {
            echo '<input class="form-check-input" id="referal-yes" name="conf_set6" type="radio" value="1" />';
        } 
        echo '<label class="form-check-label" for="referal-yes">' . $lang_apsetting['yes'] . '</label>';
    echo '</div>';

    echo '<div class="form-check form-check-inline">';
        if (get_configuration('pgFbComm') == 0) {
            echo '<input class="form-check-input" id="referal-no" name="conf_set6" type="radio" value="0" checked>';
        } else {
            echo '<input class="form-check-input" id="referal-no" name="conf_set6" type="radio" value="0" />';
        } 
        echo '<label class="form-check-label" for="referal-no">' . $lang_apsetting['no'] . '</label>';
    echo '</div>';

    echo '<div class="col-sm-10">';
    echo '<button class="btn btn-primary" type="submit" />' . $localization->string('save') . '</button></div>
    </form>';
    echo '<br /><a href="settings.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a>';
}

if ($action == "seteight") {
    echo '<h1>' . $lang_apsetting['other'] . '</h1>';

    echo '<form method="post" action="settings.php?action=editeight">';

    echo '<p>' . $lang_apsetting['maxlogfile'] . ':<br /><input name="conf_set58" maxlength="3" value="' . get_configuration('maxLogData') . '" /></p>';
    echo '<p>' . $lang_apsetting['maxbantime'] . ':<br /><input name="conf_set76" maxlength="3" value="' . round(get_configuration('maxBanTime') / 1440) . '" /></p>';

    echo '<br /><button class="btn btn-primary" type="submit" />' . $localization->string('save') . '</button></form><hr>';
    echo '<br /><a href="settings.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a>';
}

// database settings
if ($action == "setnine") {
    echo '<h1>Database settings</h1>';

    echo '<form method="post" action="settings.php?action=editnine">';

    echo '<p>Database host:<br /><input name="conf_set77" maxlength="40" value="' . get_configuration('dbhost') . '" /></p>';
    echo '<p>' . $lang_apsetting['username'] . ':<br /><input name="conf_set78" maxlength="40" value="' . get_configuration('dbuser') . '" /></p>';
    echo '<p>' . $lang_apsetting['password'] . ':<br /><input name="conf_set79" maxlength="40" value="' . get_configuration('dbpass') . '" /></p>';
    echo '<p>' . $lang_apsetting['dbname'] . ':<br /><input name="conf_set80" maxlength="40" value="' . get_configuration('dbname') . '" /></p>';
    echo '<p>Crossdomain table prefix:<br /><input name="conf_set71" maxlength="12" value="' . get_configuration('tablePrefix') . '" /></p>';

    echo '<br /><button class="btn btn-primary" type="submit" />' . $localization->string('save') . '</button></form><hr>';
    echo '<br /><a href="settings.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a>';
} 

echo '<p><a href="index.php" class="btn btn-outline-primary sitelink">' . $localization->string('admpanel') . '</a><br />';
echo '<a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>