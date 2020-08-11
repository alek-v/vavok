<?php 
/*
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   11.08.2020. 14:32:29
*/

require_once"../include/startup.php";

if (!$users->is_reg() || !$users->is_administrator(101)) {
    $vavok->redirect_to("../pages/error.php?error=auth");
}

$action = isset($_GET['action']) ? $vavok->check($_GET['action']) : '';

// Мain settings update
if ($action == "editone") {

	// Check fields
    if (!isset($_POST['conf_set1']) || !isset($_POST['conf_set2']) || !isset($_POST['conf_set3']) || !isset($_POST['conf_set8']) || !isset($_POST['conf_set9']) || !isset($_POST['conf_set10']) || !isset($_POST['conf_set11']) || !isset($_POST['conf_set14']) || !isset($_POST['conf_set21']) || !isset($_POST['conf_set29']) || !isset($_POST['conf_set61']) || !isset($_POST['conf_set62']) || !isset($_POST['conf_set63'])) {
        $vavok->redirect_to("settings.php?action=setone&isset=mp_nosset");
    }

    $fields = array('keypass','webtheme','quarantine','adminNick','adminEmail','timeZone','title','homeUrl','transferProtocol','floodTime','siteDefaultLang','openReg','regConfirm','siteOff');

    $values = array(
        $vavok->check($_POST['conf_set1']),
        $vavok->check($_POST['conf_set2']),
        $vavok->check($_POST['conf_set3']),
        $vavok->check($_POST['conf_set8']),
        htmlspecialchars(stripslashes(trim($_POST['conf_set9']))),
        $vavok->check($_POST['conf_set10']),
        $vavok->check($_POST['conf_set11']),
        $vavok->check($_POST['conf_set14']),
        $vavok->check($_POST['conf_set21']),
        (int)$_POST['conf_set29'],
        $vavok->check($_POST['conf_set47']),
        (int)$_POST['conf_set61'],
        (int)$_POST['conf_set62'],
        (int)$_POST['conf_set63']
    );

    // Update settings
    $db->update(DB_PREFIX . 'settings', $fields, $values);

    // update .htaccess file
    // dont force https
$htaccess_tp_nos = '# force https protocol
#RewriteCond %{HTTPS} !=on
#RewriteRule ^.*$ https://%{SERVER_NAME}%{REQUEST_URI} [R,L]';

        // force https
$htaccess_tp_s = '# force https protocol
RewriteCond %{HTTPS} !=on
RewriteRule ^.*$ https://%{SERVER_NAME}%{REQUEST_URI} [R,L]';

    if ($vavok->get_configuration('transferProtocol') == 'HTTPS' && ($_POST['conf_set21'] == 'auto' || $_POST['conf_set21'] == 'HTTP')) {

        // Disable forcing HTTPS in .htaccess

        $file = file_get_contents('../.htaccess');

        $start = strpos($file, '# force https protocol');
        $strlen = mb_strlen($htaccess_tp_s); // find string length

        $file = substr_replace($file, $htaccess_tp_nos, $start, $strlen);

        file_put_contents('../.htaccess', $file);

    } elseif ($_POST['conf_set21'] == 'HTTPS' && ($vavok->get_configuration('transferProtocol') == 'HTTP' || $vavok->get_configuration('transferProtocol') == 'auto')) {
        
        // Enable forcing HTTPS in .htaccess

        $file = file_get_contents('../.htaccess');

        $start = strpos($file, '# force https protocol');
        $strlen = mb_strlen($htaccess_tp_nos); // find string length


        $file = substr_replace($file, $htaccess_tp_s, $start, $strlen);

        file_put_contents('../.htaccess', $file);
    }

    $vavok->redirect_to("settings.php?isset=mp_yesset");

}

if ($action == "edittwo") {

	if (!isset($_POST['conf_set4']) || !isset($_POST['conf_set5']) || !isset($_POST['conf_set7']) || !isset($_POST['conf_set32']) || !isset($_POST['conf_set74'])) {
        $vavok->redirect_to("settings.php?action=settwo&isset=mp_nosset");
    }

    $fields = array(
    	'showtime',
    	'pageGenTime',
    	'showOnline',
    	'cookieConsent',
    	'showCounter'
    );

    $values = array(
        (int)$_POST['conf_set4'],
        (int)$_POST['conf_set5'],
        (int)$_POST['conf_set7'],
        (int)$_POST['conf_set32'], // cookie consent
        (int)$_POST['conf_set74']
    );

    // Update data
	$db->update(DB_PREFIX . 'settings', $fields, $values);

	$vavok->redirect_to("settings.php?isset=mp_yesset");
	
} 

if ($action == "editthree") {

    if (!isset($_POST['conf_set20']) || !isset($_POST['conf_set22']) || !isset($_POST['conf_set24']) || !isset($_POST['conf_set56'])) {
        $vavok->redirect_to("settings.php?action=setthree&isset=mp_nosset");
    }

    $fields = array(
        'bookGuestAdd',
        'maxPostChat',
        'maxPostNews',
        'subMailPacket'
    );

    $values = array(
        (int)$_POST['conf_set20'],
        (int)$_POST['conf_set22'],
        (int)$_POST['conf_set24'],
        (int)$_POST['conf_set56']
    );

    $db->update(DB_PREFIX . 'settings', $fields, $values);

    $vavok->redirect_to("settings.php?isset=mp_yesset");

}

