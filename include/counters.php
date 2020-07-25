<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       http://vavok.net
* Online, hit & click counter
* Updated:   24.07.2020. 21:49:19
*/

// don't count visits it this is cron job
if (stristr($_SERVER['PHP_SELF'], '/cronjob/') == true) { exit; }

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

if (!$users->is_reg()) {
    $user_id = 0;
} else { $user_id = $users->user_id; }

$bz_ip = $users->find_ip();
$xmatch = $user_id . '-' . $users->find_ip() . '-' . $users->user_browser();

// delete entries that are older than the time (minutes) set in $bz_sess_timeout - inactive users
$db->delete(get_configuration('tablePrefix') . 'online',  "date + " . $bz_seconds . " < " . $bz_date);

if ($db->count_row(get_configuration('tablePrefix') . 'online', "usr_chck = '" . $xmatch . "'") > 0) {
    while ($bz_row = $db->get_data(get_configuration('tablePrefix') . 'online', "usr_chck = '" . $xmatch . "'")) {
        if (isset($bz_row['usr_chck']) && $bz_row['usr_chck'] == $xmatch) {
            $fields = array();
            $fields[] = 'date';
            $fields[] = 'page';
             
            $values = array();
            $values[] = $bz_date;
            $values[] = $_SERVER['PHP_SELF'];
             
            $db->update(get_configuration('tablePrefix') . 'online', $fields, $values, "usr_chck = '" . $xmatch . "'");
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
            'bot' => detect_bot()
            );
            $db->insert_data(get_configuration('tablePrefix') . 'online', $values);
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
    'bot' => detect_bot()
    );
    $db->insert_data(get_configuration('tablePrefix') . 'online', $values);
    unset($values);
} 

    // counter
    $counts = $db->get_data(get_configuration('tablePrefix') . 'counter');

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

$db->update(get_configuration('tablePrefix') . 'counter', $fields, $values);
unset($fields, $values);

// show stats
$counter_online = $db->count_row(get_configuration('tablePrefix') . 'online');
$counter_reg = $db->count_row(get_configuration('tablePrefix') . 'online', "user > 0");

$counter_host = $new_visits_today;
$counter_all = $new_total_visits;

$counter_hits = $new_clicks_today;
$counter_allhits = $new_total_clicks;
 

?>