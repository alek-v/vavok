<?php
/**
 * Author:    Aleksandar Vranešević
 * URL:       https://vavok.net
 */

namespace App\Classes;
use App\Classes\Validations;
use Pimple\Container;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer {
    protected object $container;
    protected object $db;
    protected object $validations;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->db = $container['db'];
    }

    /**
     * Send email
     *
     * @param string $usermail
     * @param string $subject
     * @param string $msg
     * @param string $mailfrom
     * @param string $name
     * @return bool
     */
    function send($usermail, $subject, $msg, $mailfrom = '', $name = '')
    {
        $this->validations = new Validations;

        /**
         * Generate default email
         */
        if (empty($mailfrom)) {
            $mailfrom = $_SERVER['HTTP_HOST'];

            if (substr($mailfrom, 0, 2) == 'm.') $mailfrom = substr($mailfrom, 2);
            if (substr($mailfrom, 0, 4) == 'www.') $mailfrom = substr($mailfrom, 4);

            $mailfrom = 'no_reply@' . $mailfrom;
        }

        /**
         * Default name
         */
        if (empty($name)) $name = $this->container['core']->configuration('title');

        // Support for unicode emails
        if ($this->validations->isUnicode($usermail) && function_exists('idn_to_ascii')) {
            // convert to ascii
            $usermail = idn_to_ascii($usermail);
        }

        $available_authentication = false;

        require APPDIR . 'used/.available_emails.php';

        /**
         * Check if data for authentication exists for email we use to send email
         */
        foreach ($available_mails as $key) {
            if (in_array($mailfrom, $key)) {
                $available_authentication = true; // Use authentication, data for authentication is available

                $mail_username = $key['username'];
                $mail_password = $key['password'];
                $mail_port = $key['port'];
                $mail_host = $key['host'];
            }
        }

        // Create a new PHPMailer instance
        $mail = new PHPMailer();

        /**
         * First try to send with authentication if authentication data exists
         */
        if (!$available_authentication) {
            //Set who the message is to be sent from
            $mail->setFrom($mailfrom, $name);
        } else {
            //Tell PHPMailer to use SMTP
            $mail->isSMTP();
            //Enable SMTP debugging
            //SMTP::DEBUG_OFF = off (for production use)
            //SMTP::DEBUG_CLIENT = client messages
            //SMTP::DEBUG_SERVER = client and server messages
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
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
        $mail->addAddress($usermail);
        //Set the subject line
        $mail->Subject = $subject;
        // Convert to HTML if message is plain text
        if ($this->container['core']->isTextHtml($msg)) {
            $mail->msgHTML($msg);
        } else {
            $mail->msgHTML($this->container['core']->getbbcode($msg));
        }
        //Replace the plain text body with one created manually
        $mail->AltBody = $msg;

        //send the message
        if (!$mail->send()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Add email to the queue
     * 
     * @param string $usermail
     * @param string $subject
     * @param string $message
     * @param string $sender_mail
     * @param string $sender_name
     * @param string $priority
     * @return void
     */
    function queue_email($usermail, $subject, $message, $sender_mail = '', $sender_name = '', $priority = '')
    {
        // User who added email to the queue
        $user_id = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;

        $data = array(
            'uad' => $user_id,
            'sender' => $sender_name,
            'sender_mail' => $sender_mail,
            'recipient' => $usermail,
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
     * While adding new subscription it can be added without option (blank) or with one from the list
     */
    function emailSubscriptionOptions()
    {
        $subs = file_get_contents(APPDIR . 'used/subnames.dat');

        return array_filter(explode('||', $subs));
    }
}