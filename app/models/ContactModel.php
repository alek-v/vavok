<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class ContactModel extends BaseModel {
    /**
     * Index page
     */
    public function index()
    {
        // Users data
        $data['user'] = $this->user_data;
        $data['tname'] = '{@localization[contact]}}';
        // Add data to page <head> to show Google reCAPTCHA
        $data['headt'] = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
        $data['content'] = '';

        if (!$this->user->userAuthenticated()) {
            $usernameAndMail = $this->model('ParsePage');
            $usernameAndMail->load('contact/usernameAndMail_guest');
        } else {
            $usernameAndMail = $this->model('ParsePage');
            $usernameAndMail->load("contact/usernameAndMail_registered");
            $usernameAndMail->set('log', $this->user->show_username());
            $usernameAndMail->set('user_email', $this->user->user_info('email'));
        }

        // Show reCAPTCHA
        $data['security_code'] = '<div class="g-recaptcha" data-sitekey="' . $this->configuration('recaptcha_sitekey') . '"></div>';

        // Page data
        $data['usernameAndMail'] = $usernameAndMail->output();

        return $data;
    }

    /**
     * Send email
     */
    public function send()
    {
        // Users data
        $data['user'] = $this->user_data;
        $data['tname'] = '{@localization[contact]}}';
        $data['content'] = '';

        // Localization
        $localization = $this->model('Localization');
        $localization->load();

        // Check name
        if (empty($this->postAndGet('name'))) $data['content'] .= $this->showDanger('{@localization[noname]}}');

        // Check email body
        if (empty($this->postAndGet('body'))) $data['content'] .= $this->showDanger('{@localization[nobody]}}');

        // Validate email address
        if (!$this->user->validate_email($this->postAndGet('umail'))) $data['content'] .= $this->showDanger('{@localization[noemail]}}');

        // Redirect if response is false
        if ($this->recaptchaResponse($this->postAndGet('g-recaptcha-response'))['success'] == false) $data['content'] .= $this->showDanger('{@localization[wrongcode]}}');

        // Send email if there is no error
        if (empty($data['content'])) {
            $mail = new Mailer();
            $mail->queue_email($this->configuration('adminEmail'), $localization->string('msgfrmst') . ' ' . $this->configuration('title'), $this->postAndGet('body') . "\r\n\r\n\r\n-----------------------------------------\r\nSender: {$this->postAndGet('name')}\r\nSender's email: {$this->postAndGet('umail')}\r\nBrowser: " . $this->user->user_browser() . "\r\nIP: " . $this->user->find_ip() . "\r\n" . $localization->string('datesent') . ": " . date('d.m.Y. / H:i'), '', '', 'normal');

            // Email sent
            $data['content'] .= $this->showSuccess('{@localization[emailsent]}}');
        }

        // Back link
        $data['content'] .= $this->sitelink(HOMEDIR . 'contact', '{@localization[back]}}', '<p>', '</p>');

        return $data;
    }
}