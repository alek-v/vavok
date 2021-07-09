<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if ($vavok->post_and_get('action') == 'go') {
    // Check name
    if (empty($vavok->post_and_get('name'))) { $vavok->redirect_to('./?isset=noname'); }

    // Check email body
    if (empty($vavok->post_and_get('body'))) { $vavok->redirect_to('./?isset=nobody'); }

    // Validate email address
    if (!$vavok->go('users')->validate_email($vavok->post_and_get('umail'))) { $vavok->redirect_to('./?isset=noemail'); }

    // Redirect if response is false
    if ($vavok->recaptcha_response($vavok->post_and_get('g-recaptcha-response'))['success'] == false) { $vavok->redirect_to('./?isset=vrcode'); }

    // Send email
    $mail = new Mailer();
    $mail->queue_email($vavok->get_configuration('adminEmail'), $vavok->go('localization')->string('msgfrmst') . " " . $vavok->get_configuration("title"), $vavok->post_and_get('body') . "\r\n\r\n\r\n-----------------------------------------\r\nSender: {$vavok->post_and_get('name')}\r\nSender's email: {$vavok->post_and_get('umail')}\r\nBrowser: " . $vavok->go('users')->user_browser() . "\r\nIP: " . $vavok->go('users')->find_ip() . "\r\n" . $vavok->go('localization')->string('datesent') . ": " . date('d.m.Y. / H:i'), '', '', 'normal');

    // Email sent
    $vavok->redirect_to('./?isset=mail');
}

if (empty($vavok->post_and_get('action'))) {
    // Page title
    $vavok->go('current_page')->page_title = $vavok->go('localization')->string('contact');

    // Add data to page <head> to show Google reCAPTCHA
    $vavok->go('current_page')->append_head_tags('<script src="https://www.google.com/recaptcha/api.js" async defer></script>');

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

    // Show reCAPTCHA
    $showPage->set('security_code', '<div class="g-recaptcha" data-sitekey="' . $vavok->get_configuration('recaptcha_sitekey') . '"></div>');
} 

// show page
echo $showPage->output();

$vavok->require_footer();

?>