if ($action == "editfour") {

    if (!isset($_POST['conf_set38']) || !isset($_POST['conf_set39']) || !isset($_POST['conf_set49'])) {
        $vavok->redirect_to("settings.php?action=setfour&isset=mp_nosset");
    }

    // Update main config
    $fields = array(
        'photoFileSize',
        'maxPhotoPixels',
        'forumAccess',
        'forumChLang'
    );

    $values = array(
        (int)$_POST['conf_set38'] * 1024,
        (int)$_POST['conf_set39'],
        (int)$_POST['conf_set49'],
        (int)$_POST['conf_set68']
    );

    // Update
    $db->update(DB_PREFIX . 'settings', $fields, $values);

    // update gallery settings
    $gallery_file = file(BASEDIR . "used/dataconfig/gallery.dat");
    if (empty($gallery_file)) $gallery_file = '||||||||||';
    $gallery_data = explode("|", $gallery_file[0]);

    $gallery_data[0] = (int)$_POST['gallery_set0']; // users can upload
    $gallery_data[8] = (int)$_POST['gallery_set8']; // photos per page
    $gallery_data[5] = (int)$_POST['screen_width'];
    $gallery_data[6] = (int)$_POST['screen_height'];
    $gallery_data[7] = (int)$_POST['media_buttons'];

    $gallery_text = '';
    for ($u = 0; $u < 10; $u++) {
        $gallery_text .= $gallery_data[$u] . '|';
    }

    if (isset($gallery_text)) {
        file_put_contents(BASEDIR . "used/dataconfig/gallery.dat", $gallery_text);
    }

    $vavok->redirect_to("settings.php?isset=mp_yesset");

}

if ($action == "editfive") {
	if (!isset($_POST['conf_set30'])) {
        $vavok->redirect_to("settings.php?action=setfive&isset=mp_nosset");
    }

	$udata[30] = (int)$_POST['conf_set30'];

	$db->update(DB_PREFIX . 'settings', 'pvtLimit', (int)$_POST['conf_set30']);

	$vavok->redirect_to("settings.php?isset=mp_yesset");
}

if ($action == "editseven") {

    if (!isset($_POST['conf_set6']) || !isset($_POST['conf_set51']) || !isset($_POST['conf_set70'])) {
        $vavok->redirect_to("settings.php?action=setseven&isset=mp_nosset");
    }

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

    $fields = array(
        'pgFbComm',
        'customPages',
        'refererLog',
        'showRefPage'
    );

    $values = array(
        $_POST['conf_set6'],
        $_POST['conf_set28'],
        (int)$_POST['conf_set51'],
        $_POST['conf_set70']
    );

    $db->update(DB_PREFIX . 'settings', $fields, $values);

    $vavok->redirect_to("settings.php?isset=mp_yesset");

}

if ($action == "editeight") {

    if (!isset($_POST['conf_set58']) || !isset($_POST['conf_set76'])) {
        $vavok->redirect_to("settings.php?action=seteight&isset=mp_nosset");
    }

    $fields = array(
        'maxLogData',
        'maxBanTime'
    );

    $values = array(
        (int)$_POST['conf_set58'],
        round($_POST['conf_set76'] * 1440)
    );

    // Update data
    $db->update(DB_PREFIX . 'settings', $fields, $values);

    $vavok->redirect_to("settings.php?isset=mp_yesset");

}

$current_page->page_title = "Settings";

require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

if (empty($action)) {
    echo '<a href="settings.php?action=setone" class="btn btn-outline-primary sitelink">' . $localization->string('mainset') . '</a>';
    echo '<a href="settings.php?action=settwo" class="btn btn-outline-primary sitelink">' . $localization->string('shwinfo') . '</a>';
    echo '<a href="settings.php?action=setthree" class="btn btn-outline-primary sitelink">' . $localization->string('bookchatnews') . '</a>';
    echo '<a href="settings.php?action=setfour" class="btn btn-outline-primary sitelink">' . $localization->string('forumgallery') . '</a>';
    // echo '<a href="settings.php?action=setfive" class="btn btn-outline-primary sitelink">' . $localization->string('inbox') . '</a>';
    // echo '<a href="settings.php?action=setsix" class="btn btn-outline-primary sitelink">' . $localization->string('advert') . '</a><br />';
    echo '<a href="settings.php?action=setseven" class="btn btn-outline-primary sitelink">' . $localization->string('pagemanage') . '</a>';
    echo '<a href="settings.php?action=seteight" class="btn btn-outline-primary sitelink">' . $localization->string('other') . '</a>';
} 

