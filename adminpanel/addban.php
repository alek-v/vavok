<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!empty($_GET['action'])) {
    $action = $vavok->check($_GET["action"]);
} else {
    $action = '';
} 
if (isset($_POST['users'])) {
    $user = $vavok->check($_POST['users']);
} elseif (isset($_GET['users'])) {
    $user = $vavok->check($_GET['users']);
} else { $user = ''; }

$time = time();

if (!$vavok->go('users')->is_reg()) { $vavok->redirect_to("../?error"); } 

if ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102 || $_SESSION['permissions'] == 103) {
    $vavok->go('current_page')->page_title = $vavok->go('localization')->string('banning');
    $vavok->require_header();
 

    echo '<p><img src="../images/img/partners.gif" alt=""> <b>' . $vavok->go('localization')->string('banunban') . '</b></p>';

    if (empty($action)) {
        $form = new PageGen('forms/form.tpl');
        $form->set('form_method', 'post');
        $form->set('form_action', 'addban.php?action=edit');

        $input = new PageGen('forms/input.tpl');
        $input->set('label_for', 'users');
        $input->set('label_value', $vavok->go('localization')->string('chooseuser'));
        $input->set('input_type', 'text');
        $input->set('input_name', 'users');
        $input->set('input_id', 'users');
        $input->set('maxlength', 20);

        $form->set('website_language[save]', $vavok->go('localization')->string('confirm'));
        $form->set('fields', $input->output());

        echo $form->output();

        echo '<hr>';
    } 
    // edit profile
    if ($action == "edit") {
        if (!empty($user)) {
            if (ctype_digit($user) === false) {
                $userx_id = $vavok->go('users')->getidfromnick($user);
                $users_nick = $vavok->go('users')->getnickfromid($userx_id);
            } else {
                $userx_id = $user;
                $users_nick = $vavok->go('users')->getnickfromid($user);
            } 

            $show_user = $vavok->go('db')->get_data('vavok_users', "id='" . $userx_id . "'", 'banned, perm');
            $show_prof = $vavok->go('db')->get_data('vavok_profil', "uid='" . $userx_id . "'", 'bantime, bandesc, allban, lastban');

            $user = $vavok->check($user);
            if ($userx_id != "" && $users_nick != "") {
                echo '<img src="../images/img/profiles.gif" alt=""> <b>Profile of member ' . $users_nick . '</b><br /><br />'; // update lang
                echo 'Bans: <b>' . (int)$show_prof['allban'] . '</b><br />'; // update lang
                if (ctype_digit($show_prof['lastban'])) {
                    echo '' . $vavok->go('localization')->string('lastban') . ': ' . $vavok->date_fixed($vavok->check($show_prof['lastban']), "j.m.y/H:i") . '<br />';
                } 

                echo '<br />';

                if ($show_user['perm'] >= 101 && $show_user['perm'] <= 105 && $user != $vavok->go('users')->show_username()) {
                    echo $vavok->go('localization')->string('noauthtoban') . '<br /><br />';
                } else {
                    if ($user == $vavok->go('users')->show_username()) {
                        echo '<b><font color="#FF0000">' . $vavok->go('localization')->string('myprofile') . '!</font></b><br /><br />';
                    } 

                    if ($show_prof['bantime'] > 0) {
                    $ost_time = round($show_prof['bantime'] - $time);
                	} else { $ost_time = $time; }

                    if ($show_user['banned'] < 1 || $show_prof['bantime'] < $time) {
                        $form = new PageGen('forms/form.tpl');
                        $form->set('form_method', 'post');
                        $form->set('form_action', 'addban.php?action=banuser&amp;users=' . $users_nick);

                        $input_duration = new PageGen('forms/input.tpl');
                        $input_duration->set('label_for', 'duration');
                        $input_duration->set('label_value', $vavok->go('localization')->string('banduration') . ':');
                        $input_duration->set('input_id', 'duration');
                        $input_duration->set('input_name', 'duration');

                        $input_radio_1 = new PageGen('forms/radio.tpl');
                        $input_radio_1->set('label_for', 'bform');
                        $input_radio_1->set('label_value', $vavok->go('localization')->string('minutes'));
                        $input_radio_1->set('input_id', 'bform');
                        $input_radio_1->set('input_name', 'bform');
                        $input_radio_1->set('input_value', 'min');
                        $input_radio_1->set('input_status', 'checked');

                        $input_radio_2 = new PageGen('forms/radio.tpl');
                        $input_radio_2->set('label_for', 'bform');
                        $input_radio_2->set('label_value', $vavok->go('localization')->string('hours'));
                        $input_radio_2->set('input_id', 'bform');
                        $input_radio_2->set('input_name', 'bform');
                        $input_radio_2->set('input_value', 'chas');

                        $input_radio_3 = new PageGen('forms/radio.tpl');
                        $input_radio_3->set('label_for', 'bform');
                        $input_radio_3->set('label_value', $vavok->go('localization')->string('days'));
                        $input_radio_3->set('input_id', 'bform');
                        $input_radio_3->set('input_name', 'bform');
                        $input_radio_3->set('input_value', 'sut');

                        $input_textarea = new PageGen('forms/textarea.tpl');
                        $input_textarea->set('label_for', 'udd39');
                        $input_textarea->set('label_value', $vavok->go('localization')->string('bandesc'));
                        $input_textarea->set('textarea_id', 'udd39');
                        $input_textarea->set('textarea_name', 'udd39');

                        $form->set('website_language[save]', $vavok->go('localization')->string('confirm'));
                        $form->set('fields', $form->merge(array($input_duration, $input_radio_1, $input_radio_2, $input_radio_3, $input_textarea)));
                        echo $form->output();

                        echo '<hr>';

                        echo $vavok->go('localization')->string('maxbantime') . ' ' . $vavok->formattime(round($vavok->get_configuration('maxBanTime') * 60)) . '<br />';
                        echo $vavok->go('localization')->string('bandesc1') . '<br />';
                    } else {
                        echo '<b><font color="#FF0000">' . $vavok->go('localization')->string('confban') . '</font></b><br />';
                        if (ctype_digit($show_prof['lastban'])) {
                            echo '' . $vavok->go('localization')->string('bandate') . ': ' . $vavok->date_fixed($show_prof['lastban']) . '<br />';
                        } 
                        echo $vavok->go('localization')->string('banend') . ' ' . $vavok->formattime($ost_time) . '<br />';
                        echo $vavok->go('localization')->string('bandesc') . ': ' . $vavok->check($show_prof['bandesc']) . '<br />'; 
                        echo '<a href="addban.php?action=deleteban&amp;users=' . $user . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('delban') . '</a><hr>';
                    } 
                } 
            } else {
                echo '' . $vavok->go('localization')->string('usrnoexist') . '!<br />';
            } 
        } else {
            echo '' . $vavok->go('localization')->string('nousername') . '!<br />';
        } 

    } 

    if ($action == "banuser") {
        $bform = $vavok->check($_POST['bform']);
        $udd38 = $vavok->check($_POST['duration']);
        $users_id = $vavok->go('users')->getidfromnick($user);
        $udd39 = $vavok->check($_POST['udd39']);

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
                if ($ban_time <= $vavok->get_configuration('maxBanTime')) {
                    if ($udd39 != "") {
                        $newbantime = round($time + ($ban_time * 60));
                        $newbandesc = $vavok->no_br($vavok->check($udd39), ' ');
                        $newlastban = $time;

                        $vavok_profil = $vavok->go('db')->get_data('vavok_users', "uid='" . $users_id . "'", 'allban');
                        $newallban = $vavok_profil['allban'];
                        $newallban = $newallban + 1;

                        $vavok->go('db')->update('vavok_users', 'banned', 1, "id='" . $users_id . "'");

                        $fields = array('bantime', 'bandesc', 'lastban', 'allban');
                        $values = array($newbantime, $newbandesc, $newlastban, $newallban);
                        $vavok->go('db')->update('vavok_profil', $fields, $values, "uid='" . $users_id . "'");

                        echo $vavok->go('localization')->string('usrdata') . ' ' . $user . ' ' . $vavok->go('localization')->string('edited') . '!<br />';
                        echo '<b><font color="FF0000">' . $vavok->go('localization')->string('confban') . '</font></b><br /><br />';

                        echo'<a href="addban.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a><br />';
                    } else {
                        echo '' . $vavok->go('localization')->string('noreason') . '!<br />';
                    } 
                } else {
                    echo '' . $vavok->go('localization')->string('maxbantimeare') . ' ' . round($vavok->get_configuration('maxBanTime') / 1440) . ' ' . $vavok->go('localization')->string('days') . '!<br />';
                } 
            } else {
                echo '' . $vavok->go('localization')->string('nobantime') . '!<br />';
            } 
        } else {
            echo $vavok->go('localization')->string('usrnoexist') . '!<br />';
        } 
        echo'<br /><a href="addban.php?action=edit&amp;users=' . $user . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a>';
    } 

    if ($action == "deleteban") {
        $users_id = $vavok->go('users')->getidfromnick($user);

        if ($users_id != "") {
            // update changes
            $vavok_binfo = $vavok->go('db')->get_data('vavok_profil', "uid='" . $users_id . "'", 'allban');
            $newallban = $vavok_binfo['allban'];
            if ($newallban > 0) {
                $newallban = $newallban--;
            } 

            $vavok->go('db')->update('vavok_users', 'banned', 0, "id='" . $users_id . "'");

            $fields = array('bantime', 'bandesc', 'allban');
            $values = array(0, '', $newallban);
            $vavok->go('db')->update('vavok_profil', $fields, $values, "uid='" . $users_id . "'");

            echo $vavok->go('localization')->string('usrdata') . '  ' . $user . ' ' . $vavok->go('localization')->string('edited') . '!<br />';
            echo '<b><font color="00FF00">' . $vavok->go('localization')->string('confUnBan') . '</font></b><br /><br />';

            echo'<a href="addban.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('changeotheruser') . '</a><br />';
        } else {
            echo '<p>' . $vavok->go('localization')->string('usrnoexist') . '!</p>';
        } 
        echo'<p><a href="addban.php?action=edit&amp;users=' . $user . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a></a>';
    } 
    // delete user
    if ($action == "deluser") {
        $user = $vavok->check($user);
        $vavok->go('users')->delete_user($user);

        echo '<p>' . $vavok->go('localization')->string('usrdeleted') . '!</p>';

        echo '<p><a href="addban.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a></p>';
    } 

    echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('admpanel') . '</a><br />';
    echo $vavok->homelink() . '</p>';
} else {
    $vavok->redirect_to("../?error");
} 

$vavok->require_footer();

?>
