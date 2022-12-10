<?php

use App\Classes\BaseModel;
use App\Traits\Validations;
use App\Traits\Notifications;

class ProfileModel extends BaseModel {
    use Validations, Notifications;

    /**
     * Profile page
     */
    public function index()
    {
        $data['headt'] = '
        <style>
            .photo img {
                max-width: 100px;
                max-height: 100px;
                overflow: hidden;
            }
        </style>
        ';
        $data['tname'] = $this->localization->string('profsettings');

        $form = $this->container['parse_page'];
        $form->load('forms/form');
        $form->set('form_method', 'post');
        $form->set('form_action', HOMEDIR . 'profile/save');

        /**
         * Name
         */
        $my_name = $this->container['parse_page'];
        $my_name->load('forms/input');
        $my_name->set('label_for', 'my_name');
        $my_name->set('label_value', $this->localization->string('name'));
        $my_name->set('input_id', 'my_name');
        $my_name->set('input_name', 'my_name');
        $my_name->set('input_value', $this->user->userInfo('firstname'));

        /**
         * Last name
         */
        $surname = $this->container['parse_page'];
        $surname->load('forms/input');
        $surname->set('label_for', 'surname');
        $surname->set('label_value', $this->localization->string('surname'));
        $surname->set('input_id', 'surname');
        $surname->set('input_name', 'surname');
        $surname->set('input_value', $this->user->userInfo('lastname'));

        /**
         * City
         */
        $otkel = $this->container['parse_page'];
        $otkel->load('forms/input');
        $otkel->set('label_for', 'otkel');
        $otkel->set('label_value', $this->localization->string('city'));
        $otkel->set('input_id', 'otkel');
        $otkel->set('input_name', 'otkel');
        $otkel->set('input_value', $this->user->userInfo('city'));

        /**
         * Street
         */
        $street = $this->container['parse_page'];
        $street->load('forms/input');
        $street->set('label_for', 'street');
        $street->set('label_value', $this->localization->string('street'));
        $street->set('input_id', 'street');
        $street->set('input_name', 'street');
        $street->set('input_value', $this->user->userInfo('address'));

        /**
         * Postal code
         */
        $zip = $this->container['parse_page'];
        $zip->load('forms/input');
        $zip->set('label_for', 'zip');
        $zip->set('label_value', $this->localization->string('postal'));
        $zip->set('input_id', 'zip');
        $zip->set('input_name', 'zip');
        $zip->set('input_value', $this->user->userInfo('zip'));

        /**
         * Timezone
         */
        $timezone = $this->container['parse_page'];
        $timezone->load('forms/input');
        $timezone->set('label_for', 'timezone');
        $timezone->set('label_value', $this->localization->string('timezone'));
        $timezone->set('input_id', 'timezone');
        $timezone->set('input_name', 'timezone');
        $timezone->set('input_value', $this->user->userInfo('timezone'));

        /**
         * About user
         */
        $infa = $this->container['parse_page'];
        $infa->load('forms/input');
        $infa->set('label_for', 'infa');
        $infa->set('label_value', $this->localization->string('aboutyou'));
        $infa->set('input_id', 'infa');
        $infa->set('input_name', 'infa');
        $infa->set('input_value', $this->user->userInfo('about'));

        /**
         * Email
         */
        $email = $this->container['parse_page'];
        $email->load('forms/input');
        $email->set('label_for', 'email');
        $email->set('label_value', $this->localization->string('yemail'));
        $email->set('input_id', 'email');
        $email->set('input_name', 'email');
        $email->set('input_value', $this->user->userInfo('email'));

        /**
         * Site
         */
        $site = $this->container['parse_page'];
        $site->load('forms/input');
        $site->set('label_for', 'site');
        $site->set('label_value', $this->localization->string('site'));
        $site->set('input_id', 'site');
        $site->set('input_name', 'site');
        $site->set('input_value', $this->user->userInfo('site'));

        /**
         * Site
         */
        $birthday = $this->container['parse_page'];
        $birthday->load('forms/input');
        $birthday->set('label_for', 'happy');
        $birthday->set('label_value', $this->localization->string('birthday') . ' (dd.mm.yyyy)');
        $birthday->set('input_id', 'happy');
        $birthday->set('input_name', 'happy');
        $birthday->set('input_value', $this->user->userInfo('birthday'));

        /**
         * Gender
         */
        $gender_m = $this->container['parse_page'];
        $gender_m->load('forms/radio_inline');
        $gender_m->set('label_for', 'pol');
        $gender_m->set('label_value', $this->localization->string('sex') . ': ' . $this->localization->string('male'));
        $gender_m->set('input_id', 'pol');
        $gender_m->set('input_name', 'pol');
        $gender_m->set('input_value', 'm');
        if ($this->user->userInfo('gender') == 'm') $gender_m->set('input_status', 'checked');

        $gender_f = $this->container['parse_page'];
        $gender_f->load('forms/radio_inline');
        $gender_f->set('label_for', 'pol');
        $gender_f->set('label_value',  $this->localization->string('sex') . ': ' . $this->localization->string('female'));
        $gender_f->set('input_id', 'pol');
        $gender_f->set('input_name', 'pol');
        $gender_f->set('input_value', 'z');
        if ($this->user->userInfo('gender') == 'z') $gender_f->set('input_status', 'checked');

        $gender = $this->container['parse_page'];
        $gender->load('forms/radio_group');
        $gender->set('radio_group', $gender->merge(array($gender_m, $gender_f)));

        $form->set('fields', $form->merge(array($my_name, $surname, $otkel, $street, $zip, $timezone, $infa, $email, $site, $birthday, $gender)));
        $data['profile_form'] = $form->output();

        /**
         * Change password
         */
        $form= $this->container['parse_page'];
        $form->load('forms/form');
        $form->set('form_method', 'post');
        $form->set('form_action', HOMEDIR . 'profile/newpass');

        /**
         * New pass
         */
        $newpar= $this->container['parse_page'];
        $newpar->load('forms/input');
        $newpar->set('label_for', 'newpar');
        $newpar->set('label_value', $this->localization->string('newpass'));
        $newpar->set('input_id', 'newpar');
        $newpar->set('input_name', 'newpar');

        /**
         * New pass agin
         */
        $newpar2= $this->container['parse_page'];
        $newpar2->load('forms/input');
        $newpar2->set('label_for', 'newpar2');
        $newpar2->set('label_value', $this->localization->string('passagain'));
        $newpar2->set('input_id', 'newpar2');
        $newpar2->set('input_name', 'newpar2');

        /**
         * Current password
         */
        $oldpar= $this->container['parse_page'];
        $oldpar->load('forms/input');
        $oldpar->set('label_for', 'oldpar');
        $oldpar->set('label_value', $this->localization->string('oldpass'));
        $oldpar->set('input_id', 'oldpar');
        $oldpar->set('input_name', 'oldpar');

        $form->set('fields', $form->merge(array($newpar, $newpar2, $oldpar)));
        $data['change_password'] = $form->output();

        if (!empty($this->user->userInfo('photo'))) {
            $data['profile_photo'] = '<img src="../' . $this->user->userInfo('photo') . '" alt="User\'s photo" /><br /> ';
            $data['profile_photo'] .= $this->sitelink(HOMEDIR . 'profile/photo', 'Change photo') . '<br />';
            $data['profile_photo'] .= $this->sitelink(HOMEDIR . 'profile/removephoto', 'Remove photo'); // update lang
        } else {
            $data['profile_photo'] = '<img src="../themes/images/img/no_picture.jpg" alt="No profile picture" /><br /> ';
            $data['profile_photo'] .= $this->sitelink(HOMEDIR . 'profile/photo', 'Change photography');
        }

        // Pass page to the view
        return $data;
    }

