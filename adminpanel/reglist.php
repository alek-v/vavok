<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   26.07.2020. 14:31:19
*/

require_once"../include/startup.php";

if (!$users->is_reg() || !$users->check_permissions(basename(__FILE__))) { redirect_to("../index.php?error"); } 

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

if ($act == 'conf' && !empty($usr)) {
    $fields = array('regche', 'regkey');
    $values = array('', '');
    $db->update('vavok_profil', $fields, $values, "uid='" . $usr . "'");

    $about_user = $db->get_data('vavok_about', "uid='" . $usr . "'", 'email');
    $vav_name = $users->getnickfromid($usr);

    $message = $localization->string('hello') . " " . $vav_name . "!\r\n\r\n" . $localization->string('sitemod') . " " . get_configuration('homeBase') . " " . $localization->string('confirmedreg') . ".\r\n" . $localization->string('youcanlog') . ".\r\n\r\n" . $localization->string('bye') . "!\r\n\r\n\r\n\r\n" . $users->getnickfromid($users->user_id) . "\r\n" . ucfirst(get_configuration('homeBase'));
    $newMail = new Mailer;
    $newMail->send($about_user['email'], $localization->string('msgfrmst') . " " . get_configuration('title'), $message);


    header("Location: reglist.php?isset=mp_ydelconf");
    exit;
}

$my_title = $localization->string('uncomfreg'];

require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

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
            $show_userx = $db->get_data('vavok_users', "id='" . $item['uid'] . "'", 'browsers, ipadd');
            $lnk = "<a href=\"../pages/user.php?uz=" . $item['uid'] . "\" class=\"sitelink\">" . $users->getnickfromid($item['uid']) . "</a> (" . date_fixed($item['regdate'], 'd.m.Y. / H:i') . ")";
            if ($item['regche'] == 1) {
                $bt = "" . $localization->string('notconfirmed') . "!";
                $bym = '<a href="reglist.php?act=conf&amp;usr=' . $item['uid'] . '" class="btn btn-outline-primary sitelink">' . $localization->string('confirms') . '</a>';
            } else {
                $bt = "Confirmed";
            } 

            echo ' ' . $lnk . ' IP: ' . $show_userx["ipadd"] . ' browser: ' . $show_userx["browsers"] . ' ' . $bym . '<br>';
        } 
    } else {
        echo '<img src="../images/img/reload.gif" alt="" /> ' . $localization->string('emptyunconf') . '!<br><br>';
    } 

    $navigation = new Navigation($items_per_page, $num_items, $page, 'reglist.php?');

    echo '<p>';
        echo $navigation->get_navigation();
    echo '</p>';
}

echo '<br><a href="./" class="btn btn-outline-primary sitelink">' . $localization->string('admpanel') . '</a><br>';
echo '<a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";
?>
