<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

// Get users nick and users id number
if (!empty($vavok->post_and_get('uz'))) {
    // Case when id number is used in url
    if (is_numeric($vavok->post_and_get('uz'))) {
        $users_id = $vavok->post_and_get('uz');
        $uz = $vavok->go('users')->getnickfromid($vavok->post_and_get('uz'));
    } else {
        $users_id = $vavok->go('users')->getidfromnick($vavok->post_and_get('uz'));
        $uz = $vavok->post_and_get('uz');
    }
}

// Show error page if user doesn't exist
if (!isset($users_id) || $vavok->go('db')->count_row('vavok_users', "id='{$users_id}'") == 0) {
    $vavok->go('current_page')->page_title = 'User doesn\'t exist';
    $vavok->require_header();

	echo '<div class="user_profile">';
    echo '<p><img src="' . STATIC_THEMES_URL . '/images/img/error.gif" alt="Error"> ' . $vavok->go('localization')->string('usrnoexist');
    echo '</p></div>';

    echo $vavok->homelink('<p>', '</p>');

    $vavok->require_footer();
    exit;
}

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('profile') . ' ' . $uz;
$vavok->require_header();

// Load page from template
$showPage = new PageGen('pages/user-profile/user-profile.tpl');

// Show gender image
if ($vavok->go('users')->user_info('gender', $users_id) == 'N' || $vavok->go('users')->user_info('gender', $users_id) == 'n' || empty($vavok->go('users')->user_info('gender', $users_id))) {
    $showPage->set('sex-img', '<img src="' . STATIC_THEMES_URL . '/images/img/anonim.gif" alt="" />');
} elseif ($vavok->go('users')->user_info('gender', $users_id) == 'M' or $vavok->go('users')->user_info('gender', $users_id) == 'm') {
    $showPage->set('sex-img', '<img src="' . STATIC_THEMES_URL . '/images/img/man.png" alt="Male" />');
} else {
    $showPage->set('sex-img', '<img src="' . STATIC_THEMES_URL . '/images/img/women.gif" alt="Female" />');
}

// Show nickname
$showPage->set('profile-nick', $vavok->go('localization')->string('profile') . ' ' . $uz);

// Show online status
$showPage->set('user-online', $vavok->go('users')->user_online($uz));

// Message if user need to confirm registration
if ($vavok->go('users')->user_info('regche', $users_id) == 1) $showPage->set('regCheck', '<b><font color="#FF0000">' . $vavok->go('localization')->string('notconfirmedreg') . '!</font></b><br>');

if ($vavok->go('users')->user_info('banned', $users_id) == 1 && $vavok->go('users')->user_info('bantime', $users_id) > time()) {
    $profileBanned = new PageGen('pages/user-profile/banned.tpl');
    $profileBanned->set('banned', $vavok->go('localization')->string('userbanned') . '!');
    $time_ban = round($vavok->go('users')->user_info('bantime', $users_id) - time());
    $profileBanned->set('timeLeft', $vavok->go('localization')->string('bantimeleft') . ': ' . formattime($time_ban));
    $profileBanned->set('reason', $vavok->go('localization')->string('reason') . ': ' . $vavok->go('users')->user_info('bandesc', $users_id));
    $showPage->set('banned', $profileBanned->output());
}

// Personal status
if (!empty($vavok->go('users')->user_info('status', $users_id))) {
    $personalStatus = new PageGen('pages/user-profile/status.tpl');
    $personalStatus->set('status', $vavok->go('localization')->string('status') . ':');
    $personalStatus->set('personalStatus', $vavok->check($vavok->go('users')->user_info('status', $users_id)));
    $showPage->set('personalStatus', $personalStatus->output());
}

$showPage->set('sex', $vavok->go('localization')->string('sex'));

// First name
if (!empty($vavok->go('users')->user_info('firstname', $users_id))) $showPage->set('firstname', $vavok->go('users')->user_info('firstname', $users_id));

// Last name
if (!empty($vavok->go('users')->user_info('lastname', $users_id))) $showPage->set('lastname', $vavok->go('users')->user_info('lastname', $users_id));

// User's gender
if ($vavok->go('users')->user_info('gender', $users_id) == 'N' or $vavok->go('users')->user_info('gender', $users_id) == 'n' || empty($vavok->go('users')->user_info('gender', $users_id))) {
    $showPage->set('usersSex', $vavok->go('localization')->string('notchosen'));
} elseif ($vavok->go('users')->user_info('gender', $users_id) == 'M' or $vavok->go('users')->user_info('gender', $users_id) == 'm') {
    $showPage->set('usersSex', $vavok->go('localization')->string('male'));
} else {
    $showPage->set('usersSex', $vavok->go('localization')->string('female'));
}

// City
if (!empty($vavok->go('users')->user_info('city', $users_id))) $showPage->set('city', $vavok->go('localization')->string('city') . ': ' . $vavok->check($vavok->go('users')->user_info('city', $users_id)) . '<br>');

// Abou user
if (!empty($vavok->go('users')->user_info('about', $users_id))) $showPage->set('about', $vavok->go('localization')->string('about') . ': ' . $vavok->check($vavok->go('users')->user_info('about', $users_id)) . ' <br>');

// User's birthday
if (!empty($vavok->go('users')->user_info('birthday', $users_id)) && $vavok->go('users')->user_info('birthday', $users_id) != "..") $showPage->set('birthday', $vavok->go('localization')->string('birthday') . ': ' . $vavok->check($vavok->go('users')->user_info('birthday', $users_id)) . '<br>');

