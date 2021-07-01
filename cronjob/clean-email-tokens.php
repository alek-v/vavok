<?php

require_once '../include/startup.php';

$now = new DateTime();
$new_time = $now->format('Y-m-d H:i:s');

$vavok->go('db')->delete(DB_PREFIX . 'tokens', "expiration_time < '{$new_time}'");

?>