// main settings
if ($action == "setone") {
    echo '<h1>' . $localization->string('mainset') . '</h1>';

    $form = new PageGen('forms/form.tpl');
    $form->set('form_method', 'post');
    $form->set('form_action', 'settings.php?action=editone');

    $options = '<option value="' . $vavok->get_configuration('siteDefaultLang') . '">' . $vavok->get_configuration('siteDefaultLang') . '</option>';
    $dir = opendir(BASEDIR . "include/lang");
    while ($file = readdir($dir)) {
        if (!preg_match('/[^0-9A-Za-z.\_\-]/', $file) && $file != $vavok->get_configuration('siteDefaultLang') && $file != '..' && $file != '.' && $file != "index.php" && $file != ".htaccess" && strlen($file) > 2) {
            $options .= '<option value="' . $file . '">' . $file . '</option>';
        } 
    }

    $select_lang = new PageGen('forms/select.tpl');
    $select_lang->set('label_for', 'conf_set47');
    $select_lang->set('label_value', $localization->string('language'));
    $select_lang->set('select_id', 'conf_set47');
    $select_lang->set('select_name', 'conf_set47');
    $select_lang->set('options', $options);

    $config_themes_show = str_replace("web_", "", $vavok->get_configuration('webtheme'));
    $config_themes_show = ucfirst($config_themes_show);

    $options = '<option value="' . $vavok->get_configuration('webtheme') . '">' . $config_themes_show . '</option>';
    $dir = opendir ("../themes");
    while ($file = readdir ($dir)) {
        if (!preg_match('/[^0-9A-Za-z.\_\-]/', $file) && $file != $vavok->get_configuration('webtheme') && $file != '..' && $file != '.' && $file != "index.php" && $file != ".htaccess" && $file != "templates") {
            $nfile = str_replace("web_", "", $file);
            $nfile = ucfirst($nfile);
            $options .= '<option value="' . $file . '">' . $nfile . '</option>';
        }
    }

    $select_theme = new PageGen('forms/select.tpl');
    $select_theme->set('label_for', 'conf_set2');
    $select_theme->set('label_value', $localization->string('webskin'));
    $select_theme->set('select_id', 'conf_set2');
    $select_theme->set('select_name', 'conf_set2');
    $select_theme->set('options', $options);

    // this will be admin username or system username
    $input8 = new PageGen('forms/input.tpl');
    $input8->set('label_for', 'conf_set8');
    $input8->set('label_value', $localization->string('adminusername'));
    $input8->set('input_id', 'conf_set8');
    $input8->set('input_name', 'conf_set8');
    $input8->set('input_value', $vavok->get_configuration('adminNick'));
    $input8->set('input_maxlength', 20);

    $input9 = new PageGen('forms/input.tpl');
    $input9->set('label_for', 'conf_set9');
    $input9->set('label_value', $localization->string('adminemail'));
    $input9->set('input_id', 'conf_set9');
    $input9->set('input_name', 'conf_set9');
    $input9->set('input_value', $vavok->get_configuration('adminEmail'));
    $input9->set('input_maxlength', 50);

    $input10 = new PageGen('forms/input.tpl');
    $input10->set('label_for', 'conf_set10');
    $input10->set('label_value', $localization->string('timezone'));
    $input10->set('input_id', 'conf_set10');
    $input10->set('input_name', 'conf_set10');
    $input10->set('input_value', $vavok->get_configuration('timeZone'));
    $input10->set('input_maxlength', 3);

    $input11 = new PageGen('forms/input.tpl');
    $input11->set('label_for', 'conf_set11');
    $input11->set('label_value', $localization->string('pagetitle'));
    $input11->set('input_id', 'conf_set11');
    $input11->set('input_name', 'conf_set11');
    $input11->set('input_value', $vavok->get_configuration('title'));
    $input11->set('input_maxlength', 100);

    $input14 = new PageGen('forms/input.tpl');
    $input14->set('label_for', 'conf_set14');
    $input14->set('label_value', $localization->string('siteurl'));
    $input14->set('input_id', 'conf_set14');
    $input14->set('input_name', 'conf_set14');
    $input14->set('input_value', $vavok->get_configuration('homeUrl'));
    $input14->set('input_maxlength', 50);

    $input29 = new PageGen('forms/input.tpl');
    $input29->set('label_for', 'conf_set29');
    $input29->set('label_value', $localization->string('floodtime'));
    $input29->set('input_id', 'conf_set29');
    $input29->set('input_name', 'conf_set29');
    $input29->set('input_value', $vavok->get_configuration('floodTime'));
    $input29->set('input_maxlength', 3);

    $input1 = new PageGen('forms/input.tpl');
    $input1->set('label_for', 'conf_set1');
    $input1->set('label_value', $localization->string('passkey'));
    $input1->set('input_id', 'conf_set1');
    $input1->set('input_name', 'conf_set1');
    $input1->set('input_value', $vavok->get_configuration('keypass'));
    $input1->set('input_maxlength', 25);

    // quarantine time
    $quarantine = array(0 => "" . $localization->string('disabled') . "", 21600 => "6 " . $localization->string('hours') . "", 43200 => "12 " . $localization->string('hours') . "", 86400 => "24 " . $localization->string('hours') . "", 129600 => "36 " . $localization->string('hours') . "", 172800 => "48 " . $localization->string('hours') . "");

    $options = '<option value="' . $vavok->get_configuration('quarantine') . '">' . $quarantine[$vavok->get_configuration('quarantine')] . '</option>';
    foreach($quarantine as $k => $v) {
        if ($k != $vavok->get_configuration('quarantine')) {
            $options .= '<option value="' . $k . '">' . $v . '</option>';
        }
    }

    $select_set3 = new PageGen('forms/select.tpl');
    $select_set3->set('label_for', 'conf_set3');
    $select_set3->set('label_value', $localization->string('quarantinetime'));
    $select_set3->set('select_id', 'conf_set3');
    $select_set3->set('select_name', 'conf_set3');
    $select_set3->set('options', $options);

    // transfer protocol
    $tProtocol = array('HTTPS' => 'HTTPS', 'HTTP' => 'HTTP', 'auto' => 'auto');

    $transfer_protocol = $vavok->get_configuration('transferProtocol');
    if (empty($vavok->get_configuration('transferProtocol'))) $transfer_protocol = 'auto';
    
    $options = '<option value="' . $transfer_protocol . '">' . $tProtocol[$transfer_protocol] . '</option>';

    foreach($tProtocol as $k => $v) {
        if ($k != $transfer_protocol) {
            $options .= '<option value="' . $k . '">' . $v . '</option>';
        }
    }

    $select_set21 = new PageGen('forms/select.tpl');
    $select_set21->set('label_for', 'conf_set21');
    $select_set21->set('label_value', 'Transfer protocol');
    $select_set21->set('select_id', 'conf_set21');
    $select_set21->set('select_name', 'conf_set21');
    $select_set21->set('options', $options);

    // Registration opened or closed
    $input_radio61_yes = new PageGen('forms/radio_inline.tpl');
    $input_radio61_yes->set('label_for', 'conf_set61');
    $input_radio61_yes->set('label_value', $localization->string('yes'));
    $input_radio61_yes->set('input_id', 'conf_set61');
    $input_radio61_yes->set('input_name', 'conf_set61');
    $input_radio61_yes->set('input_value', 1);
    if ($vavok->get_configuration('openReg') == 1) {
        $input_radio61_yes->set('input_status', 'checked');
    }

    $input_radio61_no = new PageGen('forms/radio_inline.tpl');
    $input_radio61_no->set('label_for', 'conf_set61');
    $input_radio61_no->set('label_value', $localization->string('no'));
    $input_radio61_no->set('input_id', 'conf_set61');
    $input_radio61_no->set('input_name', 'conf_set61');
    $input_radio61_no->set('input_value', 0);
    if ($vavok->get_configuration('openReg') == 0) {
        $input_radio61_no->set('input_status', 'checked');
    }

    $radio_group_one = new PageGen('forms/radio_group.tpl');
    $radio_group_one->set('description', $localization->string('openreg'));
    $radio_group_one->set('radio_group', $radio_group_one->merge(array($input_radio61_yes, $input_radio61_no)));

    // Does user need to confirm registration
    $input_radio62_yes = new PageGen('forms/radio_inline.tpl');
    $input_radio62_yes->set('label_for', 'conf_set62');
    $input_radio62_yes->set('label_value', $localization->string('yes'));
    $input_radio62_yes->set('input_id', 'conf_set62');
    $input_radio62_yes->set('input_name', 'conf_set62');
    $input_radio62_yes->set('input_value', 1);
    if ($vavok->get_configuration('regConfirm') == 1) {
        $input_radio62_yes->set('input_status', 'checked');
    }

    $input_radio62_no = new PageGen('forms/radio_inline.tpl');
    $input_radio62_no->set('label_for', 'conf_set62');
    $input_radio62_no->set('label_value', $localization->string('no'));
    $input_radio62_no->set('input_id', 'conf_set62');
    $input_radio62_no->set('input_name', 'conf_set62');
    $input_radio62_no->set('input_value', 0);
    if ($vavok->get_configuration('regConfirm') == 0) {
        $input_radio62_no->set('input_status', 'checked');
    }

    $radio_group_two = new PageGen('forms/radio_group.tpl');
    $radio_group_two->set('description', $localization->string('confregs'));
    $radio_group_two->set('radio_group', $radio_group_two->merge(array($input_radio62_yes, $input_radio62_no)));

    // Maintenance mode
    $input_radio63_yes = new PageGen('forms/radio_inline.tpl');
    $input_radio63_yes->set('label_for', 'conf_set63');
    $input_radio63_yes->set('label_value', $localization->string('yes'));
    $input_radio63_yes->set('input_id', 'conf_set63');
    $input_radio63_yes->set('input_name', 'conf_set63');
    $input_radio63_yes->set('input_value', 1);
    if ($vavok->get_configuration('siteOff') == 1) {
        $input_radio63_yes->set('input_status', 'checked');
    }

    $input_radio63_no = new PageGen('forms/radio_inline.tpl');
    $input_radio63_no->set('label_for', 'conf_set63');
    $input_radio63_no->set('label_value', $localization->string('no'));
    $input_radio63_no->set('input_id', 'conf_set63');
    $input_radio63_no->set('input_name', 'conf_set63');
    $input_radio63_no->set('input_value', 0);
    if ($vavok->get_configuration('siteOff') == 0) {
        $input_radio63_no->set('input_status', 'checked');
    }

    $radio_group_three = new PageGen('forms/radio_group.tpl');
    $radio_group_three->set('description', 'Maintenance');
    $radio_group_three->set('radio_group', $radio_group_three->merge(array($input_radio63_yes, $input_radio63_no)));

    $form->set('fields', $form->merge(array($select_lang, $select_theme, $input8, $input9, $input10, $input11, $input14, $input29, $input1, $select_set3, $select_set21, $radio_group_one, $radio_group_two, $radio_group_three)));
    echo $form->output();

    echo '<p><a href="settings.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a></p>';
}