    /**
     * Update profile
     */
    public function save()
    {
        if (!empty($this->postAndGet('site')) && !$this->validateUrl($this->postAndGet('site'))) $this->redirection(HOMEDIR. 'profile?isset=insite');

        // check email
        if (!empty($this->postAndGet('email')) && !$this->validateEmail($this->postAndGet('email'))) $this->redirection(HOMEDIR . 'profile?isset=noemail');

        $my_name = $this->replaceNewLines($this->postAndGet('my_name'));
        $surname = $this->replaceNewLines($this->postAndGet('surname'));
        $city = $this->replaceNewLines($this->postAndGet('otkel'));
        $street = $this->replaceNewLines($this->postAndGet('street'));
        $zip = $this->replaceNewLines($this->postAndGet('zip'));
        $infa = $this->replaceNewLines($this->postAndGet('infa'));
        $email = htmlspecialchars(strtolower($this->postAndGet('email')));
        $site = $this->replaceNewLines($this->postAndGet('site'));
        $browser = $this->replaceNewLines($this->user->user_browser());
        $ip = $this->replaceNewLines($this->user->find_ip());
        $sex = $this->replaceNewLines($this->postAndGet('pol'));
        $happy = $this->replaceNewLines($this->postAndGet('happy'));
        $timezone = $this->replaceNewLines($this->postAndGet('timezone'));

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
        $fields[] = 'timezone';

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
        $values[] = $timezone;

        /**
         * Update profile data
         */
        $this->user->updateUser($fields, $values);

        /**
         * Send email confirmation link if it is changed and save data into database
         */
        if ($this->user->userInfo('email') != $email && $this->db->countRow('tokens', "uid = '{$this->user->user_id()}' AND content = '{$email}'") < 1) {
            /**
             * Insert data to database
             */
            $token = $this->generatePassword();

            $now = new DateTime();
            $now->add(new DateInterval("P1D"));
            $new_time = $now->format('Y-m-d H:i:s');

            $data = array(
                'uid' => $this->user->user_id(),
                'type' => 'email',
                'content' => $email,
                'token' => $token,
                'expiration_time' => $new_time
            );

            $this->db->insert('tokens', $data);

            /**
             * Add email to the queue
             */
            $mailQueue = new Mailer;

            $msg = "Hello {$this->user->show_username()}<br /><br />
            In order to add this email to your profile at site {$this->websiteHomeAddress()}
            please follow link to confirm email address " . '<a href="' . $this->websiteHomeAddress() . '/profile/confirm_email/?token=' . $token . '">' . $this->websiteHomeAddress() . '/profile/confirm_email/?token=' . $token . '</a>';
            $msg .= '<br /><br />If you received this email by mistake please ignore it.';

            $mailQueue->queueEmail($email, 'Confirm new email address', $msg);
        }

        $this->redirection('./profile');
    }

