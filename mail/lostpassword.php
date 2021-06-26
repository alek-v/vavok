<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

$page = isset($_GET['page']) ? $vavok->check($_GET['page']) : '';
$logus = isset($_POST['logus']) ? $vavok->check($_POST['logus']) : '';
$mailsus = isset($_POST['mailsus']) ? $vavok->check($_POST['mailsus']) : '';
$captcha = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : ''; // Captcha code

// Page settings
$vavok->go('current_page')->page_title = $vavok->go('localization')->string('lostpass');

$vavok->go('current_page')->append_head_tags('<link rel="stylesheet" href="../themes/templates/pages/registration/lost_password.css" />');

// Add data to page <head> to show Google reCAPTCHA
$vavok->go('current_page')->append_head_tags('<script src="https://www.google.com/recaptcha/api.js" async defer></script>');

$vavok->require_header();

if (empty($page) || $page == 'index') {
	$this_page = new PageGen('pages/registration/lost_password.tpl');

    // Show reCAPTCHA
    $this_page->set('security_code', '<div class="g-recaptcha" data-sitekey="' . $vavok->get_configuration('recaptcha_sitekey') . '"></div>');

	echo $this_page->output();
}

// Send mail
if ($page == 'send') {
    if (!empty($logus) && !empty($mailsus)) {
        $userx_id = $vavok->go('users')->getidfromnick($logus);
        $show_userx = $vavok->go('db')->get_data('vavok_about', "uid='" . $userx_id . "'", 'email');

        $checkmail = trim($show_userx['email']);

        if ($mailsus == $checkmail) {
			if ($vavok->recaptcha_response($captcha)['success'] == true) {

                $newpas = generate_password();
                $new = $vavok->go('users')->password_encrypt($newpas);

                $subject = $vavok->go('localization')->string('newpassfromsite') . ' ' . $vavok->get_configuration('title');
                $mail = $vavok->go('localization')->string('hello') . " " . $logus . "\r\n" . $vavok->go('localization')->string('yournewdata') . " " . $vavok->get_configuration('homeUrl') . "\r\n" . $vavok->go('localization')->string('username') . ": " . $logus . "\r\n" . $vavok->go('localization')->string('pass') . ": " . $newpas . "\r\n\r\n" . $vavok->go('localization')->string('lnkforautolog') . ":\r\n" . $vavok->get_configuration('homeUrl') . "/pages/input.php?log=" . $logus . "&pass=" . $newpas . "&cookietrue=1\r\n" . $vavok->go('localization')->string('ycchngpass')  . "\r\n";

				$send_mail = new Mailer();
				$send_mail->queue_email($mailsus, $subject, $mail);

                // Update user's profile
                $vavok->go('db')->update('vavok_users', 'pass', $new, "id='{$userx_id}'");

                echo '<p><b>' . $vavok->go('localization')->string('passgen') . '</b></p>';
            } else {
                echo '<p>' . $vavok->go('localization')->string('wrongcaptcha') . '!</p>';

                echo '<p><a href="lostpassword.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a></p>';
            } 
        } else {
            echo '<p>' . $vavok->go('localization')->string('wrongmail') . '!</p>';

            echo '<p><a href="lostpassword.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a></p>';
        } 
    } else {
        echo '<p>' . $vavok->go('localization')->string('noneededdata') . '!</p>';

        echo '<p><a href="lostpassword.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a></p>';
    } 
} 

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>