if ($action == "settwo") {
    echo '<h1>' . $localization->string('shwinfo') . '</h1>';

    $form = new PageGen('forms/form.tpl');
    $form->set('form_method', 'post');
    $form->set('form_action', 'settings.php?action=edittwo');

    /**
     * Show clock
     */
    $_4_yes = new PageGen('forms/radio_inline.tpl');
    $_4_yes->set('label_for', 'conf_set4');
    $_4_yes->set('label_value', $localization->string('yes'));
    $_4_yes->set('input_id', 'conf_set4');
    $_4_yes->set('input_name', 'conf_set4');
    $_4_yes->set('input_value', 1);
    if ($vavok->get_configuration('showtime') == 1) {
        $_4_yes->set('input_status', 'checked');
    }

    $_4_no = new PageGen('forms/radio_inline.tpl');
    $_4_no->set('label_for', 'conf_set4');
    $_4_no->set('label_value',  $localization->string('no'));
    $_4_no->set('input_id', 'conf_set4');
    $_4_no->set('input_name', 'conf_set4');
    $_4_no->set('input_value', 0);
    if ($vavok->get_configuration('showtime') == 0) {
        $_4_no->set('input_status', 'checked');
    }

    $show_clock = new PageGen('forms/radio_group.tpl');
    $show_clock->set('description', $localization->string('showclock'));
    $show_clock->set('radio_group', $show_clock->merge(array($_4_yes, $_4_no)));

    /**
     * Show page generatioin time
     */
    $_5_yes = new PageGen('forms/radio_inline.tpl');
    $_5_yes->set('label_for', 'conf_set5');
    $_5_yes->set('label_value', $localization->string('yes'));
    $_5_yes->set('input_id', 'conf_set5');
    $_5_yes->set('input_name', 'conf_set5');
    $_5_yes->set('input_value', 1);
    if ($vavok->get_configuration('pageGenTime') == 1) {
        $_5_yes->set('input_status', 'checked');
    }

    $_5_no = new PageGen('forms/radio_inline.tpl');
    $_5_no->set('label_for', 'conf_set5');
    $_5_no->set('label_value', $localization->string('no'));
    $_5_no->set('input_id', 'conf_set5');
    $_5_no->set('input_name', 'conf_set5');
    $_5_no->set('input_value', 0);
    if ($vavok->get_configuration('pageGenTime') == 0) {
        $_5_no->set('input_status', 'checked');
    }

    $page_gen = new PageGen('forms/radio_group.tpl');
    $page_gen->set('description', $localization->string('pagegen'));
    $page_gen->set('radio_group', $page_gen->merge(array($_5_yes, $_5_no)));

    /**
     * Show online
     */
    $_7_yes = new PageGen('forms/radio_inline.tpl');
    $_7_yes->set('label_for', 'conf_set7');
    $_7_yes->set('label_value', $localization->string('yes'));
    $_7_yes->set('input_id', 'conf_set7');
    $_7_yes->set('input_name', 'conf_set7');
    $_7_yes->set('input_value', 1);
    if ($vavok->get_configuration('showOnline') == 1) {
        $_7_yes->set('input_status', 'checked');
    }

    $_7_no = new PageGen('forms/radio_inline.tpl');
    $_7_no->set('label_for', 'conf_set7');
    $_7_no->set('label_value',  $localization->string('no'));
    $_7_no->set('input_id', 'conf_set7');
    $_7_no->set('input_name', 'conf_set7');
    $_7_no->set('input_value', 0);
    if ($vavok->get_configuration('showOnline') == 0) {
        $_7_no->set('input_status', 'checked');
    }

    $show_online = new PageGen('forms/radio_group.tpl');
    $show_online->set('description', $localization->string('showonline'));
    $show_online->set('radio_group', $show_online->merge(array($_7_yes, $_7_no)));

    /**
     * Show cookie consent
     */
    $_32_yes = new PageGen('forms/radio_inline.tpl');
    $_32_yes->set('label_for', 'conf_set32');
    $_32_yes->set('label_value', $localization->string('yes'));
    $_32_yes->set('input_id', 'conf_set32');
    $_32_yes->set('input_name', 'conf_set32');
    $_32_yes->set('input_value', 1);
    if ($vavok->get_configuration('cookieConsent') == 1) {
        $_32_yes->set('input_status', 'checked');
    }

    $_32_no = new PageGen('forms/radio_inline.tpl');
    $_32_no->set('label_for', 'conf_set32');
    $_32_no->set('label_value', $localization->string('no'));
    $_32_no->set('input_id', 'conf_set32');
    $_32_no->set('input_name', 'conf_set32');
    $_32_no->set('input_value', 0);
    if ($vavok->get_configuration('cookieConsent') == 0) {
        $_32_no->set('input_status', 'checked');
    }

    $cookie_consent = new PageGen('forms/radio_group.tpl');
    $cookie_consent->set('description', 'Cookie consent');
    $cookie_consent->set('radio_group', $cookie_consent->merge(array($_32_yes, $_32_no)));

    /**
     * Show counter
     */
    $incounters = array(6 => "" . $localization->string('dontshow') . "", 1 => "" . $localization->string('vsttotalvst') . "", 2 => "" . $localization->string('clicktotalclick') . "", 3 => "" . $localization->string('clickvisits') . "", 4 => "" . $localization->string('totclicktotvst'));

    $options = '<option value="' . $vavok->get_configuration('showCounter') . '">' . $incounters[$vavok->get_configuration('showCounter')] . '</option>';
    foreach($incounters as $k => $v) {
        if ($k != $vavok->get_configuration('showCounter')) {
            $options .= '<option value="' . $k . '">' . $v . '</option>';
        }
    }

    $show_counter = new PageGen('forms/select.tpl');
    $show_counter->set('label_for', 'conf_set74');
    $show_counter->set('label_value', $localization->string('countlook'));
    $show_counter->set('select_id', 'conf_set74');
    $show_counter->set('select_name', 'conf_set74');
    $show_counter->set('options', $options);

    $form->set('fields', $form->merge(array($show_clock, $page_gen, $show_online, $cookie_consent, $show_counter)));
    echo $form->output();

    echo '<p><a href="settings.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a></p>';
}