    /**
     * Delete profile
     */
    public function delete()
    {
        if ($this->postAndGet('confirmed') == 'yes') {
            $delete_id = $this->user->user_id();

            $this->user->deleteUser($delete_id);
            $this->user->logout($delete_id);

            $this->redirection(HOMEDIR);
        }

        // Page title
        $data['tname'] = '{@localization[deleteProfile]}}';

        // Pass page to the view
        return $data;
    }

    /**
     * New password
     */
    public function newpass()
    {
        $data['tname'] = '{@localization[profile]}}';

        // Passwords from both password fields should match
        if ($this->postAndGet('newpar') !== $this->postAndGet('newpar2'))
        {
            $data['content'] = $this->showDanger('{@localization[nonewpass]}}');

            // Pass page to the view
            $this->view('profile/newpassword', $data);
            exit;
        }

        // Check if old password is correct and update users password with new password
        if ($this->user->password_check($this->postAndGet('oldpar', true), $this->user->userInfo('password'))) {
            // Update password
            $this->user->updateUser('pass', $this->user->password_encrypt($this->postAndGet('newpar', true)));

            $this->redirection($this->websiteHomeAddress() . "/users/login");
        } else {
            $data['content'] = $this->showDanger('{@localization[nopass]}}');
        }

        // Pass page to the view
        return $data;
    }

    /**
     * Profile photo
     */
    public function photo()
    {
        $data['tname'] = 'Change Photo';
        $data['headt'] = '<style>
        .photo img {
            max-width: 320px;
            max-height: 320px;
            overflow: hidden;
        }
        </style>';
        $data['content'] = '';

        if (!empty($this->user->userInfo('photo'))) {
            $data['photo'] = '<div class="photo">';
            $data['photo'] .= '<h1>Your photo</h1><br /><img src="../' . $this->user->userInfo('photo') . '" alt="Users photo" />';
            $data['photo'] .= '</div>';
        }

        $form = $this->container['parse_page'];
        $form->load('forms/form_upload');
        $form->set('form_action', HOMEDIR . 'profile/savephoto');
        $form->set('form_method', 'post');
        $form->set('form_name', 'form');

        $input = $this->container['parse_page'];
        $input->load('forms/input');
        $input->set('label_for', 'file');
        $input->set('label_value', 'Change your profile photography:');
        $input->set('input_type', 'file');
        $input->set('input_name', 'file');

        $form->set('fields', $input->output());

        $data['content'] .= $form->output();

        // Pass page to the view
        return $data;
    }

