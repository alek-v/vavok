<?php 
// (c) vavok.net
require_once"../include/strtup.php";
$my_title = $lang_admin['uncomfreg'];

if (!is_reg() || !checkPermissions(basename(__FILE__))) {
    header ("Location: ../index.php?error");
    exit;
} 

$act = isset($_GET['act']) ? check($_GET['act']) : '';
if (!empty($_GET['action'])) {
    $action = check($_GET["action"]);
} else {
    $action = '';
} 
if (isset($_GET['usr'])) {
    $usr = check($_GET['usr']);
} 
if (!empty($_GET['page'])) {
    $page = check($_GET["page"]);
} else {
    $page = '';
} 
if (!empty($_GET['view'])) {
    $view = check($_GET["view"]);
} else {
    $view = '';
} 

$time = time();

if ($act == 'conf' && !empty($usr)) {
    $fields = array('regche', 'regkey');
    $values = array('', '');
    $db->update('vavok_profil', $fields, $values, "uid='" . $usr . "'");

    $about_user = $db->select('vavok_about', "uid='" . $usr . "'", '', 'email');
    $vav_name = getnickfromid($usr);

    $message = "" . $lang_admin['hello'] . " " . $vav_name . "!\r\n\r\n" . $lang_admin['sitemod'] . " " . $config["homeBase"] . " " . $lang_admin['confirmedreg'] . ".\r\n" . $lang_admin['youcanlog'] . ".\r\n\r\n" . $lang_admin['bye'] . "!\r\n\r\n\r\n\r\n" . getnickfromid($user_id) . "\r\n" . ucfirst($config["homeBase"]) . "";
    sendmail($about_user['email'], "" . $lang_home['msgfrmst'] . " " . $config["title"], $message);

    header("Location: reglist.php?isset=mp_ydelconf");
    exit;
} 

include_once"../themes/$config_themes/index.php";

if (isset($_GET['isset'])) {
    $isset = check($_GET['isset']);
    echo '<div align="center"><b><font color="#FF0000">';
    echo get_isset();
    echo '</font></b></div>';
} 

if (empty($action)) {
    if ($page == "" || $page <= 0)$page = 1;
    $noi = $db->count_row('vavok_profil', "regche='1' OR regche='2'");
    $num_items = $noi; //changable
    $items_per_page = 20;
    $num_pages = ceil($num_items / $items_per_page);
    if (($page > $num_pages) && $page != 1)$page = $num_pages;
    $limit_start = ($page-1) * $items_per_page;
    if ($limit_start < 0) {
        $limit_start = 0;
    } 

    $sql = "SELECT uid, regche, regdate, lastvst FROM vavok_profil WHERE regche='1' OR regche='2' ORDER BY regdate LIMIT $limit_start, $items_per_page";

    if ($num_items > 0) {
        foreach ($db->query($sql) as $item) {
            $show_userx = $db->select('vavok_users', "id='" . $item['uid'] . "'", '', 'browsers, ipadd');
            $lnk = "<a href=\"../pages/user.php?uz=" . $item['uid'] . "\" class=\"sitelink\">" . getnickfromid($item['uid']) . "</a> (" . date_fixed($item['regdate'], 'd.m.Y. / H:i') . ")";
            if ($item['regche'] == "1") {
                $bt = "" . $lang_admin['notconfirmed'] . "!";
                $bym = '<a href="reglist.php?act=conf&amp;usr=' . $item['uid'] . '" class="sitelink">' . $lang_admin['confirms'] . '</a>';
            } else {
                $bt = "Confirmed";
            } 

            echo ' ' . $lnk . ' IP: ' . $show_userx["ipadd"] . ' browser: ' . $show_userx["browsers"] . ' ' . $bym . '<br>';
        } 
    } else {
        echo '<img src="../images/img/reload.gif" alt="" /> ' . $lang_admin['emptyunconf'] . '!<br><br>';
    } 

    echo '<div class="break"></div>';
    echo pageNavigation("reglist.php?", $items_per_page, $page, $num_items);
    echo '<div class="break"></div>';
	echo numbNavigation("reglist.php?", $items_per_page, $page, $num_items);
	echo '<div class="break"></div>';
}

echo '<br><a href="./" class="sitelink">' . $lang_home['admpanel'] . '</a><br>';
echo '<a href="../" class="homepage">' . $lang_home['home'] . '</a>';

include_once"../themes/$config_themes/foot.php";
?>
