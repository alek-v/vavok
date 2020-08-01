<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   01.08.2020. 19:25:19
*/

require_once"../include/startup.php";

if (!$users->is_reg() || !$users->is_administrator()) { $vavok->redirect_to('./?error=noauth'); }

if (!empty($_GET['action'])) {
    $action = $vavok->check($_GET["action"]);
} else {
    $action = '';
} 
if (!empty($_POST['users'])) {
    $user = $vavok->check($_POST['users']);
} elseif (!empty($_GET['users'])) {
    $user = $vavok->check($_GET['users']);
} else { $user = ''; }

$users_id = $users->getidfromnick($user);

require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

if (empty($action)) {
    echo '<form method="post" action="users.php?action=edit">';
    echo $localization->string('chooseuser') . ':<br>';
    echo '<input type="text" name="users" maxlength="20" /><br><br>';
    echo '<input value="' . $localization->string('showdata') . '" type="submit" /></form><hr>';
}

// change profile
if ($action == "edit") {

    if (!empty($user)) {

        $userexists = $db->get_data('vavok_users', "name='{$user}'");

        if (!empty($userexists['name'])) {

            $userx_id = $users->getidfromnick($user);
            $about_userx = $db->get_data('vavok_about', "uid='" . $userx_id . "'", 'city, about, email, site, rname');
            $userx_profil = $db->get_data('vavok_profil', "uid='" . $userx_id . "'", 'perstat, regdate, subscri, regche, allban, lastvst');
            $show_userx = $db->get_data('vavok_users', "id='" . $userx_id . "'", 'perm, browsers, banned, ipadd');
            
            if (!empty($userx_id)) {

                echo '<img src="../images/img/profiles.gif" alt=""> ' . $localization->string('usrprofile') . ' ' . $user . '<br>';

                if ($users->show_username() != $vavok->get_configuration('adminNick') && $user == $vavok->get_configuration('adminNick')) {
                    echo '<br>' . $localization->string('noauthtoedit') . '!<br>';
                    require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";
                    exit;
                } 

                if (($users->show_username() != $vavok->get_configuration('adminNick')) && ($show_userx['perm'] == 101 || $show_userx['perm'] == 102 || $show_userx['perm'] == 103 || $show_userx['perm'] == 105) && $users->show_username() != $user) {
                    echo '<br>' . $localization->string('noauthtoban') . '!<br>';
                    require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";
                    exit;
                } 
                $casenick = strcasecmp($user, $users->show_username());
                if ($casenick == 0) {
                    echo '<b><font color="red">' . $localization->string('myprofile') . '!</font></b><br><br>';
                } 

                echo '<form method="post" action="users.php?action=upgrade&amp;users=' . $user . '">';

                $userx_access = (int)$show_userx['perm'];

                if ($_SESSION['permissions'] == 101 && $users->show_username() == $vavok->get_configuration('adminNick')) {
                    $array_dostup = array(101 => "" . $localization->string('access101') . "", 102 => "" . $localization->string('access102') . "", 103 => "" . $localization->string('access103') . "", 105 => "" . $localization->string('access105') . "", 106 => "" . $localization->string('access106') . "", 107 => "" . $localization->string('access107') . "");
                    if ($userx_access == "0" || empty($userx_access)) {
                        $userx_access = "107";
                    } 

                    echo $localization->string('accesslevel') . ':<br>';
                    echo '<select name="udd7"><option value="' . $userx_access . '">' . $array_dostup[$userx_access] . '</option>';

                    foreach($array_dostup as $k => $v) {
                        if ($k != $userx_access) {
                            echo '<option value="' . $k . '">' . $v . '</option>';
                        } 
                    } 
                    echo '</select><br>';
                } 

                // website permitions for various sections
                if (file_exists('specperm.php')) {
                    echo '<a href="specperm.php?users=' . $userx_id . '" class="btn btn-outline-primary sitelink">Special permitions</a><br />';
                }

                echo $localization->string('newpassinfo') . ':<br><input name="udd1" /><br>';
                echo $localization->string('city') . ':<br><input name="udd2" value="' . $about_userx['city'] . '" /><br>';
                echo $localization->string('aboutyou') . ':<br><input name="udd3" value="' . $about_userx['about'] . '" /><br>';
                echo $localization->string('yemail') . ':<br><input name="udd4" value="' . $about_userx['email'] . '" /><br>';
                echo $localization->string('site') . ':<br><input name="udd5" value="' . $about_userx['site'] . '" /><br>'; 
                // echo $localization->string('regdate') . ':<br><input name="udd6" value="' . $vavok->date_fixed($vavok->check($userx_profil[1]), "d.m.Y") . '" /><br>';
                echo $localization->string('browser') . ':<br><input name="udd13" value="' . $show_userx['browsers'] . '" /><br>';
                echo $localization->string('name') . ':<br><input name="udd29" value="' . $about_userx['rname'] . '" /><br>';
                echo $localization->string('perstatus') . ':<br><input name="udd40" value="' . $userx_profil['perstat'] . '" /><br>';

                echo $localization->string('sitenews') . ': ';
                if ($userx_profil['subscri'] == "1") {
                    echo '<b>' . $localization->string('subscribed') . '</b><br>';
                } else {
                    echo '<b>' . $localization->string('notsubed') . '</b><br>';
                } 
                if ($show_userx['banned'] == "1") {
                    echo '<font color="#FF0000"><b>' . $localization->string('confban') . '</b></font><br>';
                } 
                if ($userx_profil['regche'] == "1") {
                    echo '<font color="#FF0000"><b>' . $localization->string('notactivated') . '</b></font><br>';
                } 
                echo $localization->string('numbbans') . ': <b>' . (int)$userx_profil['allban'] . '</b><br>';
                echo $localization->string('lastvst') . ': <b>' . $vavok->date_fixed($userx_profil['lastvst'], 'j.m.Y. / H:i') . '</b><br>';
                echo 'IP: <b>' . $show_userx['ipadd'] . '</b><br>';

                echo '<br><input value="' . $localization->string('save') . '" type="submit" /></form><hr>';

                if ($userx_access < 101 || $userx_access > 105) {
                    echo '<b><a href="users.php?action=poddel&amp;users=' . $user . '" class="btn btn-outline-primary sitelink">' . $localization->string('deluser') . '</a></b>';
                } 
            } else {
                echo $localization->string('usrnoexist') . '!';
            } 
        } else {
            echo $localization->string('usrnoexist') . '!';
        } 
    } else {
        echo $localization->string('usrnoexist') . '!';
    } 

    echo '<br><a href="users.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a>';
}

