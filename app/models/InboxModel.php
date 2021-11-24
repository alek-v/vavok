<?php

class InboxModel extends Controller {
    protected object $db;
    protected object $user;
    protected object $localization;
	protected array  $user_data = [
		'authenticated' => false,
		'admin_status' => 'user',
		'language' => 'english'
	];

    public function __construct()
    {
        $this->db = new Database;

        $this->user = $this->model('User');

        // Check if user is authenticated
        if ($this->user->is_reg()) $this->user_data['authenticated'] = true;
        // Admin status
        if ($this->user->is_administrator()) $this->user_data['admin_status'] = 'administrator';
        if ($this->user->is_moderator()) $this->user_data['admin_status'] = 'moderator';
        // Users laguage
        $this->user_data['language'] = $this->user->get_user_language();

        // Localization
        $this->localization = $this->model('Localization');
        $this->localization->load();
    }

    public function index()
    {
        // Users data
        $data['user'] = $this->user_data;
        // Disable access for unregistered users
        if (!$this->user->is_reg()) $this->redirect_to(HOMEDIR);

        // Update notification data
        if ($this->db->count_row('notif', "uid='{$this->user->user_id()}' AND type='inbox'") > 0) $this->db->update('notif', 'lstinb', 0, "uid='{$this->user->user_id()}' AND type='inbox'");

        $data['headt'] = '<meta name="robots" content="noindex">
        <script src="' . HOMEDIR . 'include/js/inbox.js"></script>
        <script src="' . HOMEDIR . 'include/js/ajax.js"></script>';
        $data['tname'] = '{@website_language[inbox]}}';
        $data['content'] = '';

        $num_items = $this->user->getpmcount($this->user->user_id());
        $items_per_page = 10;
    
        // navigation
        $navigation = new Navigation($items_per_page, $num_items, $this->post_and_get('page'), 'inbox.php?');
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
            $item['name'] = $item['byuid'] == 0 ? 'System' : $this->user->getnickfromid($item['byuid']);
    
            // don't list user twice
            if (!in_array($item['name'], $senders)) {
                $i = $i++;
    
                // add user to list
                array_push($senders, $item['name']);
    
                if ($item['unread'] == 1) {
                    $iml = '<img src="../themes/images/img/new.gif" alt="New message" />';
                } else { $iml = ''; }
    
                $lnk = $this->sitelink('inbox/dialog?who=' . $item['byuid'], $iml . ' ' . $item['name']);
                $data['content'] .= '<p>' . $lnk . '</p>';
            }
        }

        // navigation    
        $data['content'] .= $navigation->get_navigation();

        } else {
            $data['content'] .= '<p><img src="../themes/images/img/reload.gif" alt=""> No messages</p>';
        }

        $data['content'] .= $this->sitelink('inbox/sendto', 'Send message') . '<br />';

        // Pass page to the controller
        return $data;
    }

    public function dialog()
    {
        // Users data
        $data['user'] = $this->user_data;

        $who = !empty($this->post_and_get('who')) ? $this->user->getidfromnick($this->post_and_get('who')) : 0;

        $data['content'] = '';

        if (!isset($who) || ($who > 0 && empty($this->user->getnickfromid($who)))) {
            $data['content'] = $this->show_danger('User does not exist');
        } else {
            $data['who'] = $who;

            $pms = $this->db->count_row('inbox', "(byuid='" . $this->user->user_id() . "' AND touid='" . $who . "') OR (byuid='" . $who . "' AND touid='" . $this->user->user_id() . "') AND (deleted IS NULL OR deleted = '" . $who . "') ORDER BY timesent");
    
            $num_items = $pms;
            $items_per_page = 50;
            $limit_start = $num_items - $items_per_page;
            if ($limit_start < 0) $limit_start = 0;

            $data['send_readonly'] = $who == 0 ? 'readonly' : '';

            $this->db->update('inbox', 'unread', 0, "byuid='" . $who . "' AND touid='" . $this->user->user_id() . "'");

            $pms = "SELECT * FROM inbox WHERE (byuid = '" . $this->user->user_id() . "' AND touid = '" . $who . "') OR (byuid='" . $who . "' AND touid = '" . $this->user->user_id() . "') AND (deleted IS NULL OR deleted = '" . $who . "') ORDER BY timesent DESC LIMIT $limit_start, $items_per_page";
            foreach ($this->db->query($pms) as $pm) {
                $sender_nick = $pm['byuid'] == 0 ? 'System' : $this->user->getnickfromid($pm['byuid']);
                $bylnk = $pm['byuid'] == 0 ? 'System ' : $vavok->sitelink(HOMEDIR . 'users/u/' . $pm['byuid'], $sender_nick) . ' ';
                $data['content'] .= $bylnk;
                $tmopm = date("d m y - h:i:s", $pm['timesent']);
                $data['content'] .= "$tmopm<br />";
        
                $data['content'] .= $this->user->parsepm($pm['text']);
        
                $data['content'] .= '<hr />';
            }
        }

        $data['content'] .= $this->sitelink(HOMEDIR . 'inbox', '{@website_language[inbox]}}', '<p>', '</p>');
        $data['content'] .= $this->homelink('<p>', '</p>');
        $data['tname'] = '{@website_language[inbox]}}';

        // Pass page to the controller
        return $data;
    }

    public function sendto()
    {
        // Users data
        $data['user'] = $this->user_data;
        $data['tname'] = '{@website_language[inbox]}}';
        $data['links'] = $this->sitelink(HOMEDIR. 'inbox', '{@website_language[inbox]}}', '<p>', '</p>');
        $data['links'] .= $this->homelink('<p>', '</p>');

        // Pass page to the controller
        return $data;
    }
}