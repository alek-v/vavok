<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

/**
 * Delete emails that has been sent
 */
$vavok->go('db')->delete('email_queue', "sent = 1 AND timesent < (NOW() - INTERVAL 1 DAY)");

?>