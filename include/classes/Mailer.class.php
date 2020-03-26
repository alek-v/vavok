<?php
// (c) vavok.net

class Mailer {
	// send email
	function send($usermail, $subject, $msg, $mail = "", $name = "") {
	    global $config_srvhost;

	    if (empty($mail)) {
	        $mail = $config_srvhost;
	        if (substr($mail, 0, 2) == 'm.') {
	            $mail = substr($str, 2);
	        } 
	        if (substr($mail, 0, 4) == 'www.') {
	            $mail = substr($str, 4);
	        }
	        $mail = 'no_reply@' . $mail;
	        $name = getConfiguration('title');
	    } 
	    $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

	    $adds = "From: " . $name . " <" . $mail . ">\n";
	    $adds .= "X-sender: " . $name . " <" . $mail . ">\n";
	    // is it html or plain text
	    if (stristr($msg, '<html') == true) {
	    	$adds .= "Content-Type: text/html; charset=utf-8\n";
	    } else {
	    	$adds .= "Content-Type: text/plain; charset=utf-8\n";
		}
	    $adds .= "MIME-Version: 1.0\n";
	    $adds .= "Content-Transfer-Encoding: 8bit\n";
	    $adds .= "X-Mailer: PHP v." . phpversion();

	    return mail($usermail, $subject, $msg, $adds);
	}

	// add to queue
	function queue_email($usermail, $subject, $msg, $senderMail = "", $senderName = "") {
		global $db, $user_id;

		$data = array(
			'uad' => $user_id,
			'sender' => $senderName,
			'sender_mail' => $senderMail,
			'recipient' => $usermail,
			'subject' => $subject,
			'content' => $msg,
			'sent' => 0,
			'timeadded' => date("Y-m-d H:i:s")
		);

		$db->insert_data('email_queue', $data);

	}

}


?>