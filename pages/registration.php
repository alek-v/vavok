<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if ($vavok->post_and_get('action') == 'reguser') {
    $str1 = mb_strlen($vavok->post_and_get('log'));
    $str2 = mb_strlen($vavok->post_and_get('par'));

    if ($str1 > 20 || $str2 > 20) {
        $vavok->redirect_to('registration.php?isset=biginfo');
    } elseif ($str1 < 3 || $str2 < 3) {
        $vavok->redirect_to('registration.php?isset=smallinfo');
    } elseif (!$vavok->go('users')->validate_username($vavok->post_and_get('log'))) {
        $vavok->redirect_to('registration.php?isset=useletter');
    } elseif ($vavok->post_and_get('par') !== $vavok->post_and_get('pars')) {
        $vavok->redirect_to('registration.php?isset=nopassword');
    }

    // Meta tag for this page
    $vavok->go('current_page')->append_head_tags('<meta name="robots" content="noindex">');

    // Page title
    $vavok->go('current_page')->page_title = $vavok->go('localization')->string('registration');

    // load theme header
    $vavok->require_header();

    // Continue if email does not exist in database
    if (!$vavok->go('users')->email_exists($vavok->post_and_get('meil'))) {
        // Continue if username does not exists in database
        if (!$vavok->go('users')->username_exists($vavok->post_and_get('log'))) {
            // Continue if email is valid
            if ($vavok->go('users')->validate_email($vavok->post_and_get('meil'))) {
                // Check reCAPTCHA
                if ($vavok->recaptcha_response($vavok->post_and_get('g-recaptcha-response'))['success'] == true) {
                    $password = $vavok->check($vavok->post_and_get('par'));
                    $mail = htmlspecialchars(stripslashes(strtolower($vavok->post_and_get('meil'))));

                    if ($vavok->get_configuration('regConfirm') == 1) {
                        $registration_key = time() + 24 * 60 * 60;
                    } else {
                        $registration_key = '';
                    }

                    // register user
                    $vavok->go('users')->register($vavok->post_and_get('log'), $password, $vavok->get_configuration('regConfirm'), $registration_key, MY_THEME, $mail); // register user
                     
                    // Send email with registration data
                    if ($vavok->get_configuration('regConfirm') == 1) {
                        $needkey = "<p>" . $vavok->go('localization')->string('emailpart5') . "</p>
                        <p>" . $vavok->go('localization')->string('yourkey') . ": " . $registration_key . "</p>
                        <p>" . $vavok->go('localization')->string('emailpart6') . ":</p>
                        <p>" . $vavok->website_home_address() . "/pages/key.php?action=inkey&key=" . $registration_key . "</p>
                        <p>" . $vavok->go('localization')->string('emailpart7') . "</p>";
                    } else {
                        $needkey = '<br />';
                    }

                    $subject = $vavok->go('localization')->string('regonsite') . ' ' . $vavok->get_configuration('title');
                    $regmail = "<p>" . $vavok->go('localization')->string('hello') . " " . $vavok->post_and_get('log') . "!</p>
                    <p>" . $vavok->go('localization')->string('emailpart1') . " " . $vavok->get_configuration('homeUrl') . "</p>
                    <p>" . $vavok->go('localization')->string('emailpart2') . ":</p>
                    <p>" . $vavok->go('localization')->string('username') . ": " . $vavok->post_and_get('log') . "</p>
                    <p>" . $needkey . "" . $vavok->go('localization')->string('emailpart3') . "</p>
                    <p>" . $vavok->go('localization')->string('emailpart4') . "</p>";

                    // Send confirmation email
                    $newMail = new Mailer;

                    // Add to the email queue
                    $newMail->queue_email($mail, $subject, $regmail, '', '', $priority = 'high');

                    // Registration completed successfully
                    $completed = 'successfully';

                    // registration successfully, show info
                    echo '<p>' . $vavok->go('localization')->string('regoknick') . ': <b>' . $vavok->post_and_get('log') . '</b> <br /><br /></p>';

                    if ($vavok->get_configuration('regConfirm') == 1) {
                        /**
                         * Confirm registration
                         */
                        $form = new PageGen('forms/form.tpl');
                        $form->set('form_method', 'post');
                        $form->set('form_action', 'key.php?action=inkey');

                        $input = new PageGen('forms/input.tpl');
                        $input->set('label_for', 'key');
                        $input->set('label_value', $vavok->go('localization')->string('yourkey'));
                        $input->set('input_type', 'text');
                        $input->set('input_id', 'key');
                        $input->set('input_name', 'key');
                        $input->set('input_placeholder', '');

                        $form->set('website_language[save]', $vavok->go('localization')->string('confirm'));
                        $form->set('fields', $input->output());
                        echo $form->output();

                        /**
                         * Resend email
                         */
                        $form = new PageGen('forms/form.tpl');
                        $form->set('form_method', 'post');
                        $form->set('form_action', 'key.php?action=resendkey');

                        $input = new PageGen('forms/input.tpl');
                        $input->set('input_type', 'hidden');
                        $input->set('input_id', 'recipient');
                        $input->set('input_name', 'recipient');
                        $input->set('input_value', $mail);

                        $form->set('website_language[save]', $vavok->go('localization')->string('resend'));
                        $form->set('fields', $input->output());
                        echo $form->output();

                        echo '<p><b>' . $vavok->go('localization')->string('enterkeymessage') . '</b></p>';
                    } else {
                    	echo '<p>' . $vavok->go('localization')->string('loginnow') . '</p>';
                    }
                } else {
                    echo '<p>' . $vavok->go('localization')->string('badcaptcha') . '</p>';
                }
            } else {
                echo '<p>' . $vavok->go('localization')->string('badmail') . "</p>";
            }
        } else {
            echo '<p>' . $vavok->go('localization')->string('userexists') . "</p>";
        }
    } else {
        echo '<p>' . $vavok->go('localization')->string('emailexists') . '</p>';
    }

    // Show back link if registration is not completed
    if (!isset($completed)) echo $vavok->sitelink(HOMEDIR . 'pages/registration.php', $vavok->go('localization')->string('back'), '<p>', '</p>');

    echo $vavok->homelink('<p>', '</p>');

    $vavok->require_footer();
    exit;
}

