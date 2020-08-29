<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   29.08.2020. 1:30:05
 */

require_once"../include/startup.php";

$action = isset($_GET['action']) ? $vavok->check($_GET['action']) : '';
$recipient_mail = isset($_POST['recipient']) ? $vavok->check($_POST['recipient']) : '';
$recipient_id = isset($_GET['uid']) ? $vavok->check($_GET['uid']) : '';

// resend registration email with key
if ($action == 'resendkey') {
	// if user id is not in url, get it from submited email
	if (empty($recipient_id)) {
    	$recipient_id = $users->get_id_from_mail($recipient_mail);
	}

    // check if user really need to confirm registration
    $check = $db->count_row('vavok_profil', "uid = '{$recipient_id}' AND regche = 1");

    if ($check > 0) {
    	// Get user's email if it is not submited
    	if (empty($recipient_mail)) {
    		$recipient_mail = $users->get_user_info($recipient_id, 'email');
    	}

        $email = $db->get_data('email_queue', "recipient='{$recipient_mail}'");

        // resend confirmation email
        $sendMail = new Mailer();

        // send mail
        $result = $sendMail->send($email['recipient'], $email['subject'], $email['content']);

        // update sent date
        $fields = array('timesent');
        $values = array(date("Y-m-d H:i:s"));
        
        // update data if email is sent
        if ($result == true) {
            $db->update('email_queue', $fields, $values, 'id = ' . $email['id']);
        }
    }

    $vavok->redirect_to('key.php?uid=' . $recipient_id . '&isset=mail');
}

$current_page->page_title = $localization->string('confreg');
$vavok->require_header();

if (empty($action)) {
    if ($users->is_reg()) {
        echo '<p>' . $localization->string('wellcome') . ', <b>' . $users->show_username() . '!</b><br>';
        echo $localization->string('confinfo') . '</p>';
    }

    /**
     * Confirm code
     */
    $form = new PageGen('forms/form.tpl');
    $form->set('form_method', 'post');
    $form->set('form_action', 'key.php?action=inkey&uid=' . $recipient_id);

    $input = new PageGen('forms/input.tpl');
    $input->set('label_for', 'key');
    $input->set('label_value', $localization->string('key'));
    $input->set('input_name', 'key');
    $input->set('input_id', 'key');
    $input->set('input_maxlength', 20);

    $form->set('website_language[save]', $localization->string('confirm'));
    $form->set('fields', $input->output());
    echo $form->output();

    /**
     * Resend code
     */
    $form = new PageGen('forms/form.tpl');
    $form->set('form_method', 'post');
    $form->set('form_action', 'key.php?action=resendkey&amp;uid=' . $recipient_id);
    $form->set('website_language[save]', $localization->string('resend'));
    echo $form->output();

    echo '<p>' . $localization->string('actinfodel') . '</p>';
}

// check comfirmation code
if ($action == "inkey") {
    if (isset($_GET['key'])) {
        $key = $vavok->check(trim($_GET['key']));
    } else {
        $key = $vavok->check(trim($_POST['key']));
    }

    if (!empty($key)) {
        if (!$db->update('vavok_profil', array('regche', 'regkey'), array('', ''), "regkey='{$key}'")) {
            echo '<p>' . $localization->string('keynotok') . '!</p>';

            echo $vavok->sitelink(HOMEDIR . 'pages/key.php?uid=' . $recipient_id, $localization->string('back')) . '</p>';
        } else {
            echo '<p>' . $localization->string('keyok') . '!</p>';

            echo $vavok->sitelink(HOMEDIR . 'pages/login.php', $localization->string('login'), '<p>', '</p>');
        }
    } else {
        echo '<p>' . $localization->string('nokey') . '!</p>';

        echo $vavok->sitelink(HOMEDIR . 'pages/key.php?uid=' . $recipient_id, $localization->string('back'), '<p>', '</p>');
    }
}

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>