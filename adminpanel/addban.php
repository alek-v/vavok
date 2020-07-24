<?php 
// (c) vavok.net
require_once"../include/startup.php";

if (!empty($_GET['action'])) {
    $action = check($_GET["action"]);
} else {
    $action = '';
} 
if (isset($_POST['users'])) {
    $user = check($_POST['users']);
} elseif (isset($_GET['users'])) {
    $user = check($_GET['users']);
} else { $user = ''; }

$time = time();

if (!$users->is_reg()) { redirect_to("../?error"); } 

if ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102 || $_SESSION['permissions'] == 103) {
    $my_title = $lang_admin['banning'];
    require_once BASEDIR . "themes/" . MY_THEME . "/index.php";
 

    echo '<img src="../images/img/partners.gif" alt=""> <b>' . $lang_admin['banunban'] . '</b><br /><br />';

    if (empty($action)) {
        echo $lang_admin['chooseuser'] . ':<br />';

        echo '<form method="post" action="addban.php?action=edit">';
        echo '<input name="users" maxlength="20" /><br /><br />';
        echo '<input value="' . $lang_home['confirm'] . '" type="submit" /></form><hr>';
    } 
    // edit profile
    if ($action == "edit") {
        if (!empty($user)) {
            if (ctype_digit($user) === false) {
                $userx_id = $users->getidfromnick($user);
                $users_nick = $users->getnickfromid($userx_id);
            } else {
                $userx_id = $user;
                $users_nick = $users->getnickfromid($user);
            } 

            $show_user = $db->get_data('vavok_users', "id='" . $userx_id . "'", 'banned, perm');
            $show_prof = $db->get_data('vavok_profil', "uid='" . $userx_id . "'", 'bantime, bandesc, allban, lastban');

            $user = check($user);
            if ($userx_id != "" && $users_nick != "") {
                echo '<img src="../images/img/profiles.gif" alt=""> <b>Profile of member ' . $users_nick . '</b><br /><br />'; // update lang
                echo 'Bans: <b>' . (int)$show_prof['allban'] . '</b><br />'; // update lang
                if (ctype_digit($show_prof['lastban'])) {
                    echo '' . $lang_admin['lastban'] . ': ' . date_fixed(check($show_prof['lastban']), "j.m.y/H:i") . '<br />';
                } 

                echo '<br />';

                if ($show_user['perm'] >= 101 && $show_user['perm'] <= 105 && $user != $users->show_username()) {
                    echo $lang_admin['noauthtoban'] . '<br /><br />';
                } else {
                    if ($user == $users->show_username()) {
                        echo '<b><font color="#FF0000">' . $lang_admin['myprofile'] . '!</font></b><br /><br />';
                    } 

                    if ($show_prof['bantime'] > 0) {
                    $ost_time = round($show_prof['bantime'] - $time);
                	} else { $ost_time = $time; }

                    if ($show_user['banned'] < 1 || $show_prof['bantime'] < $time) {
                        echo '<form method="post" action="addban.php?action=banuser&amp;users=' . $users_nick . '">';
                        echo $lang_admin['banduration'] . ':<br /><input name="duration" /><br />';

                        echo '<input name="bform" type="radio" value="min" checked> ' . $lang_admin['minutes'] . '<br />';
                        echo '<input name="bform" type="radio" value="chas"> ' . $lang_admin['hours'] . '<br />';
                        echo '<input name="bform" type="radio" value="sut"> ' . $lang_admin['days'] . '<br />';

                        echo $lang_admin['bandesc'] . ':<br /><textarea name="udd39" cols="25" rows="3"></textarea><br />';
                        echo '<input value="' . $lang_home['confirm'] . '" type="submit"></form><hr>';

                        echo $lang_admin['maxbantime'] . ' ' . formattime(round($config["maxBanTime"] * 60)) . '<br />';
                        echo $lang_admin['bandesc1'] . '<br />';
                    } else {
                        echo '<b><font color="#FF0000">' . $lang_admin['confban'] . '</font></b><br />';
                        if (ctype_digit($show_prof['lastban'])) {
                            echo '' . $lang_admin['bandate'] . ': ' . date_fixed($show_prof['lastban']) . '<br />';
                        } 
                        echo $lang_admin['banend'] . ' ' . formattime($ost_time) . '<br />';
                        echo $lang_admin['bandesc'] . ': ' . check($show_prof['bandesc']) . '<br />'; 
                        // echo 'Kaznio: <a href="../pages/user.php?uz=' . check($udc[63]) . '&amp;' . SID . '">' . check($udc[63]) . '</a><br /><br />';
                        echo '<a href="addban.php?action=deleteban&amp;users=' . $user . '" class="btn btn-outline-primary sitelink">' . $lang_admin['delban'] . '</a><hr>';
                    } 
                } 
            } else {
                echo '' . $lang_admin['usrnoexist'] . '!<br />';
            } 
        } else {
            echo '' . $lang_admin['nousername'] . '!<br />';
        } 

    } 

    if ($action == "banuser") {
        $bform = check($_POST['bform']);
        $udd38 = check($_POST['duration']);
        $users_id = $users->getidfromnick($user);
        $udd39 = check($_POST['udd39']);

        if ($users_id != "") {
            if ($bform == "min") {
                $ban_time = $udd38;
            } 
            if ($bform == "chas") {
                $ban_time = round($udd38 * 60);
            } 
            if ($bform == "sut") {
                $ban_time = round($udd38 * 60 * 24);
            } 

            if ($ban_time != "") {
                if ($ban_time <= $config["maxBanTime"]) {
                    if ($udd39 != "") {
                        $newbantime = round($time + ($ban_time * 60));
                        $newbandesc = no_br(check($udd39), ' ');
                        $newlastban = $time;

                        $vavok_profil = $db->get_data('vavok_users', "uid='" . $users_id . "'", 'allban');
                        $newallban = $vavok_profil['allban'];
                        $newallban = $newallban + 1;

                        $db->update('vavok_users', 'banned', 1, "id='" . $users_id . "'");

                        $fields = array('bantime', 'bandesc', 'lastban', 'allban');
                        $values = array($newbantime, $newbandesc, $newlastban, $newallban);
                        $db->update('vavok_profil', $fields, $values, "uid='" . $users_id . "'");

                        echo $lang_admin['usrdata'] . ' ' . $user . ' ' . $lang_admin['edited'] . '!<br />';
                        echo '<b><font color="FF0000">' . $lang_admin['confban'] . '</font></b><br /><br />';

                        echo'<a href="addban.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br />';
                    } else {
                        echo '' . $lang_admin['noreason'] . '!<br />';
                    } 
                } else {
                    echo '' . $lang_admin['maxbantimeare'] . ' ' . round($config["maxBanTime"] / 1440) . ' ' . $lang_admin['days'] . '!<br />';
                } 
            } else {
                echo '' . $lang_admin['nobantime'] . '!<br />';
            } 
        } else {
            echo $lang_admin['usrnoexist'] . '!<br />';
        } 
        echo'<br /><a href="addban.php?action=edit&amp;users=' . $user . '" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a>';
    } 

    if ($action == "deleteban") {
        $users_id = $users->getidfromnick($user);

        if ($users_id != "") {
            // update changes
            $vavok_binfo = $db->get_data('vavok_profil', "uid='" . $users_id . "'", 'allban');
            $newallban = $vavok_binfo['allban'];
            if ($newallban > 0) {
                $newallban = $newallban--;
            } 

            $db->update('vavok_users', 'banned', 0, "id='" . $users_id . "'");

            $fields = array('bantime', 'bandesc', 'allban');
            $values = array(0, '', $newallban);
            $db->update('vavok_profil', $fields, $values, "uid='" . $users_id . "'");

            echo $lang_admin['usrdata'] . '  ' . $user . ' ' . $lang_admin['edited'] . '!<br />';
            echo '<b><font color="00FF00">' . $lang_admin['confUnBan'] . '</font></b><br /><br />';

            echo'<a href="addban.php" class="btn btn-outline-primary sitelink">' . $lang_admin['changeotheruser'] . '</a><br />';
        } else {
            echo'' . $lang_home['usrnoexist'] . '!<br />';
        } 
        echo'<br /><a href="addban.php?action=edit&amp;users=' . $user . '" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a>';
    } 
    // delete user
    if ($action == "deluser") {
        $user = check($user);
        $users->delete_user($user);

        echo '' . $lang_admin['usrdeleted'] . '!<br />';

        echo '<br /><a href="addban.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a>';
    } 

    echo '<br /><a href="index.php" class="btn btn-outline-primary sitelink">' . $lang_home['admpanel'] . '</a><br />';
    echo '<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a><br />';
} else {
    redirect_to("../?error");
} 

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>
