<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

$recipient_mail = isset($_POST['recipient']) ? $vavok->check($_POST['recipient']) : '';
$recipient_id = isset($_GET['uid']) ? $vavok->check($_GET['uid']) : '';

// resend registration email with key
if ($vavok->post_and_get('action') == 'resendkey') {
	// if user id is not in url, get it from submited email
	if (empty($recipient_id)) {
    	$recipient_id = $vavok->go('users')->get_id_from_mail($recipient_mail);
	}

    // check if user really need to confirm registration
    $check = $vavok->go('db')->count_row('vavok_profil', "uid = '{$recipient_id}' AND regche = 1");

    if ($check > 0) {
    	// Get user's email if it is not submited
    	if (empty($recipient_mail)) {
    		$recipient_mail = $vavok->go('users')->get_user_info($recipient_id, 'email');
    	}

        $email = $vavok->go('db')->get_data('email_queue', "recipient='{$recipient_mail}'");

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

// check comfirmation code
if ($vavok->post_and_get('action') == 'inkey') {
    $key = isset($_GET['key']) ? $vavok->check(trim($_GET['key'])) : $vavok->check(trim($_POST['key']));

    if (!empty($key)) {
        if (!$vavok->go('db')->update('vavok_profil', array('regche', 'regkey'), array('', ''), "regkey='{$key}'")) {
            echo '<p>' . $vavok->go('localization')->string('keynotok') . '!</p>';

            echo $vavok->sitelink(HOMEDIR . 'pages/key.php?uid=' . $recipient_id, $vavok->go('localization')->string('back')) . '</p>';
        } else {
            echo '<p>' . $vavok->go('localization')->string('keyok') . '!</p>';

            echo $vavok->sitelink(HOMEDIR . 'pages/login.php', $vavok->go('localization')->string('login'), '<p>', '</p>');
        }
    } else {
        echo '<p>' . $vavok->go('localization')->string('nokey') . '!</p>';

        echo $vavok->sitelink(HOMEDIR . 'pages/key.php?uid=' . $recipient_id, $vavok->go('localization')->string('back'), '<p>', '</p>');
    }
}

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>