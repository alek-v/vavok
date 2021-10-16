<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

$recipient_mail = $vavok->post_and_get('recipient');
$recipient_id = $vavok->post_and_get('uid');

// resend registration email with key
if ($vavok->post_and_get('action') == 'resendkey') {
	// if user id is not in url, get it from submited email
	if (empty($recipient_id)) $recipient_id = $vavok->go('users')->id_from_email($recipient_mail);

    // check if user really need to confirm registration
    if ($vavok->go('users')->user_info('regche', $recipient_id) == 1) {
    	// Get users email if it is not submited
    	if (empty($recipient_mail)) $recipient_mail = $vavok->go('users')->user_info('email', $recipient_id);

        $email = $vavok->go('db')->get_data(DB_PREFIX . 'email_queue', "recipient='{$recipient_mail}'");

        /**
         * Check if it is too early to resend email
         */

        // Get time when message is sent, if it is empty use current time
        $time_key_sent = !empty($email['timesent']) ? $email['timesent'] : date("Y-m-d H:i:s");

        $origin = new DateTime($time_key_sent);
        $target = new DateTime(date("Y-m-d H:i:s")); // Current time
        $interval = $origin->diff($target);

        // Redirect if it is too early to send new message
        if ((int)$interval->format('%i') < 2) $vavok->redirect_to('key.php?uid=' . $recipient_id . '&isset=tooearly');

        // resend confirmation email
        $sendMail = new Mailer();

        // send mail
        $result = $sendMail->send($email['recipient'], $email['subject'], $email['content']);

        // update sent date
        $fields = array('timesent');
        $values = array(date("Y-m-d H:i:s"));
        
        // update data if email is sent
        if ($result == true) {
            $vavok->go('db')->update('email_queue', $fields, $values, 'id = ' . $email['id']);
        }
    }

    $vavok->redirect_to('key.php?uid=' . $recipient_id . '&isset=mail');
}

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('confreg');
$vavok->require_header();

if (empty($vavok->post_and_get('action'))) {
    if ($vavok->go('users')->is_reg()) {
        echo '<p>' . $vavok->go('localization')->string('wellcome') . ', <b>' . $vavok->go('users')->show_username() . '!</b><br>';
        echo $vavok->go('localization')->string('confinfo') . '</p>';
    }

    /**
     * Confirm code
     */
    $form = new PageGen('forms/form.tpl');
    $form->set('form_method', 'post');
    $form->set('form_action', 'key.php?action=inkey&uid=' . $recipient_id);

    $input = new PageGen('forms/input.tpl');
    $input->set('label_for', 'key');
    $input->set('label_value', $vavok->go('localization')->string('key'));
    $input->set('input_name', 'key');
    $input->set('input_id', 'key');
    $input->set('input_maxlength', 20);

    $form->set('website_language[save]', $vavok->go('localization')->string('confirm'));
    $form->set('fields', $input->output());
    echo $form->output();

    /**
     * Resend code
     */
    $form = new PageGen('forms/form.tpl');
    $form->set('form_method', 'post');
    $form->set('form_action', 'key.php?action=resendkey&amp;uid=' . $recipient_id);
    $form->set('website_language[save]', $vavok->go('localization')->string('resend'));
    echo $form->output();

    echo '<p>' . $vavok->go('localization')->string('actinfodel') . '</p>';
}

// Check confirmation code
if ($vavok->post_and_get('action') == 'inkey') {
    if (!empty($vavok->post_and_get('key'))) {
        if (!$vavok->go('users')->confirm_registration($vavok->post_and_get('key'))) {
            echo $vavok->show_danger($vavok->go('localization')->string('keynotok'));

            echo $vavok->sitelink(HOMEDIR . 'pages/key.php?uid=' . $recipient_id, $vavok->go('localization')->string('back')) . '</p>';
        } else {
            echo $vavok->show_success($vavok->go('localization')->string('keyok'));

            echo $vavok->sitelink(HOMEDIR . 'pages/login.php', $vavok->go('localization')->string('login'), '<p>', '</p>');
        }
    } else {
        echo $vavok->show_danger($vavok->go('localization')->string('nokey'));

        echo $vavok->sitelink(HOMEDIR . 'pages/key.php?uid=' . $recipient_id, $vavok->go('localization')->string('back'), '<p>', '</p>');
    }
}

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>