if ($action == "setthree") {
    echo '<h1>' . $localization->string('gbnewschatset') . '</h1>';

    $form = new PageGen('forms/form.tpl');
    $form->set('form_method', 'post');
    $form->set('form_action', 'settings.php?action=editthree');

    /**
     * Allow guests to write in guestbook
     */
    $_20_yes = new PageGen('forms/radio_inline.tpl');
    $_20_yes->set('label_for', 'conf_set20');
    $_20_yes->set('label_value', $localization->string('yes'));
    $_20_yes->set('input_id', 'conf_set20');
    $_20_yes->set('input_name', 'conf_set20');
    $_20_yes->set('input_value', 1);
    if ($vavok->get_configuration('bookGuestAdd') == 1) {
        $_20_yes->set('input_status', 'checked');
    }

    $_20_no = new PageGen('forms/radio_inline.tpl');
    $_20_no->set('label_for', 'conf_set20');
    $_20_no->set('label_value', $localization->string('no'));
    $_20_no->set('input_id', 'conf_set20');
    $_20_no->set('input_name', 'conf_set20');
    $_20_no->set('input_value', 0);
    if ($vavok->get_configuration('bookGuestAdd') == 0) {
        $_20_no->set('input_status', 'checked');
    }

    $gb_write = new PageGen('forms/radio_group.tpl');
    $gb_write->set('description', $localization->string('allowguestingb'));
    $gb_write->set('radio_group', $gb_write->merge(array($_20_yes, $_20_no)));

    /**
     * Max chat posts
     */
    $input22 = new PageGen('forms/input.tpl');
    $input22->set('label_for', 'conf_set22');
    $input22->set('label_value', $localization->string('maxinchat'));
    $input22->set('input_id', 'conf_set22');
    $input22->set('input_name', 'conf_set22');
    $input22->set('input_value', $vavok->get_configuration('maxPostChat'));
    $input22->set('input_maxlength', 4);

    /**
     * Max news posts
     */
    $input24 = new PageGen('forms/input.tpl');
    $input24->set('label_for', 'conf_set24');
    $input24->set('label_value', $localization->string('maxnews'));
    $input24->set('input_id', 'conf_set24');
    $input24->set('input_name', 'conf_set24');
    $input24->set('input_value', $vavok->get_configuration('maxPostNews'));
    $input24->set('input_maxlength', 5);

    /**
     * Mails in one package
     */
    $input56 = new PageGen('forms/input.tpl');
    $input56->set('label_for', 'conf_set56');
    $input56->set('label_value', $localization->string('onepassmail'));
    $input56->set('input_id', 'conf_set56');
    $input56->set('input_name', 'conf_set56');
    $input56->set('input_value', $vavok->get_configuration('subMailPacket'));
    $input56->set('input_maxlength', 3);

    $form->set('fields', $form->merge(array($gb_write, $input22, $input24, $input56)));
    echo $form->output();

    echo '<p><a href="settings.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a></p>';
}

