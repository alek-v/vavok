<?php
// modified: 10.1.2016. 3:36:45
// (c) vavok.net
include BASEDIR . "lang/" . $config["language"] . "/homeinfo.php";

function greet_user()
{
	global $curr_hour, $lang_homeinfo, $user_id, $log, $config;
	
	$greet = '';
	
if ($users->is_reg()) {
    $user_time = $config["timeZone"] * 3600;
    $curr_hour = date("H", time() + $user_time);
    if ($curr_hour > 24) {
        $curr_hour = round($curr_hour-24);
    } 
    if ($curr_hour < 0) {
        $curr_hour = round($curr_hour + 24);
    } 
	
    if ($curr_hour <= 4 || $curr_hour >= 23) {
        $greet = '<font color="#FF0000"><b>' . $lang_homeinfo['goodevening'] . ', <a href="/pages/user.php?uz=' . $user_id .'">' . getnickfromid($user_id) . '</a></b></font><br><br>';
    } 
    if ($curr_hour >= 5 && $curr_hour <= 10) {
        $greet = '<font color="#FF0000"><b>' . $lang_homeinfo['goodmorning'] . ', <a href="/pages/user.php?uz=' . $user_id .'">' . getnickfromid($user_id) . '</a></b></font><br><br>';
    } 
    if ($curr_hour >= 11 && $curr_hour <= 17) {
        $greet = '<font color="#FF0000"><b>' . $lang_homeinfo['goodafthernoon'] . ', <a href="/pages/user.php?uz=' . $user_id .'">' . getnickfromid($user_id) . '</a></b></font><br><br>';
    } 
    if ($curr_hour >= 18 && $curr_hour <= 22) {
        $greet = '<font color="#FF0000"><b>' . $lang_homeinfo['goodevening'] . ', <a href="/pages/user.php?uz=' . $user_id .'">' . getnickfromid($user_id) . '</a></b></font><br><br>';
    } 
    }
if ($config["showtime"] == "1") {
    $greet .= '<div align="center">' . date_fixed(time(), "d.m.Y.") . '<br />' . date_fixed(time(), "H:i") . '</div><br />';
}
    
    return $greet;
}

?>