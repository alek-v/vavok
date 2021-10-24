<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Send emails in the queue
 */

require_once '../include/startup.php';

/**
 * New package should be sent every $diff_time minutes
 */
$diff_time = 1;

// when last email packag has been sent
$time_sent = intval(file_get_contents(BASEDIR . 'used/email_queue_sent.dat'));

// check if sending of new package is too early
if ($diff_time * 60 + $time_sent > time()) exit; /*/ It is too early to send /*/

$new_time = time(); // time new package is sent, every second is important :-)

$sendMail = new Mailer();

$sql = "SELECT * FROM email_queue WHERE sent = 0 ORDER BY FIELD(priority,
        'high',
        'normal',
        'low') LIMIT 0, " . $vavok->get_configuration('subMailPacket');

$i = 0;
foreach ($vavok->go('db')->query($sql) as $email) {
        // send damn mail
        $result = $sendMail->send($email['recipient'], $email['subject'], $email['content'], $email['sender_mail'], $email['sender']);

        // update sent status
        $fields = array('sent', 'timesent');
        $values = array(1, date("Y-m-d H:i:s"));

        // update data if email is sent
        if ($result == true) {
            	$vavok->go('db')->update('email_queue', $fields, $values, 'id = ' . $email['id']);

            	$i++; // number of successfully sent emails
	}
}

/**
 * Update time of last sent mail
 */
if ($i > 0) $vavok->write_data_file('email_queue_sent.dat', $new_time);

?>