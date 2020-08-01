<?php
// (c) vavok.net

require_once"../include/startup.php";

$action = isset($_GET['action']) ? $vavok->check($_GET['action']) : '';

if (!$users->is_reg() || !$users->is_administrator()) { $vavok->redirect_to("../"); } 

if (empty($action)) {
	phpinfo();
}
if ($action == 'magic_quotes_check') {
	if (get_magic_quotes_gpc()) {
		echo "Magic quotes are enabled";
	} else {
		echo "Magic quotes are disabled";
	}
}
?>