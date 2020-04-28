<?php
// (c) vavok.net
require_once"../include/strtup.php";
include_once"../themes/$config_themes/index.php";

if (isset($_GET['page'])) {$page = check($_GET['page']);}
if (isset($_POST['logus'])) {$logus = check($_POST['logus']);}
if (isset($_POST['mailsus'])) {$mailsus = check($_POST['mailsus']);}


if (!isset($page) || $page == '' || $page == 'index') {
    //echo $lang_mail['howtolostpass'] . '.<br /><br />';


    echo '<form method="post" action="resend.php?page=send">';
    echo $lang_home['username'] . ':<br>';
    echo '<input name="logus" maxlength="20" /><br>';
    echo 'E-Mail: <br><input name="mailsus" type="text" value="@" /><br>';
    
    echo '' . $lang_home['captcha'] . ' <br>
    <img id="captcha" src="../include/plugins/securimage/securimage_show.php" alt="CAPTCHA Image" />
    <br>
    <input type="text" name="captcha_code" size="10" maxlength="6" />
<a href="#" onclick="document.getElementById(\'captcha\').src = \'../include/plugins/securimage/securimage_show.php?\' + Math.random(); return false">[ Different Image ]</a>
    <br><br>';
    
    echo '<input value="' . $lang_home['confirm'] . '" type="submit" /></form><hr>';
} 
if ($page == 'send') {
    if (!empty($logus) && !empty($mailsus)) {
        $userx_id = $users->getidfromnick($logus);
        $show_userx = $db->select('vavok_about', "uid='" . $userx_id . "'", '', 'email');
        $show_profilx = $db->select('vavok_profil', "uid='" . $userx_id . "'", '', 'regkey');

        $checkmail = trim($show_userx['email']);
		$reg_keyold = trim($show_profilx['regkey']);

        if ($mailsus == $checkmail && !empty($reg_keyold)) {
            
            require_once '../include/plugins/securimage/securimage.php';
            $securimage = new Securimage();

            if ($securimage->check($_POST['captcha_code']) == true) {

                $reg_key = time() + 24 * 60 * 60;

                $subject = 'Activation key ' . $config["title"];
                $mail = $lang_mail['hello'] . " " . $logus . "\r\n\r\nYour new registration key is " . $reg_key . ",\r\nor you can go to link " . $config_srvhost . "/pages/key.php?action=inkey&key=" . $reg_key . "";
								// update lang
                sendmail($mailsus, $subject, $mail); 
                // update user's profile
                mysql_query("UPDATE vavok_profil SET regkey='" . $reg_key . "' WHERE uid='" . $userx_id . "'");

                echo '<b>Activation key has been sent!</b><br><br>'; // update lang
            } else {
                echo $lang_mail['wrongcaptcha'] . '!<br><br>';
                echo '<a href="resend.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br>';;
            } 
        } else {
            echo $lang_mail['wrongmail'] . '!<br><br>';
            echo '<a href="resend.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br>';;
        } 
    } else {
        echo $lang_mail['noneededdata'] . '!<br><br>';
        echo '<a href="resend.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br>';;
    } 
} 

echo '<p><a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';
include_once"../themes/" . $config_themes . "/foot.php";

?>