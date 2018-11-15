<?php
// (c) vavok.net

class Mailer {
	public function __construct() {
		$config = getConfiguration();
	}

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
	        $name = $config["title"];
	    } 
	    $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

	    $adds = "From: " . $name . " <" . $mail . ">\n";
	    $adds .= "X-sender: " . $name . " <" . $mail . ">\n";
	    $adds .= "Content-Type: text/plain; charset=utf-8\n";
	    $adds .= "MIME-Version: 1.0\n";
	    $adds .= "Content-Transfer-Encoding: 8bit\n";
	    $adds .= "X-Mailer: PHP v." . phpversion();

	    return mail($usermail, $subject, $msg, $adds);
	} 

}


?>