if ($action == "setfour") {

    $kbs = $vavok->get_configuration('photoFileSize') / 1024;

    // forum settings
    echo '<h1>' . $localization->string('forumandgalset') . '</h1>';

    $form = new PageGen('forms/form.tpl');
    $form->set('form_method', 'post');
    $form->set('form_action', 'settings.php?action=editfour');

    /**
     * Allow access to forum
     */
    $_49_yes = new PageGen('forms/radio_inline.tpl');
    $_49_yes->set('label_for', 'conf_set49');
    $_49_yes->set('label_value', $localization->string('yes'));
    $_49_yes->set('input_id', 'conf_set49');
    $_49_yes->set('input_name', 'conf_set49');
    $_49_yes->set('input_value', 1);
    if ($vavok->get_configuration('forumAccess') == 1) {
        $_49_yes->set('input_status', 'checked');
    }

    $_49_no = new PageGen('forms/radio_inline.tpl');
    $_49_no->set('label_for', 'conf_set49');
    $_49_no->set('label_value', $localization->string('no'));
    $_49_no->set('input_id', 'conf_set49');
    $_49_no->set('input_name', 'conf_set49');
    $_49_no->set('input_value', 0);
    if ($vavok->get_configuration('forumAccess') == 0) {
        $_49_no->set('input_status', 'checked');
    }

    $forum_access = new PageGen('forms/radio_group.tpl');
    $forum_access->set('description', $localization->string('forumon'));
    $forum_access->set('radio_group', $forum_access->merge(array($_49_yes, $_49_no)));

    /**
     * Forum language dropdown
     */
    $_68_yes = new PageGen('forms/radio_inline.tpl');
    $_68_yes->set('label_for', 'conf_set68');
    $_68_yes->set('label_value', $localization->string('yes'));
    $_68_yes->set('input_id', 'conf_set68');
    $_68_yes->set('input_name', 'conf_set68');
    $_68_yes->set('input_value', 1);
    if ($vavok->get_configuration('forumChLang') == 1) {
        $_68_yes->set('input_status', 'checked');
    }

    $_68_no = new PageGen('forms/radio_inline.tpl');
    $_68_no->set('label_for', 'conf_set68');
    $_68_no->set('label_value', $localization->string('no'));
    $_68_no->set('input_id', 'conf_set68');
    $_68_no->set('input_name', 'conf_set68');
    $_68_no->set('input_value', 0);
    if ($vavok->get_configuration('forumChLang') == 0) {
        $_68_no->set('input_status', 'checked');
    }

    $forum_dropdown = new PageGen('forms/radio_group.tpl');
    $forum_dropdown->set('description', 'Show language dropdown');
    $forum_dropdown->set('radio_group', $forum_dropdown->merge(array($_68_yes, $_68_no)));

    /**
     * Gallery settings
     */
    $gallery_config = file(BASEDIR . "used/dataconfig/gallery.dat");
    if (!empty($gallery_config)) {
        $gallery_data = explode("|", $gallery_config[0]);
    } else {
        $gallery_data = explode("|", '|||||||||||||');
    }

    /**
     * Gallery photos per page
     */
    $gallery_set8 = new PageGen('forms/input.tpl');
    $gallery_set8->set('label_for', 'gallery_set8');
    $gallery_set8->set('label_value', $localization->string('photosperpg'));
    $gallery_set8->set('input_id', 'gallery_set8');
    $gallery_set8->set('input_name', 'gallery_set8');
    $gallery_set8->set('input_value', $gallery_data[8]);
    $gallery_set8->set('input_maxlength', 2);

    /**
     * Gallery max screen width
     */
    $screen_width = new PageGen('forms/input.tpl');
    $screen_width->set('label_for', 'screen_width');
    $screen_width->set('label_value', 'Maximum width in gallery');
    $screen_width->set('input_id', 'screen_width');
    $screen_width->set('input_name', 'screen_width');
    $screen_width->set('input_value', $gallery_data[5]);
    $screen_width->set('input_maxlength', 5);

    /**
     * Gallery max screen height
     */
    $screen_height = new PageGen('forms/input.tpl');
    $screen_height->set('label_for', 'screen_height');
    $screen_height->set('label_value', 'Maximum height in gallery');
    $screen_height->set('input_id', 'screen_height');
    $screen_height->set('input_name', 'screen_height');
    $screen_height->set('input_value', $gallery_data[6]);
    $screen_height->set('input_maxlength', 5);

    /**
     * Gallery social network buttons
     */
    $media_buttons_yes = new PageGen('forms/radio_inline.tpl');
    $media_buttons_yes->set('label_for', 'media_buttons');
    $media_buttons_yes->set('label_value', $localization->string('yes'));
    $media_buttons_yes->set('input_id', 'media_buttons');
    $media_buttons_yes->set('input_name', 'media_buttons');
    $media_buttons_yes->set('input_value', 1);
    if ($gallery_data[7] == 1) {
        $media_buttons_yes->set('input_status', 'checked');
    }

    $media_buttons_no = new PageGen('forms/radio_inline.tpl');
    $media_buttons_no->set('label_for', 'media_buttons');
    $media_buttons_no->set('label_value', $localization->string('no'));
    $media_buttons_no->set('input_id', 'media_buttons');
    $media_buttons_no->set('input_name', 'media_buttons');
    $media_buttons_no->set('input_value', 0);
    if ($gallery_data[7] == 0) {
        $media_buttons_no->set('input_status', 'checked');
    }

    $sn_buttons = new PageGen('forms/radio_group.tpl');
    $sn_buttons->set('description', 'Social media like buttons in gallery');
    $sn_buttons->set('radio_group', $sn_buttons->merge(array($media_buttons_yes, $media_buttons_no)));

    /**
     * Gallery max upload size
     */
    $conf_set38 = new PageGen('forms/input.tpl');
    $conf_set38->set('label_for', 'conf_set38');
    $conf_set38->set('label_value', $localization->string('photomaxkb'));
    $conf_set38->set('input_id', 'conf_set38');
    $conf_set38->set('input_name', 'conf_set38');
    $conf_set38->set('input_value', (int)$kbs);
    $conf_set38->set('input_maxlength', 8);

    /**
     * Gallery max upload pixel size
     */
    $conf_set39 = new PageGen('forms/input.tpl');
    $conf_set39->set('label_for', 'conf_set39');
    $conf_set39->set('label_value', $localization->string('photopx'));
    $conf_set39->set('input_id', 'conf_set39');
    $conf_set39->set('input_name', 'conf_set39');
    $conf_set39->set('input_value', (int)$vavok->get_configuration('maxPhotoPixels'));
    $conf_set39->set('input_maxlength', 4);

    /**
     * Gallery uploads
     */
    $gallery_set0_yes = new PageGen('forms/radio_inline.tpl');
    $gallery_set0_yes->set('label_for', 'gallery_set0');
    $gallery_set0_yes->set('label_value', $localization->string('yes'));
    $gallery_set0_yes->set('input_id', 'gallery_set0');
    $gallery_set0_yes->set('input_name', 'gallery_set0');
    $gallery_set0_yes->set('input_value', 1);
    if ($gallery_data[0] == 1) {
        $gallery_set0_yes->set('input_status', 'checked');
    }

    $gallery_set0_no = new PageGen('forms/radio_inline.tpl');
    $gallery_set0_no->set('label_for', 'gallery_set0');
    $gallery_set0_no->set('label_value', $localization->string('no'));
    $gallery_set0_no->set('input_id', 'gallery_set0');
    $gallery_set0_no->set('input_name', 'gallery_set0');
    $gallery_set0_no->set('input_value', 0);
    if ($gallery_data[0] == 0) {
        $gallery_set0_no->set('input_status', 'checked');
    }

    $gallery_uploads = new PageGen('forms/radio_group.tpl');
    $gallery_uploads->set('description', 'Users can upload');
    $gallery_uploads->set('radio_group', $gallery_uploads->merge(array($gallery_set0_yes, $gallery_set0_no)));

    $form->set('fields', $form->merge(array($forum_access, $forum_dropdown, $gallery_set8, $screen_width, $screen_height, $sn_buttons, $conf_set38, $conf_set39, $gallery_uploads)));
    echo $form->output();

    echo '<p><a href="settings.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a></p>';
}