// meta tag for this page
$vavok->go('current_page')->append_head_tags('<meta name="robots" content="noindex">');
$vavok->go('current_page')->append_head_tags('<link rel="stylesheet" href="' . HOMEDIR . 'themes/templates/pages/registration/register.css">');
// Add data to page <head> to show Google reCAPTCHA
$vavok->go('current_page')->append_head_tags('<script src="https://www.google.com/recaptcha/api.js" async defer></script>');

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('registration');
$vavok->require_header();

if ($vavok->get_configuration('openReg') == 1) {
	if ($vavok->go('users')->is_reg()) {
		$this_page = new PageGen('pages/registration/already_registered.tpl');

		$this_page->set('message', $vavok->go('users')->username . ', ' . $vavok->go('localization')->string('againreg'));

		echo $this_page->output();
	} else {
		$this_page = new PageGen('pages/registration/register.tpl');

		if (!empty($vavok->post_and_get('ptl'))) $this_page->set('page_to_load', $vavok->check($vavok->post_and_get('ptl')));

		$this_page->set('registration_info', $vavok->go('localization')->string('reginfo'));

		if ($vavok->get_configuration('regConfirm') == 1) $this_page->set('registration_key_info', $vavok->go('localization')->string('keyinfo'));

		if ($vavok->get_configuration('quarantine') > 0) $this_page->set('quarantine_info', $vavok->go('localization')->string('quarantine1') . ' ' . round($vavok->get_configuration('quarantine') / 3600) . ' ' . $vavok->go('localization')->string('quarantine2'));

        // Show reCAPTCHA
        $this_page->set('security_code', '<div class="g-recaptcha" data-sitekey="' . $vavok->get_configuration('recaptcha_sitekey') . '"></div>');

		echo $this_page->output();
		}
} else {
	$this_page = new PageGen('pages/registration/registration_stopped.tpl');
	$this_page->set('message', $vavok->go('localization')->string('regstoped'));
	echo $this_page->output();
}

$vavok->require_footer();

?>