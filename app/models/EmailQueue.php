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

    /**
     * Send using cronjob
     */
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
     * Clean email queue using cronjob
     * Delete emails that has been sent
     */
    public function clean()
    {
        $this->db->delete('email_queue', "sent = 1 AND timesent < (NOW() - INTERVAL 1 DAY)");
    }

    /**
     * Add mails to email queue
     */
    public function email_queue()
    {
        // Users data
        $page_data['user'] = $this->user_data;

        // Page data
        $page_data['tname'] = 'eMail queue';
        $page_data['content'] = '';

        // Checking access permitions
        if (!$this->user_data['authenticated'] || !$this->user->administrator(101)) $this->redirection('../');

        $news_queue_limit = 1000; // how manu emails to add to queue at once

        // text editor
        $emailEditor = $this->model('Pagemanager');
        $loadTextEditor = $emailEditor->loadPageEditor();

        // choose field selector
        $textEditor = str_replace('#selector', '#msg', $loadTextEditor);

        // add to page header
        $page_data['headt'] = $textEditor;

        if (empty($this->postAndGet('action'))) {
            $subNames = $this->_mailer->emailSubscriptionOptions();

            $page_data['content'] .= '<h1>Add emails to the queue</h1>';

            $page_data['content'] .= '<form action="./email_queue?action=add" method="post" />';
            $page_data['content'] .= '<p>Sender name:<br /><input type="text" name="sender" value=""><br /></p>';
            $page_data['content'] .= '<p>Sender email:<br /><input type="text" name="email" value=""><br /></p>';
            $page_data['content'] .= '<p>Subject:<br /><input type="text" name="theme" value=""><br /></p>';
            $page_data['content'] .= '<label for="msg">Email content:</label>';
            $page_data['content'] .= '<textarea col="20" row="20" id="msg" name="msg"></textarea><br />';
            $page_data['content'] .= '<label for="subname">Subscription name:</label>
            <select id="subname" name="subname">
                <option value="">Send to all</option>';
                foreach ($subNames as $option) {
                    $page_data['content'] .= '<option value="' . $option . '">' . $option . '</option>';
                }
            $page_data['content'] .= '</select>';
            $page_data['content'] .= '<p><input type="submit" value="Add to email queue"></p>';
            $page_data['content'] .= '</form><hr>';

            $page_data['content'] .= '
            <div>
                <p>*optional</p>
                <p><strong>{unsubscribe-link}</strong> - place for link to unsubscribe<br />
                <strong>{unsubscribe-link-name}</strong> - name of clickable link (only for HTML emails)</p>
            </div>';
        }

        if ($this->postAndGet('action') == 'add') {
            $page_data['headt'] .= '
            <script type="text/javascript">
            function formAutoSubmit () {
            var frm = document.getElementById("sendmail");
            frm.submit();
            }
            /* delay sending for a few seconds */
            function startSendingDelayed () {
            setTimeout(function() { formAutoSubmit(); }, 3000);
            }

            window.onload = startSendingDelayed;
            </script>';

            $dates = $this->correctDate(time(), 'd.m.Y. / H:i');

            // check is it plain text email or html
            $msg = $_POST['msg'];

            // this is plain text email
            // we need to strip tags
            if (!stristr($msg, '<html')) {
                $msg = str_replace('&nbsp;', '', strip_tags(no_br($msg, "\n")));
            }

            $theme = $this->check($_POST['theme']); // subject
            $subName = isset($_POST['subname']) == true ? $this->check($_POST['subname']) : ''; // subscription name
            $sender = $this->check($_POST['sender']); // sender name
            $email = $this->check($_POST['email']); // sender email
            $last = isset($_GET['last']) == true ? $this->check($_GET['last']) : 0;

            if (!empty($subName)) {
                $send_count = $this->db->countRow('subs', "subscription_name = '" . $subName . "'");
            }
            // Queue mail for all subscribers
            else {
                $send_count = $this->db->countRow('subs');
            }

            $next = $last + $news_queue_limit;
            if ($next > $send_count) {
                $next = $send_count;
            }

            if (!empty($subName)) {
                $sql = "SELECT * FROM subs WHERE subscription_name = '" . $subName . "' ORDER BY id LIMIT $last, " . $news_queue_limit;
            }
            // Queue mail for all subscribers
            else {
                $sql = "SELECT * FROM subs ORDER BY id LIMIT $last, " . $news_queue_limit;
            }

            foreach ($this->db->query($sql) as $res) {
                $this->_mailer->queue_email($res["user_mail"], $theme, $msg, $email, $sender);
            } 

            $last = $next;
            if ($last < $send_count) {
                $per = round(100 * $last / $send_count);

                $page_data['content'] .= '<br>Adding to queue, please wait... <img src="../themes/images/img/loading.gif" alt="" /><br>Successfully added: ' . (int)$per . '%<br><br>';

                $page_data['content'] .= '<form name="sendmail" class="sendmail" id="sendmail" action="./email_queue?action=add&last=' . $last . '" method="post" />';
                $page_data['content'] .= '<input type="hidden" name="sender" value="' . $sender . '">';
                $page_data['content'] .= '<input type="hidden" name="email" value="' . $email . '">';
                $page_data['content'] .= '<input type="hidden" name="theme" value="' . $theme . '">';
                $page_data['content'] .= '<input type="hidden" name="msg" value="' . $msg . '">';
                $page_data['content'] .= '<input type="submit" value="Continue adding to queue"></form><hr>';
            } else {
                $page_data['content'] .= '<img src="../themes/images/img/reload.gif" alt="" /> Email added to the queue for all subscribers!<br>';
            }

            $page_data['content'] .= '<br>Users subscribed to news: ' . (int)$send_count . '<br>';
            $page_data['content'] .= '<p><br><br><img src="../themes/images/img/back.gif" alt="" /> <a href="./email_queue">{@localization[back]}}</a></p>'; // update lang
        }

        $page_data['content'] .= '<p>';
        $page_data['content'] .= $this->sitelink('./', $this->localization->string('adminpanel')) . '<br />';
        $page_data['content'] .= $this->homelink();
        $page_data['content'] .= '</p>';

        return $page_data;
    }
}