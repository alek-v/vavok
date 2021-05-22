<?php
// (c) vavok.net

require_once '../include/startup.php';

$action = isset($_GET['action']) ? $vavok->check($_GET['action']) : '';

if (!$vavok->go('users')->is_reg() || !$vavok->go('users')->is_administrator()) { $vavok->redirect_to("../"); } 

if (empty($action)) {
	phpinfo();
}
?>