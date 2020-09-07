<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
*/

include "../include/startup.php";

/**
 * Delete emails that has been sent
 */
$vavok->go('db')->delete('email_queue', "sent = 1 AND timesent < (NOW() - INTERVAL 1 DAY)");

?>