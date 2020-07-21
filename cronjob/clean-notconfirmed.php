<?php
// modified: 26.3.2015. 2:09:28
// (c) vavok.net

include"../include/startup.php";
//include"/home/vavoknet/public_html/include/startup.php";

// hours user have to confirm registration
$confirmationHours = 48;

$confirmationTime = $confirmationHours * 60 * 60;

foreach ($db->query("SELECT `regdate`, `uid` FROM `vavok_profil` WHERE `regche`='1'") as $userCheck) {
	if (($userCheck['regdate'] + $confirmationTime) < time()) {
// delete user if he didn't confirmed registration within $confirmationHours
delete_users($userCheck['uid']);
}
}
exit;

// this cron job will delete users if they didn't complete registration within $confirmationHours
// php /home/username/public_html/cronjob/clean-notconfirmed.php >/dev/null - this should be your php script path and command to run cron job
// if this is not working, try using 
// php -q /home/vavoknet/public_html/cronjob/clean-notconfirmed.php
// or
// wget example.com/cronjob/clean-notconfirmed.php
// or
// curl http://example.com/cronjob/clean-notconfirmed.php >/dev/null 2>&1
// cron job should run this scirpt twice per hour, once per hour or at least every few hours
?>