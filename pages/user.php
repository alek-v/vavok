<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!empty($vavok->post_and_get('uz'))) {
    if (is_numeric($vavok->post_and_get('uz'))) {
        $users_id = $vavok->post_and_get('uz');
        $uz = $vavok->go('users')->getnickfromid($vavok->post_and_get('uz'));
    } else {
        $users_id = $vavok->go('users')->getidfromnick($vavok->post_and_get('uz'));
        $uz = $vavok->post_and_get('uz');
    } 
} else { $vavok->redirect_to("../"); }

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('profile') . " " . $uz;
$vavok->require_header();

$checkIfExist = $vavok->go('db')->count_row('vavok_users', "id='{$users_id}'");
$about_user = $vavok->go('db')->get_data('vavok_about', "uid='{$users_id}'", 'sex, photo, city, about, birthday, site');
$user_profil = $vavok->go('db')->get_data('vavok_profil', "uid='{$users_id}'", 'regche, bantime, bandesc, perstat, forummes, chat, commadd, subscri, regdate, lastvst');
$show_user = $vavok->go('db')->get_data('vavok_users', "id='{$users_id}'");

$showPage = new PageGen("pages/user-profile/user-profile.tpl");

// if user doesn't exist show error page
if ($checkIfExist < 1) {
	echo '<div class="user_profile">';
    echo '<p><img src="../images/img/error.gif" alt="error.gif"> ' . $vavok->go('localization')->string('usrnoexist');
    echo '</p></div>';

    echo $vavok->homelink('<p>', '</p>');

    $vavok->require_footer();
    exit;
}

if ($about_user['sex'] == "N" || $about_user['sex'] == "n" || empty($about_user['sex'])) {
    $showPage->set('sex-img', '<img src="../images/img/anonim.gif" alt="" />');
} elseif ($about_user['sex'] == "M" or $about_user['sex'] == "m") {
    $showPage->set('sex-img', '<img src="../images/img/man.png" alt="" />');
} else {
    $showPage->set('sex-img', '<img src="../images/img/women.gif" alt="" />');
}

$showPage->set('profile-nick', $vavok->go('localization')->string('profile') . ' ' . $uz);
$showPage->set('user-online', $vavok->go('users')->user_online($uz));

if ($user_profil['regche'] == 1) {
    $showPage->set('regCheck', '<b><font color="#FF0000">' . $vavok->go('localization')->string('notconfirmedreg') . '!</font></b><br>');
} else {
    $showPage->set('regCheck', '');
}

if ($show_user['banned'] == "1" && $user_profil['bantime'] > time()) {
    $profileBanned = new PageGen("pages/user-profile/banned.tpl");
    $profileBanned->set('banned', $vavok->go('localization')->string('userbanned') . '!');
    $time_ban = round($user_profil['bantime'] - time());
    $profileBanned->set('timeLeft', $vavok->go('localization')->string('bantimeleft') . ': ' . formattime($time_ban));
    $profileBanned->set('reason', $vavok->go('localization')->string('reason') . ': ' . $user_profil['bandesc']);

    $showPage->set('banned', $profileBanned->output());
} else {
    $showPage->set('banned', '');
}

if (!empty($user_profil['perstat'])) {
    $personalStatus = new PageGen("pages/user-profile/status.tpl");
    $personalStatus->set('status', $vavok->go('localization')->string('status') . ':');
    $personalStatus->set('personalStatus', $vavok->check($user_profil['perstat']));
    $showPage->set('personalStatus', $personalStatus->output());
} else {
    $showPage->set('personalStatus', '');
}

$showPage->set('sex', $vavok->go('localization')->string('sex') . '');

if ($about_user['sex'] == "N" or $about_user['sex'] == 'n' or $about_user['sex'] == '') {
    $showPage->set('usersSex', $vavok->go('localization')->string('notchosen'));
} elseif ($about_user['sex'] == "M" or $about_user['sex'] == "m") {
    $showPage->set('usersSex', $vavok->go('localization')->string('male'));
} else {
    $showPage->set('usersSex', $vavok->go('localization')->string('female'));
}
if ($about_user['city'] != "") {
    $showPage->set('city', $vavok->go('localization')->string('city') . ': ' . $vavok->check($about_user['city']) . '<br>');
} else {
    $showPage->set('city', '');
}
if ($about_user['about'] != "") {
    $showPage->set('about', $vavok->go('localization')->string('about') . ': ' . $vavok->check($about_user['about']) . ' <br>');
} else {
    $showPage->set('about', '');
}
if (!empty($about_user['birthday']) && $about_user['birthday'] != "..") {
    $showPage->set('birthday', $vavok->go('localization')->string('birthday') . ': ' . $vavok->check($about_user['birthday']) . '<br>');
} else {
    $showPage->set('birthday', '');
}

if ($vavok->get_configuration('forumAccess') == 1) {
    $showPage->set('forumPosts', $vavok->go('localization')->string('formposts') . ': ' . (int)$user_profil['forummes'] . '<br>');
} else {
    $showPage->set('forumPosts', '');
}

if (!empty($show_user['browsers'])) {
    $showPage->set('browser', $vavok->go('localization')->string('browser') . ': ' . $vavok->check($show_user['browsers']) . ' <br>');
} else {
    $showPage->set('browser', '');
}

