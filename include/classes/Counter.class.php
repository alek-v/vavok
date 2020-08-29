<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
* Online, hit & click counter
 * Updated:   21.08.2020. 20:54:11
*/

class Counter {
    public $counter_online;
    public $counter_reg;
    public $counter_host;
    public $counter_all;
    public $counter_hits;
    public $counter_allhits;
    private $db;
    private $users;
    private $vavok;

    function __construct()
    {
        global $db, $users, $vavok;

        $this->db = $db;
        $this->users = $users;
        $this->vavok = $vavok;

        $day = date("d");
        $hour = date("H");
        // $daysm=date("t");
        $found = 0;
        $user = "";
        $arrtimehour = mktime(date("H"), 0, 0, date("m"), date("d"), date("Y"));
        $arrtimeday = mktime(0, 0, 0, date("m"), date("d"), date("Y"));

        // set session timeout limit in minutes
        $bz_sess_timeout = 10;

        // setup variables for use in the script
        $bz_sess_timeout = (double)$bz_sess_timeout;
        $bz_seconds = $bz_sess_timeout * 60;
        $session_del = time() + $bz_seconds;

        $bz_date = time();

        if (!$this->users->is_reg()) {
            $user_id = 0;
        } else { $user_id = $this->users->user_id; }

        $bz_ip = $this->users->find_ip();
        $xmatch = $user_id . '-' . $this->users->find_ip() . '-' . $this->users->user_browser();

        // delete entries that are older than the time (minutes) set in $bz_sess_timeout - inactive users
        $this->db->delete(DB_PREFIX . 'online',  "date + " . $bz_seconds . " < " . $bz_date);

        if ($this->db->count_row(DB_PREFIX . 'online', "usr_chck = '{$xmatch}'") > 0) {
            while ($bz_row = $this->db->get_data(DB_PREFIX . 'online', "usr_chck = '{$xmatch}'")) {
                if (isset($bz_row['usr_chck']) && $bz_row['usr_chck'] == $xmatch) {
                    $fields = array();
                    $fields[] = 'date';
                    $fields[] = 'page';
                     
                    $values = array();
                    $values[] = $bz_date;
                    $values[] = $_SERVER['PHP_SELF'];
                     
                    $this->db->update(DB_PREFIX . 'online', $fields, $values, "usr_chck = '{$xmatch}'");
                    unset($fields, $values);
                    
                    $found = 1;
                    break;
                } else {
                    $values = array(
                    'date' => $bz_date,
                    'ip' => $bz_ip,
                    'page' => $_SERVER['PHP_SELF'],
                    'user' => $user_id,
                    'usr_chck' => $xmatch,
                    'bot' => $this->vavok->detect_bot()
                    );
                    $this->db->insert_data(DB_PREFIX . 'online', $values);
                    unset($values);
                } 
            } 
        } else {
            $values = array(
            'date' => $bz_date,
            'ip' => $bz_ip,
            'page' => $_SERVER['PHP_SELF'],
            'user' => $user_id,
            'usr_chck' => $xmatch,
            'bot' => $this->vavok->detect_bot()
            );
            $this->db->insert_data(DB_PREFIX . 'online', $values);
            unset($values);
        } 

        // counter
        $counts = $this->db->get_data(DB_PREFIX . 'counter');

        $current_day = $counts['day'];
        $clicks_today = $counts['clicks_today'];
        $total_clicks = $counts['clicks_total'];
        $new_visits_today = $counts['visits_today']; // visits today
    	$new_total_visits = $counts['visits_total']; // total visits

        // current day
        if (empty($current_day) || !isset($current_day)) {
        	$current_day = $day;
    	}
    	if ($current_day != $day) {
    		$current_day = $day;
    		$clicks_today = 0;
    		$new_visits_today = 0;
    	}

        // clicks
        $new_clicks_today = $clicks_today + 1; // clicks today
        $new_total_clicks = $total_clicks + 1; // total clicks

        // visits
        if ($found == 0) {
            $new_visits_today = $new_visits_today + 1; // visits today
            $new_total_visits = $counts['visits_total'] + 1; // total visits
        }

        // update data
        $fields = array();
        $fields[] = 'day';
        $fields[] = 'month';
        $fields[] = 'clicks_today';
        $fields[] = 'clicks_total';
        $fields[] = 'visits_today';
        $fields[] = 'visits_total';

        $values = array();
        $values[] = $day;
        $values[] = date("m");
        $values[] = $new_clicks_today;
        $values[] = $new_total_clicks;
        $values[] = $new_visits_today;
        $values[] = $new_total_visits;

        $this->db->update(DB_PREFIX . 'counter', $fields, $values);
        unset($fields, $values);

        // show stats
        $this->counter_online = $this->db->count_row(DB_PREFIX . 'online');
        $this->counter_reg = $this->db->count_row(DB_PREFIX . 'online', "user > 0");

        $this->counter_host = $new_visits_today;
        $this->counter_all = $new_total_visits;

        $this->counter_hits = $new_clicks_today;
        $this->counter_allhits = $new_total_clicks;

    }

}

?>