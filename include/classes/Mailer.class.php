<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */


class Mailer {
	private $vavok;

	function __construct()
	{
		global $vavok;

		$this->vavok = $vavok;
	}

	/**
	 * Send email
	 *
	 * @param string $usermail
	 * @param string $subject
	 * @param string $msg
	 * @param string $mail
	 * @param string $name
	 * @return bool
	 */
	function send($usermail, $subject, $msg, $mail = "", $name = "")
	{
		/**
		 * Generate default email
		 */
	    if (empty($mail)) {
	        $mail = $_SERVER['HTTP_HOST'];

	        if (substr($mail, 0, 2) == 'm.') $mail = substr($mail, 2);
	        if (substr($mail, 0, 4) == 'www.') $mail = substr($mail, 4);

	        $mail = 'no_reply@' . $mail;
	    }

	    /**
	     * Default name
	     */
	    if (empty($name)) $name = $this->vavok->get_configuration('title');

	    // support for unicode emails
	    if ($this->vavok->is_unicode($usermail) && function_exists('idn_to_ascii')) {
	    	// convert to ascii
	    	$usermail = idn_to_ascii($usermail);
	    }

	    $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

	    $adds = "From: " . $name . " <" . $mail . ">\r\n";
	    $adds .= "X-sender: " . $name . " <" . $mail . ">\r\n";
	    // is it html or plain text
	    if (stristr($msg, '<html') == true) {
	    	$adds .= "Content-Type: text/html; charset=utf-8\r\n";
	    } else {
	    	$adds .= "Content-Type: text/plain; charset=utf-8\r\n";
		}
	    $adds .= "MIME-Version: 1.0\r\n";
	    $adds .= "Content-Transfer-Encoding: 8bit\r\n";
	    $adds .= "X-Mailer: VavokMailer/1.0\r\n";

	    $result = mail($usermail, $subject, $msg, $adds);
	    
	    if (!$result) return false;
		else return true;
	}

	// add to queue
	function queue_email($usermail, $subject, $msg, $senderMail = "", $senderName = "")
	{
		$data = array(
			'uad' => $this->vavok->go('users')->user_id,
			'sender' => $senderName,
			'sender_mail' => $senderMail,
			'recipient' => $usermail,
			'subject' => $subject,
			'content' => $msg,
			'sent' => 0,
			'timeadded' => date("Y-m-d H:i:s")
		);

		$this->vavok->go('db')->insert_data(DB_PREFIX . 'email_queue', $data);
	}

	// get subscription options
	// while adding new subscription it can be added without option (blank) or with one from the list
	function email_sub_options ()
	{
		$subs = file_get_contents(BASEDIR . 'used/subnames.dat');

		return array_filter(explode('||', $subs));
	}
}


?>