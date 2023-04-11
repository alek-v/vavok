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
        $this->page_data['page_title'] = '{@localization[contact]}}';
        // Add data to page <head> to show Google reCAPTCHA
        $this->page_data['head_tags'] = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';

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
        $this->page_data['security_code'] = '<div class="g-recaptcha" data-sitekey="' . $this->configuration->getValue('recaptcha_site_key') . '"></div>';

        // Page data
        $this->page_data['usernameAndMail'] = $usernameAndMail->output();

        return $this->page_data;
    }

    /**
     * Send email
     *
     * @return array
     */
    public function send(): array
    {
        $this->page_data['page_title'] = '{@localization[contact]}}';

        // Check name
        if (empty($this->postAndGet('name'))) {
            $this->page_data['content'] .= $this->showDanger('{@localization[noname]}}');
        }

        // Check email body
        if (empty($this->postAndGet('body'))) {
            $this->page_data['content'] .= $this->showDanger('{@localization[nobody]}}');
        }

        // Validate email address
        if (!$this->validateEmail($this->postAndGet('umail'))) {
            $this->page_data['content'] .= $this->showDanger('{@localization[noemail]}}');
        }

        // Redirect if response is false
        if ($this->recaptchaResponse($this->postAndGet('g-recaptcha-response'))['success'] == false) {
            $this->page_data['content'] .= $this->showDanger('{@localization[wrongcode]}}');
        }

        // Send email if there is no error
        if (empty($this->page_data['content'])) {
            $email_content = $this->postAndGet('body') . "<br /><br /><br />
            -----------------------------------------<br />
            Sender: {$this->postAndGet('name')}<br />
            Sender's email: {$this->postAndGet('umail')}<br />
            Browser: {$this->user->userBrowser()}<br />
            IP: {$this->user->findIpAddress()}<br />
            {$this->localization->string('datesent')}: " . date($this->localization->showAll()['date_format'] . ' / ' . $this->localization->showAll()['time_format']);

            // Insert email text into the email template
            $template = $this->container['parse_page'];
            $template->load('email_templates/default');
            $template->set('subject', $this->localization->string('message_from_site') . ' ' . $this->configuration->getValue('title'));
            $template->set('body', $email_content);
            $email_body = $template->output();

            $mail = new Mailer($this->container);
            $mail->queueEmail(
                $this->configuration->getValue('admin_email'),
                $this->localization->string('message_from_site') . ' ' . $this->configuration->getValue('title'),
                $email_body,
                '',
                '',
                'normal'
            );

            // Email sent
            $this->page_data['content'] .= $this->showSuccess('{@localization[emailsent]}}');
        }

        // Back link
        $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'contact', '{@localization[back]}}', '<p>', '</p>');

        return $this->page_data;
    }
}