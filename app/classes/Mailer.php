<?php
/**
 * Author:    Aleksandar Vranešević
 * URL:       https://vavok.net
 */

namespace App\Classes;

use App\Traits\Core;
use App\Traits\Validations;
use Pimple\Container;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer {
    use Core, Validations;

    protected Database $db;
    protected Config $configuration;

    public function __construct(protected Container $container)
    {
        $this->db = $this->container['db'];
        $this->configuration = $this->container['config'];
    }

    /**
     * Send email
     *
     * @param string $user_mail
     * @param string $subject
     * @param string $msg
     * @param string $mail_from
     * @param string $name
     * @return bool
     */
    function send(string $user_mail, string $subject, string $msg, string $mail_from = '', string $name = ''): bool
    {
        // Generate default email
        if (empty($mail_from)) {
            $mail_from = $_SERVER['HTTP_HOST'];

            if (substr($mail_from, 0, 2) == 'm.') $mail_from = substr($mail_from, 2);
            if (substr($mail_from, 0, 4) == 'www.') $mail_from = substr($mail_from, 4);

            $mail_from = 'no_reply@' . $mail_from;
        }

        // Default name
        if (empty($name)) {
            $name = $this->configuration->getValue('title');
        }

        // Support for unicode emails
        if (mb_detect_encoding($user_mail) != 'ASCII') {
            // Convert to ASCII
            $user_mail = idn_to_ascii($user_mail);
        }

        // Default value
        $available_authentication = false;

        // Email accounts with authentication data
        $available_mails = $this->emailAccounts();

        // Check if data for authentication exist for email we use to send email
        foreach ($available_mails as $key) {
            if (in_array($mail_from, $key)) {
                $available_authentication = true; // Use authentication, data for authentication is available

                $mail_username = $key['username'];
                $mail_password = $key['password'];
                $mail_port = $key['port'];
                $mail_host = $key['host'];
            }
        }

        // Create a new PHPMailer instance
        $mail = new PHPMailer();

        // First, try to send with authentication if authentication data exists
        if (!$available_authentication) {
            //Set who the message is to be sent from
            $mail->setFrom($mail_from, $name);
        } else {
            //Tell PHPMailer to use SMTP
            $mail->isSMTP();
            //Set the hostname of the mail server
            $mail->Host = $mail_host;
            //Set the SMTP port number - likely to be 25, 465 or 587
            $mail->Port = $mail_port;
            //Whether to use SMTP authentication
            $mail->SMTPAuth = true;
            //Username to use for SMTP authentication
            $mail->Username = $mail_username;
            //Password to use for SMTP authentication
            $mail->Password = $mail_password;
            //Set who the message is to be sent from
            $mail->setFrom($mail_username, $name);
        }

        // Set charset
        $mail->CharSet = 'utf-8';
        //Set who the message is to be sent to
        $mail->addAddress($user_mail);
        //Set the subject line
        $mail->Subject = $subject;
        // Convert to HTML if message is plain text
        if ($this->isTextHtml($msg)) {
            $mail->msgHTML($msg);
        } else {
            $mail->msgHTML($this->getbbcode($msg));
        }
        //Replace the plain text body with one created manually
        $mail->AltBody = $msg;

        // Send the message
        if (!$mail->send()) return false;

        return true;
    }

    /**
     * Add email to the queue
     * 
     * @param string $user_mail
     * @param string $subject
     * @param string $message
     * @param string $sender_mail
     * @param string $sender_name
     * @param string $priority
     * @return void
     */
    function queueEmail(string $user_mail, string $subject, string $message, string $sender_mail = '', string $sender_name = '', string $priority = ''): void
    {
        // User who added email to the queue
        $user_id = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;

        $data = array(
            'uad' => $user_id,
            'sender' => $sender_name,
            'sender_mail' => $sender_mail,
            'recipient' => $user_mail,
            'subject' => $subject,
            'content' => $message,
            'sent' => 0,
            'timeadded' => date("Y-m-d H:i:s"),
            'priority' => $priority
        );

        $this->db->insert('email_queue', $data);
    }

    /**
     * Get subscription options
     *
     * @return array
     */
    public function emailSubscriptionOptions(): array
    {
        $subs = json_decode(file_get_contents(STORAGEDIR . 'subscription_names.dat'), true);

        return $subs = !empty($subs) ? $subs : array();
    }

    /**
     * Save email subscription options
     *
     * @param array $options
     * @return bool
     */
    public function saveSubscriptionOptions(array $options): bool
    {
        $options = json_encode($options);

        file_put_contents(STORAGEDIR . 'subscription_names.dat', $options);

        return true;
    }

    /**
     * Add email subscription options
     *
     * @param string $option
     * @param string $description
     * @return bool
     */
    public function addSubscriptionOption(string $option, string $description): bool
    {
        $all_options = $this->emailSubscriptionOptions();

        // Add new value to the array
        $all_options[$option] = $description;

        $this->saveSubscriptionOptions($all_options);

        return true;
    }

    /**
     * Delete subscription option
     *
     * @param string $option
     * @return bool
     */
    public function deleteSubscriptionOption(string $option): bool
    {
        $all_options = $this->emailSubscriptionOptions();

        // Remove the key
        unset($all_options[$option]);

        // Save options
        $this->saveSubscriptionOptions($all_options);

        return true;
    }

    /**
     * Email accounts with authentication
     * 
     * @return array
     */
    private function emailAccounts(): array
    {
        // Include file with authentication data
        require STORAGEDIR . 'available_emails.php';

        return $available_mails;
    }

    /**
     * Use email template, and insert email in the queue
     * 
     * @param string $email
     * @param string $subject
     * @param string $message
     * @param array $options
     * @return void
     */
    public function queueHtmlEmail(string $email, string $subject, string $message, array $options = []): void
    {
        $options['email_template'] ??= 'default';
        $options['sender_email'] ??= '';
        $options['sender_name'] ??= '';
        $options['priority'] ??= 'normal';

        // Insert email text into the email template
        $template = $this->container['parse_page'];
        $template->load('email_templates/' . $options['email_template']);
        $template->set('subject', $subject);
        $template->set('body', $message);
        $email_body = $template->output();

        $this->queueEmail($email, $subject, $email_body, $options['sender_email'], $options['sender_name'], $options['priority']);
    }
}