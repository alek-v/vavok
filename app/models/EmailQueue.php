<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class EmailQueue extends BaseModel {
    private object $_mailer;

    public function __construct()
    {
        parent::__construct();

        // Instantiate mailer
        $this->_mailer = new Mailer;
    }

    public function send()
    {
        // New package to be sent every $diff_time minutes
        $diff_time = 1;

        // When last email packag has been sent
        $time_sent = intval(file_get_contents(APPDIR . 'used/email_queue_sent.dat'));

        // Check if sending of new package is too early
        if ($diff_time * 60 + $time_sent > time()) exit;

        // Time new package is sent, every second is important :-)
        $new_time = time();

        $sql = "SELECT * FROM email_queue WHERE sent = 0 ORDER BY FIELD(priority,
                'high',
                'normal',
                'low') LIMIT 0, " . $this->configuration('subMailPacket');

        $i = 0;
        foreach ($this->db->query($sql) as $email) {
                // send damn mail
                $result = $this->_mailer->send($email['recipient'], $email['subject'], $email['content'], $email['sender_mail'], $email['sender']);

                // update sent status
                $fields = array('sent', 'timesent');
                $values = array(1, date("Y-m-d H:i:s"));

                // update data if email is sent
                if ($result == true) {
                        $this->db->update('email_queue', $fields, $values, 'id = ' . $email['id']);
                        // number of successfully sent emails
                        $i++; 
                }
        }

        // Update time of last sent mail
        if ($i > 0) $this->writeDataFile('email_queue_sent.dat', $new_time);
    }

    /**
     * Clean email queue
     * Delete emails that has been sent
     */
    public function clean()
    {
        $this->db->delete('email_queue', "sent = 1 AND timesent < (NOW() - INTERVAL 1 DAY)");
    }
}