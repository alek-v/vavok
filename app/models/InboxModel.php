<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Navigation;

class InboxModel extends BaseModel {
    public function index()
    {
        // Users data
        $data['user'] = $this->user_data;

        // Disable access for unregistered users
        if (!$this->user->userAuthenticated()) $this->redirection(HOMEDIR);

        // Update notification data
        if ($this->db->countRow('notif', "uid='{$this->user->user_id()}' AND type='inbox'") > 0) $this->db->update('notif', 'lstinb', 0, "uid='{$this->user->user_id()}' AND type='inbox'");

        $data['headt'] = '<meta name="robots" content="noindex">';
        $data['tname'] = '{@localization[inbox]}}';
        $data['content'] = '';

        $num_items = $this->user->getpmcount($this->user->user_id());
        $items_per_page = 10;
    
        // navigation
        $navigation = new Navigation($items_per_page, $num_items, $this->postAndGet('page'), 'inbox.php?');
        $limit_start = $navigation->start()['start']; // starting point
    
        if ($num_items > 0) {
        $sql = "SELECT * FROM inbox
        WHERE touid='{$this->user->user_id()}' AND (deleted IS NULL OR deleted <> '{$this->user->user_id()}')
        ORDER BY timesent DESC
        LIMIT $limit_start, $items_per_page";
    
        $senders = array();
        $i = 0;
        foreach ($this->db->query($sql) as $item) {
            // Get name of user
            $item['name'] = $item['byuid'] == 0 ? 'System' : $this->user->getNickFromId($item['byuid']);
    
            // don't list user twice
            if (!in_array($item['name'], $senders)) {
                $i = $i++;
    
                // add user to list
                array_push($senders, $item['name']);
    
                if ($item['unread'] == 1) {
                    $iml = '<img src="{@HOMEDIR}}themes/images/img/new.gif" alt="New message" />';
                } else { $iml = ''; }
    
                $lnk = $this->sitelink(HOMEDIR . 'inbox/dialog?who=' . $item['byuid'], $iml . ' ' . $item['name']);
                $data['content'] .= '<p>' . $lnk . '</p>';
            }
        }

        // navigation    
        $data['content'] .= $navigation->get_navigation();

        } else {
            $data['content'] .= '<p><img src="../themes/images/img/reload.gif" alt=""> No messages</p>';
        }

        $data['content'] .= $this->sitelink(HOMEDIR. 'inbox/sendto', 'Send message') . '<br />';

        // Pass page to the controller
        return $data;
    }

    public function dialog()
    {
        // Users data
        $data['user'] = $this->user_data;
        $data['content'] = '';

        // Update notification data
        if ($this->db->countRow('notif', "uid='{$this->user->user_id()}' AND type='inbox'") > 0) $this->db->update('notif', 'lstinb', 0, "uid='{$this->user->user_id()}' AND type='inbox'");

        $data['headt'] = '<meta name="robots" content="noindex">
        <script src="' . HOMEDIR . 'include/js/inbox.js"></script>
        <script src="' . HOMEDIR . 'include/js/ajax.js"></script>';

        $who = !empty($this->postAndGet('who')) ? $this->postAndGet('who') : 0;

        if (!isset($who) || ($who > 0 && empty($this->user->getNickFromId($who)))) {
            $data['content'] = $this->showDanger('User does not exist');

            return $data;
        } else {
            $data['who'] = $who;

            $pms = $this->db->countRow('inbox', "(byuid='" . $this->user->user_id() . "' AND touid='" . $who . "') OR (byuid='" . $who . "' AND touid='" . $this->user->user_id() . "') AND (deleted IS NULL OR deleted = '" . $who . "') ORDER BY timesent");
    
            $num_items = $pms;
            $items_per_page = 50;
            $limit_start = $num_items - $items_per_page;
            if ($limit_start < 0) $limit_start = 0;

            $data['send_readonly'] = $who == 0 ? 'readonly' : '';

            $this->db->update('inbox', 'unread', 0, "byuid='" . $who . "' AND touid='" . $this->user->user_id() . "'");

            $pms = "SELECT * FROM inbox WHERE (byuid = '" . $this->user->user_id() . "' AND touid = '" . $who . "') OR (byuid='" . $who . "' AND touid = '" . $this->user->user_id() . "') AND (deleted IS NULL OR deleted = '" . $who . "') ORDER BY timesent DESC LIMIT $limit_start, $items_per_page";
            foreach ($this->db->query($pms) as $pm) {
                $sender_nick = $pm['byuid'] == 0 ? 'System' : $this->user->getNickFromId($pm['byuid']);
                $bylnk = $pm['byuid'] == 0 ? 'System ' : $this->sitelink(HOMEDIR . 'users/u/' . $pm['byuid'], $sender_nick) . ' ';
                $data['content'] .= $bylnk;
                $tmopm = date("d m y - h:i:s", $pm['timesent']);
                $data['content'] .= "$tmopm<br />";
        
                $data['content'] .= $this->user->parsepm($pm['text']);
        
                $data['content'] .= '<hr />';
            }
        }

        $data['content'] .= $this->sitelink(HOMEDIR . 'inbox', '{@localization[inbox]}}', '<p>', '</p>');
        $data['content'] .= $this->homelink('<p>', '</p>');
        $data['tname'] = '{@localization[inbox]}}';

        // Pass page to the controller
        return $data;
    }

