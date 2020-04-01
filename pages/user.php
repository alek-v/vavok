<?php 
// (c) vavok.net
require_once"../include/strtup.php";

if (isset($_POST['uz'])) {
    $uz = check($_POST['uz']);
} else {
    $uz = check($_GET['uz']);
} 

if (!empty($uz)) {
if (is_numeric($uz)) {
    $users_id = $uz;
    $uz = getnickfromid($uz);
} else {
    $users_id = $uz;
    $uz = $users->getidfromnick($uz);
    header("Location: user.php?uz=" . $uz . "");
    exit;
} 
} else {
	header("Location: ../");
    exit;
}

$my_title = $lang_user['profile'] . " " . $uz;
include_once"../themes/" . $config_themes . "/index.php";


$checkIfExist = $db->count_row('vavok_users', "id='" . $users_id . "'");
$about_user = $db->select('vavok_about', "uid='" . $users_id . "'", '', 'sex, photo, city, about, birthday, site');
$user_profil = $db->select('vavok_profil', "uid='" . $users_id . "'", '', 'regche, bantime, bandesc, perstat, forummes, chat, commadd, subscri, regdate, lastvst');
$show_user = $db->select('vavok_users', "id='" . $users_id . "'", '', '*');

$showPage = new PageGen("pages/user-profile/user-profile.tpl");

// if user doesn't exist show error page
if ($checkIfExist < 1 || $users_id == 0) {
	echo '<div class="user_profile">';
    echo '<img src="../images/img/error.gif" alt="error.gif"> ' . $lang_home['usrnoexist'] . '';
    echo '</div>';
    echo '<div class="break"></div>';
echo '<div class="clear"></div>';

echo '<a href="../" class="homepage">' . $lang_home['home'] . '</a>';

include_once"../themes/" . $config_themes . "/foot.php";
exit;
}


    if ($about_user['sex'] == "N" || $about_user['sex'] == "n" || empty($about_user['sex'])) {
        $showPage->set('sex-img', '<img src="../images/img/anonim.gif" alt="" />');
    } elseif ($about_user['sex'] == "M" or $about_user['sex'] == "m") {
        $showPage->set('sex-img', '<img src="../images/img/man.png" alt="" />');
    } else {
        $showPage->set('sex-img', '<img src="../images/img/women.gif" alt="" />');
    }

    $showPage->set('profile-nick', $lang_user['profile'] . ' ' . $uz);
    $showPage->set('user-online', $users->user_online($uz));

    if ($user_profil['regche'] == "1") {
    	$showPage->set('regCheck', '<b><font color="#FF0000">' . $lang_user['notconfirmedreg'] . '!</font></b><br>');
    } else {
    	$showPage->set('regCheck', '');
    }
    
    if ($show_user['banned'] == "1" && $user_profil['bantime'] > $time) {
    	$profileBanned = new PageGen("pages/user-profile/banned.tpl");
        $profileBanned->set('banned', $lang_user['userbanned'] . '!');
        $time_ban = round($user_profil['bantime'] - $time);
        $profileBanned->set('timeLeft', $lang_user['bantimeleft'] . ': ' . formattime($time_ban));
        $profileBanned->set('reason', $lang_user['reason'] . ': ' . $user_profil['bandesc']);
    
	$showPage->set('banned', $profileBanned->output());
    } else {
    $showPage->set('banned', '');
    }

    if (!empty($user_profil['perstat'])) {
    	$personalStatus = new PageGen("pages/user-profile/status.tpl");
    	$personalStatus->set('status', $lang_user['status'] . ':');
    	$personalStatus->set('personalStatus', check($user_profil['perstat']));
    	$showPage->set('personalStatus', $personalStatus->output());
    } else {
    $showPage->set('personalStatus', '');
    }

    $showPage->set('sex', $lang_user['sex'] . '');
    if ($about_user['sex'] == "N" or $about_user['sex'] == 'n' or $about_user['sex'] == '') {
        $showPage->set('usersSex', $lang_user['notchosen']);
    } elseif ($about_user['sex'] == "M" or $about_user['sex'] == "m") {
        $showPage->set('usersSex', $lang_user['male']);
    } else {
        $showPage->set('usersSex', $lang_user['female']);
    }
    if ($about_user['city'] != "") {
        $showPage->set('city', $lang_user['city'] . ': ' . check($about_user['city']) . '<br>');
    } else {
    	$showPage->set('city', '');
    }
    if ($about_user['about'] != "") {
        $showPage->set('about', $lang_user['about'] . ': ' . check($about_user['about']) . ' <br>');
    } else {
	$showPage->set('about', '');
    }
    if (!empty($about_user['birthday']) && $about_user['birthday'] != "..") {
        $showPage->set('birthday', $lang_user['birthday'] . ': ' . check($about_user['birthday']) . '<br>');
    } else {
    $showPage->set('birthday', '');
    }

    if ($config["forumAccess"] == '1') {
        $showPage->set('forumPosts', $lang_user['formposts'] . ': ' . (int)$user_profil['forummes'] . '<br>');
    } else {
    $showPage->set('forumPosts', '');
    }

    if (!empty($show_user['browsers'])) {
        $showPage->set('browser', $lang_user['browser'] . ': ' . check($show_user['browsers']) . ' <br>');
    } else {
    	$showPage->set('browser', '');
    }

    $user_skin = $show_user['skin'];
    $user_skin = str_replace("web_", "", $user_skin);
    $user_skin = str_replace("wap_", "", $user_skin);
    $user_skin = ucfirst($user_skin);
    $showPage->set('siteSkin', $lang_user['skin'] . ': ' . check($user_skin) . '<br>');

    if ($about_user['site'] == "http://" || $about_user['site'] == "https://") {
        $about_user['site'] = "";
    } 
    if (!empty($about_user['site'])) {
        $showPage->set('site', $lang_user['site'] . ': <a href="' . check($about_user['site']) . '" target="_blank">' . $about_user['site'] . '</a><br>');
    } else {
    $showPage->set('site', '');
    }

    if (!empty($user_profil['regdate'])) {
        $showPage->set('regDate', $lang_user['regdate'] . ': ' . date_fixed(check($user_profil['regdate']), "d.m.Y.") . '<br>');
    } else {
    	$showPage->set('regDate', '');
    }

    $showPage->set('lastVisit', $lang_user['lastvisit'] . ': ' . date_fixed($user_profil['lastvst'], 'd.m.Y. / H:i'));

