<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg() || !$vavok->go('users')->is_administrator()) { $vavok->redirect_to('./?error=noauth'); }

if (!empty($_POST['users'])) {
    $user = $vavok->check($_POST['users']);
} elseif (!empty($_GET['users'])) {
    $user = $vavok->check($_GET['users']);
} else { $user = ''; }

$users_id = $vavok->go('users')->getidfromnick($user);

$vavok->require_header();

if (empty($vavok->post_and_get('action'))) {
    $form = new PageGen('forms/form.tpl');
    $form->set('form_method', 'post');
    $form->set('form_action', 'users.php?action=edit');

    $input_users = new PageGen('forms/input.tpl');
    $input_users->set('label_for', 'users');
    $input_users->set('label_value', $vavok->go('localization')->string('chooseuser') . ':');
    $input_users->set('input_name', 'users');
    $input_users->set('input_id', 'users');
    $input_users->set('input_maxlength', 20);

    $form->set('website_language[save]', $vavok->go('localization')->string('showdata'));
    $form->set('fields', $input_users->output());
    echo $form->output();
}

// change profile
if ($vavok->post_and_get('action') == 'edit') {
    if (!empty($user)) {
        $userexists = $vavok->go('db')->get_data('vavok_users', "name='{$user}'");

        if (!empty($userexists['name'])) {
            $userx_id = $vavok->go('users')->getidfromnick($user);
            
            if (!empty($userx_id)) {
                echo '<img src="../images/img/profiles.gif" alt=""> ' . $vavok->go('localization')->string('usrprofile') . ' ' . $user . '<br>';

                if ($vavok->go('users')->show_username() != $vavok->get_configuration('adminNick') && $user == $vavok->get_configuration('adminNick')) {
                    echo '<br>' . $vavok->go('localization')->string('noauthtoedit') . '!<br>';
                    $vavok->require_footer();
                    exit;
                }

                if (($vavok->go('users')->show_username() != $vavok->get_configuration('adminNick')) && ($vavok->go('users')->user_info('perm', $userx_id) == 101 || $vavok->go('users')->user_info('perm', $userx_id) == 102 || $vavok->go('users')->user_info('perm', $userx_id) == 103 || $vavok->go('users')->user_info('perm', $userx_id) == 105) && $vavok->go('users')->show_username() != $user) {
                    echo '<br>' . $vavok->go('localization')->string('noauthtoban') . '!<br>';
                    $vavok->require_footer();
                    exit;
                }

                $casenick = strcasecmp($user, $vavok->go('users')->show_username());

                if ($casenick == 0) echo '<p><b><font color="red">' . $vavok->go('localization')->string('myprofile') . '!</font></b></p>';

                if ($vavok->go('users')->user_info('banned', $userx_id) == 1) echo '<p><font color="#FF0000"><b>' . $vavok->go('localization')->string('confban') . '</b></font></p>';

                if ($vavok->go('users')->user_info('regche', $userx_id) == 1) echo '<p><font color="#FF0000"><b>' . $vavok->go('localization')->string('notactivated') . '</b></font></p>';

                $form = new PageGen('forms/form.tpl');
                $form->set('form_method', 'post');
                $form->set('form_action', 'users.php?action=upgrade&amp;users=' . $user);

                $userx_access = (int)$vavok->go('users')->user_info('perm', $userx_id);

                if ($_SESSION['permissions'] == 101 && $vavok->go('users')->show_username() == $vavok->get_configuration('adminNick')) {
                    $array_dostup = array(101 => $vavok->go('localization')->string('access101'), 102 => $vavok->go('localization')->string('access102'), 103 => $vavok->go('localization')->string('access103'), 105 => $vavok->go('localization')->string('access105'), 106 => $vavok->go('localization')->string('access106'), 107 => $vavok->go('localization')->string('access107'));

                    if ($userx_access == 0 || empty($userx_access)) $userx_access = 107;

                    $options = '<option value="' . $userx_access . '">' . $array_dostup[$userx_access] . '</option>';
                    foreach($array_dostup as $k => $v) {
                        if ($k != $userx_access) {
                            $options .= '<option value="' . $k . '">' . $v . '</option>';
                        }
                    }
                }

                $udd7 = new PageGen('forms/select.tpl');
                $udd7->set('label_for', 'udd7');
                $udd7->set('label_value', $vavok->go('localization')->string('accesslevel'));
                $udd7->set('select_id', 'udd7');
                $udd7->set('select_name', 'udd7');
                $udd7->set('options', $options);

                // website permitions for various sections
                if (file_exists('specperm.php')) {
                    echo '<a href="specperm.php?users=' . $userx_id . '" class="btn btn-outline-primary sitelink">Special permitions</a><br />';
                }

                $udd1 = new PageGen('forms/input.tpl');
                $udd1->set('label_for', 'udd1');
                $udd1->set('label_value', $vavok->go('localization')->string('newpassinfo'));
                $udd1->set('input_id', 'udd1');
                $udd1->set('input_name', 'udd1');

                $udd2 = new PageGen('forms/input.tpl');
                $udd2->set('label_for', 'udd2');
                $udd2->set('label_value', $vavok->go('localization')->string('city'));
                $udd2->set('input_id', 'udd2');
                $udd2->set('input_name', 'udd2');
                $udd2->set('input_value', $vavok->go('users')->user_info('city', $userx_id));

                $udd3 = new PageGen('forms/input.tpl');
                $udd3->set('label_for', 'udd3');
                $udd3->set('label_value', $vavok->go('localization')->string('aboutyou'));
                $udd3->set('input_id', 'udd3');
                $udd3->set('input_name', 'udd3');
                $udd3->set('input_value', $vavok->go('users')->user_info('about', $userx_id));

                $udd4 = new PageGen('forms/input.tpl');
                $udd4->set('label_for', 'udd4');
                $udd4->set('label_value', $vavok->go('localization')->string('yemail'));
                $udd4->set('input_id', 'udd4');
                $udd4->set('input_name', 'udd4');
                $udd4->set('input_value', $vavok->go('users')->user_info('email', $userx_id));

                $udd5 = new PageGen('forms/input.tpl');
                $udd5->set('label_for', 'udd5');
                $udd5->set('label_value', $vavok->go('localization')->string('site'));
                $udd5->set('input_id', 'udd5');
                $udd5->set('input_name', 'udd5');
                $udd5->set('input_value', $vavok->go('users')->user_info('site', $userx_id));

                $udd13 = new PageGen('forms/input.tpl');
                $udd13->set('label_for', 'udd13');
                $udd13->set('label_value', $vavok->go('localization')->string('browser'));
                $udd13->set('input_id', 'udd13');
                $udd13->set('input_name', 'udd13');
                $udd13->set('input_value', $vavok->go('users')->user_info('browser', $userx_id));

                $udd29 = new PageGen('forms/input.tpl');
                $udd29->set('label_for', 'udd29');
                $udd29->set('label_value', $vavok->go('localization')->string('name'));
                $udd29->set('input_id', 'udd29');
                $udd29->set('input_name', 'udd29');
                $udd29->set('input_value', $vavok->go('users')->user_info('firstname', $userx_id));

                $udd40 = new PageGen('forms/input.tpl');
                $udd40->set('label_for', 'udd40');
                $udd40->set('label_value', $vavok->go('localization')->string('perstatus'));
                $udd40->set('input_id', 'udd40');
                $udd40->set('input_name', 'udd40');
                $udd40->set('input_value', $vavok->go('users')->user_info('perstat', $userx_id));
 
                if ($vavok->go('users')->user_info('subscribed', $userx_id) == 1) {
                    $value = $vavok->go('localization')->string('subscribed');
                } else {
                    $value = $vavok->go('localization')->string('notsubed');
                }
                $subscribed = new PageGen('forms/input_readonly.tpl');
                $subscribed->set('label_for', 'subscribed');
                $subscribed->set('label_value', $vavok->go('localization')->string('sitenews'));
                $subscribed->set('input_id', 'subscribed');
                $subscribed->set('input_name', 'subscribed');
                $subscribed->set('input_placeholder', $value);

                $allban = new PageGen('forms/input_readonly.tpl');
                $allban->set('label_for', 'allban');
                $allban->set('label_value', $vavok->go('localization')->string('numbbans'));
                $allban->set('input_id', 'allban');
                $allban->set('input_placeholder', (int)$vavok->go('users')->user_info('allban', $userx_id));

                $lastvst = new PageGen('forms/input_readonly.tpl');
                $lastvst->set('label_for', 'lastvst');
                $lastvst->set('label_value', $vavok->go('localization')->string('lastvst'));
                $lastvst->set('input_id', 'lastvst');
                $lastvst->set('input_placeholder', $vavok->date_fixed($vavok->go('users')->user_info('lastvisit', $userx_id), 'j.m.Y. / H:i'));

                $ip = new PageGen('forms/input_readonly.tpl');
                $ip->set('label_for', 'ip');
                $ip->set('label_value', 'IP');
                $ip->set('input_id', 'ip');
                $ip->set('input_placeholder', $vavok->go('users')->user_info('ipaddress', $userx_id));

                $form->set('fields', $form->merge(array($udd7, $udd1, $udd2, $udd3, $udd4, $udd5, $udd13, $udd29, $udd40, $subscribed, $allban, $lastvst, $ip)));
                echo $form->output();

                if ($userx_access < 101 || $userx_access > 105) {
                    echo '<b><a href="users.php?action=poddel&amp;users=' . $user . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('deluser') . '</a></b>';
                } 
            } else {
                echo $vavok->go('localization')->string('usrnoexist') . '!';
            } 
        } else {
            echo $vavok->go('localization')->string('usrnoexist') . '!';
        } 
    } else {
        echo $vavok->go('localization')->string('usrnoexist') . '!';
    } 

    echo '<p><a href="users.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a></p>';
}

