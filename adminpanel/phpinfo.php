<?php
// (c) vavok.net

require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg() || !$vavok->go('users')->is_administrator()) $vavok->redirect_to('../');

phpinfo();

?>