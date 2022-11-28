<?php

use App\Classes\BaseModel;
use App\Classes\Navigation;
use App\Classes\Mailer;
use App\Traits\Validations;
use App\Traits\Notifications;

class UsersModel extends BaseModel {
    use Validations, Notifications;

    /**
     * @return array
     */
    public function register(): array
    {
        // Meta tag for this page
        $data['headt'] = '<meta name="robots" content="noindex">';
        // Page title
        $data['tname'] = '{@localization[registration]}}';
        $data['content'] = '';

        // Data has been sent, register the user
        if (!empty($this->postAndGet('log')) && !empty($this->postAndGet('par'))) {
            $username_length = mb_strlen($this->postAndGet('log'));
            $password_length = mb_strlen($this->postAndGet('par'));

            if ($username_length > 20) {
                $data['content'] .= $this->showDanger($this->localization->string('biginfo'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
                // Pass page to the view
                $data['page_template'] = 'users/register/register_try';
                return $data;
            } elseif ($username_length < 3 || $password_length < 3) {
                $data['content'] .= $this->showDanger($this->localization->string('smallinfo'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
                // Pass page to the view
                $data['page_template'] = 'users/register/register_try';
                return $data;
            } elseif (!$this->user->validate_username($this->postAndGet('log'))) {
                $data['content'] .= $this->showDanger($this->localization->string('useletter'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
                // Pass page to the view
                $data['page_template'] = 'users/register/register_try';
                return $data;
            } elseif ($this->postAndGet('par') !== $this->postAndGet('pars')) {
                $data['content'] .= $this->showDanger($this->localization->string('nonewpass'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
                // Pass page to the view
                $data['page_template'] = 'users/register/register_try';
                return $data;
            }
            // Continue if email does not exist in database
            elseif ($this->user->email_exists($this->postAndGet('meil'))) {
                $data['content'] .= $this->showDanger($this->localization->string('emailexists'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
                // Pass page to the view
                $data['page_template'] = 'users/register/register_try';
                return $data;
            }
            // Continue if username does not exists in database
            elseif ($this->user->username_exists($this->postAndGet('log'))) {
                $data['content'] .= $this->showDanger($this->localization->string('userexists'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
                // Pass page to the view
                $data['page_template'] = 'users/register/register_try';
                return $data;
            }
            // Continue if email is valid
            elseif (!$this->validateEmail($this->postAndGet('meil'))) {
                $data['content'] .= $this->showDanger($this->localization->string('badmail'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
                // Pass page to the view
                $data['page_template'] = 'users/register/register_try';
                return $data;
            }
            // Check reCAPTCHA
            elseif ($this->recaptchaResponse($this->postAndGet('g-recaptcha-response'))['success'] == false) {
                $data['content'] .= $this->showDanger($this->localization->string('badcaptcha'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
                // Pass page to the view
                $data['page_template'] = 'users/register/register_try';
                return $data;
            }

            $password = $this->postAndGet('par', true);
            $mail = htmlspecialchars(stripslashes(strtolower($this->postAndGet('meil'))));

            if ($this->configuration('regConfirm') == 1) {
                $registration_key = time() + 24 * 60 * 60;
            } else {
                $registration_key = '';
            }

            // Register user
            $this->user->register($this->postAndGet('log'), $password, $this->configuration('regConfirm'), $registration_key, MY_THEME, $mail, $this->localization->string('autopmreg'));

            // User's ID
            $users_id = $this->user->getIdFromNick($this->postAndGet('log'));

            // Create email with confirmation of registration
            // Email text when user need to confirm registration
            if ($this->configuration('regConfirm') == 1) {
                $needkey = "<p>" . $this->localization->string('emailpart5') . "</p>
                <p>" . $this->localization->string('yourkey') . ": " . $registration_key . "</p>
                <p>" . $this->localization->string('emailpart6') . ":</p>
                <p><a href=\"" . $this->websiteHomeAddress() . "/users/confirmkey/?key=" . $registration_key . "&uid={$users_id}\">" . $this->websiteHomeAddress() . "/users/confirmkey/?key=" . $registration_key . "&uid={$users_id}</a></p>
                <p>" . $this->localization->string('emailpart7') . "</p>";
            } else {
                $needkey = '<br />';
            }

            // Email text
            $regmail = "<p>" . $this->localization->string('hello') . " " . $this->postAndGet('log') . "!</p>
            <p>" . $this->localization->string('emailpart1') . " " . $this->configuration('homeUrl') . "</p>
            <p>" . $this->localization->string('emailpart2') . ":</p>
            <p>" . $this->localization->string('username') . ": " . $this->postAndGet('log') . "</p>
            " . $needkey . "
            <p>" . $this->localization->string('emailpart4') . "</p>";

            // Email subject
            $subject = $this->localization->string('regonsite') . ' ' . $this->configuration('title');

            // Insert email text into the email template
            $template = $this->container['parse_page'];
            $template->load('email_templates/default');
            $template->set('subject', $subject);
            $template->set('body', $regmail);
            $email_body = $template->output();

            // Send confirmation email
            $newMail = new Mailer($this->container);

            // Add to the email queue
            $newMail->queueEmail($mail, $subject, $email_body, '', '', $priority = 'high');

            // Registration completed successfully
            $completed = 'successfully';

            // registration successfully, show info
            $data['content'] .= '<p>' . $this->localization->string('regoknick') . ': <b>' . $this->postAndGet('log') . '</b> <br /><br /></p>';

            if ($this->configuration('regConfirm') == 1) {
                // Confirm registration
                $form = $this->container['parse_page'];
                $form->load('forms/form');
                $form->set('form_method', 'post');
                $form->set('form_action', '{@HOMEDIR}}users/confirmkey?uid=' . $users_id);

                $input = $this->container['parse_page'];
                $input->load('forms/input');
                $input->set('label_for', 'key');
                $input->set('label_value', $this->localization->string('yourkey'));
                $input->set('input_type', 'text');
                $input->set('input_id', 'key');
                $input->set('input_name', 'key');
                $input->set('input_placeholder', '');

                $form->set('localization[save]', $this->localization->string('confirm'));
                $form->set('fields', $input->output());
                $data['content'] .= $form->output();

                // Resend email
                $form = $this->container['parse_page'];
                $form->load('forms/form');
                $form->set('form_method', 'post');
                $form->set('form_action', '{@HOMEDIR}}users/resendkey');

                $input = $this->container['parse_page'];
                $input->load('forms/input');
                $input->set('input_type', 'hidden');
                $input->set('input_id', 'recipient');
                $input->set('input_name', 'recipient');
                $input->set('input_value', $mail);

                $form->set('localization[save]', $this->localization->string('resend'));
                $form->set('fields', $input->output());
                $data['content'] .= $form->output();

                $data['content'] .= $this->showNotification($this->localization->string('enterkeymessage'));
            } else {
                $data['content'] .= $this->showSuccess($this->localization->string('loginnow'));
            }

            // Show back link if registration is not completed
            if (!isset($completed)) $data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
        
            $data['content'] .= $this->homelink('<p>', '</p>');
        
            // Pass page to the view
            $data['page_template'] = 'users/register/register_try';
            return $data;
        }

        // Meta tag for this page
        $data['headt'] = '<meta name="robots" content="noindex">';
        $data['headt'] .= '<link rel="stylesheet" href="' . HOMEDIR . 'themes/templates/users/registration/register.css">';
        // Add data to page <head> to show Google reCAPTCHA
        $data['headt'] .= '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
        // Page title
        $data['tname'] = '{@localization[registration]}}';

        if ($this->configuration('openReg') == 1) {
            if ($this->user->userAuthenticated()) {
                $data['content'] = $this->showDanger($this->user->show_username() . ', {@localization[againreg]}}');

                // Load the view
                $data['page_template'] = 'users/register/already_registered';
                return $data;
            } else {
                if (!empty($this->postAndGet('ptl'))) $data['page_to_load'] = $this->check($this->postAndGet('ptl'));

                // information about registration confirmation
                if ($this->configuration('regConfirm') == 1) $data['registration_key_info'] = '{@localization[keyinfo]}}';

                // information about quarantine
                if ($this->configuration('quarantine') > 0) $data['quarantine_info'] = '{@localization[quarantine1]}} ' . round($this->configuration('quarantine') / 3600) . ' {@localization[quarantine2]}}';

                // Show reCAPTCHA
                if (!empty($this->configuration('recaptcha_sitekey'))) $data['security_code'] = '<div class="g-recaptcha" data-sitekey="' . $this->configuration('recaptcha_sitekey') . '"></div>';

                // Load the view
                $data['page_template'] = 'users/register/register';
                return $data;
            }
        } else {
            $data['content'] = $this->showNotification('{@localization[regstoped]}}');

            // Pass page to the view
            $data['page_template'] = 'users/register/registration_stopped';
            return $data;
        }
    }

    /**
     * Confirm registration key
     */
    public function confirmkey()
    {
        // Meta tag for this page
        $data['headt'] = '<meta name="robots" content="noindex">';
        // Page title
        $data['tname'] = '{@localization[confreg]}}';
        $data['content'] = '';

        $recipient_id = $this->postAndGet('uid');

        if (!empty($this->postAndGet('key'))) {
            if (!$this->user->confirm_registration($this->postAndGet('key'))) {
                $data['content'] .= $this->showDanger($this->localization->string('keynotok'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'users/key/?uid=' . $recipient_id, $this->localization->string('back')) . '</p>';
            } else {
                $data['content'] .= $this->showSuccess($this->localization->string('keyok'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'users/login', $this->localization->string('login'), '<p>', '</p>');
            }
        } else {
            $data['content'] .= $this->showDanger($this->localization->string('nokey'));
            $data['content'] .= $this->sitelink(HOMEDIR . 'users/key/?uid=' . $recipient_id, $this->localization->string('back'), '<p>', '</p>');
        }

        // Pass data to controller
        return $data;
    }

    /**
     * Confirm registration key
     */
    public function key()
    {
        // Meta tag for this page
        $data['headt'] = '<meta name="robots" content="noindex">';
        // Page title
        $data['tname'] = '{@localization[confreg]}}';
        $data['content'] = '';

        $recipient_id = $this->postAndGet('uid');

        // Confirm code
        $form = $this->container['parse_page'];
        $form->load('forms/form');
        $form->set('form_method', 'post');
        $form->set('form_action', '{@HOMEDIR}}users/confirmkey/?uid=' . $recipient_id);

        $input = $this->container['parse_page'];
        $input->load('forms/input');
        $input->set('label_for', 'key');
        $input->set('label_value', $this->localization->string('key'));
        $input->set('input_name', 'key');
        $input->set('input_id', 'key');
        $input->set('input_maxlength', 20);

        $form->set('localization[save]', $this->localization->string('confirm'));
        $form->set('fields', $input->output());
        $data['content'] .= $form->output();

        // Resend code
        $form = $this->container['parse_page'];
        $form->load('forms/form');
        $form->set('form_method', 'post');
        $form->set('form_action', '{@HOMEDIR}}users/resendkey/?uid=' . $recipient_id);
        $form->set('localization[save]', $this->localization->string('resend'));
        $data['content'] .= $form->output();
    
        $data['content'] .= '<p>' . $this->localization->string('actinfodel') . '</p>';

        // Pass data to the controller
        return $data;
    }

    /**
     * Resend registration key
     */
    public function resendkey()
    {
        // Meta tag for this page
        $data['headt'] = '<meta name="robots" content="noindex">';
        // Page title
        $data['tname'] = '{@localization[confreg]}}';
        $data['content'] = '';

        $recipient_id = $this->postAndGet('uid');
        $recipient_mail = $this->postAndGet('recipient');

        // Page template
        $data['page_template'] = 'users/register/resend_key';

        // if user id is not in url, get it from submited email
        if (empty($recipient_id)) $recipient_id = $this->user->id_from_email($recipient_mail);

        // Check if user really need to confirm registration
        if ($this->user->user_info('regche', $recipient_id) != 1) {
            $data['content'] .= $this->showNotification('{@localization[registration_already_confirmed]}}');

            return $data;
        }

        // Get users email if it is not submited
        if (empty($recipient_mail)) $recipient_mail = $this->user->user_info('email', $recipient_id);

        $email = $this->db->selectData('email_queue', 'recipient = :recipient', [':recipient' => $recipient_mail]);

        // Check if it is too early to resend email
        // Get time when message is sent, if it is empty use current time
        $time_key_sent = !empty($email['timesent']) ? $email['timesent'] : date("Y-m-d H:i:s");

        $origin = new DateTime($time_key_sent);
        $target = new DateTime(date("Y-m-d H:i:s")); // Current time
        $interval = $origin->diff($target);

        // Show notification if it is too early to resend email
        // User can resend message every 10 minutes
        if ((int)$interval->format('%i') < 10) {
            $data['content'] .= $this->showNotification('{@localization[too_early_to_resend]}}');
            $data['content'] .= $this->sitelink(HOMEDIR . 'users/key/?uid=' . $recipient_id, $this->localization->string('back'), '<p>', '</p>');

            return $data;
        }

        // Resend confirmation email
        $sendMail = new Mailer($this->container);
        // Send mail
        $result = $sendMail->send($email['recipient'], $email['subject'], $email['content']);

        // Sent date
        $fields = array('timesent');
        $values = array(date("Y-m-d H:i:s"));
        // Update data if email is sent
        if ($result == true) $this->db->update('email_queue', $fields, $values, 'id = ' . $email['id']);

        if ($result == true) {
            $data['content'] .= $this->showSuccess('{@localization[confmailsent]}}');
        } else {
            $data['content'] .= $this->showNotification('{@localization[confmailwillbesent]}}');
        }

        // Back link
        $data['back_link'] .= $this->sitelink(HOMEDIR . 'users/key/?uid=' . $recipient_id, $this->localization->string('back'), '<p>', '</p>');

        // Pass data to the controller
        return $data;
    }

    /**
     * Lost password
     */
    public function lostpassword()
    {
        // Page title
        $data['tname'] = '{@localization[lostpass]}}';
        // Meta tag for this page
        $data['headt'] = '<meta name="robots" content="noindex">';
        $data['headt'] .= '<link rel="stylesheet" href="../themes/templates/users/lost_password.css" />';
        // Add data to page <head> to show Google reCAPTCHA
        $data['headt'] .= '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
        $data['content'] = '';

        // Send lost password mail when data are sent
        if (!empty($this->postAndGet('logus')) && !empty($this->postAndGet('mailsus'))) {
            $userx_id = $this->user->getIdFromNick($this->postAndGet('logus'));

            $checkmail = trim($this->user->user_info('email', $userx_id));

            // Username and email does not match
            if ($this->postAndGet('mailsus') != $checkmail) {
                $data['content'] .= $this->showDanger($this->localization->string('wrongmail'));
                $data['content'] .= $this->sitelink('lostpassword', $this->localization->string('back'), '<p>', '</p>');

                // Pass page data to the view
                $data['page_template'] = 'notifications';
                return $data;
            }

            if ($this->recaptchaResponse($this->postAndGet('g-recaptcha-response'))['success'] != true) {
                $data['content'] .= $this->showDanger($this->localization->string('wrongcaptcha'));
                $data['content'] .= $this->sitelink('lostpassword', $this->localization->string('back'), '<p>', '</p>');

                // Pass page data to the view
                $data['page_template'] = 'notifications';
                return $data;
            }

            $newpas = $this->generatePassword();
            $new = $this->user->password_encrypt($newpas);

            $subject = $this->localization->string('newpassfromsite') . ' ' . $this->configuration('title');
            $mail = $this->localization->string('hello') . " " . $this->postAndGet('logus') . "<br /><br />
            " . $this->localization->string('yournewdata') . " " . $this->configuration('homeUrl') . "<br /><br />
            " . $this->localization->string('username') . ": " . $this->postAndGet('logus') . "<br />
            " . $this->localization->string('pass') . ": " . $newpas . "<br /><br />
            " . $this->localization->string('lnkforautolog') . ":<br />
            " . $this->configuration('homeUrl') . "/pages/input.php?log=" . $this->postAndGet('logus') . "&pass=" . $newpas . "&cookietrue=1<br /><br />
            " . $this->localization->string('ycchngpass');

            $send_mail = new Mailer($this->container);
            $send_mail->queueEmail($this->postAndGet('mailsus'), $subject, $mail);

            // Update users profile
            $this->user->updateUser('pass', $new, $userx_id);

            // New password has been generated
            $data['content'] .= $this->showNotification($this->localization->string('passgen'));
            $data['content'] .= $this->homelink('<p>', '</p>');

            // Pass page data to the view
            $data['page_template'] = 'notifications';
            return $data;
        }

        // Show reCAPTCHA
        if (!empty($this->configuration('recaptcha_sitekey'))) $data['security_code'] = '<div class="g-recaptcha" data-sitekey="' . $this->configuration('recaptcha_sitekey') . '"></div>';

        // Pass page data to the controller
        return $data;
    }

    /**
     * Change language
     */
    public function changelang()
    {
        // Get language
        $language = $this->postAndGet('lang');

        // Page to load after changing language
        $ptl = $this->postAndGet('ptl'); 

        if (!file_exists(APPDIR . "include/lang/" . $this->user->getPreferredLanguage($language) . "/index.php")) $this->redirection(HOMEDIR . '?error=no_lang');

        // Set new language
        if (!empty($language)) $this->user->change_language($language);

        // Ignore language url's, /index.php will do the work
        if ($ptl == '/en/' || $ptl == '/sr/') $ptl = '';

        if (!empty($ptl)) {
            $this->redirection($ptl);
        } else {
            $this->redirection(HOMEDIR);
        }
    }

    /**
     * Ignore list
     */
    public function ignore()
    {
        // Redirect unauthenticated users
        if (!$this->user->userAuthenticated()) $this->redirection(HOMEDIR);

        $data['tname'] = '{@localization[ignorlist]}}';
        $data['content'] = '';

        // Add or remove user from ignore list
        if ($this->postAndGet('action') == 'ignore') {
            $tnick = $this->user->getNickFromId($this->postAndGet('who'));

            if ($this->postAndGet('todo') == 'add') {
                if ($this->user->ignoreres($this->user->user_id(), $this->postAndGet('who')) == 1) {
                    $this->db->insert('blocklist', array('name' => $this->user->user_id(), 'target' => $this->postAndGet('who')));

                    $data['content'] .= "<img src=\"/themes/images/img/open.gif\" alt=\"o\"/> " . $this->localization->string('user') . " $tnick " . $this->localization->string('sucadded') . "<br>";
                } else {
                    $data['content'] .= "<img src=\"/themes/images/img/close.gif\" alt=\"x\"/> " . $this->localization->string('cantadd') . " " . $tnick . " " . $this->localization->string('inignor') . "<br>";
                }
            } elseif ($this->postAndGet('todo') == 'del') {
                if ($this->user->ignoreres($this->user->user_id(), $this->postAndGet('who')) == 2) {
                    $this->db->delete('blocklist', "name='{$this->user->user_id()}' AND target='" . $this->postAndGet('who') . "'");

                    $data['content'] .= "<img src=\"/themes/images/img/open.gif\" alt=\"o\"/> $tnick " . $this->localization->string('deltdfrmignor') . "<br>";
                } else {
                    $data['content'] .= "<img src=\"/themes/images/img/close.gif\" alt=\"x\"/> $tnick " . $this->localization->string('notinignor') . "<br>";
                }
            }

            $data['content'] .= $this->sitelink(HOMEDIR . 'users/ignore', $this->localization->string('ignorlist'), '<p>', '</p>');

            // Pass page to the view
            return $data;
        }

        $num_items = $this->db->countRow('blocklist', "name='{$this->user->user_id()}'");
        $items_per_page = 10;

        $navigation = new Navigation($items_per_page, $num_items, HOMEDIR . 'users/ignore/?'); // start navigation

        $limit_start = $navigation->start()['start'];

        $sql = "SELECT target FROM blocklist WHERE name='{$this->user->user_id()}' LIMIT $limit_start, $items_per_page";

        if ($num_items > 0) {
            foreach ($this->db->query($sql) as $item) {
                $tnick = $this->user->getNickFromId($item['target']);

                $lnk = $this->sitelink(HOMEDIR . 'users/' . $item['target'], $tnick);
                $data['content'] .= "$lnk: ";
                $data['content'] .= $this->sitelink(HOMEDIR . 'users/ignore/?action=ignore&who=' . $item['target'] . '&todo=del', '<img src="../themes/images/img/close.gif" alt=""> ' . $this->localization->string('delete')) . '<br>';
            }
        } else {
            $data['content'] .= '<img src="' . HOMEDIR . 'themes/images/img/reload.gif" alt="" /> ' . $this->localization->string('ignorempty') . '<br><br>';
        }

        $data['content'] .= $navigation->getNavigation();

        // Pass page to the controller
        return $data;
    }

    /**
     * Contact list
     */
    public function contacts()
    {
        // Redirect unauthenticated users
        if (!$this->user->userAuthenticated()) $this->redirection(HOMEDIR);

        $data['tname'] = '{@localization[contacts]}}';
        $data['content'] = '';

        // Add or remove from contacts
        if ($this->postAndGet('action') == 'contacts') {
            $tnick = $this->user->getNickFromId($this->postAndGet('who'));
    
            if ($this->postAndGet('todo') == 'add') {
                if ($this->user->ignoreres($this->user->user_id(), $this->postAndGet('who')) == 1 && !$this->user->isbuddy($this->postAndGet('who'), $this->user->user_id)) {
                    $this->db->insert('buddy', array('name' => $this->user->user_id(), 'target' => $this->postAndGet('who')));
    
                    header ("Location: " . HOMEDIR . "users/contacts/?isset=kontakt_add");
                    exit;
                } else {
                    header ("Location: " . HOMEDIR . "users/contacts/?isset=kontakt_noadd");
                    exit;
                }
            } elseif ($this->postAndGet('todo') == 'del') {
                $this->db->delete('buddy', "name='{$this->user->user_id()}' AND target='" . $this->postAndGet('who') . "'");
    
                $this->redirection(HOMEDIR . 'users/contacts/?isset=kontakt_del');
            }
        }

        $num_items = $this->db->countRow('buddy', "name='{$this->user->user_id()}'");
        $items_per_page = 10;

        $navigation = new Navigation($items_per_page, $num_items, HOMEDIR . 'users/contacts/?'); // start navigation

        $limit_start = $navigation->start()['start']; // starting point

        $sql = "SELECT target FROM buddy WHERE name='{$this->user->user_id()}' LIMIT $limit_start, $items_per_page";

        if ($num_items > 0) {
            foreach ($this->db->query($sql) as $item) {
                $tnick = $this->user->getNickFromId($item['target']);
                $lnk = $this->sitelink(HOMEDIR . 'users/u/' . $item['target'], $tnick);
                $data['content'] .= $this->user->userOnline($tnick) . " " . $lnk . ": ";
                $data['content'] .= $this->sitelink(HOMEDIR . 'users/contacts/?action=contacts&who=' . $item['target'] . '&todo=del', '<img src="' . HOMEDIR . 'themes/images/img/close.gif" alt=""> ' . $this->localization->string('delete')) . '<br />';
            }
        } else {
            $data['content'] .= '<p><img src="' . HOMEDIR . 'themes/images/img/reload.gif" alt=""> {@localization[nobuddy]}}</p>';
        }

        $data['content'] .= $navigation->getNavigation();

        // Pass page to the controller
        return $data;
    }

    public function mymenu()
    {
        // Disable access for unregistered users
        if (!$this->user->userAuthenticated()) $this->redirection(HOMEDIR);

        // Page data
        $data['tname'] = '{@localization[mymenu]}}';

        // Pass data
        return $data;
    }

    /**
     * Settings
     */
    public function settings($params = [])
    {
        $data['tname'] = '{@localization[settings]}}';
        $data['content'] = '';

        // Disable access for unregistered users
        if (!$this->user->userAuthenticated()) $this->redirection(HOMEDIR);

        // Save settings
        if (isset($params[0]) && $params[0] == 'save') {
            // Users timezone
            $user_timezone = !empty($this->postAndGet('timezone')) ? $this->check($this->postAndGet('timezone')) : 0;
            // Redirect if timezone is incorrect
            if (preg_match("/[^0-9+-]/", $user_timezone)) $this->redirection(HOMEDIR . 'users/settings/?isset=incorrect');

            // Subscription to site news
            $subnews = !empty($this->postAndGet('subnews')) ? $this->check($this->postAndGet('subnews')) : '';

            // New message notifications
            $inbox_notification = !empty($this->postAndGet('inbnotif')) ? $this->check($this->postAndGet('inbnotif')) : '';

            if (empty($this->postAndGet('lang'))) $this->redirection(HOMEDIR . 'users/settings/?isset=incorrect');

            /**
             * Site newsletter
             */
            if ($this->postAndGet('subnews') == 1) {
                $email_check = $this->db->selectData('subs', 'user_mail = :user_mail', [':user_mail' => $this->user->user_info('email')], 'user_mail');

                if (!empty($email_check['user_mail'])) {
                    $result = 'error2'; // Error! Email already exist in database!
                    
                    $subnewss = 1;
                    $randkey = $this->generatePassword();
                } 

                if (empty($result)) {
                    $randkey = $this->generatePassword();
                    
                    $this->db->insert('subs', array('user_id' => $this->user->user_id(), 'user_mail' => $this->user->user_info('email'), 'user_pass' => $randkey));

                    $result = 'ok'; // sucessfully subscribed to site news!
                    $subnewss = 1;
                } 
            }
            else {
                $email_check = $this->db->selectData('subs', 'user_id = :user_id', [':user_id' => $this->user->user_id()], 'user_mail');

                if (empty($email_check['user_mail'])) {
                    $result = 'error';
                    $subnews = 0;
                    $randkey = '';
                } else {
                    // unsub
                    $this->db->delete('subs', "user_id='{$this->user->user_id()}'");

                    $result = 'no';
                    $subnews = 0;
                    $randkey = '';
                } 
            }

            // update changes
            $fields = array();
            $fields[] = 'ipadd';
            $fields[] = 'timezone';

            $values = array();
            $values[] = $this->user->find_ip();
            $values[] = $user_timezone;

            $this->user->updateUser($fields, $values);
            unset($fields, $values);

            // Update language
            $this->user->change_language($this->postAndGet('lang'));

            // update email notificatoins
            $fields = array();
            $fields[] = 'subscri';
            $fields[] = 'newscod';
            $fields[] = 'lastvst';

            $values = array();
            $values[] = $subnews;
            $values[] = $randkey;
            $values[] = time();

            $this->user->updateUser($fields, $values);
            unset($fields, $values);

            // Notification settings
            $inbox_notification = empty($inbox_notification) ? 0 : 1;

            $check_inb = $this->db->countRow('notif', "uid='{$this->user->user_id()}' AND type='inbox'");
            if ($check_inb > 0) {
                $this->db->update('notif', 'active', $inbox_notification, "uid='{$this->user->user_id()}' AND type='inbox'");
            } else {
                $this->db->insert('notif', array('active' => $inbox_notification, 'uid' => $this->user->user_id(), 'type' => 'inbox'));
            }

            // redirect
            $this->redirection(HOMEDIR . 'users/settings/?isset=editsetting');
        }

        $inbox_notif = $this->db->selectData('notif', 'uid = :uid AND type = :type', [':uid' => $this->user->user_id(), ':type' => 'inbox'], 'active');

        $form = $this->container['parse_page'];
        $form->load('forms/form');
        $form->set('form_method', 'post');
        $form->set('form_action', HOMEDIR . 'users/settings/save');

        $options = '<option value="' . $this->user->user_info('language') . '">' . $this->user->user_info('language') . '</option>';
        $dir = opendir(APPDIR . 'include/lang');
        while ($file = readdir ($dir)) {
            if (!preg_match("/[^a-z0-9_-]/", $file) && ($file != $this->user->user_info('language')) && strlen($file) > 2) {
                $options .= '<option value="' . $file . '">' . $file . '</option>';
            } 
        }

        $choose_lang = $this->container['parse_page'];
        $choose_lang->load('forms/select');
        $choose_lang->set('label_for', 'lang');
        $choose_lang->set('label_value', $this->localization->string('lang'));
        $choose_lang->set('select_id', 'lang');
        $choose_lang->set('select_name', 'lang');
        $choose_lang->set('options', $options);

        /**
         * Subscribe to site newsletter
         */
        $subnews_yes = $this->container['parse_page'];
        $subnews_yes->load('forms/radio_inline');
        $subnews_yes->set('label_for', 'subnews');
        $subnews_yes->set('label_value', $this->localization->string('yes'));
        $subnews_yes->set('input_id', 'subnews');
        $subnews_yes->set('input_name', 'subnews');
        $subnews_yes->set('input_value', 1);
        if ($this->user->user_info('subscribed') == 1) $subnews_yes->set('input_status', 'checked');

        $subnews_no = $this->container['parse_page'];
        $subnews_no->load('forms/radio_inline');
        $subnews_no->set('label_for', 'subnews');
        $subnews_no->set('label_value', $this->localization->string('no'));
        $subnews_no->set('input_id', 'subnews');
        $subnews_no->set('input_name', 'subnews');
        $subnews_no->set('input_value', 0);
        if ($this->user->user_info('subscribed') == 0 || empty($this->user->user_info('subscribed'))) $subnews_no->set('input_status', 'checked');

        $subnews = $this->container['parse_page'];
        $subnews->load('forms/radio_group');
        $subnews->set('description', $this->localization->string('subscribetonews'));
        $subnews->set('radio_group', $subnews->merge(array($subnews_yes, $subnews_no)));

        /**
         * Receive new message notification
         */
        $msgnotif_yes = $this->container['parse_page'];
        $msgnotif_yes->load('forms/radio_inline');
        $msgnotif_yes->set('label_for', 'inbnotif');
        $msgnotif_yes->set('label_value', $this->localization->string('yes'));
        $msgnotif_yes->set('input_id', 'inbnotif');
        $msgnotif_yes->set('input_name', 'inbnotif');
        $msgnotif_yes->set('input_value', 1);
        if ($inbox_notif['active'] == 1) $msgnotif_yes->set('input_status', 'checked');

        $msgnotif_no = $this->container['parse_page'];
        $msgnotif_no->load('forms/radio_inline');
        $msgnotif_no->set('label_for', 'inbnotif');
        $msgnotif_no->set('label_value', $this->localization->string('no'));
        $msgnotif_no->set('input_id', 'inbnotif');
        $msgnotif_no->set('input_name', 'inbnotif');
        $msgnotif_no->set('input_value', 0);
        if ($inbox_notif['active'] == 0 || empty($inbox_notif['active'])) $msgnotif_no->set('input_status', 'checked');

        $msgnotif = $this->container['parse_page'];
        $msgnotif->load('forms/radio_group');
        $msgnotif->set('description', 'Receive new message notification');
        $msgnotif->set('radio_group', $msgnotif->merge(array($msgnotif_yes, $msgnotif_no)));

        $form->set('fields', $form->merge(array($choose_lang, $subnews, $msgnotif)));
        $data['content'] .= $form->output();

        // Pass page to the controller
        return $data;
    }

    /**
     * Ban information
     */
    public function ban()
    {
        $data['tname'] = '{@localization[banned]}}';
        $data['content'] = '';

        if (!$this->user->userAuthenticated()) $this->redirection('../');

        // Ban description
        $bandesc = $this->user->user_info('bandesc');

        // Ban time
        $time_ban = round($this->user->user_info('bantime') - time());

        if ($time_ban > 0) {
            $data['content'] .= '<img src="../themes/images/img/error.gif" alt=""> <b>{@localization[banned1]}}</b><br /><br />';
            $data['content'] .= '<b><font color="#FF0000">{@localization[bandesc]}}: ' . $bandesc . '</font></b>';

            $data['content'] .= '<br>{@localization[timetoend]}} ' . $this->formatTime($time_ban);

            $data['content'] .= '<br><br>{@localization[banno]}}: <b>' . (int)$this->user->user_info('allban') . '</b><br>';
            $data['content'] .= $this->localization->string('becarefnr') . '<br /><br />';

            // Remove session - logout user
            $this->user->logout($this->user->user_id());            
        } else {        
            $data['content'] .= '<p><img src="../themes/images/img/open.gif" alt=""> {@localization[wasbanned]}}</p>';

            if (!empty($bandesc)) {
                $data['content'] .= '<p><b><font color="#FF0000">{@localization[bandesc]}}: ' . $bandesc . '</font></b></p>';
            }
        
            $this->user->updateUser('banned', 0);
            $this->user->updateUser(array('bantime', 'bandesc'), array('', ''));
        }

        $data['content'] .= $this->homelink('<p>', '</p>');

        return $data;
    }

    /**
     * Users profile
     */
    public function users_profile($params)
    {
        $this_page['content'] = '';

        $requested_user = isset($params[0]) ? $this->check($params[0]) : '';

        // Get users nick and users id number
        if (!empty($requested_user)) {
            // Case when id number is used in url
            if (is_numeric($requested_user)) {
                $users_id = $requested_user;
                $uz = $this->user->getNickFromId($requested_user);
            } else {
                $users_id = $this->user->getIdFromNick($requested_user);
                $uz = $requested_user;
            }
        }

        // Show error page if user doesn't exist
        if (!isset($users_id) || !$this->user->id_exists($users_id)) {
            $this_page['tname'] = 'User does not exist';

            $this_page['content'] .= $this->showDanger('<img src="' . STATIC_THEMES_URL . '/images/img/error.gif" alt="Error"> ' . $this->localization->string('user_does_not_exist'));

            $this_page['content'] .= $this->homelink('<p>', '</p>');

            return $this_page;
            exit;
        }

        $this_page['tname'] = '{@localization[profile]}} ' . $uz;

        // Load page from template
        $showPage = $this->container['parse_page'];
        $showPage->load('users/user-profile/user-profile');

        // Show gender image
        if ($this->user->user_info('gender', $users_id) == 'N' || $this->user->user_info('gender', $users_id) == 'n' || empty($this->user->user_info('gender', $users_id))) {
            $showPage->set('sex-img', '<img src="' . STATIC_THEMES_URL . '/images/img/anonim.gif" width="32" height="32" alt="" />');
        } elseif ($this->user->user_info('gender', $users_id) == 'M' or $this->user->user_info('gender', $users_id) == 'm') {
            $showPage->set('sex-img', '<img src="' . STATIC_THEMES_URL . '/images/img/man.png" width="32" height="32" alt="Male" />');
        } else {
            $showPage->set('sex-img', '<img src="' . STATIC_THEMES_URL . '/images/img/women.gif" width="32" height="32" alt="Female" />');
        }

        // Show nickname
        $showPage->set('nickname', $uz);

        // Show online status
        $showPage->set('user-online', $this->user->userOnline($uz));

        // Message if user need to confirm registration
        if ($this->user->user_info('regche', $users_id) == 1) $showPage->set('regCheck', '<b><font color="#FF0000">' . $this->localization->string('notconfirmedreg') . '!</font></b><br>');

        if ($this->user->user_info('banned', $users_id) == 1 && $this->user->user_info('bantime', $users_id) > time()) {
            $profileBanned = $this->container['parse_page'];
            $profileBanned->load('users/user-profile/banned');
            $profileBanned->set('banned', $this->localization->string('userbanned') . '!');
            $time_ban = round($this->user->user_info('bantime', $users_id) - time());
            $profileBanned->set('timeLeft', $this->localization->string('bantimeleft') . ': ' . formatTime($time_ban));
            $profileBanned->set('reason', $this->localization->string('reason') . ': ' . $this->user->user_info('bandesc', $users_id));
            $showPage->set('banned', $profileBanned->output());
        }

        // Personal status
        if (!empty($this->user->user_info('status', $users_id))) {
            $personalStatus = $this->container['parse_page'];
            $personalStatus->load('users/user-profile/status');
            $personalStatus->set('status', $this->localization->string('status') . ':');
            $personalStatus->set('personalStatus', $this->check($this->user->user_info('status', $users_id)));
            $showPage->set('personalStatus', $personalStatus->output());
        }

        $showPage->set('sex', $this->localization->string('sex'));

        // First name
        if (!empty($this->user->user_info('firstname', $users_id))) $showPage->set('firstname', $this->user->user_info('firstname', $users_id));

        // Last name
        if (!empty($this->user->user_info('lastname', $users_id))) $showPage->set('lastname', $this->user->user_info('lastname', $users_id));

        // User's gender
        if ($this->user->user_info('gender', $users_id) == 'N' or $this->user->user_info('gender', $users_id) == 'n' || empty($this->user->user_info('gender', $users_id))) {
            $showPage->set('usersSex', $this->localization->string('notchosen'));
        } elseif ($this->user->user_info('gender', $users_id) == 'M' or $this->user->user_info('gender', $users_id) == 'm') {
            $showPage->set('usersSex', $this->localization->string('male'));
        } else {
            $showPage->set('usersSex', $this->localization->string('female'));
        }

        // City
        if (!empty($this->user->user_info('city', $users_id))) $showPage->set('city', $this->localization->string('city') . ': ' . $this->check($this->user->user_info('city', $users_id)) . '<br>');

        // About user
        if (!empty($this->user->user_info('about', $users_id))) $showPage->set('about', $this->localization->string('about') . ': ' . $this->check($this->user->user_info('about', $users_id)) . ' <br>');

        // User's birthday
        if (!empty($this->user->user_info('birthday', $users_id)) && $this->user->user_info('birthday', $users_id) != "..") $showPage->set('birthday', $this->localization->string('birthday') . ': ' . $this->check($this->user->user_info('birthday', $users_id)) . '<br>');

        // User's browser
        if (!empty($this->user->user_info('browser', $users_id))) $showPage->set('browser', $this->localization->string('browser') . ': ' . $this->check($this->user->user_info('browser', $users_id)) . ' <br>');

        // Website
        if (!empty($this->user->user_info('site', $users_id)) && $this->user->user_info('site', $users_id) != 'http://' && $this->user->user_info('site', $users_id) != 'https://') $showPage->set('site', $this->localization->string('site') . ': <a href="' . $this->check($this->user->user_info('site', $users_id)) . '" target="_blank">' . $this->user->user_info('site', $users_id) . '</a><br>');

        // Registration date
        if (!empty($this->user->user_info('regdate', $users_id))) $showPage->set('regDate', $this->localization->string('regdate') . ': ' . $this->correctDate($this->check($this->user->user_info('regdate', $users_id)), 'd.m.Y.') . '<br>');

        // Last visit
        $timezone = $this->user->userAuthenticated() ? $this->user->user_info('timezone') : $this->configuration('timezone');
        $showPage->set('lastVisit', $this->localization->string('lastvisit') . ': ' . $this->correctDate($this->user->user_info('lastvisit', $users_id), 'd.m.Y. / H:i', $timezone, true));

        if ($this->user->userAuthenticated() && ($this->user->moderator() || $this->user->administrator())) {
            $ipAddress = $this->container['parse_page'];
            $ipAddress->load('users/user-profile/ip-address');
            $ipAddress->set('ip-address', 'IP address: <a href="' . HOMEDIR . $this->configuration('mPanel') . '/ip_information/?ip=' . $this->check($this->user->user_info('ipaddress', $users_id)) . '" target="_blank">'  . $this->check($this->user->user_info('ipaddress', $users_id)) . '</a>');
            $showPage->set('ip-address', $ipAddress->output());
        }

        if ($uz != $this->user->getNickFromId($this->user->user_id()) && $this->user->userAuthenticated()) {
            $userMenu = $this->container['parse_page'];
            $userMenu->load('users/user-profile/user-menu');
            $userMenu->set('add-to', $this->localization->string('addto'));
            $userMenu->set('contacts', '<a href="' . HOMEDIR . 'users/contacts/?action=contacts&todo=add&who=' . $users_id . '">' . $this->localization->string('addtocontacts') . '</a>');

            if (!$this->user->isignored($users_id, $this->user->user_id())) {
            //$userMenu->set('add-to', $this->localization->string('addto']);
            $userMenu->set('ignore', '<a href="' . HOMEDIR . 'users/ignore/?action=ignore&todo=add&who=' . $users_id . '">{@localization[ignore]}}</a>');
            $userMenu->set('sendMessage', '<br /><a href="' . HOMEDIR . 'inbox/?action=dialog&who=' . $users_id . '">{@localization[sendmsg]}}</a><br>');
            } else {
                $userMenu->set('ignore', '{@localization[ignore]}}<br />');
            }

            if ($this->user->userAuthenticated() && ($this->user->moderator() || $this->user->administrator())) $userMenu->set('banUser', '<a href="' . HOMEDIR . $this->configuration('mPanel') . '/addban/?action=edit&users=' . $uz . '">{@localization[bandelban]}}</a><br>');

            if ($this->user->userAuthenticated() && $this->user->administrator(101)) $userMenu->set('updateProfile', '<a href="' . HOMEDIR . $this->configuration('mPanel') . '/users/?action=edit&users=' . $uz . '">{@localization[update]}}</a><br>');

            $showPage->set('userMenu', $userMenu->output());
        } elseif ($this->user->getNickFromId($this->user->user_id()) == $uz && $this->user->userAuthenticated()) {
            $adminMenu = $this->container['parse_page'];
            $adminMenu->load('users/user-profile/admin-update-profile');
            $adminMenu->set('profileLink', '<a href="' . HOMEDIR . 'profile">{@localization[updateprofile]}}</a>');
            $showPage->set('userMenu', $adminMenu->output());
        }

        if (!empty($this->user->user_info('photo', $users_id))) {
            $ext = strtolower(substr($this->user->user_info('photo', $users_id), strrpos($this->user->user_info('photo', $users_id), '.') + 1));

            if ($users_id != $this->user->user_id()) {
                $showPage->set('userPhoto', '<img src="' . HOMEDIR . $this->user->user_info('photo', $users_id) . '" alt="Profile picture" /><br>');
            } else {
                $showPage->set('userPhoto', '<a href="' . HOMEDIR . 'profile/photo"><img src="' . HOMEDIR . $this->user->user_info('photo', $users_id) . '" alt="Profile picture" /></a>');
            }
        }

        // Homepage link
        $showPage->set('homepage', $this->homelink());

        // Show page
        $this_page['content'] .= $showPage->output(); 

        return $this_page;
    }
}