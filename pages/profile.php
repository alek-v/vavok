<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   29.08.2020. 1:33:34
 */

require_once"../include/startup.php";

if (!$users->is_reg()) $vavok->redirect_to("../?isset=inputoff");

$action = isset($_GET['action']) ? $vavok->check($_GET['action']) : '';

switch ($action) {
	case 'save':
		if (!empty($_POST['site']) && !$vavok->validateURL($_POST['site'])) $vavok->redirect_to("profile.php?isset=insite");

		// check email
		if (!empty($_POST['email']) && !$users->validate_email($_POST['email'])) $vavok->redirect_to("profile.php?isset=noemail");

		// check birthday
		// if (!empty($happy) && !preg_match("/^[0-9]{2}+\.[0-9]{2}+\.([0-9]{2}|[0-9]{4})$/",$happy)){header ("Location: profile.php?isset=inhappy"); exit;}

		$my_name = $vavok->no_br($vavok->check($_POST['my_name']));
		$surname = $vavok->no_br($vavok->check($_POST['surname']));
		$city = $vavok->no_br($vavok->check($_POST['otkel']));
		$street = $vavok->no_br($vavok->check($_POST['street']));
		$zip = $vavok->no_br($vavok->check($_POST['zip']));
		$infa = $vavok->no_br($vavok->check($_POST['infa']));
		$email = htmlspecialchars(strtolower($_POST['email']));
		$site = $vavok->no_br($vavok->check($_POST['site']));
		$browser = $vavok->no_br($vavok->check($users->user_browser()));
		$ip = $vavok->no_br($vavok->check($users->find_ip()));
		$sex = $vavok->no_br($vavok->check($_POST['pol']));
		$happy = $vavok->no_br($vavok->check($_POST['happy']));

		$fields = array();
		$fields[] = 'city';
		$fields[] = 'about';
		$fields[] = 'email';
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
		$values[] = $email;
		$values[] = $site;
		$values[] = $sex;
		$values[] = $happy;
		$values[] = $my_name;
		$values[] = $surname;
		$values[] = $street;
		$values[] = $zip;

		$db->update('vavok_about', $fields, $values, "uid='{$users->user_id}'");

		$vavok->redirect_to("./profile.php?isset=editprofil");
		break;

	case 'delete_profile':

		$confirmed = isset($_GET['confirmed']) ? $vavok->check($_GET['confirmed']) : '';

		if ($confirmed == 'yes') {
			$delete_id = $users->user_id;

			$users->logout($delete_id);
			$users->delete_user($delete_id);

			$vavok->redirect_to(HOMEDIR);
		}

		$current_page->page_title = $localization->string('deleteProfile');
		$vavok->require_header();

		echo '<p>' . $localization->string('deleteConfirm') . '</p>';

		echo '<p><a href="profile.php?action=delete_profile&confirmed=yes" class="btn btn-danger">' . $localization->string('deleteProfile') . '</a></p>';
		echo '<p><a href="profile.php" class="btn btn-primary">' . $localization->string('back') . '</a></p>';

		echo $vavok->homelink('<p>', '</p>');

		$vavok->require_footer();

		break;

	default:
		$current_page->append_head_tags('
		<style type="text/css">
		    .photo img {
		        max-width: 100px;
		        max-height: 100px;
		        overflow: hidden;
		    }
		</style>
		');

		$current_page->page_title = $localization->string('profsettings');
		$vavok->require_header();

		echo '<div class="row">';

		$about_user = $db->get_data('vavok_about', "uid='{$users->user_id}'");
		$user_profil = $db->get_data('vavok_profil', "uid='{$users->user_id}'", 'subscri');
		$show_user = $db->get_data('vavok_users', "id='{$users->user_id}'", 'skin, banned, browsers');

		echo '<div class="col-sm">';

			echo '<div class="row">';

			    echo '<h1>' . $localization->string('profsettings') . '</h1>';

			    $form = new PageGen('forms/form.tpl');
			    $form->set('form_method', 'post');
			    $form->set('form_action', 'profile.php?action=save');

			    /**
			     * Name
			     */
			    $my_name = new PageGen('forms/input.tpl');
			    $my_name->set('label_for', 'my_name');
			    $my_name->set('label_value', $localization->string('name'));
			    $my_name->set('input_id', 'my_name');
			    $my_name->set('input_name', 'my_name');
			    $my_name->set('input_value', $about_user['rname']);

			    /**
			     * Last name
			     */
			    $surname = new PageGen('forms/input.tpl');
			    $surname->set('label_for', 'surname');
			    $surname->set('label_value', $localization->string('surname'));
			    $surname->set('input_id', 'surname');
			    $surname->set('input_name', 'surname');
			    $surname->set('input_value', $about_user['surname']);

			    /**
			     * City
			     */
			    $otkel = new PageGen('forms/input.tpl');
			    $otkel->set('label_for', 'otkel');
			    $otkel->set('label_value', $localization->string('city'));
			    $otkel->set('input_id', 'otkel');
			    $otkel->set('input_name', 'otkel');
			    $otkel->set('input_value', $about_user['city']);

			    /**
			     * Street
			     */
			    $street = new PageGen('forms/input.tpl');
			    $street->set('label_for', 'street');
			    $street->set('label_value', $localization->string('street'));
			    $street->set('input_id', 'street');
			    $street->set('input_name', 'street');
			    $street->set('input_value', $about_user['address']);

			    /**
			     * Postal code
			     */
			    $zip = new PageGen('forms/input.tpl');
			    $zip->set('label_for', 'zip');
			    $zip->set('label_value', $localization->string('postal'));
			    $zip->set('input_id', 'zip');
			    $zip->set('input_name', 'zip');
			    $zip->set('input_value', $about_user['zip']);

			    /**
			     * About user
			     */
			    $infa = new PageGen('forms/input.tpl');
			    $infa->set('label_for', 'infa');
			    $infa->set('label_value', $localization->string('aboutyou'));
			    $infa->set('input_id', 'infa');
			    $infa->set('input_name', 'infa');
			    $infa->set('input_value', $about_user['about']);

			    /**
			     * Email
			     */
			    $email = new PageGen('forms/input.tpl');
			    $email->set('label_for', 'email');
			    $email->set('label_value', $localization->string('yemail'));
			    $email->set('input_id', 'email');
			    $email->set('input_name', 'email');
			    $email->set('input_value', $about_user['email']);

			    /**
			     * Site
			     */
			    $site = new PageGen('forms/input.tpl');
			    $site->set('label_for', 'site');
			    $site->set('label_value', $localization->string('site'));
			    $site->set('input_id', 'site');
			    $site->set('input_name', 'site');
			    $site->set('input_value', $about_user['site']);

			    /**
			     * Site
			     */
			    $birthday = new PageGen('forms/input.tpl');
			    $birthday->set('label_for', 'happy');
			    $birthday->set('label_value', $localization->string('birthday') . ' (dd.mm.yyyy)');
			    $birthday->set('input_id', 'happy');
			    $birthday->set('input_name', 'happy');
			    $birthday->set('input_value', $about_user['birthday']);

			    /**
			     * Gender
			     */
			    $gender_m = new PageGen('forms/radio_inline.tpl');
			    $gender_m->set('label_for', 'pol');
			    $gender_m->set('label_value', $localization->string('sex') . ': ' . $localization->string('male'));
			    $gender_m->set('input_id', 'pol');
			    $gender_m->set('input_name', 'pol');
			    $gender_m->set('input_value', 'm');
			    if ($about_user['sex'] == 'm') {
			        $gender_m->set('input_status', 'checked');
			    }

			    $gender_f = new PageGen('forms/radio_inline.tpl');
			    $gender_f->set('label_for', 'pol');
			    $gender_f->set('label_value',  $localization->string('sex') . ': ' . $localization->string('female'));
			    $gender_f->set('input_id', 'pol');
			    $gender_f->set('input_name', 'pol');
			    $gender_f->set('input_value', 'z');
			    if ($about_user['sex'] == 'z') {
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
			    $newpar->set('label_value', $localization->string('newpass'));
			    $newpar->set('input_id', 'newpar');
			    $newpar->set('input_name', 'newpar');

			    /**
			     * New pass agin
			     */
			    $newpar2 = new PageGen('forms/input.tpl');
			    $newpar2->set('label_for', 'newpar2');
			    $newpar2->set('label_value', $localization->string('passagain'));
			    $newpar2->set('input_id', 'newpar2');
			    $newpar2->set('input_name', 'newpar2');

			    /**
			     * Current password
			     */
			    $oldpar = new PageGen('forms/input.tpl');
			    $oldpar->set('label_for', 'oldpar');
			    $oldpar->set('label_value', $localization->string('oldpass'));
			    $oldpar->set('input_id', 'oldpar');
			    $oldpar->set('input_name', 'oldpar');

			    $form->set('fields', $form->merge(array($newpar, $newpar2, $oldpar)));
			    echo $form->output();

			echo '</div>';

			echo '<div class="row">';
				echo '<p><a href="profile.php?action=delete_profile" class="btn btn-danger">' . $localization->string('deleteProfile') . '</a></p>';
			echo '</div>';

		echo '</div>';

		echo '<div class="col-sm">';
		    echo '<div class="photo">';
		    if (!empty($about_user['photo'])) {
		        echo '<img src="../' . $about_user['photo'] . '" alt=""><br /> ';
		        echo $vavok->sitelink('photo.php', 'Change photo') . '<br />';
				echo $vavok->sitelink('photo.php?action=remove', 'Remove photo'); // update lang
		    } else {
		        echo '<img src="../images/img/no_picture.jpg" alt="" /><br /> ';
		        echo $vavok->sitelink('photo.php', 'Change photo');
		    }
		    echo '</div>';

		echo '</div>';

		echo '</div>';

		$vavok->homelink('<p>', '</p>');

		$vavok->require_footer();

		break;
}

?>