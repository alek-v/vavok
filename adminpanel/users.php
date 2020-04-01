<?php 
// (c) vavok.net
require_once"../include/strtup.php";

if (!empty($_GET['action'])) {
    $action = check($_GET["action"]);
} else {
    $action = '';
} 
if (!empty($_POST['users'])) {
    $users = check($_POST['users']);
} elseif (!empty($_GET['users'])) {
    $users = check($_GET['users']);
} else { $users = ''; }

$users_id = $users->getidfromnick($users);

if (is_reg()) {
    if ($accessr == 101 || $accessr == 102) {
        include_once"../themes/$config_themes/index.php";
        if (isset($_GET['isset'])) {
            $isset = check($_GET['isset']);
            echo '<div align="center"><b><font color="#FF0000">';
            echo get_isset();
            echo '</font></b></div>';
        } 
        if (empty($action)) {
            echo '<form method="post" action="users.php?action=edit">';
            echo '' . $lang_admin['chooseuser'] . ':<br>';
            echo '<input type="text" name="users" maxlength="20" /><br><br>';
            echo '<input value="' . $lang_admin['showdata'] . '" type="submit" /></form><hr>';
        } 
        // change profile
        if ($action == "edit") {
            if (!empty($users)) {
                $userexists = $db->select('vavok_users', "name='" . $users . "'", '', '*');

                if (!empty($userexists['name'])) {
                    $userx_id = $users->getidfromnick($users);
                    $about_userx = $db->select('vavok_about', "uid='" . $userx_id . "'", '', 'city, about, email, site, rname');
                    $userx_profil = $db->select('vavok_profil', "uid='" . $userx_id . "'", '', 'perstat, regdate, subscri, regche, allban, lastvst');
                    $show_userx = $db->select('vavok_users', "id='" . $userx_id . "'", '', 'perm, browsers, banned, ipadd');
                    if ($userx_id != "") {
                        echo '<img src="../images/img/profiles.gif" alt=""> ' . $lang_admin['usrprofile'] . ' ' . $users . '<br>';

                        if ($log != $config["adminNick"] && $users == $config["adminNick"]) {
                            echo '<br>' . $lang_admin['noauthtoedit'] . '!<br>';
                            include_once"../themes/$config_themes/foot.php";
                            exit;
                        } 

                        if (($log != $config["adminNick"]) && ($show_userx['perm'] == 101 || $show_userx['perm'] == 102 || $show_userx['perm'] == 103 || $show_userx['perm'] == 105) && $log != $users) {
                            echo '<br>' . $lang_admin['noauthtoban'] . '!<br>';
                            include_once"../themes/$config_themes/foot.php";
                            exit;
                        } 
                        $casenick = strcasecmp($users, $log);
                        if ($casenick == 0) {
                            echo '<b><font color="red">' . $lang_admin['myprofile'] . '!</font></b><br><br>';
                        } 

                        echo '<form method="post" action="users.php?action=upgrade&amp;users=' . $users . '">';

                        $userx_access = (int)$show_userx['perm'];

                        if ($accessr == 101 && $log == $config["adminNick"]) {
                            $array_dostup = array(101 => "" . $lang_home['access101'] . "", 102 => "" . $lang_home['access102'] . "", 103 => "" . $lang_home['access103'] . "", 105 => "" . $lang_home['access105'] . "", 106 => "" . $lang_home['access106'] . "", 107 => "" . $lang_home['access107'] . "");
                            if ($userx_access == "0" || empty($userx_access)) {
                                $userx_access = "107";
                            } 

                            echo $lang_admin['accesslevel'] . ':<br>';
                            echo '<select name="udd7"><option value="' . $userx_access . '">' . $array_dostup[$userx_access] . '</option>';

                            foreach($array_dostup as $k => $v) {
                                if ($k != $userx_access) {
                                    echo '<option value="' . $k . '">' . $v . '</option>';
                                } 
                            } 
                            echo '</select><br>';
                        } 

                        echo '<a href="specperm.php?users=' . $userx_id . '" class="sitelink">Special permitions</a><br />';
                        echo $lang_admin['newpassinfo'] . ':<br><input name="udd1" /><br>';
                        echo $lang_admin['city'] . ':<br><input name="udd2" value="' . $about_userx['city'] . '" /><br>';
                        echo $lang_admin['aboutyou'] . ':<br><input name="udd3" value="' . $about_userx['about'] . '" /><br>';
                        echo 'Email:<br><input name="udd4" value="' . $about_userx['email'] . '" /><br>';
                        echo $lang_admin['site'] . ':<br><input name="udd5" value="' . $about_userx['site'] . '" /><br>'; 
                        // echo $lang_admin['regdate'] . ':<br><input name="udd6" value="' . date_fixed(check($userx_profil[1]), "d.m.Y") . '" /><br>';
                        echo $lang_admin['browser'] . ':<br><input name="udd13" value="' . $show_userx['browsers'] . '" /><br>';
                        echo $lang_admin['name'] . ':<br><input name="udd29" value="' . $about_userx['rname'] . '" /><br>';
                        echo $lang_admin['perstatus'] . ':<br><input name="udd40" value="' . $userx_profil['perstat'] . '" /><br>';

                        echo $lang_admin['sitenews'] . ': ';
                        if ($userx_profil['subscri'] == "1") {
                            echo '<b>' . $lang_admin['subscribed'] . '</b><br>';
                        } else {
                            echo '<b>' . $lang_admin['notsubed'] . '</b><br>';
                        } 
                        if ($show_userx['banned'] == "1") {
                            echo '<font color="#FF0000"><b>' . $lang_admin['confban'] . '</b></font><br>';
                        } 
                        if ($userx_profil['regche'] == "1") {
                            echo '<font color="#FF0000"><b>' . $lang_admin['notactivated'] . '</b></font><br>';
                        } 
                        echo '' . $lang_admin['numbbans'] . ': <b>' . (int)$userx_profil['allban'] . '</b><br>';
                        echo $lang_admin['lastvst'] . ': <b>' . date_fixed($userx_profil['lastvst'], 'j.m.Y. / H:i') . '</b><br>';
                        echo 'IP: <b>' . $show_userx['ipadd'] . '</b><br>';

                        echo '<br><input value="' . $lang_home['save'] . '" type="submit" /></form><hr>';

                        if ($userx_access < 101 || $userx_access > 105) {
                            echo '<b><a href="users.php?action=poddel&amp;users=' . $users . '" class="sitelink">' . $lang_admin['deluser'] . '</a></b>';
                        } 
                    } else {
                        echo $lang_admin['usrnoexist'] . '!';
                    } 
                } else {
                    echo $lang_admin['usrnoexist'] . '!';
                } 
            } else {
                echo $lang_admin['usrnoexist'] . '!';
            } 

            echo '<br><a href="users.php" class="sitelink">' . $lang_home['back'] . '</a>';
        } 
        // update changes
        if ($action == "upgrade") {
            $udd1 = check($_POST['udd1']);
            $udd2 = check($_POST['udd2']);
            $udd3 = check($_POST['udd3']);
            $udd4 = check($_POST['udd4']);
            $udd5 = check($_POST['udd5']);
            $udd6 = check($_POST['udd6']);
            $udd7 = check($_POST['udd7']); // access level
            $udd8 = check($_POST['udd8']);
            $udd9 = check($_POST['udd9']);
            $udd10 = check($_POST['udd10']);
            $udd11 = check($_POST['udd11']);
            $udd12 = check($_POST['udd12']);
            $udd13 = check($_POST['udd13']);
            $udd29 = check($_POST['udd29']);
            $udd43 = check($_POST['udd43']);

            if (isValidEmail($udd4)) {
                if (empty($udd5) || validateURL($udd5) === true) {
                    $users_id = $users->getidfromnick($users);
                    if (!empty($users_id)) {
                        list($uday, $umonth, $uyear) = explode(".", $udd6);
                        $usecond = mktime('0', '0', '0', $umonth, $uday, $uyear);
                        $udd6 = $usecond; 
                        // update profil
                        $userx_pass = $db->select('vavok_users', "id='" . $users_id . "'", '', 'pass');

                        if ($udd1 != "") {
                            $newpass = md5($udd1);
                        } 

                        if (!empty($newpass)) {
                            $db->update('vavok_users', 'pass', no_br($newpass), "id='" . $users_id . "'");
                        }

                        // access level
                        if (!empty($udd7) && $udd7 != "") {
                            $db->update('vavok_users', 'perm', (int)$udd7, "id='" . $users_id . "'");
                        }
                        // put access to files
                        if ($udd7 == 101 || $udd7 == 102 || $udd7 == 103 || $udd7 == 105 || $udd7 == 106) {
                            $handle = fopen("../used/dataadmin/moderator_pages.dat", "r");
                            if ($handle) {
                                while (($line = fgets($handle)) !== false) {
                                    $fileData = explode('||', $line);
                                    $accessName = trim($fileData[2]);

                                    if ($db->count_row('specperm', "permname='" . $accessName . "' AND uid='" . $users_id . "'") < 1) {
                                        $values = array('permname' => $accessName, 'permacc' => 'show', 'uid' => $users_id);
                                        $db->insert_data('specperm', $values);
                                    }
                                }

                                fclose($handle);
                            }
 
                        }
                        if ($udd7 == 101 || $udd7 == 102 || $udd7 == 103 || $udd7 == 105 || $udd7 == 106) {
	                        if ($db->count_row('specperm', "permname='adminpanel' AND uid='" . $users_id . "'") < 1) {
                                $values = array(
                                    'permname' => 'adminpanel',
                                    'permacc' => 'show',
                                    'uid' => $users_id
                                );
                                $db->insert_data('specperm', $values);
	                        } 
                        }


                        $db->update('vavok_users', 'browsers', no_br(check($udd13)), "id='" . $users_id . "'");

                        $fields = array('city', 'about', 'email', 'site', 'rname');
                        $values = array(no_br(check($udd2)), check($udd3), no_br(htmlspecialchars(stripslashes(strtolower($udd4)))), no_br(check($udd5)), no_br(check($udd29)));
                        $db->update('vavok_about', $fields, $values, "uid='" . $users_id . "'");
                        
                        $db->update('vavok_profil', 'perstat', no_br(check($udd40)), "uid='" . $users_id . "'");

                        echo $lang_admin['usrdataupd'] . '!<br>';

                        if (!empty($udd1)) {
                            echo '<font color=red>' . $lang_admin['passchanged'] . ': ' . $udd1 . '</font> <br>';
                        } 
                        echo '<a href="users.php" class="sitelink">' . $lang_admin['changeotheruser'] . '</a><br>';
                    } else {
                        echo $lang_admin['usrnoexist'] . '!<br>';
                    }
                } else {
                    echo $lang_admin['urlnotok'] . '!<br>';
                } 
            } else {
                echo $lang_admin['emailnotok'] . '<br>';
            } 
            echo '<br><a href="users.php?action=edit&amp;users=' . $users . '" class="sitelink">' . $lang_home['back'] . '</a>';
        } 
        // confirm delete
        if ($action == "poddel") {
            echo $lang_admin['confusrdel'] . ' <b>' . $users . '</b>?<br><br>';
            echo '<b><a href="users.php?action=deluser&amp;users=' . $users . '" class="sitelink">' . $lang_admin['deluser'] . '</a></b>';

            echo '<br><a href="users.php?action=edit&amp;users=' . $users . '" class="sitelink">' . $lang_home['back'] . '</a>';
        } 
        // delete user
        if ($action == "deluser") {
            if ($users != $config["adminNick"]) {
                $userx_id = $users->getidfromnick($users);
                $show_userx = $db->select('vavok_users', "id='" . $userx_id . "'", '', 'perm');

                if ($show_userx['perm'] < 101 || $show_userx['perm'] > 105) {
                    $users->delete_user($users);
                    echo $lang_admin['usrdeleted'] . '!<br>';

                    echo '<br><a href="users.php" class="sitelink">' . $lang_admin['changeotheruser'] . '</a><br>';
                } else {
                    echo $lang_admin['noaccessdel'] . '<br>';
                    echo '<br><a href="users.php?action=edit&amp;users=' . $users . '" class="sitelink">' . $lang_home['back'] . '</a>';
                } 
            } 
        } 

        echo '<p><a href="index.php" class="sitelink">' . $lang_home['admpanel'] . '</a><br>';
        echo '<a href="../" class="homepage">' . $lang_home['home'] . '</a></p>';
    } else {
        header ("Location: ../?error");
        exit;
    } 
} else {
    header ("Location: ../?error");
    exit;
} 

include_once"../themes/" . $config_themes . "/foot.php";
?>
