<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URL:       http://vavok.net
* Updated:   19.07.2020. 21:18:21
*/

require_once"../include/strtup.php";

$action = isset($_GET['action']) ? check($_GET['action']) : '';

$mediaLikeButton = 'off'; // dont show like buttons


if ($action == 'reguser') {

$log = isset($_POST['log']) ? check($_POST['log']) : '';
$pass = isset($_POST['par']) ? check($_POST['par']) : '';
$pass2 = isset($_POST['pars']) ? check($_POST['pars']) : '';
$meil = isset($_POST['meil']) ? check($_POST['meil']) : '';
$pagetoload = isset($_POST['ptl']) ? check($_POST['ptl']) : '';

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

// Page title
$my_title = $lang_home['registration'];

// load theme header
include_once"../themes/$config_themes/index.php";

// check email
$check_mail = $db->count_row('vavok_about', "email='{$meil}'");
if ($check_mail > 0) { $check_mail = "no"; }

// check nick
$check_users = $db->count_row('vavok_users', "name='{$log}'");
if ($check_users > 0) { $check_users = "no"; }

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
                        $config_themes = check($config_themes);
                        $config["regConfirm"] = (int)$config["regConfirm"];

                        if ($config["regConfirm"] == "1") {
                            $registration_key = time() + 24 * 60 * 60;
                        } else {
                            $registration_key = '';
                        }

                        // register user
                        $users->register($log, $password, $config["regConfirm"], $registration_key, $config_themes, $mail); // register user
                         
                        // send email with reg. data
                        if ($config["regConfirm"] == "1") {
                            $needkey = "\r\n\r\n" . $lang_home['emailpart5'] . "\r\n" . $lang_home['yourkey'] . ": " . $registration_key . "\r\n" . $lang_home['emailpart6'] . ":\r\n\r\n" . website_home_address() . "/pages/key.php?action=inkey&key=" . $registration_key . "\r\n\r\n" . $lang_home['emailpart7'] . "\r\n\r\n";
                        } else {
                            $needkey = "\r\n\r\n";
                        } 

                        $subject = $lang_home['regonsite'] . ' ' . $config["title"];
                        $regmail = $lang_home['hello'] . " " . $log . "\r\n" . $lang_home['emailpart1'] . " " . $config["homeUrl"] . " \r\n" . $lang_home['emailpart2'] . ":\r\n\r\n" . $lang_home['username'] . ": " . $log . "\r\n" . $needkey . "" . $lang_home['emailpart3'] . "\r\n" . $lang_home['emailpart4'] . "";

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
                        echo '<p>' . $lang_home['regoknick'] . ': <b>' . $log . '</b> <br /><br /></p>';

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

                            echo '<p><b>' . $lang_home['enterkeymessage'] . '</b></p>';

                        } else {

                        	echo '<p>' . $lang_home['loginnow'] . '<br /></p>';

                        }

                    } else {

                        echo $lang_home['badcaptcha'] . '!<br />';
                        
                    } 

                } else {

                    echo $lang_home['badmail'] . "<br />";

                }

        } else {

            echo $lang_home['userexists'] . "<br />";

        } 

    } else {

        echo $lang_home['emailexists'] . '<br />';

    } 

} else {

    echo $lang_home['toomuchslashes'] . '<br />';

} 
 

echo '<p><a href="registration.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br />';
echo '<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';

include_once"../themes/" . $config_themes . "/foot.php";
exit;

}



$log = isset($log) ? $log = check($log) : $log = '';

// meta tag for this page
$genHeadTag = '<meta name="robots" content="noindex">';
$genHeadTag .= '<link rel="stylesheet" href="../themes/templates/pages/registration/register.css">';

$my_title = $lang_home['registration'];
include_once"../themes/" . $config_themes . "/index.php";


if ($config["openReg"] == "1") {

	if ($users->is_reg()) {

		$current_page = new PageGen('pages/registration/already_registered.tpl');

		$current_page->set('message', $log . ', ' . $lang_home['againreg']);

		echo $current_page->output();

	} else {

		$current_page = new PageGen('pages/registration/register.tpl');

		if (!empty($_GET['ptl'])) {
			$current_page->set('page_to_load', check($_GET['ptl']));
		}

		$current_page->set('registration_info', $lang_home['reginfo']);

		if ($config["regConfirm"] == "1") {
			$current_page->set('registration_key_info', $lang_home['keyinfo']);
		}

		if ($config["quarantine"] > 0) {
			$current_page->set('quarantine_info', $lang_home['quarantine1'] . ' ' . round($config["quarantine"] / 3600) . ' ' . $lang_home['quarantine2']);
		}

		echo $current_page->output();
		
		}

} else {

	$current_page = new PageGen('pages/registration/registration_stopped.tpl');

	$current_page->set('message', $lang_home['regstoped']);

	echo $current_page->output();

}

include_once"../themes/" . $config_themes . "/foot.php";

?>