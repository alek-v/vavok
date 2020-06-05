<?php
// (c) vavok.net - Aleksandar Vranešević

require_once"../include/strtup.php";

$log = isset($log) ? $log = check($log) : $log = '';

// meta tag for this page
$genHeadTag = '<meta name="robots" content="noindex">';
$genHeadTag .= '<link rel="stylesheet" href="../themes/templates/pages/registration/register.css">';

$mediaLikeButton = 'off'; // dont show like buttons

$my_title = $lang_reg['registration'];
include_once"../themes/" . $config_themes . "/index.php";


if ($config["openReg"] == "1") {

	if ($users->is_reg()) {

		$current_page = new PageGen('pages/registration/already_registered.tpl');

		$current_page->set('message', $log . ', ' . $lang_reg['againreg']);

		echo $current_page->output();

	} else {

		$current_page = new PageGen('pages/registration/register.tpl');


		if (isset($_GET['isset'])) {
			$isset = check($_GET['isset']);
			echo '<div align="center"><b><font color="#FF0000">';
			echo get_isset();
			echo '</font></b></div>';
		}


		if (!empty($_GET['ptl'])) {
			$current_page->set('page_to_load', check($_GET['ptl']));
		}

		$current_page->set('registration_info', $lang_reg['reginfo']);

		if ($config["regConfirm"] == "1") {
			$current_page->set('registration_key_info', $lang_reg['keyinfo']);
		}

		if ($config["quarantine"] > 0) {
			$current_page->set('quarantine_info', $lang_reg['quarantine1'] . ' ' . round($config["quarantine"] / 3600) . ' ' . $lang_reg['quarantine2']);
		}

		echo $current_page->output();
		
		}

} else {

	$current_page = new PageGen('pages/registration/registration_stopped.tpl');

	$current_page->set('message', $lang_reg['regstoped']);

	echo $current_page->output();

}

include_once"../themes/" . $config_themes . "/foot.php";

?>