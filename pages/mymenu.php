<?php
// (c) vavok.net
require_once"../include/startup.php";



require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

if ($users->is_reg()) {
	echo '
	<a href="' . BASEDIR . 'pages/inbox.php" class="btn btn-outline-primary sitelink">' . $lang_home['inbox'] . ' (' . $users->user_mail($users->user_id) . ')</a><br>
	<a href="' . BASEDIR . 'pages/ignor.php" class="btn btn-outline-primary sitelink">' . $lang_page['ignorlist'] . '</a><br>
	<a href="' . BASEDIR . 'pages/buddy.php" class="btn btn-outline-primary sitelink">' . $lang_page['contacts'] . '</a><br>
	<a href="' . BASEDIR . 'pages/profile.php" class="btn btn-outline-primary sitelink">' . $lang_page['updprof'] . '</a><br>
	<a href="' . BASEDIR . 'pages/settings.php" class="btn btn-outline-primary sitelink">' . $lang_page['settings'] . '</a><br> 
	<a href="' . BASEDIR . 'pages/input.php?action=exit" class="btn btn-outline-primary sitelink">' . $lang_home['logout'] . '</a><br>
	';
} else {
    echo $lang_home['notloged'] . '<br />';
} 

echo '<br><a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a>';


require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>