// update changes
if ($vavok->post_and_get('action') == 'upgrade') {
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

    if ($vavok->go('users')->validate_email($udd4)) {
        if (empty($udd5) || $vavok->validateURL($udd5) === true) {
            $users_id = $vavok->go('users')->getidfromnick($user);

            if (!empty($users_id)) {
                if (!empty($udd6)) {
                    list($uday, $umonth, $uyear) = explode(".", $udd6);
                    $udd6 = mktime('0', '0', '0', $umonth, $uday, $uyear);
                }

                if (!empty($udd1)) $newpass = $vavok->go('users')->password_encrypt($udd1);

                // update password
                if (!empty($newpass)) $vavok->go('db')->update(DB_PREFIX . 'vavok_users', 'pass', $vavok->no_br($newpass), "id='{$users_id}'");

                // access level
                if (!empty($udd7)) {
                    $vavok->go('db')->update(DB_PREFIX . 'vavok_users', 'perm', (int)$udd7, "id='{$users_id}'");
                }

                if ($udd7 == 101 || $udd7 == 102 || $udd7 == 103 || $udd7 == 105 || $udd7 == 106) {
                    // Insert data to database if does not exsist
                    if ($vavok->go('db')->count_row('specperm', "permname='adminpanel' AND uid='{$users_id}'") < 1) {
                        $values = array(
                            'permname' => 'adminpanel',
                            'permacc' => 'show',
                            'uid' => $users_id
                        );
                        // Insert data to database
                        $vavok->go('db')->insert(DB_PREFIX . 'specperm', $values);
                    }
                }

                $vavok->go('db')->update(DB_PREFIX . 'vavok_users', 'browsers', $vavok->no_br($vavok->check($udd13)), "id='{$users_id}'");

                $fields = array('city', 'about', 'email', 'site', 'rname');
                $values = array($vavok->no_br($vavok->check($udd2)), $vavok->check($udd3), $vavok->no_br(htmlspecialchars(stripslashes(strtolower($udd4)))), $vavok->no_br($vavok->check($udd5)), $vavok->no_br($vavok->check($udd29)));
                $vavok->go('db')->update(DB_PREFIX . 'vavok_about', $fields, $values, "uid='" . $users_id . "'");
                
                $vavok->go('db')->update(DB_PREFIX . 'vavok_profil', 'perstat', $vavok->no_br($vavok->check($udd40)), "uid='{$users_id}'");

                echo $vavok->go('localization')->string('usrdataupd') . '!<br>';

                if (!empty($udd1)) {
                    echo '<font color=red>' . $vavok->go('localization')->string('passchanged') . ': ' . $udd1 . '</font> <br>';
                } 
                echo '<a href="users.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('changeotheruser') . '</a><br>';
            } else {
                echo $vavok->go('localization')->string('usrnoexist') . '!<br>';
            }
        } else {
            echo $vavok->go('localization')->string('urlnotok') . '!<br>';
        } 
    } else {
        echo $vavok->go('localization')->string('emailnotok') . '<br>';
    } 
    echo '<br><a href="users.php?action=edit&amp;users=' . $user . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a>';
} 

// confirm delete
if ($vavok->post_and_get('action') == "poddel") {
    echo $vavok->go('localization')->string('confusrdel') . ' <b>' . $user . '</b>?<br><br>';
    echo '<b><a href="users.php?action=deluser&amp;users=' . $user . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('deluser') . '</a></b>';

    echo '<br><a href="users.php?action=edit&amp;users=' . $user . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a>';
} 

// delete user
if ($vavok->post_and_get('action') == "deluser") {
    if ($user != $vavok->get_configuration('adminNick')) {
        $userx_id = $vavok->go('users')->getidfromnick($user);

        if ($vavok->go('users')->user_info('perm', $userx_id) < 101 || $vavok->go('users')->user_info('perm', $userx_id) > 105) {
            $vavok->go('users')->delete_user($user);
            echo $vavok->go('localization')->string('usrdeleted') . '!<br>';

            echo '<br><a href="users.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('changeotheruser') . '</a><br>';
        } else {
            echo $vavok->go('localization')->string('noaccessdel') . '<br>';
            echo '<br><a href="users.php?action=edit&amp;users=' . $user . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a>';
        } 
    } 
} 

echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('admpanel') . '</a><br>';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();
?>
