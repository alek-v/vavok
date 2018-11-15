<?php 
// (c) vavok.net
include_once"include/strtup.php";

if (isset($_GET['pg']) && !empty($_GET['pg'])) {
    $pg = check($_GET['pg']);
}

// redirect if it is index
if ($pg == 'index' || empty($pg)) {
	header("Location: " . BASEDIR . "/");
	exit;
}

$open_page_lng = $db->select('pages', "pname='" . $pg . "' AND lang='" . $ln_loc . "'", '', '*');

if (!empty($open_page_lng['pname']) && !empty($open_page_lng['content'])) {
    if (!empty($open_page_lng['tname'])) {
        $my_title = $open_page_lng['tname'];
    }

    include"themes/" . $config_themes . "/index.php";
    
if ($open_page_lng['published'] == 1 && !isAdmin()) {
	echo '<p>Page is not published yet or it is unpublished!</p>'; // update lang
	echo '<p><br /><br />';
	echo '<a href="' . $connectionProtocol . $config_srvhost . '" class="homepage">' . $lang_home['home'] . '</a></p>';

  include"themes/$config_themes/foot.php";
  exit;
}
    
    echo $open_page_lng['content'];




    if ($config["pgFbComm"] == 1) {
        echo '<br />';
        echo '<div class="fb-comments" data-href="' . $media_page_url . '" data-width="470" data-num-posts="10"></div>';
        echo '<br />';
    }

    echo '<p><br /><br />';

    echo '<a href="' . $connectionProtocol . $config_srvhost . '" class="homepage">' . $lang_home['home'] . '</a></p>';

    include"themes/$config_themes/foot.php";
    exit;
} 

$open_page = $db->select('pages', "pname='" . $pg . "'", '', '*');

if (!empty($open_page['pname']) && !empty($open_page['content'])) {
    if (!empty($open_page['tname'])) {
        $my_title = $open_page['tname'];
    } 

    include"themes/" . $config_themes . "/index.php";
    
if ($open_page['published'] == 1 && !isAdmin()) {
	echo 'Page is not published yet or it is unpublished!';
	echo '<p><br /><br />';
	echo '<a href="' . $connectionProtocol . $config_srvhost . '" class="homepage">' . $lang_home['home'] . '</a></p>';

  include"themes/$config_themes/foot.php";
  exit;
}
    
    echo $open_page['content'];


    if ($config["pgFbComm"] == 1) {
        echo '<br />';
        echo '<div class="fb-comments" data-href="' . $media_page_url . '" data-width="470" data-num-posts="10"></div>';
        echo '<br />';
    }

    echo '<p><br /><br />';

    echo '<a href="' . $connectionProtocol . $config_srvhost . '" class="homepage">' . $lang_home['home'] . '</a></p>';

    include"themes/$config_themes/foot.php";
    exit;
} else {
// todo: fix error reporting
//header("Status: 404 Not Found");
header(check($_SERVER["SERVER_PROTOCOL"]) . " 404 Not Found");

include"themes/$config_themes/index.php";

?>
Error 404 - Page not found<br />
<div class="break"></div>
<a href="/" class="homepage">Home page</a>
<div class="break"></div>

<?php

include"themes/$config_themes/foot.php";

exit;
}
?>