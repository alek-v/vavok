<?php
// (c) vavok.net
require_once"../include/strtup.php";

if (isset($_GET['action'])) {
    $action = check($_GET['action']);
}

if (!is_reg() || !isadmin()) {
    header("Location: ../");
    exit;
} 

if (empty($action)) {
phpinfo();
}
if ($action == 'magic_quotes_check') {
if(get_magic_quotes_gpc()) {
	echo "Magic quotes are enabled";
} else {
	echo "Magic quotes are disabled";
}
}
?>