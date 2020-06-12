<?php 
// (c) vavok.net

require_once"../include/strtup.php";

if (!$users->is_reg() || !$users->is_administrator(101)) {
    redirect_to("../?auth_error");
}

$action = isset($_GET['action']) ? check($_GET['action']) : '';


// main settings update
if ($action == "editone") {

    if (isset($_POST['conf_set0']) && isset($_POST['conf_set1']) && $_POST['conf_set2'] != "" && $_POST['conf_set3'] != "" && $_POST['conf_set8'] != "" && $_POST['conf_set9'] != "" && $_POST['conf_set10'] != "" && $_POST['conf_set11'] != ""  && !empty($_POST['conf_set12']) && $_POST['conf_set14'] != "" && !empty($_POST['conf_set21']) && $_POST['conf_set29'] != "" && isset($_POST['conf_set61']) && isset($_POST['conf_set62']) && isset($_POST['conf_set63'])) {
        
        $ufile = file_get_contents(BASEDIR . "used/config.dat");
        $udata = explode("|", $ufile);

    	$udata[0] = check($_POST['conf_set0']);
        $udata[1] = check($_POST['conf_set1']);
        $udata[2] = check($_POST['conf_set2']);
        $udata[3] = check($_POST['conf_set3']);
        $udata[8] = check($_POST['conf_set8']);
        $udata[9] = htmlspecialchars(stripslashes(trim($_POST['conf_set9'])));
        $udata[10] = check($_POST['conf_set10']);
        $udata[11] = check($_POST['conf_set11']);
        $udata[12] = check($_POST['conf_set12']);
        $udata[14] = check($_POST['conf_set14']);
        $udata[21] = check($_POST['conf_set21']); // transfer protocol
        $udata[29] = (int)$_POST['conf_set29'];
        $udata[47] = check($_POST['conf_set47']);
        $udata[61] = (int)$_POST['conf_set61'];
        $udata[62] = (int)$_POST['conf_set62'];
        $udata[63] = (int)$_POST['conf_set63'];


        $utext = '';

        for ($u = 0; $u < $config["configKeys"]; $u++) {
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

    } else {
        redirect_to("settings.php?action=setone&isset=mp_nosset");
    } 
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

	for ($u = 0; $u < $config["configKeys"]; $u++) {
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

    for ($u = 0; $u < $config["configKeys"]; $u++) {
        $utext .= $udata[$u] . '|';
    } 

    if (!empty($udata[8]) && !empty($udata[9])) {
        $fp = fopen(BASEDIR . "used/config.dat", "a+");
        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        fputs($fp, $utext);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        unset($utext);
    } 
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

    for ($u = 0; $u < $config["configKeys"]; $u++) {
        $utext .= $udata[$u] . '|';
    } 

    $fp = fopen(BASEDIR . "used/config.dat", "a+");
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    fputs($fp, $utext);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    unset($utext);


    // update gallery settings
    $gallery_file = file(BASEDIR . "used/dataconfig/gallery.dat");
    if ($gallery_file) {
        $gallery_data = explode("|", $gallery_file[0]);

        $gallery_data[0] = (int)$_POST['gallery_set0'];
        $gallery_data[8] = (int)$_POST['gallery_set8']; // photos per page
        $gallery_data[5] = (int)$_POST['screen_width'];
        $gallery_data[6] = (int)$_POST['screen_height'];
        $gallery_data[7] = (int)$_POST['media_buttons'];


        for ($u = 0; $u < $config["configKeys"]; $u++) {
            $gallery_text .= $gallery_data[$u] . '|';
        } 

        if (isset($gallery_data[0])) {
            $fp = fopen(BASEDIR . "used/dataconfig/gallery.dat", "a+");
            flock($fp, LOCK_EX);
            ftruncate($fp, 0);
            fputs($fp, $gallery_text);
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            unset($gallery_text);
        }
    }

    header ("Location: settings.php?isset=mp_yesset");
    exit;
    } else {
    header ("Location: settings.php?action=setfour&isset=mp_nosset");
    exit;
    } 
} 

if ($action == "editfive") {
if ($_POST['conf_set30'] != "") {
$ufile = file(BASEDIR . "used/config.dat");
$udata = explode("|", $ufile[0]);

$udata[30] = (int)$_POST['conf_set30'];

for ($u = 0; $u < $config["configKeys"]; $u++) {
    $utext .= $udata[$u] . '|';
} 

if (!empty($udata[8]) && !empty($udata[9])) {
    $fp = fopen(BASEDIR . "used/config.dat", "a+");
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    fputs($fp, $utext);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    unset($utext);
} 
header ("Location: settings.php?isset=mp_yesset");
exit;
} else {
header ("Location: settings.php?action=setfive&isset=mp_nosset");
exit;
} 
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

    for ($u = 0; $u < $config["configKeys"]; $u++) {
        $utext .= $udata[$u] . '|';
    } 

    if (!empty($udata[8]) && !empty($udata[9])) {
        $fp = fopen(BASEDIR . "used/config.dat", "a+");
        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        fputs($fp, $utext);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        unset($utext);
    }

    redirect_to ("settings.php?isset=mp_yesset");

    } else {
    redirect_to ("settings.php?action=seteight&isset=mp_nosset");
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


?>