    /**
     * Save photography
     */
    public function savephoto()
    {
        // Page data
        $data['tname'] = 'Change Photography';
        $data['headt'] = '
        <style>
            .photo img {
                max-width: 100px;
                max-height: 100px;
                overflow: hidden;
            }
        </style>
        ';
        $data['content'] = '';

        // File path cannot be empty
        if (empty($_FILES['file']['tmp_name'])) $this->redirection(HOMEDIR . 'profile/photo');

        // Uploading
        $avat_size = $_FILES['file']['size'];
        $avat_name = $_FILES['file']['name'];
        $size = GetImageSize($_FILES['file']['tmp_name']);
        $width = $size[0];
        $height = $size[1];
        $av_file = file($_FILES['file']['tmp_name']);
        $av_string = substr($avat_name, strrpos($avat_name, '.') + 1);
        $av_string = strtolower($av_string);

        if ($avat_size < 5242880) {
            if ($width < 1024 && $height < 1024) {
                if ($av_string == "gif" || $av_string == "jpg" || $av_string == "jpeg" || $av_string == "png") {
                    if ($av_file) {
                        // Remove old photo
                        if (file_exists(STORAGEDIR . "dataphoto/" . $this->user->user_id() . ".jpg")) {
                            unlink(STORAGEDIR . "dataphoto/" . $this->user->user_id() . ".jpg");
                        } elseif (file_exists(STORAGEDIR . "dataphoto/" . $this->user->user_id() . ".png")) {
                            unlink(STORAGEDIR . "dataphoto/" . $this->user->user_id() . ".png");
                        } elseif (file_exists(STORAGEDIR . "dataphoto/" . $this->user->user_id() . ".gif")) {
                            unlink(STORAGEDIR . "dataphoto/" . $this->user->user_id() . ".gif");
                        } elseif (file_exists(STORAGEDIR . "dataphoto/" . $this->user->user_id() . ".jpeg")) {
                            unlink(STORAGEDIR . "dataphoto/" . $this->user->user_id() . ".jpeg");
                        }

                        // Add new photo
                        copy($_FILES['file']['tmp_name'], STORAGEDIR . "dataphoto/" . $this->user->user_id() . "." . $av_string);
                        $ch = $_FILES['file']['tmp_name'];
                        chmod($ch, 0777);
                        chmod(STORAGEDIR . "dataphoto/" . $this->user->user_id() . "." . $av_string . "", 0777);

                        $this->user->updateUser('photo', 'gallery/photo/' . $this->user->user_id());

                        $data['user_id'] = $this->user->user_id();

                        // Pass page to the view
                        $this->view('profile/savephoto', $data);
                        exit;
                    } else {
                        $data['content'] .= $this->showDanger('Error uploading photography');
                    }
                } else {
                    $data['content'] .= $this->showDanger($this->localization->string('badfileext'));
                }
            } else {
                $data['content'] .= $this->showDanger('Photography must be under 1024px');
            }
        } else {
            $data['content'] .= $this->showDanger($this->localization->string('filemustb') . ' under 5 MB');
        }

        $data['content'] .= $this->sitelink(HOMEDIR . 'profile/photo', $this->localization->string('back'), '<p>', '</p>');

        // Pass page to the view
        return $data;
    }

    /**
     * Remove profile photo
     */
    public function removephoto()
    {
        // Page data
        $data['tname'] = 'Remove Photography';
        $data['content'] = '';

        if (file_exists(STORAGEDIR . "dataphoto/" . $this->user->user_id() . ".jpg")) {
            unlink(STORAGEDIR . "dataphoto/" . $this->user->user_id() . ".jpg");
        } elseif (file_exists(STORAGEDIR . "dataphoto/" . $this->user->user_id() . ".png")) {
            unlink(STORAGEDIR . "dataphoto/" . $this->user->user_id() . ".png");
        } elseif (file_exists(STORAGEDIR . "dataphoto/" . $this->user->user_id() . ".gif")) {
            unlink(STORAGEDIR . "dataphoto/" . $this->user->user_id() . ".gif");
        } elseif (file_exists(STORAGEDIR . "dataphoto/" . $this->user->user_id() . ".jpeg")) {
            unlink(STORAGEDIR . "dataphoto/" . $this->user->user_id() . ".jpeg");
        }

        // Update database
        $this->user->updateUser('photo', '');

        $data['content'] .= $this->showSuccess('Your photography has been successfully deleted!'); // update lang
        $data['content'] .= $this->sitelink(HOMEDIR . 'profile', $this->localization->string('back'), '<p>', '</p>');

        // Pass page to the view
        return $data;
    }

    /**
     * Confirm email
     */
    public function confirm_email()
    {
        // Users data
        $this_page['user'] = $this->user_data;

        // Page data
        $this_page['tname'] = 'Confirm email address';
        $this_page['content'] = '';

        // Token does not exist
        if ($this->db->countRow('tokens', "type = 'email' AND token = '{$this->postAndGet('token')}'") < 1) {
            $this_page['content'] .= $this->showDanger('{@localization[notoken]}}');

            return $this_page;
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