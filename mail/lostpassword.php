<?php
// (c) vavok.net

require_once"../include/startup.php";

$page = isset($_GET['page']) ? check($_GET['page']) : '';
$logus = isset($_POST['logus']) ? check($_POST['logus']) : '';
$mailsus = isset($_POST['mailsus']) ? check($_POST['mailsus']) : '';

// Page settings
$my_title = $lang_home['lostpass'];

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

                $subject = $lang_mail['newpassfromsite'] . ' ' . get_configuration('title');
                $mail = $lang_mail['hello'] . " " . $logus . "\r\n" . $lang_mail['yournewdata'] . " " . get_configuration('homeUrl') . "\n" . $lang_home['username'] . ": " . $logus . "\n" . $lang_home['pass'] . ": " . $newpas . "\r\n\r\n" . $lang_mail['lnkforautolog'] . ":\r\n" . get_configuration('homeUrl') . "/pages/input.php?log=" . $logus . "&pass=" . $newpas . "&cookietrue=1\r\n" . $lang_mail['ycchngpass']  . "\r\n";

				$send_mail = new Mailer();
				$send_mail->send($mailsus, $subject, $mail);

                // update user's profile
                $db->update('vavok_users', 'pass', $new, "id='{$userx_id}'");

                echo '<b>' . $lang_mail['passgen'] . '</b><br><br>';
            } else {
                echo $lang_mail['wrongcaptcha'] . '!<br><br>';
                echo '<a href="lostpassword.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br>';;
            } 
        } else {
            echo $lang_mail['wrongmail'] . '!<br><br>';
            echo '<a href="lostpassword.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br>';;
        } 
    } else {
        echo $lang_mail['noneededdata'] . '!<br><br>';
        echo '<a href="lostpassword.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br>';;
    } 
} 

echo '<p><a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>