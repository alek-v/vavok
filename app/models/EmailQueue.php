<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Mailer;
use App\Classes\PageManager;
use Pimple\Container;

class EmailQueue extends BaseModel {
    private object $_mailer;

    public function __construct(protected Container $container)
    {
        parent::__construct($container);

        // Instantiate mailer
        $this->_mailer = new Mailer($container);
    }

    /**
     * Send using cronjob
     */
    public function send()
    {
        // New package to be sent every $diff_time minutes
        $diff_time = 1;

        // When last email packag has been sent
        $time_sent = intval(file_get_contents(STORAGEDIR . 'email_queue_sent.dat'));

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
     * Add mails to the email queue
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

         // How manu emails to add to queue at once
        $news_queue_limit = 1000;

        // Text editor
        $emailEditor = new PageManager($this->container);
        $loadTextEditor = $emailEditor->loadPageEditor();

        // choose field selector
        $textEditor = str_replace('#selector', '#msg', $loadTextEditor);

        // add to page header
        $page_data['headt'] = $textEditor;

        if (empty($this->postAndGet('action'))) {
            $sub_names = $this->_mailer->emailSubscriptionOptions();

            $page_data['content'] .= '<h1>Add emails to the queue</h1>';

            $page_data['content'] .= '<form action="./email_queue?action=add" method="post" />';
            $page_data['content'] .= '<div class="form-group">
            <label for="sender">Sender name:</label>
            <input class="form-control" id="sender" type="text" name="sender" value="">
            </div>';
            $page_data['content'] .= '<div class="form-group">
            <label for="email">Sender\'s email:</label>
            <input class="form-control" id="email" type="text" name="email" value="">
            </div>';
            $page_data['content'] .= '<div class="form-group">
            <label for="theme">Subject:</label>
            <input class="form-control" id="theme" type="text" name="theme" value="">
            </div>';
            $page_data['content'] .= '<div class="form-group">
            <label for="msg">Email content:</label>';
            $page_data['content'] .= '<textarea class="form-control" rows="15" id="msg" name="msg"></textarea>
            </div>';
            $page_data['content'] .= '<div class="form-group">
            <label for="subname">Subscription name:</label>
            <select class="form-control" id="subname" name="subname">
                <option value="">Send to all</option>';
                foreach ($sub_names as $option) {
                    $page_data['content'] .= '<option value="' . $option . '">' . $option . '</option>';
                }
            $page_data['content'] .= '</select>
            </div>';
            $page_data['content'] .= '<div class="form-group">';
            $page_data['content'] .= '<label for="type">Email Type:</label>';
            $page_data['content'] .= '<select class="form-control" id="type" name="type">';
            $page_data['content'] .= '<option value="html">HTML</option>';
            $page_data['content'] .= '<option value="plain">Plain text</option>';
            $page_data['content'] .= '</select>';
            $page_data['content'] .= '</div>';
            $page_data['content'] .= '<button type="submit" class="btn btn-primary">Add to the email queue</button>';
            $page_data['content'] .= '</form>';

            $page_data['content'] .= '<hr>
            <div>
                <p>*optional</p>
                <p><strong>{unsubscribe-link}</strong> - use this code to place for link to unsubscribe<br />
                <strong>{unsubscribe-link-name}</strong> - use this code to place a name of clickable link (only for HTML emails)</p>
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
            $theme = $this->check($_POST['theme']); // subject
            $sub_name = isset($_POST['subname']) == true ? $this->check($_POST['subname']) : ''; // subscription name
            $sender = $this->check($_POST['sender']); // sender name
            $email = $this->check($_POST['email']); // sender email
            $type = $this->check($_POST['type']); // sender email
            $last = isset($_GET['last']) == true ? $this->check($_GET['last']) : 0;
            $msg = $_POST['msg'];

            // Strip tags in case we are sending plain text mail
            if ($type == 'plain') {
                $msg = str_replace('&nbsp;', '', strip_tags($this->replaceNewLines($msg, "\n")));
            }

            if (!empty($sub_name)) {
                $send_count = $this->db->countRow('subs', "subscription_name = '{$sub_name}'");
            }
            // Queue mail for all subscribers
            else {
                $send_count = $this->db->countRow('subs');
            }

            $next = $last + $news_queue_limit;
            if ($next > $send_count) {
                $next = $send_count;
            }

            if (!empty($sub_name)) {
                $sql = "SELECT * FROM subs WHERE subscription_name = '" . $sub_name . "' ORDER BY id LIMIT $last, " . $news_queue_limit;
            }
            // Queue mail for all subscribers
            else {
                $sql = "SELECT * FROM subs ORDER BY id LIMIT $last, " . $news_queue_limit;
            }

            foreach ($this->db->query($sql) as $res) {
                $this->_mailer->queueEmail($res["user_mail"], $theme, $msg, $email, $sender);
            } 

            $last = $next;
            if ($last < $send_count) {
                $per = round(100 * $last / $send_count);

                $page_data['content'] .= '<p>Adding to the queue, please wait... <img src="../themes/images/img/loading.gif" alt="" /><br>Successfully added: ' . (int)$per . '%</p>';

                $page_data['content'] .= '<form name="sendmail" class="sendmail" id="sendmail" action="./email_queue?action=add&last=' . $last . '" method="post" />';
                $page_data['content'] .= '<input type="hidden" name="sender" value="' . $sender . '">';
                $page_data['content'] .= '<input type="hidden" name="email" value="' . $email . '">';
                $page_data['content'] .= '<input type="hidden" name="theme" value="' . $theme . '">';
                $page_data['content'] .= '<input type="hidden" name="msg" value="' . $msg . '">';
                $page_data['content'] .= '<input type="hidden" name="subname" value="' . $sub_name . '">';
                $page_data['content'] .= '<input type="hidden" name="type" value="' . $type . '">';
                $page_data['content'] .= '<input type="submit" value="Continue adding to queue"></form><hr>';
            } else {
                $page_data['content'] .= '<img src="../themes/images/img/reload.gif" alt="" /> Email added to the queue for all subscribers!<br>';
            }

            $page_data['content'] .= '<p>Users subscribed to news: ' . (int)$send_count . '</p>';
            $page_data['content'] .= '<p><img src="../themes/images/img/back.gif" alt="" /> <a href="./email_queue">{@localization[back]}}</a></p>'; // update lang
        }

        $page_data['content'] .= '<p>';
        $page_data['content'] .= $this->sitelink('./', $this->localization->string('adminpanel')) . '<br />';
        $page_data['content'] .= $this->homelink();
        $page_data['content'] .= '</p>';

        return $page_data;
    }
}