// Forum posts
if ($vavok->get_configuration('forumAccess') == 1) $showPage->set('forumPosts', $vavok->go('localization')->string('formposts') . ': ' . (int)$vavok->go('users')->user_info('forummes', $users_id) . '<br>');

// User's browser
if (!empty($vavok->go('users')->user_info('browser', $users_id))) $showPage->set('browser', $vavok->go('localization')->string('browser') . ': ' . $vavok->check($vavok->go('users')->user_info('browser', $users_id)) . ' <br>');

// Website
if (!empty($vavok->go('users')->user_info('site', $users_id)) && $vavok->go('users')->user_info('site', $users_id) != 'http://' && $vavok->go('users')->user_info('site', $users_id) != 'https://') $showPage->set('site', $vavok->go('localization')->string('site') . ': <a href="' . $vavok->check($vavok->go('users')->user_info('site', $users_id)) . '" target="_blank">' . $vavok->go('users')->user_info('site', $users_id) . '</a><br>');

// Registration date
if (!empty($vavok->go('users')->user_info('regdate', $users_id))) $showPage->set('regDate', $vavok->go('localization')->string('regdate') . ': ' . $vavok->date_fixed($vavok->check($vavok->go('users')->user_info('regdate', $users_id)), 'd.m.Y.') . '<br>');

// Last visit
$timezone = $vavok->go('users')->is_reg() ? $vavok->go('users')->user_info('timezone') : $vavok->get_configuration('timezone');
$showPage->set('lastVisit', $vavok->go('localization')->string('lastvisit') . ': ' . $vavok->date_fixed($vavok->go('users')->user_info('lastvisit', $users_id), 'd.m.Y. / H:i', $timezone, true));

if ($vavok->go('users')->is_reg() && ($vavok->go('users')->is_moderator() || $vavok->go('users')->is_administrator())) {
    $ipAddress = new PageGen('pages/user-profile/ip-address.tpl');
    $ipAddress->set('ip-address', 'IP address: <a href="../' . $vavok->get_configuration('mPanel') . '/ip-informations.php?ip=' . $vavok->check($vavok->go('users')->user_info('ipaddress', $users_id)) . '" target="_blank">'  . $vavok->check($vavok->go('users')->user_info('ipaddress', $users_id)) . '</a>');
    $showPage->set('ip-address', $ipAddress->output());
}

if ($uz != $vavok->go('users')->getnickfromid($vavok->go('users')->user_id) && $vavok->go('users')->is_reg()) {
    $userMenu = new PageGen('pages/user-profile/user-menu.tpl');
    $userMenu->set('add-to', $vavok->go('localization')->string('addto'));
    $userMenu->set('contacts', '<a href="buddy.php?action=ign&amp;todo=add&amp;who=' . $users_id . '">' . $vavok->go('localization')->string('addtocontacts') . '</a>');

    if (!$vavok->go('users')->isignored($users_id, $vavok->go('users')->user_id)) {
    //$userMenu->set('add-to', $vavok->go('localization')->string('addto']);
    $userMenu->set('ignore', '<a href="ignor.php?action=ign&amp;todo=add&amp;who=' . $users_id . '">' . $vavok->go('localization')->string('ignore') . '</a>');
    $userMenu->set('sendMessage', '<br /><a href="inbox.php?action=dialog&amp;who=' . $users_id . '">' . $vavok->go('localization')->string('sendmsg') . '</a><br>');
    } else {
        $userMenu->set('ignore', $vavok->go('localization')->string('ignore') . '<br />');
    }

    if ($vavok->go('users')->is_reg() && ($vavok->go('users')->is_moderator() || $vavok->go('users')->is_administrator())) $userMenu->set('banUser', '<a href="../' . $vavok->get_configuration('mPanel') . '/addban.php?action=edit&amp;users=' . $uz . '">' . $vavok->go('localization')->string('bandelban') . '</a><br>');

    if ($vavok->go('users')->is_reg() && $vavok->go('users')->is_administrator(101)) $userMenu->set('updateProfile', '<a href="../' . $vavok->get_configuration('mPanel') . '/users.php?action=edit&amp;users=' . $uz . '">' . $vavok->go('localization')->string('update') . '</a><br>');

    $showPage->set('userMenu', $userMenu->output());
} elseif ($vavok->go('users')->getnickfromid($vavok->go('users')->user_id) == $uz && $vavok->go('users')->is_reg()) {
    $adminMenu = new PageGen('pages/user-profile/admin-update-profile.tpl');
    $adminMenu->set('profileLink', '<a href="../pages/profile.php">' . $vavok->go('localization')->string('updateprofile') . '</a>');
    $showPage->set('userMenu', $adminMenu->output());
}

if (!empty($vavok->go('users')->user_info('photo', $users_id))) {
    $ext = strtolower(substr($vavok->go('users')->user_info('photo', $users_id), strrpos($vavok->go('users')->user_info('photo', $users_id), '.') + 1));

    if ($users_id != $vavok->go('users')->user_id) {
        $showPage->set('userPhoto', '<img src="../' . $vavok->go('users')->user_info('photo', $users_id) . '" alt="Profile picture" /><br>');
    } else {
        $showPage->set('userPhoto', '<a href="../pages/photo.php"><img src="../' . $vavok->go('users')->user_info('photo', $users_id) . '" alt="Profile picture" /></a>');
    }
}

// Homepage link
$showPage->set('homepage', $vavok->homelink());

// Show page
echo $showPage->output(); 

$vavok->require_footer();
?>