// update changes
if ($action == "upgrade") {

    $udd1 = isset($_POST['udd1']) ? $vavok->check($_POST['udd1']) : '';
    $udd2 = isset($_POST['udd2']) ? $vavok->check($_POST['udd2']) : '';
    $udd3 = isset($_POST['udd3']) ? $vavok->check($_POST['udd3']) : '';
    $udd4 = isset($_POST['udd4']) ? $vavok->check($_POST['udd4']) : '';
    $udd5 = isset($_POST['udd5']) ? $vavok->check($_POST['udd5']) : '';
    $udd6 = isset($_POST['udd6']) ? $vavok->check($_POST['udd6']) : '';
    $udd7 = isset($_POST['udd7']) ? $vavok->check($_POST['udd7']) : ''; // access level
    $udd8 = isset($_POST['udd8']) ? $vavok->check($_POST['udd8']) : '';
    $udd9 = isset($_POST['udd9']) ? $vavok->check($_POST['udd9']) : '';
    $udd10 = isset($_POST['udd10']) ? $vavok->check($_POST['udd10']) : '';
    $udd11 = isset($_POST['udd11']) ? $vavok->check($_POST['udd11']) : '';
    $udd12 = isset($_POST['udd12']) ? $vavok->check($_POST['udd12']) : '';
    $udd13 = isset($_POST['udd13']) ? $vavok->check($_POST['udd13']) : '';
    $udd29 = isset($_POST['udd29']) ? $vavok->check($_POST['udd29']) : '';
    $udd40 = isset($_POST['udd40']) ? $vavok->check($_POST['udd40']) : '';
    $udd43 = isset($_POST['udd43']) ? $vavok->check($_POST['udd43']) : '';

    if ($users->validate_email($udd4)) {

        if (empty($udd5) || $vavok->validateURL($udd5) === true) {

            $users_id = $users->getidfromnick($user);

            if (!empty($users_id)) {
                if (!empty($udd6)) {
                    list($uday, $umonth, $uyear) = explode(".", $udd6);
                    $udd6 = mktime('0', '0', '0', $umonth, $uday, $uyear);
                }

                // update profil
                $userx_pass = $db->get_data('vavok_users', "id='{$users_id}'", 'pass');

                if ($udd1 != "") {
                    $newpass = $users->password_encrypt($udd1);
                } 

                if (!empty($newpass)) {
                    $db->update('vavok_users', 'pass', $vavok->no_br($newpass), "id='{$users_id}'");
                }

                // access level
                if (!empty($udd7)) {
                    $db->update('vavok_users', 'perm', (int)$udd7, "id='{$users_id}'");
                }

                if ($udd7 == 101 || $udd7 == 102 || $udd7 == 103 || $udd7 == 105 || $udd7 == 106) {

                    // Insert data to database if does not exsist
                    if ($db->count_row('specperm', "permname='adminpanel' AND uid='{$users_id}'") < 1) {

                        $values = array(
                            'permname' => 'adminpanel',
                            'permacc' => 'show',
                            'uid' => $users_id
                        );
                        // Insert data to database
                        $db->insert_data('specperm', $values);

                    }

                }


                $db->update('vavok_users', 'browsers', $vavok->no_br($vavok->check($udd13)), "id='{$users_id}'");

                $fields = array('city', 'about', 'email', 'site', 'rname');
                $values = array(no_br($vavok->check($udd2)), $vavok->check($udd3), $vavok->no_br(htmlspecialchars(stripslashes(strtolower($udd4)))), $vavok->no_br($vavok->check($udd5)), $vavok->no_br($vavok->check($udd29)));
                $db->update('vavok_about', $fields, $values, "uid='" . $users_id . "'");
                
                $db->update('vavok_profil', 'perstat', no_br($vavok->check($udd40)), "uid='{$users_id}'");

                echo $localization->string('usrdataupd') . '!<br>';

                if (!empty($udd1)) {
                    echo '<font color=red>' . $localization->string('passchanged') . ': ' . $udd1 . '</font> <br>';
                } 
                echo '<a href="users.php" class="btn btn-outline-primary sitelink">' . $localization->string('changeotheruser') . '</a><br>';
            } else {
                echo $localization->string('usrnoexist') . '!<br>';
            }
        } else {
            echo $localization->string('urlnotok') . '!<br>';
        } 
    } else {
        echo $localization->string('emailnotok') . '<br>';
    } 
    echo '<br><a href="users.php?action=edit&amp;users=' . $user . '" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a>';
} 

