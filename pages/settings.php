<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   29.08.2020. 1:32:04
 */

require_once"../include/startup.php";

if (!$users->is_reg()) {
	$vavok->redirect_to("../index.php?isset=inputoff");
}

$action = isset($_GET['action']) ? $vavok->check($_GET['action']) : '';

// Save settings
if ($action == 'save') {
	$lang = isset($_POST['lang']) ? $vavok->check($_POST['lang']) : '';
	$user_timezone = isset($_POST['timezone']) ? $vavok->check($_POST['timezone']) : 0;
	$subnews = isset($_POST['subnews']) ? $vavok->check($_POST['subnews']) : '';
	$inbox_notification = isset($_POST['inbnotif']) ? $vavok->check($_POST['inbnotif']) : '';
		
	$email = $db->get_data('vavok_about', "uid='{$users->user_id}'", 'email')['email'];
	$notif = $db->get_data('notif', "uid='{$users->user_id}' AND type='inbox'", 'email');

	if (empty($lang)) {
	    header ("Location: settings.php?isset=incorrect");
	    exit;
	} 

	if (!isset($user_timezone)) {
	    $user_timezone = '0';
	}
	if (preg_match("/[^0-9+-]/", $user_timezone)) {
	    header ("Location: settings.php?isset=incorrect");
	    exit;
	}

	/**
	 * Site newsletter
	 */
	if ($subnews == 1) {
	    $email_check = $db->get_data('subs', "user_mail='{$email}'", 'user_mail');

	    if (!empty($email_check['user_mail'])) {
	        $result = 'error2'; // Error! Email already exist in database!
	        
	        $subnewss = 1;
	        $randkey = $vavok->generate_password();
	    } 

	    if (empty($result)) {
	        $randkey = $vavok->generate_password();
	        
	        $db->insert_data('subs', array('user_id' => $users->user_id, 'user_mail' => $email, 'user_pass' => $randkey));

	        $result = 'ok'; // sucessfully subscribed to site news!
	        $subnewss = 1;
	    } 
	}
	else {
	    $email_check = $db->get_data('subs', "user_id='{$users->user_id}'", 'user_mail');

	    if (empty($email_check['user_mail'])) {
	        $result = 'error';
	        $subnews = 0;
	        $randkey = "";
	    } else {
	    	// unsub
	        $db->delete('subs', "user_id='{$users->user_id}'");
	    	
	        $result = 'no';
	        $subnews = 0;
	        $randkey = "";
	    } 
	}

	// update changes
	$fields = array();
	$fields[] = 'ipadd';
	$fields[] = 'timezone';

	$values = array();
	$values[] = $users->find_ip();
	$values[] = $user_timezone;
	 
	$db->update('vavok_users', $fields, $values, "id='{$users->user_id}'");
	unset($fields, $values);

	// Update language
	$users->change_language($lang);

	// update email notificatoins
	$fields = array();
	$fields[] = 'subscri';
	$fields[] = 'newscod';
	$fields[] = 'lastvst';
	 
	$values = array();
	$values[] = $subnews;
	$values[] = $randkey;
	$values[] = time();
	 
	$db->update('vavok_profil', $fields, $values, "uid='{$users->user_id}'");
	unset($fields, $values);

	// notification settings
	if (!isset($inbox_notification)) {
		$inbox_notification = 1;
	}

	$check_inb = $db->count_row('notif', "uid='{$users->user_id}' AND type='inbox'");
	if ($check_inb > 0) {
	    $db->update('notif', 'active', $inbox_notification, "uid='{$users->user_id}' AND type='inbox'");
	} else {
		$db->insert_data('notif', array('active' => $inbox_notification, 'uid' => $users->user_id, 'type' => 'inbox'));
	}

	// redirect
	$vavok->redirect_to("./settings.php?isset=editsetting");

}

$current_page->page_title = $localization->string('settings');
$vavok->require_header();