$user_skin = $show_user['skin'];
$user_skin = str_replace("web_", "", $user_skin);
$user_skin = str_replace("wap_", "", $user_skin);
$user_skin = ucfirst($user_skin);
$showPage->set('siteSkin', $vavok->go('localization')->string('skin') . ': ' . $vavok->check($user_skin) . '<br>');

if ($about_user['site'] == "http://" || $about_user['site'] == "https://") {
    $about_user['site'] = "";
} 
if (!empty($about_user['site'])) {
    $showPage->set('site', $vavok->go('localization')->string('site') . ': <a href="' . $vavok->check($about_user['site']) . '" target="_blank">' . $about_user['site'] . '</a><br>');
} else {
    $showPage->set('site', '');
}

if (!empty($user_profil['regdate'])) {
    $showPage->set('regDate', $vavok->go('localization')->string('regdate') . ': ' . $vavok->date_fixed($vavok->check($user_profil['regdate']), "d.m.Y.") . '<br>');
} else {
    $showPage->set('regDate', '');
}

$showPage->set('lastVisit', $vavok->go('localization')->string('lastvisit') . ': ' . $vavok->date_fixed($user_profil['lastvst'], 'd.m.Y. / H:i'));

if ($vavok->go('users')->is_reg() && ($vavok->go('users')->is_moderator() || $vavok->go('users')->is_administrator())) {
    $ipAddress = new PageGen("pages/user-profile/ip-address.tpl");
    $ipAddress->set('ip-address', 'IP address: <a href="../' . $vavok->get_configuration('mPanel') . '/ip-informations.php?ip=' . $show_user['ipadd'] . '" target="_blank">'  . $show_user['ipadd'] . '</a>');

    $showPage->set('ip-address', $ipAddress->output());
} else {
    $showPage->set('ip-address', '');
}

if ($uz != $vavok->go('users')->getnickfromid($vavok->go('users')->user_id) && $vavok->go('users')->is_reg()) {

    $userMenu = new PageGen("pages/user-profile/user-menu.tpl");
    $userMenu->set('add-to', $vavok->go('localization')->string('addto'));
    $userMenu->set('contacts', '<a href="buddy.php?action=ign&amp;todo=add&amp;who=' . $users_id . '">' . $vavok->go('localization')->string('contact') . '</a>');
    if (!$vavok->go('users')->isignored($users_id, $vavok->go('users')->user_id)) {
    //$userMenu->set('add-to', $vavok->go('localization')->string('addto']);
    $userMenu->set('ignore', '<a href="ignor.php?action=ign&amp;todo=add&amp;who=' . $users_id . '">' . $vavok->go('localization')->string('ignore') . '</a>');
    $userMenu->set('sendMessage', '<br /><a href="inbox.php?action=dialog&amp;who=' . $users_id . '">' . $vavok->go('localization')->string('sendmsg') . '</a><br>');

    } else {
        $userMenu->set('ignore', $vavok->go('localization')->string('ignore') . '<br />');
        $userMenu->set('sendMessage', '');
    } 

    if ($vavok->go('users')->is_reg() && ($vavok->go('users')->is_moderator() || $vavok->go('users')->is_administrator())) {
        $userMenu->set('banUser', '<a href="../' . $vavok->get_configuration('mPanel') . '/addban.php?action=edit&amp;users=' . $uz . '">' . $vavok->go('localization')->string('bandelban') . '</a><br>');
    } else {
        $userMenu->set('banUser', '');
    }
    if ($vavok->go('users')->is_reg() && $vavok->go('users')->is_administrator(101)) {
        $userMenu->set('updateProfile', '<a href="../' . $vavok->get_configuration('mPanel') . '/users.php?action=edit&amp;users=' . $uz . '">' . $vavok->go('localization')->string('update') . '</a><br>');
    } else {
        $userMenu->set('updateProfile', '');
    }

    $showPage->set('userMenu', $userMenu->output());

} elseif ($vavok->go('users')->getnickfromid($vavok->go('users')->user_id) == $uz && $vavok->go('users')->is_reg()) {

    $adminMenu = new PageGen("pages/user-profile/admin-update-profile.tpl");
    $adminMenu->set('profileLink', '<a href="../pages/profile.php">' . $vavok->go('localization')->string('updateprofile') . '</a>');
    $showPage->set('userMenu', $adminMenu->output());

} else {
    $showPage->set('userMenu', ''); 
}

if (!empty($about_user['photo'])) {

    $ext = strtolower(substr($about_user['photo'], strrpos($about_user['photo'], '.') + 1));

    if ($users_id != $vavok->go('users')->user_id) {
        $showPage->set('userPhoto', '<img src="../' . $about_user['photo'] . '" alt="" /><br>');
    } else {
        $showPage->set('userPhoto', '<a href="../pages/photo.php"><img src="../' . $about_user['photo'] . '" alt="" /></a>');
    }

}  else { // update
    $showPage->set('userPhoto', '');
}

$showPage->set('homepage', $vavok->homelink());

echo $showPage->output(); 

$vavok->require_footer();
?>