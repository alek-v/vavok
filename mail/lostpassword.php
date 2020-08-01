<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   01.08.2020. 0:23:39
*/

require_once"../include/startup.php";

$page = isset($_GET['page']) ? $vavok->check($_GET['page']) : '';
$logus = isset($_POST['logus']) ? $vavok->check($_POST['logus']) : '';
$mailsus = isset($_POST['mailsus']) ? $vavok->check($_POST['mailsus']) : '';

// Page settings
$my_title = $localization->string('lostpass');

$genHeadTag = '<link rel="stylesheet" href="../themes/templates/pages/registration/lost_password.css" />';

require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

if (empty($page) || $page == 'index') {
	
	$this_page = new PageGen('pages/registration/lost_password.tpl');

	echo $this_page->output();
	
}

// Send mail
if ($page == 'send') {

    if (!empty($logus) && !empty($mailsus)) {

        $userx_id = $users->getidfromnick($logus);
        $show_userx = $db->get_data('vavok_about', "uid='" . $userx_id . "'", 'email');

        $checkmail = trim($show_userx['email']);

        if ($mailsus == $checkmail) {

			require_once BASEDIR . 'include/plugins/securimage/securimage.php';
			$securimage = new Securimage();

			if ($securimage->check($_POST['captcha_code']) == true) {

                $newpas = generate_password();
                $new = $users->password_encrypt($newpas);

                $subject = $localization->string('newpassfromsite') . ' ' . $vavok->get_configuration('title');
                $mail = $localization->string('hello') . " " . $logus . "\r\n" . $localization->string('yournewdata') . " " . $vavok->get_configuration('homeUrl') . "\n" . $localization->string('username') . ": " . $logus . "\n" . $localization->string('pass') . ": " . $newpas . "\r\n\r\n" . $localization->string('lnkforautolog') . ":\r\n" . $vavok->get_configuration('homeUrl') . "/pages/input.php?log=" . $logus . "&pass=" . $newpas . "&cookietrue=1\r\n" . $localization->string('ycchngpass')  . "\r\n";

				$send_mail = new Mailer();
				$send_mail->send($mailsus, $subject, $mail);

                // Update user's profile
                $db->update('vavok_users', 'pass', $new, "id='{$userx_id}'");

                echo '<p><b>' . $localization->string('passgen') . '</b></p>';
            } else {
                echo '<p>' . $localization->string('wrongcaptcha') . '!</p>';

                echo '<p><a href="lostpassword.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a></p>';
            } 
        } else {
            echo '<p>' . $localization->string('wrongmail') . '!</p>';

            echo '<p><a href="lostpassword.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a></p>';
        } 
    } else {
        echo '<p>' . $localization->string('noneededdata') . '!</p>';

        echo '<p><a href="lostpassword.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a></p>';
    } 
} 

echo '<p><a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>