if ($users->is_reg()) {

	$show_user = $db->get_data('vavok_users', "id='{$users->user_id}'", 'lang, mskin, skin');
	$page_set = $db->get_data('page_setting', "uid='{$users->user_id}'");
	$user_profil = $db->get_data('vavok_profil', "uid='{$users->user_id}'", 'subscri');
	$inbox_notif = $db->get_data('notif', "uid='{$users->user_id}' AND type='inbox'", 'active');

	$form = new PageGen('forms/form.tpl');
	$form->set('form_method', 'post');
	$form->set('form_action', 'settings.php?action=save');

	$options = '<option value="' . $show_user['lang'] . '">' . $show_user['lang'] . '</option>';
    $dir = opendir(BASEDIR . "include/lang");
    while ($file = readdir ($dir)) {
        if (!preg_match("/[^a-z0-9_-]/", $file) && ($file != $show_user['lang']) && strlen($file) > 2) {
            $options .= '<option value="' . $file . '">' . $file . '</option>';
        } 
    }
    $choose_lang = new PageGen('forms/select.tpl');
    $choose_lang->set('label_for', 'lang');
    $choose_lang->set('label_value', $localization->string('lang'));
    $choose_lang->set('select_id', 'lang');
    $choose_lang->set('select_name', 'lang');
    $choose_lang->set('options', $options);

    /**
     * Subscribe to site newsletter
     */
    $subnews_yes = new PageGen('forms/radio_inline.tpl');
    $subnews_yes->set('label_for', 'subnews');
    $subnews_yes->set('label_value', $localization->string('yes'));
    $subnews_yes->set('input_id', 'subnews');
    $subnews_yes->set('input_name', 'subnews');
    $subnews_yes->set('input_value', 1);
    if ($user_profil['subscri'] == 1) {
        $subnews_yes->set('input_status', 'checked');
    }

    $subnews_no = new PageGen('forms/radio_inline.tpl');
    $subnews_no->set('label_for', 'subnews');
    $subnews_no->set('label_value', $localization->string('no'));
    $subnews_no->set('input_id', 'subnews');
    $subnews_no->set('input_name', 'subnews');
    $subnews_no->set('input_value', 0);
    if ($user_profil['subscri'] == 0 || empty($user_profil['subscri'])) {
        $subnews_no->set('input_status', 'checked');
    }

    $subnews = new PageGen('forms/radio_group.tpl');
    $subnews->set('description', $localization->string('subscribetonews'));
    $subnews->set('radio_group', $subnews->merge(array($subnews_yes, $subnews_no)));

    /**
     * Receive new message notification
     */
    $msgnotif_yes = new PageGen('forms/radio_inline.tpl');
    $msgnotif_yes->set('label_for', 'inbnotif');
    $msgnotif_yes->set('label_value', $localization->string('yes'));
    $msgnotif_yes->set('input_id', 'inbnotif');
    $msgnotif_yes->set('input_name', 'inbnotif');
    $msgnotif_yes->set('input_value', 1);
    if ($inbox_notif['active'] == 1) {
        $msgnotif_yes->set('input_status', 'checked');
    }

    $msgnotif_no = new PageGen('forms/radio_inline.tpl');
    $msgnotif_no->set('label_for', 'inbnotif');
    $msgnotif_no->set('label_value', $localization->string('no'));
    $msgnotif_no->set('input_id', 'inbnotif');
    $msgnotif_no->set('input_name', 'inbnotif');
    $msgnotif_no->set('input_value', 0);
    if ($inbox_notif['active'] == 0 || empty($inbox_notif['active'])) {
        $msgnotif_no->set('input_status', 'checked');
    }

    $msgnotif = new PageGen('forms/radio_group.tpl');
    $msgnotif->set('description', 'Receive new message notification');
    $msgnotif->set('radio_group', $msgnotif->merge(array($msgnotif_yes, $msgnotif_no)));

    $form->set('fields', $form->merge(array($choose_lang, $subnews, $msgnotif)));
    echo $form->output();

} else {
    echo '<p>' . $localization->string('notloged') . '</p>';
} 

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>