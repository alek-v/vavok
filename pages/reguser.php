<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URL:       http://vavok.net
* Updated:   02.06.2020. 19:59:26
*/


require_once"../include/strtup.php";

$my_title = $lang_reguser['registration'];

$mediaLikeButton = 'off'; // dont show like buttons

$log = check($_POST['log']);
$pass = check($_POST['par']);
$pass2 = check($_POST['pars']);
$meil = check($_POST['meil']);
$pagetoload = !empty($_POST['ptl']) ? check($_POST['ptl']) : '';

$str1 = mb_strlen($log);
$str2 = mb_strlen($pass);

if ($str1 > 20 || $str2 > 20) {
    redirect_to("registration.php?isset=biginfo");
} elseif ($str1 < 3 || $str2 < 3) {
    redirect_to("registration.php?isset=smallinfo");
} elseif (!$users->validate_username($log)) {
    redirect_to("registration.php?isset=useletter");
} elseif ($pass !== $pass2) {
    redirect_to("registration.php?isset=nopassword");
}

// meta tag for this page
$genHeadTag = '<meta name="robots" content="noindex">';

// load theme header
include_once"../themes/$config_themes/index.php";

// check email
$check_mail = $db->count_row('vavok_about', "email='" . $meil . "'");
if ($check_mail > 0) {
    $check_mail = "no";
}

// check nick
$check_users = $db->count_row('vavok_users', "name='" . $log . "'");
if ($check_users > 0) {
    $check_users = "no";
}

// check for '-'
$substr_log = substr_count($log, "-");

if ($substr_log < 3) {
    if ($check_mail != "no") {
        if ($check_users != "no") {
                if ($users->validate_email($meil)) {
                    require_once '../include/plugins/securimage/securimage.php';
                    $securimage = new Securimage();

                    if ($securimage->check($_POST['captcha_code']) == true) {

                        $log = check($log);
                        $password = check($pass);

                        $mail = htmlspecialchars(stripslashes(strtolower($meil)));
                        $brow = check($users->user_browser());
                        $config_themes = check($config_themes);
                        $config["regConfirm"] = (int)$config["regConfirm"];

                        if ($config["regConfirm"] == "1") {
                            $registration_key = time() + 24 * 60 * 60;
                        } else {
                            $registration_key = '';
                        }

                        // register user
                        $regdate = time();
                        register($log, $password, $regdate, $config["regConfirm"], $registration_key, $config_themes, $brow, $ip, $mail); // register user
                         
                        // send email with reg. data
                        if ($config["regConfirm"] == "1") {
                            $needkey = "\r\n\r\n" . $lang_reguser['emailpart5'] . "\r\n" . $lang_reguser['yourkey'] . ": " . $registration_key . "\r\n" . $lang_reguser['emailpart6'] . ":\r\n\r\n" . website_home_address() . "/pages/key.php?action=inkey&key=" . $registration_key . "\r\n\r\n" . $lang_reguser['emailpart7'] . "\r\n\r\n";
                        } else {
                            $needkey = "\r\n\r\n";
                        } 

                        $subject = $lang_reguser['regonsite'] . ' ' . $config["title"];
                        $regmail = $lang_reguser['hello'] . " " . $log . "\r\n" . $lang_reguser['emailpart1'] . " " . $config["homeUrl"] . " \r\n" . $lang_reguser['emailpart2'] . ":\r\n\r\n" . $lang_home['username'] . ": " . $log . "\r\n" . $needkey . "" . $lang_reguser['emailpart3'] . "\r\n" . $lang_reguser['emailpart4'] . "";

                        // Send confirmation email
                        $newMail = new Mailer;
                        $newMail->send($mail, $subject, $regmail);

                        // Add to email queue and mark as send. Use it to resend email if requested
                        $values = array(
                        	'uad' => 1,
                        	'recipient' => $mail,
                        	'subject' => $subject,
                        	'content' => $regmail,
                        	'sent' => 1,
                        	'timesent' => date("Y-m-d H:i:s"),
                        	'timeadded' => date("Y-m-d H:i:s")

                        );
                        $db->insert_data('email_queue', $values);

                        // registration successfully, show info
                        echo '<p>' . $lang_reguser['regoknick'] . ': <b>' . $log . '</b> <br /><br /></p>';

                        // confirm registration
                        if ($config["regConfirm"] == "1") {

                        	echo '
							<form method="post" action="key.php?action=inkey">

								<div class="form-group">
									<label for="key">' . $lang_home['yourkey'] . '</label>
									<input type="text" class="form-control" id="key" name="key" placeholder="">
								</div>
								<button type="submit" class="btn btn-primary">' . $lang_home['confirm'] . '</button>

							</form>
							<form method="post" action="key.php?action=resendkey">

								<div class="form-group">
									<input type="hidden" class="form-control" id="recipient" name="recipient" value="' . $mail . '">
								</div>
								<button type="submit" class="btn btn-primary">' . $lang_home['resend'] . '</button>

							</form>
                        	';

                            echo '<p><b>' . $lang_reguser['enterkeymessage'] . '</b></p>';

                        } else {

                        	echo '<p>' . $lang_reguser['loginnow'] . '<br /></p>';

                        }

                    } else {
                        echo $lang_reguser['badcaptcha'] . '!<br />';
                    } 
                } else {
                    echo $lang_reguser['badmail'] . "<br />";
                }
        } else {
            echo $lang_reguser['userexists'] . "<br />";
        } 
    } else {
        echo $lang_reguser['emailexists'] . '<br />';
    } 
} else {
    echo $lang_reguser['toomuchslashes'] . '<br />';
} 
 

echo '<p><a href="registration.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br />';
echo '<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';

include_once"../themes/" . $config_themes . "/foot.php";
?>