if ($action == "setseven") {
    echo '<h1>' . $localization->string('pagessets') . '</h1>';

    $form = new PageGen('forms/form.tpl');
    $form->set('form_method', 'post');
    $form->set('form_action', 'settings.php?action=editseven');

    /**
     * Custom pages
     */
    $conf_set28 = new PageGen('forms/input.tpl');
    $conf_set28->set('label_for', 'custom-pages');
    $conf_set28->set('label_value', $localization->string('customPageUrl'));
    $conf_set28->set('input_id', 'custom-pages');
    $conf_set28->set('input_name', 'conf_set28');
    $conf_set28->set('input_value', $vavok->get_configuration('customPages'));

    /**
     * Max referer data
     */
    $conf_set51 = new PageGen('forms/input.tpl');
    $conf_set51->set('label_for', 'referals');
    $conf_set51->set('label_value', $localization->string('maxrefererdata'));
    $conf_set51->set('input_id', 'referals');
    $conf_set51->set('input_name', 'conf_set51');
    $conf_set51->set('input_value', $vavok->get_configuration('refererLog'));
    $conf_set51->set('input_maxlength', 3);

    /**
     * Show referal page
     */
    $conf_set70yes = new PageGen('forms/radio_inline.tpl');
    $conf_set70yes->set('label_for', 'referal-yes');
    $conf_set70yes->set('label_value', $localization->string('yes'));
    $conf_set70yes->set('input_id', 'referal-yes');
    $conf_set70yes->set('input_name', 'conf_set70');
    $conf_set70yes->set('input_value', 1);
    if ($vavok->get_configuration('showRefPage') == 1) {
        $conf_set70yes->set('input_status', 'checked');
    }

    $conf_set70no = new PageGen('forms/radio_inline.tpl');
    $conf_set70no->set('label_for', 'referal-no');
    $conf_set70no->set('label_value', $localization->string('no'));
    $conf_set70no->set('input_id', 'referal-no');
    $conf_set70no->set('input_name', 'conf_set70');
    $conf_set70no->set('input_value', 0);
    if ($vavok->get_configuration('showRefPage') == 0) {
        $conf_set70no->set('input_status', 'checked');
    }

    $show_refpage = new PageGen('forms/radio_group.tpl');
    $show_refpage->set('description', $localization->string('showrefpage'));
    $show_refpage->set('radio_group', $show_refpage->merge(array($conf_set70yes, $conf_set70no)));

    /**
     * Allow Facebook comments on pages
     */
    $conf_set6yes = new PageGen('forms/radio_inline.tpl');
    $conf_set6yes->set('label_for', 'fb_comm_yes');
    $conf_set6yes->set('label_value', $localization->string('yes'));
    $conf_set6yes->set('input_id', 'fb_comm_yes');
    $conf_set6yes->set('input_name', 'conf_set6');
    $conf_set6yes->set('input_value', 1);
    if ($vavok->get_configuration('pgFbComm') == 1) {
        $conf_set6yes->set('input_status', 'checked');
    }

    $conf_set6no = new PageGen('forms/radio_inline.tpl');
    $conf_set6no->set('label_for', 'fb_comm_no');
    $conf_set6no->set('label_value', $localization->string('no'));
    $conf_set6no->set('input_id', 'fb_comm_no');
    $conf_set6no->set('input_name', 'conf_set6');
    $conf_set6no->set('input_value', 0);
    if ($vavok->get_configuration('pgFbComm') == 0) {
        $conf_set6no->set('input_status', 'checked');
    }

    $fb_comm = new PageGen('forms/radio_group.tpl');
    $fb_comm->set('description', 'Facebook comments on pages');
    $fb_comm->set('radio_group', $fb_comm->merge(array($conf_set6yes, $conf_set6no)));

    $form->set('fields', $form->merge(array($conf_set28, $conf_set51, $show_refpage, $fb_comm)));
    echo $form->output();

    echo '<p><a href="settings.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a></p>';
}

