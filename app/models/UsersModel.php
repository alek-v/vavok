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
        $this->page_data['head_tags'] = '<meta name="robots" content="noindex">';
        // Page title
        $this->page_data['page_title'] = '{@localization[registration]}}';

        // Data has been sent, register the user
        if (!empty($this->postAndGet('log')) && !empty($this->postAndGet('par'))) {
            $username_length = mb_strlen($this->postAndGet('log'));
            $password_length = mb_strlen($this->postAndGet('par'));

            if ($username_length > 20) {
                $this->page_data['content'] .= $this->showDanger($this->localization->string('biginfo'));
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
                // Pass page to the view
                $this->page_data['page_template'] = 'users/register/register_try';
                return $this->page_data;
            } elseif ($username_length < 3 || $password_length < 3) {
                $this->page_data['content'] .= $this->showDanger($this->localization->string('smallinfo'));
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
                // Pass page to the view
                $this->page_data['page_template'] = 'users/register/register_try';
                return $this->page_data;
            } elseif (!$this->user->validateUsername($this->postAndGet('log'))) {
                $this->page_data['content'] .= $this->showDanger($this->localization->string('useletter'));
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
                // Pass page to the view
                $this->page_data['page_template'] = 'users/register/register_try';
                return $this->page_data;
            } elseif ($this->postAndGet('par') !== $this->postAndGet('pars')) {
                $this->page_data['content'] .= $this->showDanger($this->localization->string('nonewpass'));
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
                // Pass page to the view
                $this->page_data['page_template'] = 'users/register/register_try';
                return $this->page_data;
            }
            // Continue if email does not exist in database
            elseif ($this->user->emailExists($this->postAndGet('meil'))) {
                $this->page_data['content'] .= $this->showDanger($this->localization->string('emailexists'));
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
                // Pass page to the view
                $this->page_data['page_template'] = 'users/register/register_try';
                return $this->page_data;
            }
            // Continue if username does not exists in database
            elseif ($this->user->usernameExists($this->postAndGet('log'))) {
                $this->page_data['content'] .= $this->showDanger($this->localization->string('userexists'));
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
                // Pass page to the view
                $this->page_data['page_template'] = 'users/register/register_try';
                return $this->page_data;
            }
            // Continue if email is valid
            elseif (!$this->validateEmail($this->postAndGet('meil'))) {
                $this->page_data['content'] .= $this->showDanger($this->localization->string('badmail'));
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
                // Pass page to the view
                $this->page_data['page_template'] = 'users/register/register_try';
                return $this->page_data;
            }
            // Check reCAPTCHA
            elseif ($this->recaptchaResponse($this->postAndGet('g-recaptcha-response'))['success'] == false) {
                $this->page_data['content'] .= $this->showDanger($this->localization->string('badcaptcha'));
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
                // Pass page to the view
                $this->page_data['page_template'] = 'users/register/register_try';
                return $this->page_data;
            }

            $password = $this->postAndGet('par', true);
            $mail = htmlspecialchars(stripslashes(strtolower($this->postAndGet('meil'))));

            if ($this->configuration->getValue('confirm_registration') == 1) {
                $registration_key = time() + 24 * 60 * 60;
            } else {
                $registration_key = '';
            }

            // Register user
            $this->user->register($this->postAndGet('log'), $password, $this->configuration->getValue('confirm_registration'), $registration_key, MY_THEME, $mail, $this->localization->string('autopmreg'));

            // User's ID
            $users_id = $this->user->getIdFromNick($this->postAndGet('log'));

            // Create email with confirmation of registration
            // Email text when user need to confirm registration
            if ($this->configuration->getValue('confirm_registration') == 1) {
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
            <p>" . $this->localization->string('emailpart1') . " " . $this->configuration->getValue('home_address') . "</p>
            <p>" . $this->localization->string('emailpart2') . ":</p>
            <p>" . $this->localization->string('username') . ": " . $this->postAndGet('log') . "</p>
            " . $needkey . "
            <p>" . $this->localization->string('emailpart4') . "</p>";

            // Email subject
            $subject = $this->localization->string('regonsite') . ' ' . $this->configuration->getValue('title');

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
            $this->page_data['content'] .= '<p>' . $this->localization->string('regoknick') . ': <b>' . $this->postAndGet('log') . '</b> <br /><br /></p>';

            if ($this->configuration->getValue('confirm_registration') == 1) {
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
                $this->page_data['content'] .= $form->output();

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
                $this->page_data['content'] .= $form->output();

                $this->page_data['content'] .= $this->showNotification($this->localization->string('enterkeymessage'));
            } else {
                $this->page_data['content'] .= $this->showSuccess($this->localization->string('loginnow'));
            }

            // Show back link if registration is not completed
            if (!isset($completed)) {
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'users/register', $this->localization->string('back'), '<p>', '</p>');
            }

            // Pass page to the view
            $this->page_data['page_template'] = 'users/register/register_try';
            return $this->page_data;
        }

        // Meta tag for this page
        $this->page_data['head_tags'] = '<meta name="robots" content="noindex">';
        $this->page_data['head_tags'] .= '<link rel="stylesheet" href="' . HOMEDIR . 'themes/templates/users/registration/register.css">';
        // Add data to page <head> to show Google reCAPTCHA
        $this->page_data['head_tags'] .= '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
        // Page title
        $this->page_data['page_title'] = '{@localization[registration]}}';

        if ($this->configuration->getValue('registration_opened') == 1) {
            if ($this->user->userAuthenticated()) {
                $this->page_data['content'] = $this->showDanger($this->user->showUsername() . ', {@localization[againreg]}}');

                // Load the view
                $this->page_data['page_template'] = 'users/register/already_registered';
                return $this->page_data;
            } else {
                if (!empty($this->postAndGet('ptl'))) {
                    $this->page_data['page_to_load'] = $this->securityCheck($this->postAndGet('ptl'));
                }

                // information about registration confirmation
                if ($this->configuration->getValue('confirm_registration') == 1) {
                    $this->page_data['registration_key_info'] = '{@localization[keyinfo]}}';
                }

                // information about quarantine
                if ($this->configuration->getValue('quarantine') > 0) {
                    $this->page_data['quarantine_info'] = '{@localization[quarantine1]}} ' . round($this->configuration->getValue('quarantine') / 3600) . ' {@localization[quarantine2]}}';
                }

                // Show reCAPTCHA
                if (!empty($this->configuration->getValue('recaptcha_site_key'))) {
                    $this->page_data['security_code'] = '<div class="g-recaptcha" data-sitekey="' . $this->configuration->getValue('recaptcha_site_key') . '"></div>';
                }

                // Load the view
                $this->page_data['page_template'] = 'users/register/register';
                return $this->page_data;
            }
        } else {
            $this->page_data['content'] = $this->showNotification('{@localization[regstoped]}}');

            // Pass page to the view
            $this->page_data['page_template'] = 'users/register/registration_stopped';
            return $this->page_data;
        }
    }

    /**
     * Confirm registration key
     */
    public function confirmkey()
    {
        // Meta tag for this page
        $this->page_data['head_tags'] = '<meta name="robots" content="noindex">';
        // Page title
        $this->page_data['page_title'] = '{@localization[confreg]}}';

        $recipient_id = $this->postAndGet('uid');

        if (!empty($this->postAndGet('key'))) {
            if (!$this->user->confirmRegistration($this->postAndGet('key'))) {
                $this->page_data['content'] .= $this->showDanger($this->localization->string('keynotok'));
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'users/key/?uid=' . $recipient_id, $this->localization->string('back')) . '</p>';
            } else {
                $this->page_data['content'] .= $this->showSuccess($this->localization->string('keyok'));
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'users/login', $this->localization->string('login'), '<p>', '</p>');
            }
        } else {
            $this->page_data['content'] .= $this->showDanger($this->localization->string('nokey'));
            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'users/key/?uid=' . $recipient_id, $this->localization->string('back'), '<p>', '</p>');
        }

        // Pass data to controller
        return $this->page_data;
    }

    /**
     * Confirm registration key
     */
    public function key()
    {
        // Meta tag for this page
        $this->page_data['head_tags'] = '<meta name="robots" content="noindex">';
        // Page title
        $this->page_data['page_title'] = '{@localization[confreg]}}';

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
        $this->page_data['content'] .= $form->output();

        // Resend code
        $form = $this->container['parse_page'];
        $form->load('forms/form');
        $form->set('form_method', 'post');
        $form->set('form_action', '{@HOMEDIR}}users/resendkey/?uid=' . $recipient_id);
        $form->set('localization[save]', $this->localization->string('resend'));
        $this->page_data['content'] .= $form->output();
    
        $this->page_data['content'] .= '<p>' . $this->localization->string('actinfodel') . '</p>';

        // Pass data to the controller
        return $this->page_data;
    }

    /**
     * Resend registration key
     */
    public function resendkey()
    {
        // Meta tag for this page
        $this->page_data['head_tags'] = '<meta name="robots" content="noindex">';
        // Page title
        $this->page_data['page_title'] = '{@localization[confreg]}}';

        $recipient_id = $this->postAndGet('uid');
        $recipient_mail = $this->postAndGet('recipient');

        // Page template
        $this->page_data['page_template'] = 'users/register/resend_key';

        // if user id is not in url, get it from submited email
        if (empty($recipient_id)) $recipient_id = $this->user->idFromEmail($recipient_mail);

        // Check if user really need to confirm registration
        if ($this->user->userInfo('registration_activated', $recipient_id) != 1) {
            $this->page_data['content'] .= $this->showNotification('{@localization[registration_already_confirmed]}}');

            return $this->page_data;
        }

        // Get users email if it is not submited
        if (empty($recipient_mail)) $recipient_mail = $this->user->userInfo('email', $recipient_id);

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
            $this->page_data['content'] .= $this->showNotification('{@localization[too_early_to_resend]}}');
            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'users/key/?uid=' . $recipient_id, $this->localization->string('back'), '<p>', '</p>');

            return $this->page_data;
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
            $this->page_data['content'] .= $this->showSuccess('{@localization[confmailsent]}}');
        } else {
            $this->page_data['content'] .= $this->showNotification('{@localization[confmailwillbesent]}}');
        }

        // Back link
        $this->page_data['back_link'] .= $this->sitelink(HOMEDIR . 'users/key/?uid=' . $recipient_id, $this->localization->string('back'), '<p>', '</p>');

        // Pass data to the controller
        return $this->page_data;
    }

    /**
     * Lost password
     */
    public function lostpassword()
    {
        // Page title
        $this->page_data['page_title'] = '{@localization[lostpass]}}';
        // Meta tag for this page
        $this->page_data['head_tags'] = '<meta name="robots" content="noindex">';
        $this->page_data['head_tags'] .= '<link rel="stylesheet" href="../themes/templates/users/lost_password.css" />';
        // Add data to page <head> to show Google reCAPTCHA
        $this->page_data['head_tags'] .= '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';

        // Send lost password mail when data are sent
        if (!empty($this->postAndGet('logus')) && !empty($this->postAndGet('mailsus'))) {
            $userx_id = $this->user->getIdFromNick($this->postAndGet('logus'));

            $checkmail = trim($this->user->userInfo('email', $userx_id));

            // Username and email does not match
            if ($this->postAndGet('mailsus') != $checkmail) {
                $this->page_data['content'] .= $this->showDanger($this->localization->string('wrongmail'));
                $this->page_data['content'] .= $this->sitelink('lostpassword', $this->localization->string('back'), '<p>', '</p>');

                // Pass page data to the view
                $this->page_data['page_template'] = 'notifications';
                return $this->page_data;
            }

            if ($this->recaptchaResponse($this->postAndGet('g-recaptcha-response'))['success'] != true) {
                $this->page_data['content'] .= $this->showDanger($this->localization->string('wrongcaptcha'));
                $this->page_data['content'] .= $this->sitelink('lostpassword', $this->localization->string('back'), '<p>', '</p>');

                // Pass page data to the view
                $this->page_data['page_template'] = 'notifications';
                return $this->page_data;
            }

            $newpas = $this->generatePassword();
            $new = $this->user->passwordEncrypt($newpas);

            $subject = $this->localization->string('newpassfromsite') . ' ' . $this->configuration->getValue('title');
            $mail = $this->localization->string('hello') . " " . $this->postAndGet('logus') . "<br /><br />
            " . $this->localization->string('yournewdata') . " " . $this->configuration->getValue('home_address') . "<br /><br />
            " . $this->localization->string('username') . ": " . $this->postAndGet('logus') . "<br />
            " . $this->localization->string('pass') . ": " . $newpas . "<br /><br />
            " . $this->localization->string('you_can_change_password');

            $send_mail = new Mailer($this->container);
            $send_mail->queueEmail($this->postAndGet('mailsus'), $subject, $mail);

            // Update users profile
            $this->user->updateUser('pass', $new, $userx_id);

            // New password has been generated
            $this->page_data['content'] .= $this->showNotification($this->localization->string('passgen'));

            // Pass page data to the view
            $this->page_data['page_template'] = 'notifications';
            return $this->page_data;
        }

        // Show reCAPTCHA
        if (!empty($this->configuration->getValue('recaptcha_site_key'))) {
            $this->page_data['security_code'] = '<div class="g-recaptcha" data-sitekey="' . $this->configuration->getValue('recaptcha_site_key') . '"></div>';
        }

        // Pass page data to the controller
        return $this->page_data;
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

        if (!file_exists(APPDIR . "include/lang/" . $this->user->getPreferredLanguage($language) . "/index.php")) $this->redirection(HOMEDIR . '?error=no_localization');

        // Set new language
        if (!empty($language)) $this->user->changeLanguage($language);

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

        $this->page_data['page_title'] = '{@localization[ignorlist]}}';

        // Add or remove user from ignore list
        if ($this->postAndGet('action') == 'ignore') {
            $tnick = $this->user->getNickFromId($this->postAndGet('who'));

            if ($this->postAndGet('todo') == 'add') {
                if ($this->user->blockCheckResult($this->user->userIdNumber(), $this->postAndGet('who')) == 1) {
                    $this->db->insert('blocklist', array('name' => $this->user->userIdNumber(), 'target' => $this->postAndGet('who')));

                    $this->page_data['content'] .= "<img src=\"/themes/images/img/open.gif\" alt=\"o\"/> " . $this->localization->string('user') . " $tnick " . $this->localization->string('sucadded') . "<br>";
                } else {
                    $this->page_data['content'] .= "<img src=\"/themes/images/img/close.gif\" alt=\"x\"/> " . $this->localization->string('cantadd') . " " . $tnick . " " . $this->localization->string('inignor') . "<br>";
                }
            } elseif ($this->postAndGet('todo') == 'del') {
                if ($this->user->blockCheckResult($this->user->userIdNumber(), $this->postAndGet('who')) == 2) {
                    $this->db->delete('blocklist', "name='{$this->user->userIdNumber()}' AND target='" . $this->postAndGet('who') . "'");

                    $this->page_data['content'] .= "<img src=\"/themes/images/img/open.gif\" alt=\"o\"/> $tnick " . $this->localization->string('deltdfrmignor') . "<br>";
                } else {
                    $this->page_data['content'] .= "<img src=\"/themes/images/img/close.gif\" alt=\"x\"/> $tnick " . $this->localization->string('notinignor') . "<br>";
                }
            }

            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'users/ignore', $this->localization->string('ignorlist'), '<p>', '</p>');

            // Pass page to the view
            return $this->page_data;
        }

        $num_items = $this->db->countRow('blocklist', "name='{$this->user->userIdNumber()}'");
        $items_per_page = 10;

        $navigation = new Navigation($items_per_page, $num_items, HOMEDIR . 'users/ignore/?'); // start navigation

        $limit_start = $navigation->start()['start'];

        $sql = "SELECT target FROM blocklist WHERE name='{$this->user->userIdNumber()}' LIMIT $limit_start, $items_per_page";

        if ($num_items > 0) {
            foreach ($this->db->query($sql) as $item) {
                $tnick = $this->user->getNickFromId($item['target']);
                $lnk = $this->sitelink(HOMEDIR . 'users/' . $item['target'], $tnick);

                $this->page_data['content'] .= "$lnk: ";
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'users/ignore/?action=ignore&who=' . $item['target'] . '&todo=del', '<img src="../themes/images/img/close.gif" alt=""> ' . $this->localization->string('delete')) . '<br>';
            }
        } else {
            $this->page_data['content'] .= '<img src="' . HOMEDIR . 'themes/images/img/reload.gif" alt="" /> ' . $this->localization->string('ignorempty') . '<br><br>';
        }

        $this->page_data['content'] .= $navigation->getNavigation();

        // Pass page to the controller
        return $this->page_data;
    }

    /**
     * Contact list
     */
    public function contacts()
    {
        // Redirect unauthenticated users
        if (!$this->user->userAuthenticated()) $this->redirection(HOMEDIR);

        $this->page_data['page_title'] = '{@localization[contacts]}}';

        // Add or remove from contacts
        if ($this->postAndGet('action') == 'contacts') {
            $tnick = $this->user->getNickFromId($this->postAndGet('who'));

            if ($this->postAndGet('todo') == 'add') {
                if ($this->user->blockCheckResult($this->user->userIdNumber(), $this->postAndGet('who')) == 1 && !$this->user->checkContact($this->postAndGet('who'), $this->user->userIdNumber())) {
                    $this->db->insert('buddy', array('name' => $this->user->userIdNumber(), 'target' => $this->postAndGet('who')));

                    header ("Location: " . HOMEDIR . "users/contacts/?isset=kontakt_add");
                    exit;
                } else {
                    header ("Location: " . HOMEDIR . "users/contacts/?isset=kontakt_noadd");
                    exit;
                }
            } elseif ($this->postAndGet('todo') == 'del') {
                $this->db->delete('buddy', "name='{$this->user->userIdNumber()}' AND target='" . $this->postAndGet('who') . "'");
    
                $this->redirection(HOMEDIR . 'users/contacts/?isset=kontakt_del');
            }
        }

        $num_items = $this->db->countRow('buddy', "name='{$this->user->userIdNumber()}'");
        $items_per_page = 10;

        $navigation = new Navigation($items_per_page, $num_items, HOMEDIR . 'users/contacts/?'); // start navigation

        $limit_start = $navigation->start()['start']; // starting point

        $sql = "SELECT target FROM buddy WHERE name='{$this->user->userIdNumber()}' LIMIT $limit_start, $items_per_page";

        if ($num_items > 0) {
            foreach ($this->db->query($sql) as $item) {
                $tnick = $this->user->getNickFromId($item['target']);
                $lnk = $this->sitelink(HOMEDIR . 'users/u/' . $item['target'], $tnick);
                $this->page_data['content'] .= $this->user->userOnline($tnick) . " " . $lnk . ": ";
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'users/contacts/?action=contacts&who=' . $item['target'] . '&todo=del', '<img src="' . HOMEDIR . 'themes/images/img/close.gif" alt=""> ' . $this->localization->string('delete')) . '<br />';
            }
        } else {
            $this->page_data['content'] .= '<p><img src="' . HOMEDIR . 'themes/images/img/reload.gif" alt=""> {@localization[nobuddy]}}</p>';
        }

        $this->page_data['content'] .= $navigation->getNavigation();

        // Pass page to the controller
        return $this->page_data;
    }

    public function mymenu()
    {
        // Disable access for unregistered users
        if (!$this->user->userAuthenticated()) {
            $this->redirection(HOMEDIR);
        }

        // Page data
        $this->page_data['page_title'] = '{@localization[mymenu]}}';

        // Pass data
        return $this->page_data;
    }

    /**
     * Settings
     */
    public function settings($params = [])
    {
        $this->page_data['page_title'] = '{@localization[settings]}}';

        // Disable access for unregistered users
        if (!$this->user->userAuthenticated()) $this->redirection(HOMEDIR);

        // Save settings
        if (isset($params[0]) && $params[0] == 'save') {
            // Users timezone
            $user_timezone = !empty($this->postAndGet('timezone')) ? $this->postAndGet('timezone') : 0;
            // Redirect if timezone is incorrect
            if (preg_match("/[^0-9+-]/", $user_timezone)) $this->redirection(HOMEDIR . 'users/settings/?isset=incorrect');

            // Subscription to site news
            $subnews = !empty($this->postAndGet('subnews')) ? $this->postAndGet('subnews') : '';

            // New message notifications
            $inbox_notification = !empty($this->postAndGet('inbnotif')) ? $this->postAndGet('inbnotif') : '';

            if (empty($this->postAndGet('localization'))) $this->redirection(HOMEDIR . 'users/settings/?isset=incorrect');

            /**
             * Site newsletter
             */
            if ($this->postAndGet('subnews') == 1) {
                $email_check = $this->db->selectData('subs', 'user_mail = :user_mail', [':user_mail' => $this->user->userInfo('email')], 'user_mail');

                if (!empty($email_check['user_mail'])) {
                    $result = 'error2'; // Error! Email already exist in database!
                    
                    $subnewss = 1;
                    $randkey = $this->generatePassword();
                } 

                if (empty($result)) {
                    $randkey = $this->generatePassword();
                    
                    $this->db->insert('subs', array('user_id' => $this->user->userIdNumber(), 'user_mail' => $this->user->userInfo('email'), 'user_pass' => $randkey));

                    $result = 'ok'; // sucessfully subscribed to site news!
                    $subnewss = 1;
                } 
            }
            else {
                $email_check = $this->db->selectData('subs', 'user_id = :user_id', [':user_id' => $this->user->userIdNumber()], 'user_mail');

                if (empty($email_check['user_mail'])) {
                    $result = 'error';
                    $subnews = 0;
                    $randkey = '';
                } else {
                    // unsub
                    $this->db->delete('subs', "user_id='{$this->user->userIdNumber()}'");

                    $result = 'no';
                    $subnews = 0;
                    $randkey = '';
                } 
            }

            // update changes
            $fields = array();
            $fields[] = 'ip_address';
            $fields[] = 'timezone';

            $values = array();
            $values[] = $this->user->findIpAddress();
            $values[] = $user_timezone;

            $this->user->updateUser($fields, $values);
            unset($fields, $values);

            // Update language
            $this->user->changeLanguage($this->postAndGet('localization'));

            // update email notificatoins
            $fields = array();
            $fields[] = 'subscribed';
            $fields[] = 'subscription_code';
            $fields[] = 'last_visit';

            $values = array();
            $values[] = $subnews;
            $values[] = $randkey;
            $values[] = time();

            $this->user->updateUser($fields, $values);
            unset($fields, $values);

            // Notification settings
            $inbox_notification = empty($inbox_notification) ? 0 : 1;

            $check_inb = $this->db->countRow('notif', "uid='{$this->user->userIdNumber()}' AND type='inbox'");
            if ($check_inb > 0) {
                $this->db->update('notif', 'active', $inbox_notification, "uid='{$this->user->userIdNumber()}' AND type='inbox'");
            } else {
                $this->db->insert('notif', array('active' => $inbox_notification, 'uid' => $this->user->userIdNumber(), 'type' => 'inbox'));
            }

            // redirect
            $this->redirection(HOMEDIR . 'users/settings/?isset=editsetting');
        }

        $inbox_notif = $this->db->selectData('notif', 'uid = :uid AND type = :type', [':uid' => $this->user->userIdNumber(), ':type' => 'inbox'], 'active');

        $form = $this->container['parse_page'];
        $form->load('forms/form');
        $form->set('form_method', 'post');
        $form->set('form_action', HOMEDIR . 'users/settings/save');

        $options = '<option value="' . $this->user->userInfo('language') . '">' . $this->user->userInfo('language') . '</option>';
        $dir = opendir(APPDIR . 'include/lang');
        while ($file = readdir ($dir)) {
            if (!preg_match("/[^a-z0-9_-]/", $file) && ($file != $this->user->userInfo('language')) && strlen($file) > 2) {
                $options .= '<option value="' . $file . '">' . $file . '</option>';
            } 
        }

        $choose_lang = $this->container['parse_page'];
        $choose_lang->load('forms/select');
        $choose_lang->set('label_for', 'localization');
        $choose_lang->set('label_value', $this->localization->string('localization'));
        $choose_lang->set('select_id', 'localization');
        $choose_lang->set('select_name', 'localization');
        $choose_lang->set('options', $options);

        // Subscribe to site newsletter
        $subnews_yes = $this->container['parse_page'];
        $subnews_yes->load('forms/radio_inline');
        $subnews_yes->set('label_for', 'subnews');
        $subnews_yes->set('label_value', $this->localization->string('yes'));
        $subnews_yes->set('input_id', 'subnews');
        $subnews_yes->set('input_name', 'subnews');
        $subnews_yes->set('input_value', 1);
        if ($this->user->userInfo('subscribed') == 1) $subnews_yes->set('input_status', 'checked');

        $subnews_no = $this->container['parse_page'];
        $subnews_no->load('forms/radio_inline');
        $subnews_no->set('label_for', 'subnews');
        $subnews_no->set('label_value', $this->localization->string('no'));
        $subnews_no->set('input_id', 'subnews');
        $subnews_no->set('input_name', 'subnews');
        $subnews_no->set('input_value', 0);
        if ($this->user->userInfo('subscribed') == 0 || empty($this->user->userInfo('subscribed'))) $subnews_no->set('input_status', 'checked');

        $subnews = $this->container['parse_page'];
        $subnews->load('forms/radio_group');
        $subnews->set('description', $this->localization->string('subscribetonews'));
        $subnews->set('radio_group', $subnews->merge(array($subnews_yes, $subnews_no)));

        // Receive new message notification
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
        $this->page_data['content'] .= $form->output();

        // Pass page to the controller
        return $this->page_data;
    }

    /**
     * Ban information
     */
    public function ban()
    {
        $this->page_data['page_title'] = '{@localization[banned]}}';

        if (!$this->user->userAuthenticated()) $this->redirection('../');

        // Ban description
        $bandesc = $this->user->userInfo('ban_description');

        // Ban time
        $time_ban = round($this->user->userInfo('ban_time') - time());

        if ($time_ban > 0) {
            $this->page_data['content'] .= '<img src="../themes/images/img/error.gif" alt=""> <b>{@localization[banned1]}}</b><br /><br />';
            $this->page_data['content'] .= '<b><font color="#FF0000">{@localization[bandesc]}}: ' . $bandesc . '</font></b>';

            $this->page_data['content'] .= '<br>{@localization[timetoend]}} ' . $this->formatTime($time_ban);

            $this->page_data['content'] .= '<br><br>{@localization[banno]}}: <b>' . (int)$this->user->userInfo('all_bans') . '</b><br>';
            $this->page_data['content'] .= $this->localization->string('becarefnr') . '<br /><br />';

            // Remove session - logout user
            $this->user->logout();
        } else {        
            $this->page_data['content'] .= '<p><img src="../themes/images/img/open.gif" alt=""> {@localization[wasbanned]}}</p>';

            if (!empty($bandesc)) {
                $this->page_data['content'] .= '<p><b><font color="#FF0000">{@localization[bandesc]}}: ' . $bandesc . '</font></b></p>';
            }
        
            $this->user->updateUser('banned', 0);
            $this->user->updateUser(array('ban_time', 'ban_description'), array('', ''));
        }

        return $this->page_data;
    }

    /**
     * Users profile
     */
    public function users_profile($params)
    {
        $requested_user = isset($params[0]) ? $params[0] : '';

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
        if (!isset($users_id) || !$this->user->idExists($users_id)) {
            $this->page_data['page_title'] = 'User does not exist';

            $this->page_data['content'] .= $this->showDanger('<img src="' . STATIC_THEMES_URL . '/images/img/error.gif" alt="Error"> ' . $this->localization->string('user_does_not_exist'));

            return $this->page_data;
            exit;
        }

        $this->page_data['page_title'] = '{@localization[profile]}} ' . $uz;

        // Load page from template
        $showPage = $this->container['parse_page'];
        $showPage->load('users/user-profile/user-profile');

        // Show gender image
        if ($this->user->userInfo('gender', $users_id) == 'N' || $this->user->userInfo('gender', $users_id) == 'n' || empty($this->user->userInfo('gender', $users_id))) {
            $showPage->set('sex-img', '<img src="' . STATIC_THEMES_URL . '/images/img/anonim.gif" width="32" height="32" alt="" />');
        } elseif ($this->user->userInfo('gender', $users_id) == 'M' or $this->user->userInfo('gender', $users_id) == 'm') {
            $showPage->set('sex-img', '<img src="' . STATIC_THEMES_URL . '/images/img/man.png" width="32" height="32" alt="Male" />');
        } else {
            $showPage->set('sex-img', '<img src="' . STATIC_THEMES_URL . '/images/img/women.gif" width="32" height="32" alt="Female" />');
        }

        // Show nickname
        $showPage->set('nickname', $uz);

        // Show online status
        $showPage->set('user-online', $this->user->userOnline($uz));

        // Message if user need to confirm registration
        if ($this->user->userInfo('registration_activated', $users_id) == 1) $showPage->set('regCheck', '<b><font color="#FF0000">' . $this->localization->string('notconfirmedreg') . '!</font></b><br>');

        if ($this->user->userInfo('banned', $users_id) == 1 && $this->user->userInfo('ban_time', $users_id) > time()) {
            $profileBanned = $this->container['parse_page'];
            $profileBanned->load('users/user-profile/banned');
            $profileBanned->set('banned', $this->localization->string('userbanned') . '!');
            $time_ban = round($this->user->userInfo('ban_time', $users_id) - time());
            $profileBanned->set('timeLeft', $this->localization->string('bantimeleft') . ': ' . formatTime($time_ban));
            $profileBanned->set('reason', $this->localization->string('reason') . ': ' . $this->user->userInfo('ban_description', $users_id));
            $showPage->set('banned', $profileBanned->output());
        }

        // Personal status
        if (!empty($this->user->userInfo('status', $users_id))) {
            $personalStatus = $this->container['parse_page'];
            $personalStatus->load('users/user-profile/status');
            $personalStatus->set('status', $this->localization->string('status') . ':');
            $personalStatus->set('personalStatus', $this->user->userInfo('status', $users_id));
            $showPage->set('personalStatus', $personalStatus->output());
        }

        $showPage->set('sex', $this->localization->string('sex'));

        // First name
        if (!empty($this->user->userInfo('first_name', $users_id))) {
            $showPage->set('first_name', $this->user->userInfo('first_name', $users_id));
        }

        // Last name
        if (!empty($this->user->userInfo('last_name', $users_id))) {
            $showPage->set('last_name', $this->user->userInfo('last_name', $users_id));
        }

        // User's gender
        if ($this->user->userInfo('gender', $users_id) == 'N' or $this->user->userInfo('gender', $users_id) == 'n' || empty($this->user->userInfo('gender', $users_id))) {
            $showPage->set('usersSex', $this->localization->string('notchosen'));
        } elseif ($this->user->userInfo('gender', $users_id) == 'M' or $this->user->userInfo('gender', $users_id) == 'm') {
            $showPage->set('usersSex', $this->localization->string('male'));
        } else {
            $showPage->set('usersSex', $this->localization->string('female'));
        }

        // City
        if (!empty($this->user->userInfo('city', $users_id))) {
            $showPage->set('city', $this->localization->string('city') . ': ' . $this->user->userInfo('city', $users_id));
        }

        // About user
        if (!empty($this->user->userInfo('about', $users_id))) {
            $showPage->set('about', $this->localization->string('about') . ': ' . $this->user->userInfo('about', $users_id));
        }

        // User's birthday
        if (!empty($this->user->userInfo('birthday', $users_id)) && $this->user->userInfo('birthday', $users_id) != "..") {
            $showPage->set('birthday', $this->localization->string('birthday') . ': ' . $this->user->userInfo('birthday', $users_id));
        }

        // User's browser
        if (!empty($this->user->userInfo('browser', $users_id))) {
            $showPage->set('browser', $this->localization->string('browser') . ': ' . $this->user->userInfo('browser', $users_id));
        }

        // Website
        if (!empty($this->user->userInfo('site', $users_id)) && $this->user->userInfo('site', $users_id) != 'http://' && $this->user->userInfo('site', $users_id) != 'https://') {
            $showPage->set('site', $this->localization->string('site') . ': <a href="' . $this->user->userInfo('site', $users_id) . '" target="_blank">' . $this->user->userInfo('site', $users_id) . '</a>');
        }

        // Registration date
        if (!empty($this->user->userInfo('registration_date', $users_id))) {
            $showPage->set('regDate', $this->localization->string('regdate') . ': ' . $this->correctDate($this->user->userInfo('registration_date', $users_id), $this->localization->showAll()['date_format']));
        }

        // Last visit
        $timezone = $this->user->userAuthenticated() ? $this->user->userInfo('timezone') : $this->configuration->getValue('timezone');
        $showPage->set('last_visit', $this->localization->string('last_visit') . ': ' . $this->correctDate($this->user->userInfo('last_visit', $users_id), $this->localization->showAll()['date_format'] . ' / ' . $this->localization->showAll()['time_format'], $timezone, true));

        if ($this->user->userAuthenticated() && ($this->user->moderator() || $this->user->administrator())) {
            $ipAddress = $this->container['parse_page'];
            $ipAddress->load('users/user-profile/ip-address');
            $ipAddress->set('ip-address', 'IP address: <a href="' . HOMEDIR . $this->configuration->getValue('admin_panel') . '/ip_information/?ip=' . $this->user->userInfo('ip_address', $users_id) . '" target="_blank">'  . $this->user->userInfo('ip_address', $users_id) . '</a>');
            $showPage->set('ip-address', $ipAddress->output());
        }

        if ($uz != $this->user->getNickFromId($this->user->userIdNumber()) && $this->user->userAuthenticated()) {
            $userMenu = $this->container['parse_page'];
            $userMenu->load('users/user-profile/user-menu');
            $userMenu->set('add-to', $this->localization->string('addto'));
            $userMenu->set('contacts', '<a href="' . HOMEDIR . 'users/contacts/?action=contacts&todo=add&who=' . $users_id . '">' . $this->localization->string('addtocontacts') . '</a>');

            if (!$this->user->isUserBlocked($users_id, $this->user->userIdNumber())) {
                $userMenu->set('ignore', '<a href="' . HOMEDIR . 'users/ignore/?action=ignore&todo=add&who=' . $users_id . '">{@localization[ignore]}}</a>');
                $userMenu->set('sendMessage', '<br /><a href="' . HOMEDIR . 'inbox/?action=dialog&who=' . $users_id . '">{@localization[sendmsg]}}</a><br>');
            } else {
                $userMenu->set('ignore', '{@localization[ignore]}}<br />');
            }

            if ($this->user->userAuthenticated() && ($this->user->moderator() || $this->user->administrator())) $userMenu->set('banUser', '<a href="' . HOMEDIR . $this->configuration->getValue('admin_panel') . '/addban/?action=edit&users=' . $uz . '">{@localization[bandelban]}}</a><br>');

            if ($this->user->userAuthenticated() && $this->user->administrator(101)) $userMenu->set('updateProfile', '<a href="' . HOMEDIR . $this->configuration->getValue('admin_panel') . '/users/?action=edit&users=' . $uz . '">{@localization[update]}}</a><br>');

            $showPage->set('userMenu', $userMenu->output());
        } elseif ($this->user->getNickFromId($this->user->userIdNumber()) == $uz && $this->user->userAuthenticated()) {
            $adminMenu = $this->container['parse_page'];
            $adminMenu->load('users/user-profile/admin-update-profile');
            $adminMenu->set('profileLink', '<a href="' . HOMEDIR . 'profile">{@localization[updateprofile]}}</a>');
            $showPage->set('userMenu', $adminMenu->output());
        }

        if (!empty($this->user->userInfo('photo', $users_id))) {
            $ext = strtolower(substr($this->user->userInfo('photo', $users_id), strrpos($this->user->userInfo('photo', $users_id), '.') + 1));

            if ($users_id != $this->user->userIdNumber()) {
                $showPage->set('userPhoto', '<img src="' . HOMEDIR . $this->user->userInfo('photo', $users_id) . '" alt="Profile picture" /><br>');
            } else {
                $showPage->set('userPhoto', '<a href="' . HOMEDIR . 'profile/photo"><img src="' . HOMEDIR . $this->user->userInfo('photo', $users_id) . '" alt="Profile picture" /></a>');
            }
        }

        // Homepage link
        $showPage->set('homepage', $this->homelink());

        // Show page
        $this->page_data['content'] .= $showPage->output(); 

        return $this->page_data;
    }
}