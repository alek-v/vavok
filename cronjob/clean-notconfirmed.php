<?php
// (c) vavok.net

require_once '../include/startup.php';

$vavok->go('users')->clean_unconfirmed();

// this cron job will delete users if they didn't complete registration within $confirmationHours
// php /home/username/public_html/cronjob/clean-notconfirmed.php >/dev/null - this should be your php script path and command to run cron job
// if this is not working, try using 
// php -q /home/vavoknet/public_html/cronjob/clean-notconfirmed.php
// or
// wget example.com/cronjob/clean-notconfirmed.php
// or
// curl http://example.com/cronjob/clean-notconfirmed.php >/dev/null 2>&1
// cron job should run this script twice per hour or at least every few hours
?>