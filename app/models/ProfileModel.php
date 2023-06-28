<?php

use App\Classes\BaseModel;
use App\Classes\Mailer;
use App\Traits\Validations;
use App\Traits\Notifications;

class ProfileModel extends BaseModel {
    use Validations, Notifications;

    /**
     * Profile page
     */
    public function index()
    {
        if (!$this->user->userAuthenticated()) {
            $this->redirection(HOMEDIR . 'users/login');
        }

        $this->page_data['head_tags'] = '<style>
            .photo img {
                max-width: 130px;
                max-height: 130px;
            }
        </style>';
        $this->page_data['page_title'] = $this->localization->string('profile_settings');

        $form = $this->container['parse_page'];
        $form->load('forms/form');
        $form->set('form_method', 'post');
        $form->set('form_action', HOMEDIR . 'profile/save');

        // Name
        $my_name = $this->container['parse_page'];
        $my_name->load('forms/input');
        $my_name->set('label_for', 'my_name');
        $my_name->set('label_value', $this->localization->string('name'));
        $my_name->set('input_id', 'my_name');
        $my_name->set('input_name', 'my_name');
        $my_name->set('input_value', $this->user->userInfo('first_name'));

        // Last name
        $surname = $this->container['parse_page'];
        $surname->load('forms/input');
        $surname->set('label_for', 'surname');
        $surname->set('label_value', $this->localization->string('last_name'));
        $surname->set('input_id', 'surname');
        $surname->set('input_name', 'surname');
        $surname->set('input_value', $this->user->userInfo('last_name'));

        // City
        $city = $this->container['parse_page'];
        $city->load('forms/input');
        $city->set('label_for', 'city');
        $city->set('label_value', $this->localization->string('city'));
        $city->set('input_id', 'city');
        $city->set('input_name', 'city');
        $city->set('input_value', $this->user->userInfo('city'));

        // Street
        $street = $this->container['parse_page'];
        $street->load('forms/input');
        $street->set('label_for', 'street');
        $street->set('label_value', $this->localization->string('street'));
        $street->set('input_id', 'street');
        $street->set('input_name', 'street');
        $street->set('input_value', $this->user->userInfo('address'));

        // Postal code
        $zip = $this->container['parse_page'];
        $zip->load('forms/input');
        $zip->set('label_for', 'zip');
        $zip->set('label_value', $this->localization->string('postal'));
        $zip->set('input_id', 'zip');
        $zip->set('input_name', 'zip');
        $zip->set('input_value', $this->user->userInfo('zip'));

        // Timezone
        $timezone = $this->container['parse_page'];
        $timezone->load('forms/input');
        $timezone->set('label_for', 'timezone');
        $timezone->set('label_value', $this->localization->string('timezone'));
        $timezone->set('input_id', 'timezone');
        $timezone->set('input_name', 'timezone');
        $timezone->set('input_value', $this->user->userInfo('timezone'));

         // About user
        $about_user = $this->container['parse_page'];
        $about_user->load('forms/textarea');
        $about_user->set('label_for', 'about_user');
        $about_user->set('label_value', $this->localization->string('aboutyou'));
        $about_user->set('textarea_id', 'about_user');
        $about_user->set('textarea_name', 'about_user');
        $about_user->set('textarea_value', $this->user->userInfo('about'));

        // Email
        $email = $this->container['parse_page'];
        $email->load('forms/input');
        $email->set('label_for', 'email');
        $email->set('label_value', $this->localization->string('email'));
        $email->set('input_id', 'email');
        $email->set('input_name', 'email');
        $email->set('input_value', $this->user->userInfo('email'));

        // Site
        $site = $this->container['parse_page'];
        $site->load('forms/input');
        $site->set('label_for', 'site');
        $site->set('label_value', $this->localization->string('site'));
        $site->set('input_id', 'site');
        $site->set('input_name', 'site');
        $site->set('input_value', $this->user->userInfo('site'));

        // Birthday
        $birthday = $this->container['parse_page'];
        $birthday->load('forms/input');
        $birthday->set('label_for', 'happy');
        $birthday->set('label_value', $this->localization->string('birthday') . ' (dd.mm.yyyy)');
        $birthday->set('input_id', 'happy');
        $birthday->set('input_name', 'happy');
        $birthday->set('input_value', $this->user->userInfo('birthday'));

        // Gender
        $gender_m = $this->container['parse_page'];
        $gender_m->load('forms/radio_inline');
        $gender_m->set('label_for', 'gender');
        $gender_m->set('label_value', $this->localization->string('sex') . ': ' . $this->localization->string('male'));
        $gender_m->set('input_id', 'gender');
        $gender_m->set('input_name', 'gender');
        $gender_m->set('input_value', 'm');
        if ($this->user->userInfo('gender') == 'm') $gender_m->set('input_status', 'checked');

        $gender_f = $this->container['parse_page'];
        $gender_f->load('forms/radio_inline');
        $gender_f->set('label_for', 'gender');
        $gender_f->set('label_value',  $this->localization->string('sex') . ': ' . $this->localization->string('female'));
        $gender_f->set('input_id', 'gender');
        $gender_f->set('input_name', 'gender');
        $gender_f->set('input_value', 'z');
        if ($this->user->userInfo('gender') == 'z') $gender_f->set('input_status', 'checked');

        $gender = $this->container['parse_page'];
        $gender->load('forms/radio_group');
        $gender->set('radio_group', $gender->merge(array($gender_m, $gender_f)));

        $form->set('fields', $form->merge(array($my_name, $surname, $city, $street, $zip, $timezone, $about_user, $email, $site, $birthday, $gender)));
        $this->page_data['profile_form'] = $form->output();

        // Change password
        $form = $this->container['parse_page'];
        $form->load('forms/form');
        $form->set('form_method', 'post');
        $form->set('form_action', HOMEDIR . 'profile/newpass');

        // New pass
        $newpar = $this->container['parse_page'];
        $newpar->load('forms/input');
        $newpar->set('label_for', 'newpar');
        $newpar->set('label_value', $this->localization->string('newpass'));
        $newpar->set('input_id', 'newpar');
        $newpar->set('input_name', 'newpar');

        // New pass again
        $newpar2 = $this->container['parse_page'];
        $newpar2->load('forms/input');
        $newpar2->set('label_for', 'newpar2');
        $newpar2->set('label_value', $this->localization->string('passagain'));
        $newpar2->set('input_id', 'newpar2');
        $newpar2->set('input_name', 'newpar2');

        // Current password
        $oldpar = $this->container['parse_page'];
        $oldpar->load('forms/input');
        $oldpar->set('label_for', 'oldpar');
        $oldpar->set('label_value', $this->localization->string('oldpass'));
        $oldpar->set('input_id', 'oldpar');
        $oldpar->set('input_name', 'oldpar');

        $form->set('fields', $form->merge(array($newpar, $newpar2, $oldpar)));
        $this->page_data['change_password'] = $form->output();

        if (!empty($this->user->userInfo('photo'))) {
            $this->page_data['profile_photo'] = '<img src="../' . $this->user->userInfo('photo') . '" alt="User\'s photo" /><br /> ';
            $this->page_data['profile_photo'] .= $this->sitelink(HOMEDIR . 'profile/photo', 'Change photo') . '<br />';
            $this->page_data['profile_photo'] .= $this->sitelink(HOMEDIR . 'profile/removephoto', 'Remove photo'); // update lang
        } else {
            $this->page_data['profile_photo'] = '<img src="../themes/images/img/no_picture.jpg" alt="No profile picture" /><br /> ';
            $this->page_data['profile_photo'] .= $this->sitelink(HOMEDIR . 'profile/photo', 'Change photography');
        }

        // Pass page to the view
        return $this->page_data;
    }