if (is_reg() && (ismod() || isadmin())) {
    $ipAddress = new PageGen("pages/user-profile/ip-address.tpl");
    $ipAddress->set('ip-address', 'IP address: <a href="../' . $config["mPanel"] . '/ip-informations.php?ip=' . $show_user['ipadd'] . '" target="_blank">'  . $show_user['ipadd'] . '</a>');

    $showPage->set('ip-address', $ipAddress->output());
} else {
    $showPage->set('ip-address', '');
}


    if ($uz != getnickfromid($user_id) && $users->is_reg()) {
    	$userMenu = new PageGen("pages/user-profile/user-menu.tpl");
        $userMenu->set('add-to', $lang_user['addto']);
        $userMenu->set('contacts', '<a href="buddy.php?action=ign&amp;todo=add&amp;who=' . $users_id . '">' . $lang_user['contact'] . '</a>');
        if (!isignored($users_id, $user_id)) {
        	//$userMenu->set('add-to', $lang_user['addto']);
            $userMenu->set('ignore', '<a href="ignor.php?action=ign&amp;todo=add&amp;who=' . $users_id . '">' . $lang_user['ignore'] . '</a>');
            $userMenu->set('sendMessage', '<br /><a href="inbox.php?action=sendpm&amp;who=' . $users_id . '">' . $lang_page['sendmsg'] . '</a><br>');
        } else {
            $userMenu->set('ignore', $lang_user['ignore'] . '<br />');
            $userMenu->set('sendMessage', '');
        } 

        if (is_reg() && (ismod() || isadmin())) {
            $userMenu->set('banUser', '<a href="../' . $config["mPanel"] . '/addban.php?action=edit&amp;users=' . $uz . '">' . $lang_user['bandelban'] . '</a><br>');
        } else {
        $userMenu->set('banUser', '');
        }
        if (is_reg() && isadmin('', 101)) {
        $userMenu->set('updateProfile', '<a href="../' . $config["mPanel"] . '/users.php?action=edit&amp;users=' . $uz . '">' . $lang_user['update'] . '</a><br>');
        } else {
        $userMenu->set('updateProfile', '');
        }
        $showPage->set('userMenu', $userMenu->output());
    } elseif (getnickfromid($user_id) == $uz && $users->is_reg()) {
    	$adminMenu = new PageGen("pages/user-profile/admin-update-profile.tpl");
        $adminMenu->set('profileLink', '<a href="../pages/profil.php">' . $lang_user['updateprofile'] . '</a>');
       $showPage->set('userMenu', $adminMenu->output()); 
    } else {
    $showPage->set('userMenu', ''); 
    }

    if (!empty($about_user['photo'])) {
        $ext = strtolower(substr($about_user['photo'], strrpos($about_user['photo'], '.') + 1));
        if ($users_id != $user_id) {
            $showPage->set('userPhoto', '<img src="../' . $about_user['photo'] . '" alt="" /><br>');
        } else {
            $showPage->set('userPhoto', '<a href="../pages/photo.php"><img src="../' . $about_user['photo'] . '" alt="" /></a>');
        } 
    }  else { // update
    $showPage->set('userPhoto', '');
    }

$showPage->set('homepage', '<a href="../" class="homepage">' . $lang_home['home'] . '</a>');

echo $showPage->output(); 

include_once"../themes/" . $config_themes . "/foot.php";
?>