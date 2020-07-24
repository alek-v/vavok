<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       http://vavok.net
* Updated:   22.07.2020. 1:52:59
*/

require_once"../include/startup.php";

$my_title = $lang_ban['banned'];

if ($users->is_reg()) {

    $vavok_userx = $db->get_data('vavok_users', "id='" . $user_id . "'", 'banned');
    $show_prof = $db->get_data('vavok_profil', "uid='" . $user_id . "'", 'bantime, bandesc, allban');

    $banned = $vavok_userx['banned'];
    $bantime = $show_prof['bantime'];
    $bandesc = $show_prof['bandesc'];
    $allban = $show_prof['allban'];

    $time_ban = round($bantime - time());

        if ($time_ban > 0) {

            // remove session - logout user
            $users->logout($user_id);

            // headers could not be send before cookies, so we load it here
            require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

            echo '<img src="../images/img/error.gif" alt=""> <b>' . $lang_ban['banned1'] . '</b><br /><br />';
            echo '<b><font color="#FF0000">' . $lang_ban['bandesc'] . ': ' . $bandesc . '</font></b>';
            //echo '<strong>You are logged out</strong>'; TODO - update lang and show message

            echo '<br>' . $lang_ban['timetoend'] . ' ' . formattime($time_ban);

            echo '<br><br>' . $lang_ban['banno'] . ': <b>' . (int)$allban . '</b><br>';
            echo $lang_ban['becarefnr'] . '<br /><br />';

        } else {

            require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

            echo '<img src="../images/img/open.gif" alt=""> ' . $lang_ban['wasbanned'] . '<br /><br />';
            if ($bandesc != "") {
                echo '<b><font color="#FF0000">' . $lang_ban['bandesc'] . ': ' . $bandesc . '</font></b><br>';
            } 


            echo $lang_ban['endbanadvice'] . ' <b><a href="siterules.php" class="btn btn-outline-primary sitelink">' . $lang_ban['siterules'] . '</a></b><br>';

            $db->update('vavok_users', 'banned', 0, "id='" . $user_id . "'");
            $db->update('vavok_profil', array('bantime', 'bandesc'), array('', ''), "uid='" . $user_id . "'");

        } 
 
} else {
    redirect_to("../");
} 

echo '<p><a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>