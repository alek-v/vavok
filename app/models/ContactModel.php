<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Mailer;
use App\Traits\Validations;
use App\Traits\Notifications;

class ContactModel extends BaseModel {
    use Validations, Notifications;

    /**
     * Index page
     *
     * @return array
     */
    public function index(): array
    {
        $data['tname'] = '{@localization[contact]}}';
        // Add data to page <head> to show Google reCAPTCHA
        $data['headt'] = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
        $data['content'] = '';

        if (!$this->user->userAuthenticated()) {
            $usernameAndMail = $this->container['parse_page'];
            $usernameAndMail->load('contact/usernameAndMail_guest');
        } else {
            $usernameAndMail = $this->container['parse_page'];
            $usernameAndMail->load("contact/usernameAndMail_registered");
            $usernameAndMail->set('log', $this->user->showUsername());
            $usernameAndMail->set('user_email', $this->user->userInfo('email'));
        }

        // Show reCAPTCHA
        $data['security_code'] = '<div class="g-recaptcha" data-sitekey="' . $this->configuration('recaptcha_sitekey') . '"></div>';

        // Page data
        $data['usernameAndMail'] = $usernameAndMail->output();

        return $data;
    }

    /**
     * Send email
     *
     * @return array
     */
    public function send(): array
    {
        $data['tname'] = '{@localization[contact]}}';
        $data['content'] = '';

        // Check name
        if (empty($this->postAndGet('name'))) $data['content'] .= $this->showDanger('{@localization[noname]}}');

        // Check email body
        if (empty($this->postAndGet('body'))) $data['content'] .= $this->showDanger('{@localization[nobody]}}');

        // Validate email address
        if (!$this->validateEmail($this->postAndGet('umail'))) $data['content'] .= $this->showDanger('{@localization[noemail]}}');

        // Redirect if response is false
        if ($this->recaptchaResponse($this->postAndGet('g-recaptcha-response'))['success'] == false) $data['content'] .= $this->showDanger('{@localization[wrongcode]}}');

        // Send email if there is no error
        if (empty($data['content'])) {
            $email_content = $this->postAndGet('body') . "<br /><br /><br />
            -----------------------------------------<br />
            Sender: {$this->postAndGet('name')}<br />
            Sender's email: {$this->postAndGet('umail')}<br />
            Browser: {$this->user->user_browser()}<br />
            IP: {$this->user->findIpAddress()}<br />
            {$this->localization->string('datesent')}: " . date('d.m.Y. / H:i');

            // Insert email text into the email template
            $template = $this->container['parse_page'];
            $template->load('email_templates/default');
            $template->set('subject', $this->localization->string('message_from_site') . ' ' . $this->configuration('title'));
            $template->set('body', $email_content);
            $email_body = $template->output();

            $mail = new Mailer($this->container);
            $mail->queueEmail(
                $this->configuration('adminEmail'),
                $this->localization->string('message_from_site') . ' ' . $this->configuration('title'),
                $email_body,
                '',
                '',
                'normal'
            );

            // Email sent
            $data['content'] .= $this->showSuccess('{@localization[emailsent]}}');
        }

        // Back link
        $data['content'] .= $this->sitelink(HOMEDIR . 'contact', '{@localization[back]}}', '<p>', '</p>');

        return $data;
    }
}