    /**
     * Update profile
     */
    public function save()
    {
        if (!$this->user->userAuthenticated()) {
            $this->redirection(HOMEDIR . 'users/login');
        }

        if (!empty($this->postAndGet('site')) && !$this->validateUrl($this->postAndGet('site'))) {
            $this->redirection(HOMEDIR. 'profile/?isset=insite');
        }

        // check email
        if (!empty($this->postAndGet('email')) && !$this->validateEmail($this->postAndGet('email'))) {
            $this->redirection(HOMEDIR . 'profile/?isset=noemail');
        }

        $email = strtolower($this->postAndGet('email'));

        $fields = array();
        $fields[] = 'city';
        $fields[] = 'about';
        $fields[] = 'site';
        $fields[] = 'sex';
        $fields[] = 'birthday';
        $fields[] = 'first_name';
        $fields[] = 'last_name';
        $fields[] = 'address';
        $fields[] = 'zip';
        $fields[] = 'timezone';

        $values = array();
        $values[] = $this->postAndGet('city');
        $values[] = $this->postAndGet('about_user');
        $values[] = $this->postAndGet('site');
        $values[] = $this->postAndGet('gender');
        $values[] = $this->postAndGet('happy');
        $values[] = $this->postAndGet('my_name');
        $values[] = $this->postAndGet('surname');
        $values[] = $this->postAndGet('street');
        $values[] = $this->postAndGet('zip');
        $values[] = $this->postAndGet('timezone');

        // Update profile data
        $this->user->updateUser($fields, $values);

        // Send email confirmation link if it is changed and save data into database
        if ($this->user->userInfo('email') != $email && $this->db->countRow('tokens', "uid = '{$this->user->userIdNumber()}' AND content = '{$email}'") < 1) {
            // Insert data to database
            $token = $this->generatePassword();

            $now = new DateTime();
            $now->add(new DateInterval("P1D"));
            $new_time = $now->format('Y-m-d H:i:s');

            $data = array(
                'uid' => $this->user->userIdNumber(),
                'type' => 'email',
                'content' => $email,
                'token' => $token,
                'expiration_time' => $new_time
            );

            $this->db->insert('tokens', $data);

            // Add email to the queue
            $mailQueue = new Mailer();

            $msg = "Hello {$this->user->showUsername()}<br /><br />
            In order to add this email to your profile at site {$this->websiteHomeAddress()}
            please follow link to confirm email address " . '<a href="' . $this->websiteHomeAddress() . '/profile/confirm_email/?token=' . $token . '">' . $this->websiteHomeAddress() . '/profile/confirm_email/?token=' . $token . '</a>';
            $msg .= '<br /><br />If you received this email by mistake please ignore it.';

            $mailQueue->queueEmail($email, 'Confirm new email address', $msg);
        }

        $this->redirection(HOMEDIR . 'profile');
    }