// confirm delete
if ($action == "poddel") {
    echo $localization->string('confusrdel') . ' <b>' . $user . '</b>?<br><br>';
    echo '<b><a href="users.php?action=deluser&amp;users=' . $user . '" class="btn btn-outline-primary sitelink">' . $localization->string('deluser') . '</a></b>';

    echo '<br><a href="users.php?action=edit&amp;users=' . $user . '" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a>';
} 

// delete user
if ($action == "deluser") {
    if ($user != $vavok->get_configuration('adminNick')) {
        $userx_id = $users->getidfromnick($user);
        $show_userx = $db->get_data('vavok_users', "id='" . $userx_id . "'", 'perm');

        if ($show_userx['perm'] < 101 || $show_userx['perm'] > 105) {
            $users->delete_user($user);
            echo $localization->string('usrdeleted') . '!<br>';

            echo '<br><a href="users.php" class="btn btn-outline-primary sitelink">' . $localization->string('changeotheruser') . '</a><br>';
        } else {
            echo $localization->string('noaccessdel') . '<br>';
            echo '<br><a href="users.php?action=edit&amp;users=' . $user . '" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a>';
        } 
    } 
} 

echo '<p><a href="index.php" class="btn btn-outline-primary sitelink">' . $localization->string('admpanel') . '</a><br>';
echo '<a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';


require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";
?>