    public function sendto()
    {
        // Data sent, redirect to dialog
        if (!empty($this->postAndGet('who')) && $this->user->getIdFromNick($this->postAndGet('who')) > 0) {
            $this->redirection(HOMEDIR . 'inbox/dialog?who=' . $this->user->getIdFromNick($this->postAndGet('who')));
        }

        // Users data
        $data['user'] = $this->user_data;
        $data['tname'] = '{@localization[inbox]}}';
        $data['links'] = $this->sitelink(HOMEDIR. 'inbox', '{@localization[inbox]}}', '<p>', '</p>');
        $data['links'] .= $this->homelink('<p>', '</p>');

        // Pass page to the controller
        return $data;
    }

    /**
     * Send private message
     */
    public function send_message()
    {
        // Users data
        $data['user'] = $this->user_data;

        if (!$this->user->userAuthenticated()) $this->redirection(HOMEDIR . 'pages/login');

        // This is ajax request
        // Counter will not threat this as new click/visit
        if (!defined('DYNAMIC_REQUEST')) define('DYNAMIC_REQUEST', true);

        $pmtext = !empty($this->postAndGet('pmtext')) ? $this->postAndGet('pmtext') : '';
        $who = !empty($this->postAndGet('who')) ? $this->postAndGet('who') : '';

        // dont send message to system
        if ($who == 0 || empty($who)) exit;

        $inbox_notif = $this->db->selectData('notif', 'uid = :uid AND type = :type', [':uid' => $this->user->user_id(), ':type' => 'inbox'], 'active');

        $whonick = $this->user->getNickFromId($who);
        $byuid = $this->user->user_id();

        $stmt = $this->db->query("SELECT MAX(timesent) FROM inbox WHERE byuid='{$byuid}'");
        $lastpm = (integer) $stmt->fetch(PDO::FETCH_COLUMN);
        $stmt->closeCursor();

        $pmfl = $lastpm + 0; // 0 is $this->configuration("floodTime")

        if ($pmfl < time()) {
            if (!$this->user->isignored($byuid, $who)) {
                $this->user->send_pm($pmtext, $this->user->user_id(), $who);

                echo 'sent';
            } else {
                echo 'not_sent';
            } 
        }
    }

    /**
     * Receive private message
     */
    public function receive_message()
    {
        // Users data
        $data['user'] = $this->user_data;

        // This is ajax request
        // Counter will not threat this as new click/visit
        if (!defined('DYNAMIC_REQUEST')) define('DYNAMIC_REQUEST', true);

        if (!$this->user->userAuthenticated()) $this->redirection(HOMEDIR . 'users/login');

        // if there is last message id set
        if (!empty($this->postAndGet('lastid'))) {
            $sql = "SELECT * FROM inbox WHERE id > {$this->postAndGet('lastid')} AND ((byuid = {$this->postAndGet('who')} OR touid = {$this->user->user_id()}) or (byuid = {$this->user->user_id()} OR touid = {$this->postAndGet('who')})) ORDER BY id DESC LIMIT 1";
        } else {
            // no last id, load unread message
            $sql = "SELECT * FROM inbox WHERE ((byuid = {$this->postAndGet('who')} OR touid = {$this->user->user_id()}) or (byuid = {$this->user->user_id()} OR touid = {$this->postAndGet('who')})) ORDER BY id DESC LIMIT 1";
        }

        foreach($this->db->query($sql) as $item) {
            echo $this->user->getNickFromId($item['byuid']) . ':|:' . $this->user->parsepm($item['text']) . ':|:' . $item['id'] . ':|:' . $item['byuid'] . ':|:' . date("d.m.y. - H:i:s", $item['timesent']);

            // update read status
            if ($this->user->user_id() == $item['touid']) {
                $this->db->update('inbox', 'unread', 0, "id = {$item['id']} LIMIT 1");
            }
        }
    }
}