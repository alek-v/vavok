<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

$action = isset($_GET['action']) ? $vavok->check($_GET['action']) : '';
$name = isset($_POST['name']) ? $vavok->check($_POST['name']) : '';
$body = isset($_POST['body']) ? $vavok->check($_POST['body']) : '';
$umail = isset($_POST['umail']) ? $vavok->check($_POST['umail']) : '';

if ($action == 'go') {
    // Check name
    if (empty($name)) { $vavok->redirect_to('./?isset=noname'); }

    // Check email body
    if (empty($body)) { $vavok->redirect_to('./?isset=nobody'); }

    // Validate email address
    if (!$vavok->go('users')->validate_email($umail)) { $vavok->redirect_to('./?isset=noemail'); }

    // Captcha code
    require BASEDIR . 'include/plugins/securimage/securimage.php';
    $securimage = new Securimage();

    // Check captcha code
    if (!$securimage->check($_POST['captcha_code'])) { $vavok->redirect_to('./?isset=vrcode'); }

    // Send email
    $mail = new Mailer();
    $mail->send($vavok->get_configuration('adminEmail'), $vavok->go('localization')->string('msgfrmst') . " " . $vavok->get_configuration("title"), $body . "\r\n\r\n\r\n-----------------------------------------\r\nBrowser: " . $vavok->go('users')->user_browser() . "\r\nIP: " . $vavok->go('users')->find_ip() . "\r\n" . $vavok->go('localization')->string('datesent') . ": " . date('d.m.Y. / H:i', $vavok->get_configuration("siteTime")), $umail, $name);

    // Email sent
    $vavok->redirect_to('./?isset=mail');
}

if (empty($action)) {
    $vavok->go('current_page')->page_title = $vavok->go('localization')->string('contact');

    $vavok->require_header();

    // generate page
    $showPage = new PageGen("mail/mail_index.tpl");

    if (!$vavok->go('users')->is_reg()) {
        $usernameAndMail = new PageGen('mail/usernameAndMail_guest.tpl');
        $showPage->set('usernameAndMail', $usernameAndMail->output());
    } else {
        $user_email = $vavok->go('db')->get_data('vavok_about', "uid='{$vavok->go('users')->user_id}'", 'email');

        $usernameAndMail = new PageGen("mail/usernameAndMail_registered.tpl");
        $usernameAndMail->set('log', $vavok->go('users')->show_username());
        $usernameAndMail->set('user_email', $user_email['email']);

        $showPage->set('usernameAndMail', $usernameAndMail->output());
    }
} 

// show page
echo $showPage->output();

$vavok->require_footer();

?>