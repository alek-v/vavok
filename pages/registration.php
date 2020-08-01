<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URL:       http://vavok.net
* Updated:   01.08.2020. 2:28:30
*/

require_once"../include/startup.php";

$action = isset($_GET['action']) ? $vavok->check($_GET['action']) : '';

if ($action == 'reguser') {

$log = isset($_POST['log']) ? $vavok->check($_POST['log']) : '';
$pass = isset($_POST['par']) ? $vavok->check($_POST['par']) : '';
$pass2 = isset($_POST['pars']) ? $vavok->check($_POST['pars']) : '';
$meil = isset($_POST['meil']) ? $vavok->check($_POST['meil']) : '';
$pagetoload = isset($_POST['ptl']) ? $vavok->check($_POST['ptl']) : '';

$str1 = mb_strlen($log);
$str2 = mb_strlen($pass);

if ($str1 > 20 || $str2 > 20) {
    $vavok->redirect_to("registration.php?isset=biginfo");
} elseif ($str1 < 3 || $str2 < 3) {
    $vavok->redirect_to("registration.php?isset=smallinfo");
} elseif (!$users->validate_username($log)) {
    $vavok->redirect_to("registration.php?isset=useletter");
} elseif ($pass !== $pass2) {
    $vavok->redirect_to("registration.php?isset=nopassword");
}

// meta tag for this page
$genHeadTag = '<meta name="robots" content="noindex">';

// Page title
$my_title = $localization->string('registration');

// load theme header
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

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

                        $password = $vavok->check($pass);

                        $mail = htmlspecialchars(stripslashes(strtolower($meil)));

                        if ($vavok->get_configuration('regConfirm') == 1) {
                            $registration_key = time() + 24 * 60 * 60;
                        } else {
                            $registration_key = '';
                        }

                        // register user
                        $users->register($log, $password, $vavok->get_configuration('regConfirm'), $registration_key, MY_THEME, $mail); // register user
                         
                        // send email with reg. data
                        if ($vavok->get_configuration('regConfirm') == "1") {
                            $needkey = "\r\n\r\n" . $localization->string('emailpart5') . "\r\n" . $localization->string('yourkey') . ": " . $registration_key . "\r\n" . $localization->string('emailpart6') . ":\r\n\r\n" . $vavok->website_home_address() . "/pages/key.php?action=inkey&key=" . $registration_key . "\r\n\r\n" . $localization->string('emailpart7') . "\r\n\r\n";
                        } else {
                            $needkey = "\r\n\r\n";
                        } 

                        $subject = $localization->string('regonsite') . ' ' . $vavok->get_configuration('title');
                        $regmail = $localization->string('hello') . " " . $log . "\r\n" . $localization->string('emailpart1') . " " . $vavok->get_configuration('homeUrl') . " \r\n" . $localization->string('emailpart2') . ":\r\n\r\n" . $localization->string('username') . ": " . $log . "\r\n" . $needkey . "" . $localization->string('emailpart3') . "\r\n" . $localization->string('emailpart4') . "";

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
                        	'timeadded' => date("Y-m-d H:i:s"),
                            'sender' => ''

                        );
                        $db->insert_data('email_queue', $values);

                        // Registration completed successfully
                        $completed = 'successfully';

                        // registration successfully, show info
                        echo '<p>' . $localization->string('regoknick') . ': <b>' . $log . '</b> <br /><br /></p>';

                        // confirm registration
                        if ($vavok->get_configuration('regConfirm') == "1") {

                        	echo '
							<form method="post" action="key.php?action=inkey">

								<div class="form-group">
									<label for="key">' . $localization->string('yourkey') . '</label>
									<input type="text" class="form-control" id="key" name="key" placeholder="">
								</div>
								<button type="submit" class="btn btn-primary">' . $localization->string('confirm') . '</button>

							</form>
							<form method="post" action="key.php?action=resendkey">

								<div class="form-group">
									<input type="hidden" class="form-control" id="recipient" name="recipient" value="' . $mail . '">
								</div>
								<button type="submit" class="btn btn-primary">' . $localization->string('resend') . '</button>

							</form>
                        	';

                            echo '<p><b>' . $localization->string('enterkeymessage') . '</b></p>';

                        } else {

                        	echo '<p>' . $localization->string('loginnow') . '<br /></p>';

                        }

                    } else {

                        echo $localization->string('badcaptcha') . '!<br />';
                        
                    } 

                } else {

                    echo $localization->string('badmail') . "<br />";

                }

        } else {

            echo $localization->string('userexists') . "<br />";

        } 

    } else {

        echo $localization->string('emailexists') . '<br />';

    } 

} else {

    echo $localization->string('toomuchslashes') . '<br />';

}

// Show back link if registration is not completed
if (!isset($completed)) { echo '<p><a href="registration.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a></p>'; }

echo '<p><a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";
exit;

}

$log = isset($log) ? $log = $vavok->check($log) : $log = '';

// meta tag for this page
$genHeadTag = '<meta name="robots" content="noindex">';
$genHeadTag .= '<link rel="stylesheet" href="../themes/templates/pages/registration/register.css">';

$my_title = $localization->string('registration');
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";


if ($vavok->get_configuration('openReg') == "1") {

	if ($users->is_reg()) {

		$current_page = new PageGen('pages/registration/already_registered.tpl');

		$current_page->set('message', $log . ', ' . $localization->string('againreg'));

		echo $current_page->output();

	} else {

		$current_page = new PageGen('pages/registration/register.tpl');

		if (!empty($_GET['ptl'])) {
			$current_page->set('page_to_load', $vavok->check($_GET['ptl']));
		}

		$current_page->set('registration_info', $localization->string('reginfo'));

		if ($vavok->get_configuration('regConfirm') == "1") {
			$current_page->set('registration_key_info', $localization->string('keyinfo'));
		}

		if ($vavok->get_configuration('quarantine') > 0) {
			$current_page->set('quarantine_info', $localization->string('quarantine1') . ' ' . round($vavok->get_configuration('quarantine') / 3600) . ' ' . $localization->string('quarantine2'));
		}

		echo $current_page->output();
		
		}

} else {

	$current_page = new PageGen('pages/registration/registration_stopped.tpl');

	$current_page->set('message', $localization->string('regstoped'));

	echo $current_page->output();

}

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>