if ($action == "seteight") {
    echo '<h1>' . $localization->string('other') . '</h1>';

    $form = new PageGen('forms/form.tpl');
    $form->set('form_method', 'post');
    $form->set('form_action', 'settings.php?action=editeight');

    /**
     * Max error logs in file
     */
    $conf_set58 = new PageGen('forms/input.tpl');
    $conf_set58->set('label_for', 'conf_set58');
    $conf_set58->set('label_value', $localization->string('maxlogfile'));
    $conf_set58->set('input_id', 'conf_set58');
    $conf_set58->set('input_name', 'conf_set58');
    $conf_set58->set('input_value', $vavok->get_configuration('maxLogData'));
    $conf_set58->set('input_maxlength', 3);

    /**
     * Max ban time
     */
    $conf_set76 = new PageGen('forms/input.tpl');
    $conf_set76->set('label_for', 'conf_set76');
    $conf_set76->set('label_value', $localization->string('maxbantime'));
    $conf_set76->set('input_id', 'conf_set76');
    $conf_set76->set('input_name', 'conf_set76');
    $conf_set76->set('input_value', round($vavok->get_configuration('maxBanTime') / 1440));
    $conf_set76->set('input_maxlength', 3);

    $form->set('fields', $form->merge(array($conf_set58, $conf_set76)));
    echo $form->output();

    echo '<p><a href="settings.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a></p>';
}

echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $localization->string('admpanel') . '</a><br />';
echo '<a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>