    /**
     * Delete profile
     */
    public function delete()
    {
        if (!$this->user->userAuthenticated()) {
            $this->redirection(HOMEDIR . 'users/login');
        }

        if ($this->postAndGet('confirmed') == 'yes') {
            $delete_id = $this->user->userIdNumber();

            $this->user->deleteUser($delete_id);

            $this->redirection(HOMEDIR);
        }

        // Page title
        $this->page_data['page_title'] = '{@localization[delete_profile]}}';

        // Pass page to the view
        return $this->page_data;
    }

    /**
     * New password
     */
    public function newpass()
    {
        $this->page_data['page_title'] = '{@localization[profile]}}';

        // Passwords from both password fields should match
        if ($this->postAndGet('newpar') !== $this->postAndGet('newpar2')) {
            $this->page_data['content'] = $this->showDanger('{@localization[nonewpass]}}');

            // Pass page to the view
            return $this->page_data;
        }

        // Check if old password is correct and update users password with new password
        if ($this->user->passwordCheck($this->postAndGet('oldpar', true), $this->user->userInfo('password'))) {
            // Update password
            $this->user->updateUser('pass', $this->user->passwordEncrypt($this->postAndGet('newpar', true)));

            $this->redirection($this->websiteHomeAddress() . "/users/login");
        } else {
            $this->page_data['content'] = $this->showDanger('{@localization[nopass]}}');
        }

        // Pass page to the view
        return $this->page_data;
    }

    /**
     * Profile photo
     */
    public function photo()
    {
        if (!$this->user->userAuthenticated()) {
            $this->redirection(HOMEDIR . 'users/login');
        }

        $this->page_data['page_title'] = 'Change Photo';
        $this->page_data['head_tags'] = '<style>
        .photo img {
            max-width: 320px;
            max-height: 320px;
            overflow: hidden;
        }
        </style>';

        if (!empty($this->user->userInfo('photo'))) {
            $this->page_data['photo'] = '<div class="photo">';
            $this->page_data['photo'] .= '<h1>Your photo</h1><br /><img src="../' . $this->user->userInfo('photo') . '" alt="Users photo" />';
            $this->page_data['photo'] .= '</div>';
        }

        $form = $this->container['parse_page'];
        $form->load('forms/form_upload');
        $form->set('form_action', HOMEDIR . 'profile/savephoto');
        $form->set('form_method', 'post');
        $form->set('form_name', 'form');

        $input = $this->container['parse_page'];
        $input->load('forms/input');
        $input->set('label_for', 'file');
        $input->set('label_value', 'Change your profile photography');
        $input->set('input_type', 'file');
        $input->set('input_name', 'file');

        $form->set('fields', $input->output());

        $this->page_data['content'] .= $form->output();

        // Pass page to the view
        return $this->page_data;
    }

