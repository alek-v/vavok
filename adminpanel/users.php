<?php 
/*
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   11.08.2020. 14:31:41
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
    $form = new PageGen('forms/form.tpl');
    $form->set('form_method', 'post');
    $form->set('form_action', 'users.php?action=edit');

    $input_users = new PageGen('forms/input.tpl');
    $input_users->set('label_for', 'users');
    $input_users->set('label_value', $localization->string('chooseuser') . ':');
    $input_users->set('input_name', 'users');
    $input_users->set('input_id', 'users');
    $input_users->set('input_maxlength', 20);

    $form->set('website_language[save]', $localization->string('showdata'));
    $form->set('fields', $input_users->output());
    echo $form->output();
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
                    echo '<p><b><font color="red">' . $localization->string('myprofile') . '!</font></b></p>';
                }

                if ($show_userx['banned'] == 1) {
                    echo '<p><font color="#FF0000"><b>' . $localization->string('confban') . '</b></font></p>';
                }

                if ($userx_profil['regche'] == 1) {
                    echo '<p><font color="#FF0000"><b>' . $localization->string('notactivated') . '</b></font></p>';
                }

                $form = new PageGen('forms/form.tpl');
                $form->set('form_method', 'post');
                $form->set('form_action', 'users.php?action=upgrade&amp;users=' . $user);

                $userx_access = (int)$show_userx['perm'];

                if ($_SESSION['permissions'] == 101 && $users->show_username() == $vavok->get_configuration('adminNick')) {
                    $array_dostup = array(101 => $localization->string('access101'), 102 => $localization->string('access102'), 103 => $localization->string('access103'), 105 => $localization->string('access105'), 106 => $localization->string('access106'), 107 => $localization->string('access107'));
                    if ($userx_access == 0 || empty($userx_access)) {
                        $userx_access = 107;
                    }

                    $options = '<option value="' . $userx_access . '">' . $array_dostup[$userx_access] . '</option>';

                    foreach($array_dostup as $k => $v) {
                        if ($k != $userx_access) {
                            $options .= '<option value="' . $k . '">' . $v . '</option>';
                        }
                    }
                }
                $udd7 = new PageGen('forms/select.tpl');
                $udd7->set('label_for', 'udd7');
                $udd7->set('label_value', $localization->string('accesslevel'));
                $udd7->set('select_id', 'udd7');
                $udd7->set('select_name', 'udd7');
                $udd7->set('options', $options);

                // website permitions for various sections
                if (file_exists('specperm.php')) {
                    echo '<a href="specperm.php?users=' . $userx_id . '" class="btn btn-outline-primary sitelink">Special permitions</a><br />';
                }

                $udd1 = new PageGen('forms/input.tpl');
                $udd1->set('label_for', 'udd1');
                $udd1->set('label_value', $localization->string('newpassinfo'));
                $udd1->set('input_id', 'udd1');
                $udd1->set('input_name', 'udd1');

                $udd2 = new PageGen('forms/input.tpl');
                $udd2->set('label_for', 'udd2');
                $udd2->set('label_value', $localization->string('city'));
                $udd2->set('input_id', 'udd2');
                $udd2->set('input_name', 'udd2');
                $udd2->set('input_value', $about_userx['city']);

                $udd3 = new PageGen('forms/input.tpl');
                $udd3->set('label_for', 'udd3');
                $udd3->set('label_value', $localization->string('aboutyou'));
                $udd3->set('input_id', 'udd3');
                $udd3->set('input_name', 'udd3');
                $udd3->set('input_value', $about_userx['about']);

                $udd4 = new PageGen('forms/input.tpl');
                $udd4->set('label_for', 'udd4');
                $udd4->set('label_value', $localization->string('yemail'));
                $udd4->set('input_id', 'udd4');
                $udd4->set('input_name', 'udd4');
                $udd4->set('input_value', $about_userx['email']);

                $udd5 = new PageGen('forms/input.tpl');
                $udd5->set('label_for', 'udd5');
                $udd5->set('label_value', $localization->string('site'));
                $udd5->set('input_id', 'udd5');
                $udd5->set('input_name', 'udd5');
                $udd5->set('input_value', $about_userx['site']);

                $udd13 = new PageGen('forms/input.tpl');
                $udd13->set('label_for', 'udd13');
                $udd13->set('label_value', $localization->string('browser'));
                $udd13->set('input_id', 'udd13');
                $udd13->set('input_name', 'udd13');
                $udd13->set('input_value', $show_userx['browsers']);

                $udd29 = new PageGen('forms/input.tpl');
                $udd29->set('label_for', 'udd29');
                $udd29->set('label_value', $localization->string('name'));
                $udd29->set('input_id', 'udd29');
                $udd29->set('input_name', 'udd29');
                $udd29->set('input_value', $about_userx['rname']);

                $udd40 = new PageGen('forms/input.tpl');
                $udd40->set('label_for', 'udd40');
                $udd40->set('label_value', $localization->string('perstatus'));
                $udd40->set('input_id', 'udd40');
                $udd40->set('input_name', 'udd40');
                $udd40->set('input_value', $userx_profil['perstat']);
 
                if ($userx_profil['subscri'] == 1) {
                    $value = $localization->string('subscribed');
                } else {
                    $value = $localization->string('notsubed');
                }
                $subscribed = new PageGen('forms/input_readonly.tpl');
                $subscribed->set('label_for', 'subscribed');
                $subscribed->set('label_value', $localization->string('sitenews'));
                $subscribed->set('input_id', 'subscribed');
                $subscribed->set('input_name', 'subscribed');
                $subscribed->set('input_placeholder', $value);

                $allban = new PageGen('forms/input_readonly.tpl');
                $allban->set('label_for', 'allban');
                $allban->set('label_value', $localization->string('numbbans'));
                $allban->set('input_id', 'allban');
                $allban->set('input_placeholder', (int)$userx_profil['allban']);

                $lastvst = new PageGen('forms/input_readonly.tpl');
                $lastvst->set('label_for', 'lastvst');
                $lastvst->set('label_value', $localization->string('lastvst'));
                $lastvst->set('input_id', 'lastvst');
                $lastvst->set('input_placeholder', $vavok->date_fixed($userx_profil['lastvst'], 'j.m.Y. / H:i'));

                $ip = new PageGen('forms/input_readonly.tpl');
                $ip->set('label_for', 'ip');
                $ip->set('label_value', 'IP');
                $ip->set('input_id', 'ip');
                $ip->set('input_placeholder', $show_userx['ipadd']);

                $form->set('fields', $form->merge(array($udd7, $udd1, $udd2, $udd3, $udd4, $udd5, $udd13, $udd29, $udd40, $subscribed, $allban, $lastvst, $ip)));
                echo $form->output();

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

    echo '<p><a href="users.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a></p>';
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
                $values = array($vavok->no_br($vavok->check($udd2)), $vavok->check($udd3), $vavok->no_br(htmlspecialchars(stripslashes(strtolower($udd4)))), $vavok->no_br($vavok->check($udd5)), $vavok->no_br($vavok->check($udd29)));
                $db->update('vavok_about', $fields, $values, "uid='" . $users_id . "'");
                
                $db->update('vavok_profil', 'perstat', $vavok->no_br($vavok->check($udd40)), "uid='{$users_id}'");

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

echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $localization->string('admpanel') . '</a><br>';
echo '<a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";
?>
