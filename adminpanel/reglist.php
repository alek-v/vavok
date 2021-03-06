<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg() || !$vavok->go('users')->check_permissions(basename(__FILE__))) { $vavok->redirect_to("../index.php?error"); } 

$action = isset($_GET['action']) ? $vavok->check($_GET['action']) : '';
$usr = isset($_GET['usr']) ? $vavok->check($_GET['usr']) : '';
$page = isset($_GET['page']) ? $vavok->check($_GET['page']) : '';

if ($action == 'conf' && !empty($usr)) {
    $fields = array('regche', 'regkey');
    $values = array('', '');
    $vavok->go('db')->update('vavok_profil', $fields, $values, "uid='" . $usr . "'");

    $about_user = $vavok->go('db')->get_data('vavok_about', "uid='" . $usr . "'", 'email');
    $vav_name = $vavok->go('users')->getnickfromid($usr);

    $message = $vavok->go('localization')->string('hello') . " " . $vav_name . "!\r\n\r\n" . $vavok->go('localization')->string('sitemod') . " " . $vavok->get_configuration('homeBase') . " " . $vavok->go('localization')->string('confirmedreg') . ".\r\n" . $vavok->go('localization')->string('youcanlog') . ".\r\n\r\n" . $vavok->go('localization')->string('bye') . "!\r\n\r\n\r\n\r\n" . $vavok->go('users')->getnickfromid($vavok->go('users')->user_id) . "\r\n" . ucfirst($vavok->get_configuration('homeBase'));
    $newMail = new Mailer;
    $newMail->send($about_user['email'], $vavok->go('localization')->string('msgfrmst') . " " . $vavok->get_configuration('title'), $message);


    header("Location: reglist.php?isset=mp_ydelconf");
    exit;
}

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('uncomfreg');

$vavok->require_header();

if (empty($action)) {
    if ($page == "" || $page <= 0)$page = 1;
    $noi = $vavok->go('db')->count_row('vavok_profil', "regche='1' OR regche='2'");
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
        foreach ($vavok->go('db')->query($sql) as $item) {
            $show_userx = $vavok->go('db')->get_data('vavok_users', "id='" . $item['uid'] . "'", 'browsers, ipadd');
            $lnk = "<a href=\"../pages/user.php?uz=" . $item['uid'] . "\" class=\"sitelink\">" . $vavok->go('users')->getnickfromid($item['uid']) . "</a> (" . $vavok->date_fixed($item['regdate'], 'd.m.Y. / H:i') . ")";
            if ($item['regche'] == 1) {
                $bt = "" . $vavok->go('localization')->string('notconfirmed') . "!";
                $bym = '<a href="reglist.php?action=conf&amp;usr=' . $item['uid'] . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('confirms') . '</a>';
            } else {
                $bt = "Confirmed";
            } 

            echo '<p>' . $lnk . ' IP: ' . $show_userx["ipadd"] . ' ' . $vavok->go('localization')->string('browser') . ': ' . $show_userx["browsers"] . ' ' . $bym . '</p>';
        } 
    } else {
        echo '<p><img src="../images/img/reload.gif" alt="" /> ' . $vavok->go('localization')->string('emptyunconf') . '!</p>';
    } 

    $navigation = new Navigation($items_per_page, $num_items, $page, 'reglist.php?');

    echo '<p>';
        echo $navigation->get_navigation();
    echo '</p>';
}

echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('admpanel') . '</a><br />';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();
?>