    /**
     * Save photography
     */
    public function savephoto()
    {
        if (!$this->user->userAuthenticated()) {
            $this->redirection(HOMEDIR . 'users/login');
        }

        // Page data
        $this->page_data['page_title'] = 'Change Photography';
        $this->page_data['head_tags'] = '<style>
            .photo img {
                max-width: 100px;
                max-height: 100px;
                overflow: hidden;
            }
        </style>';

        // File path cannot be empty
        if (empty($_FILES['file']['tmp_name'])) {
            $this->redirection(HOMEDIR . 'profile/photo');
        }

        // Uploading
        $avat_size = $_FILES['file']['size'];
        $avat_name = $_FILES['file']['name'];
        $size = GetImageSize($_FILES['file']['tmp_name']);
        $width = $size[0];
        $height = $size[1];
        $av_file = file($_FILES['file']['tmp_name']);
        $av_string = substr($avat_name, strrpos($avat_name, '.') + 1);
        $av_string = strtolower($av_string);
        $upload_error = null;

        // Check file size
        if ($avat_size > 5242880) {
            $this->page_data['content'] .= $this->showDanger($this->localization->string('filemustb') . ' under 5 MB');

            $upload_error = 1;
        }

        // Check image size in pixels
        if ($width > 1024 || $height > 1024) {
            $this->page_data['content'] .= $this->showDanger('Photography must be 1024px or smaller.');

            $upload_error = 1;
        }

        // Allowed image formats
        if ($av_string != "gif" && $av_string != "jpg" && $av_string != "jpeg" && $av_string != "png") {
            $this->page_data['content'] .= $this->showDanger($this->localization->string('badfileext'));

            $upload_error = 1;
        }

        if ($upload_error == null) {
            // Remove old photo
            if (file_exists(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . ".jpg")) {
                unlink(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . ".jpg");
            } elseif (file_exists(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . ".png")) {
                unlink(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . ".png");
            } elseif (file_exists(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . ".gif")) {
                unlink(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . ".gif");
            } elseif (file_exists(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . ".jpeg")) {
                unlink(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . ".jpeg");
            }

            // Add new photo
            copy($_FILES['file']['tmp_name'], STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . "." . $av_string);
            $ch = $_FILES['file']['tmp_name'];
            chmod($ch, 0777);
            chmod(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . "." . $av_string . "", 0777);

            $this->user->updateUser('photo', 'gallery/photo/' . $this->user->userIdNumber());

            $this->page_data['user_id'] = $this->user->userIdNumber();

            // Pass page to the view
            $this->page_data['content'] .= $this->showNotification('Photography has been updated.');
        }

        $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'profile/photo', $this->localization->string('back'), '<p>', '</p>');

        // Pass page content to the view
        return $this->page_data;
    }

    /**
     * Remove profile photo
     */
    public function removephoto()
    {
        if (!$this->user->userAuthenticated()) {
            $this->redirection(HOMEDIR . 'users/login');
        }

        // Page data
        $this->page_data['page_title'] = 'Remove Photography';

        if (file_exists(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . ".jpg")) {
            unlink(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . ".jpg");
        } elseif (file_exists(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . ".png")) {
            unlink(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . ".png");
        } elseif (file_exists(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . ".gif")) {
            unlink(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . ".gif");
        } elseif (file_exists(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . ".jpeg")) {
            unlink(STORAGEDIR . "dataphoto/" . $this->user->userIdNumber() . ".jpeg");
        }

        // Update database
        $this->user->updateUser('photo', '');

        $this->page_data['content'] .= $this->showSuccess('Your photography has been successfully deleted!'); // update lang
        $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'profile', $this->localization->string('back'), '<p>', '</p>');

        // Pass page to the view
        return $this->page_data;
    }

    /**
     * Confirm email
     */
    public function confirm_email()
    {
        // Users data
        $this->page_data['user'] = $this->user_data;

        // Page data
        $this->page_data['page_title'] = 'Confirm email address';

        // Token does not exist
        if ($this->db->countRow('tokens', "type = 'email' AND token = '{$this->postAndGet('token')}'") < 1) {
            $this->page_data['content'] .= $this->showDanger('{@localization[notoken]}}');

            return $this->page_data;
        }

        // Get token data
        $data = $this->db->selectData('tokens', 'type = :type AND token = :token', [':type' => 'email', ':token' => $this->postAndGet('token')]);

        // Update email
        $this->user->updateUser('email', $data['content'], $data['uid']);

        // Remove token
        $this->db->delete('tokens', "type = 'email' AND token = '{$this->postAndGet('token')}'");

        $this->redirection(HOMEDIR . 'profile');
    }
}