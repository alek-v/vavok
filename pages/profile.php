<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg()) $vavok->redirect_to('../?isset=inputoff');

switch ($vavok->post_and_get('action')) {
	case 'save':
		if (!empty($_POST['site']) && !$vavok->validateURL($_POST['site'])) $vavok->redirect_to('profile.php?isset=insite');

		// check email
		if (!empty($_POST['email']) && !$vavok->go('users')->validate_email($_POST['email'])) $vavok->redirect_to('profile.php?isset=noemail');

		// check birthday
		// if (!empty($happy) && !preg_match("/^[0-9]{2}+\.[0-9]{2}+\.([0-9]{2}|[0-9]{4})$/",$happy)){header ("Location: profile.php?isset=inhappy"); exit;}

		$my_name = $vavok->no_br($vavok->check($vavok->post_and_get('my_name')));
		$surname = $vavok->no_br($vavok->check($vavok->post_and_get('surname')));
		$city = $vavok->no_br($vavok->check($vavok->post_and_get('otkel')));
		$street = $vavok->no_br($vavok->check($vavok->post_and_get('street')));
		$zip = $vavok->no_br($vavok->check($vavok->post_and_get('zip')));
		$infa = $vavok->no_br($vavok->check($vavok->post_and_get('infa')));
		$email = htmlspecialchars(strtolower($vavok->post_and_get('email')));
		$site = $vavok->no_br($vavok->check($vavok->post_and_get('site')));
		$browser = $vavok->no_br($vavok->check($vavok->go('users')->user_browser()));
		$ip = $vavok->no_br($vavok->check($vavok->go('users')->find_ip()));
		$sex = $vavok->no_br($vavok->check($vavok->post_and_get('pol')));
		$happy = $vavok->no_br($vavok->check($vavok->post_and_get('happy')));

		$fields = array();
		$fields[] = 'city';
		$fields[] = 'about';
		$fields[] = 'site';
		$fields[] = 'sex';
		$fields[] = 'birthday';
		$fields[] = 'rname';
		$fields[] = 'surname';
		$fields[] = 'address';
		$fields[] = 'zip';

		$values = array();
		$values[] = $city;
		$values[] = $infa;
		$values[] = $site;
		$values[] = $sex;
		$values[] = $happy;
		$values[] = $my_name;
		$values[] = $surname;
		$values[] = $street;
		$values[] = $zip;

		/**
		 * Update profile data
		 */
		$vavok->go('db')->update('vavok_about', $fields, $values, "uid='{$vavok->go('users')->user_id}'");

		/**
		 * Send email confirmation link if it is changed and save data into database
		 */
		if ($vavok->go('users')->user_info('email') != $email && $vavok->go('db')->count_row(DB_PREFIX . 'tokens', "uid = '{$vavok->go('users')->user_id}' AND content = '{$email}'") < 1) {
			/**
			 * Insert data to database
			 */
			$token = $vavok->generate_password();

			$now = new DateTime();
			$now->add(new DateInterval("P1D"));
			$new_time = $now->format('Y-m-d H:i:s');

			$data = array(
				'uid' => $vavok->go('users')->user_id,
				'type' => 'email',
				'content' => $email,
				'token' => $token,
				'expiration_time' => $new_time
			);

			$vavok->go('db')->insert(DB_PREFIX . 'tokens', $data);

			/**
			 * Add email to the queue
			 */
			$mailQueue = new Mailer;

			$msg = "Hello {$vavok->go('users')->username}\r\n
In order to add this email to your profile at site {$vavok->website_home_address()}
please confirm this address here visiting confirmation link " . $vavok->website_home_address() . "/pages/confirm_email.php?token=" . $token;
			$msg .= "\r\n\r\n\r\nIf you received this email by mistake please ignore it.";

			$mailQueue->queue_email($email, 'Confirm new email address', $msg);
		}

		$vavok->redirect_to("./profile.php?isset=editprofile");
		break;

	case 'delete_profile':

		if ($vavok->post_and_get('confirmed') == 'yes') {
			$delete_id = $vavok->go('users')->user_id;

			$vavok->go('users')->logout($delete_id);
			$vavok->go('users')->delete_user($delete_id);

			$vavok->redirect_to(HOMEDIR);
		}

		$vavok->go('current_page')->page_title = $vavok->go('localization')->string('deleteProfile');
		$vavok->require_header();

		echo '<p>' . $vavok->go('localization')->string('deleteConfirm') . '</p>';

		echo '<p><a href="profile.php?action=delete_profile&confirmed=yes" class="btn btn-danger">' . $vavok->go('localization')->string('deleteProfile') . '</a></p>';
		echo '<p><a href="profile.php" class="btn btn-primary">' . $vavok->go('localization')->string('back') . '</a></p>';

		echo $vavok->homelink('<p>', '</p>');

		$vavok->require_footer();

		break;

	default:
		$vavok->go('current_page')->append_head_tags('
		<style type="text/css">
		    .photo img {
		        max-width: 100px;
		        max-height: 100px;
		        overflow: hidden;
		    }
		</style>
		');

		$vavok->go('current_page')->page_title = $vavok->go('localization')->string('profsettings');
		$vavok->require_header();

		echo '<div class="row">';

		$about_user = $vavok->go('db')->get_data('vavok_about', "uid='{$vavok->go('users')->user_id}'");

		echo '<div class="col-sm">';

			echo '<div class="row">';

			    echo '<h1>' . $vavok->go('localization')->string('profsettings') . '</h1>';

			    $form = new PageGen('forms/form.tpl');
			    $form->set('form_method', 'post');
			    $form->set('form_action', 'profile.php?action=save');

			    /**
			     * Name
			     */
			    $my_name = new PageGen('forms/input.tpl');
			    $my_name->set('label_for', 'my_name');
			    $my_name->set('label_value', $vavok->go('localization')->string('name'));
			    $my_name->set('input_id', 'my_name');
			    $my_name->set('input_name', 'my_name');
			    $my_name->set('input_value', $vavok->go('users')->user_info('firstname'));

			    /**
			     * Last name
			     */
			    $surname = new PageGen('forms/input.tpl');
			    $surname->set('label_for', 'surname');
			    $surname->set('label_value', $vavok->go('localization')->string('surname'));
			    $surname->set('input_id', 'surname');
			    $surname->set('input_name', 'surname');
			    $surname->set('input_value', $vavok->go('users')->user_info('lastname'));

			    /**
			     * City
			     */
			    $otkel = new PageGen('forms/input.tpl');
			    $otkel->set('label_for', 'otkel');
			    $otkel->set('label_value', $vavok->go('localization')->string('city'));
			    $otkel->set('input_id', 'otkel');
			    $otkel->set('input_name', 'otkel');
			    $otkel->set('input_value', $vavok->go('users')->user_info('city'));

			    /**
			     * Street
			     */
			    $street = new PageGen('forms/input.tpl');
			    $street->set('label_for', 'street');
			    $street->set('label_value', $vavok->go('localization')->string('street'));
			    $street->set('input_id', 'street');
			    $street->set('input_name', 'street');
			    $street->set('input_value', $vavok->go('users')->user_info('address'));

			    /**
			     * Postal code
			     */
			    $zip = new PageGen('forms/input.tpl');
			    $zip->set('label_for', 'zip');
			    $zip->set('label_value', $vavok->go('localization')->string('postal'));
			    $zip->set('input_id', 'zip');
			    $zip->set('input_name', 'zip');
			    $zip->set('input_value', $vavok->go('users')->user_info('zip'));

			    /**
			     * About user
			     */
			    $infa = new PageGen('forms/input.tpl');
			    $infa->set('label_for', 'infa');
			    $infa->set('label_value', $vavok->go('localization')->string('aboutyou'));
			    $infa->set('input_id', 'infa');
			    $infa->set('input_name', 'infa');
			    $infa->set('input_value', $vavok->go('users')->user_info('about'));

			    /**
			     * Email
			     */
			    $email = new PageGen('forms/input.tpl');
			    $email->set('label_for', 'email');
			    $email->set('label_value', $vavok->go('localization')->string('yemail'));
			    $email->set('input_id', 'email');
			    $email->set('input_name', 'email');
			    $email->set('input_value', $vavok->go('users')->user_info('email'));

			    /**
			     * Site
			     */
			    $site = new PageGen('forms/input.tpl');
			    $site->set('label_for', 'site');
			    $site->set('label_value', $vavok->go('localization')->string('site'));
			    $site->set('input_id', 'site');
			    $site->set('input_name', 'site');
			    $site->set('input_value', $vavok->go('users')->user_info('site'));

			    /**
			     * Site
			     */
			    $birthday = new PageGen('forms/input.tpl');
			    $birthday->set('label_for', 'happy');
			    $birthday->set('label_value', $vavok->go('localization')->string('birthday') . ' (dd.mm.yyyy)');
			    $birthday->set('input_id', 'happy');
			    $birthday->set('input_name', 'happy');
			    $birthday->set('input_value', $vavok->go('users')->user_info('birthday'));

			    /**
			     * Gender
			     */
			    $gender_m = new PageGen('forms/radio_inline.tpl');
			    $gender_m->set('label_for', 'pol');
			    $gender_m->set('label_value', $vavok->go('localization')->string('sex') . ': ' . $vavok->go('localization')->string('male'));
			    $gender_m->set('input_id', 'pol');
			    $gender_m->set('input_name', 'pol');
			    $gender_m->set('input_value', 'm');
			    if ($vavok->go('users')->user_info('gender') == 'm') {
			        $gender_m->set('input_status', 'checked');
			    }

			    $gender_f = new PageGen('forms/radio_inline.tpl');
			    $gender_f->set('label_for', 'pol');
			    $gender_f->set('label_value',  $vavok->go('localization')->string('sex') . ': ' . $vavok->go('localization')->string('female'));
			    $gender_f->set('input_id', 'pol');
			    $gender_f->set('input_name', 'pol');
			    $gender_f->set('input_value', 'z');
			    if ($vavok->go('users')->user_info('gender') == 'z') {
			        $gender_f->set('input_status', 'checked');
			    }

			    $gender = new PageGen('forms/radio_group.tpl');
			    $gender->set('radio_group', $gender->merge(array($gender_m, $gender_f)));

			    $form->set('fields', $form->merge(array($my_name, $surname, $otkel, $street, $zip, $infa, $email, $site, $birthday, $gender)));
			    echo $form->output();

			    /**
			     * Change password
			     */
			    echo '<hr>';

			    $form = new PageGen('forms/form.tpl');
			    $form->set('form_method', 'post');
			    $form->set('form_action', 'newpass.php');

			    /**
			     * New pass
			     */
			    $newpar = new PageGen('forms/input.tpl');
			    $newpar->set('label_for', 'newpar');
			    $newpar->set('label_value', $vavok->go('localization')->string('newpass'));
			    $newpar->set('input_id', 'newpar');
			    $newpar->set('input_name', 'newpar');

			    /**
			     * New pass agin
			     */
			    $newpar2 = new PageGen('forms/input.tpl');
			    $newpar2->set('label_for', 'newpar2');
			    $newpar2->set('label_value', $vavok->go('localization')->string('passagain'));
			    $newpar2->set('input_id', 'newpar2');
			    $newpar2->set('input_name', 'newpar2');

			    /**
			     * Current password
			     */
			    $oldpar = new PageGen('forms/input.tpl');
			    $oldpar->set('label_for', 'oldpar');
			    $oldpar->set('label_value', $vavok->go('localization')->string('oldpass'));
			    $oldpar->set('input_id', 'oldpar');
			    $oldpar->set('input_name', 'oldpar');

			    $form->set('fields', $form->merge(array($newpar, $newpar2, $oldpar)));
			    echo $form->output();

			echo '</div>';

			echo '<div class="row">';
				echo '<p><a href="profile.php?action=delete_profile" class="btn btn-danger">' . $vavok->go('localization')->string('deleteProfile') . '</a></p>';
			echo '</div>';

		echo '</div>';

		echo '<div class="col-sm">';
		    echo '<div class="photo">';
		    if (!empty($vavok->go('users')->user_info('photo'))) {
		        echo '<img src="../' . $vavok->go('users')->user_info('photo') . '" alt="User\'s photo" /><br /> ';
		        echo $vavok->sitelink('photo.php', 'Change photo') . '<br />';
				echo $vavok->sitelink('photo.php?action=remove', 'Remove photo'); // update lang
		    } else {
		        echo '<img src="../images/img/no_picture.jpg" alt="" /><br /> ';
		        echo $vavok->sitelink('photo.php', 'Change photo');
		    }
		    echo '</div>';
		echo '</div>';
		echo '</div>';

		echo $vavok->homelink('<p>', '</p>');

		$vavok->require_footer();
}

?>