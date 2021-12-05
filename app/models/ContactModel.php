<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class ContactModel extends Controller {
    protected object $db;
    protected object $user;
    protected object $localization;
	protected array  $user_data = [
		'authenticated' => false,
		'admin_status' => 'user',
		'language' => 'english'
	];

    public function __construct()
    {
        $this->db = new Database;

        $this->user = $this->model('User');

        // Check if user is authenticated
        if ($this->user->is_reg()) $this->user_data['authenticated'] = true;
        // Admin status
        if ($this->user->is_administrator()) $this->user_data['admin_status'] = 'administrator';
        if ($this->user->is_moderator()) $this->user_data['admin_status'] = 'moderator';
        // Users laguage
        $this->user_data['language'] = $this->user->get_user_language();

        // Localization
        $this->localization = $this->model('Localization');
        $this->localization->load();
    }

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

        if (!$this->user->is_reg()) {
            $usernameAndMail = $this->model('ParsePage');
            $usernameAndMail->load('contact/usernameAndMail_guest');
        } else {
            $usernameAndMail = $this->model('ParsePage');
            $usernameAndMail->load("contact/usernameAndMail_registered");
            $usernameAndMail->set('log', $this->user->show_username());
            $usernameAndMail->set('user_email', $this->user->user_info('email'));
        }

        // Show reCAPTCHA
        $data['security_code'] = '<div class="g-recaptcha" data-sitekey="' . $this->get_configuration('recaptcha_sitekey') . '"></div>';

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
        if (empty($this->post_and_get('name'))) $data['content'] .= $this->show_danger('{@localization[noname]}}');

        // Check email body
        if (empty($this->post_and_get('body'))) $data['content'] .= $this->show_danger('{@localization[nobody]}}');

        // Validate email address
        if (!$this->user->validate_email($this->post_and_get('umail'))) $data['content'] .= $this->show_danger('{@localization[noemail]}}');

        // Redirect if response is false
        if ($this->recaptcha_response($this->post_and_get('g-recaptcha-response'))['success'] == false) $data['content'] .= $this->show_danger('{@localization[wrongcode]}}');

        // Send email if there is no error
        if (empty($data['content'])) {
            $mail = new Mailer();
            $mail->queue_email($this->get_configuration('adminEmail'), $localization->string('msgfrmst') . ' ' . $this->get_configuration('title'), $this->post_and_get('body') . "\r\n\r\n\r\n-----------------------------------------\r\nSender: {$this->post_and_get('name')}\r\nSender's email: {$this->post_and_get('umail')}\r\nBrowser: " . $this->user->user_browser() . "\r\nIP: " . $this->user->find_ip() . "\r\n" . $localization->string('datesent') . ": " . date('d.m.Y. / H:i'), '', '', 'normal');

            // Email sent
            $data['content'] .= $this->show_success('{@localization[emailsent]}}');
        }

        // Back link
        $data['content'] .= $this->sitelink(HOMEDIR . 'contact', '{@localization[back]}}', '<p>', '</p>');

        return $data;
    }
}