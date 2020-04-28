<?php 
// (c) vavok.net
require_once"../include/strtup.php";

$my_title = $lang_ban['banned'];

$mediaLikeButton = 'off'; // dont show like buttons

if ($users->is_reg()) {
    $vavok_userx = $db->select('vavok_users', "id='" . $user_id . "'", '', 'banned');
    $show_prof = $db->select('vavok_profil', "uid='" . $user_id . "'", '', 'bantime, bandesc, allban');

    $banned = $vavok_userx['banned'];
    $bantime = $show_prof['bantime'];
    $bandesc = $show_prof['bandesc'];
    $allban = $show_prof['allban'];

    $time = time();
    $time_ban = round($bantime - $time);

        if ($time_ban > 0) {
            // remove session - logout user
            unset($_SESSION['log']);
            unset($_SESSION['pass']);
            setcookie('cookpass', "", time() - 3600);
            setcookie('cooklog', "", time() - 3600);
            setcookie(session_name(), "", time() - 3600);
            session_unset();
            session_destroy();

            // headers could not be send before cookies, so we load it here
            include_once"../themes/$config_themes/index.php";

            echo '<img src="../images/img/error.gif" alt=""> <b>' . $lang_ban['banned1'] . '</b><br /><br />';
            echo '<b><font color="#FF0000">' . $lang_ban['bandesc'] . ': ' . $bandesc . '</font></b>';
            //echo '<strong>You are logged out</strong>'; TODO - update lang and show message

            echo '<br>' . $lang_ban['timetoend'] . ' ' . formattime($time_ban);

            echo '<br><br>' . $lang_ban['banno'] . ': <b>' . (int)$allban . '</b><br>';
            echo $lang_ban['becarefnr'] . '<br /><br />';

        } else {
            include_once"../themes/$config_themes/index.php";

            echo '<img src="../images/img/open.gif" alt=""> ' . $lang_ban['wasbanned'] . '<br /><br />';
            if ($bandesc != "") {
                echo '<b><font color="#FF0000">' . $lang_ban['bandesc'] . ': ' . $bandesc . '</font></b><br>';
            } 


            echo $lang_ban['endbanadvice'] . ' <b><a href="siterules.php" class="btn btn-outline-primary sitelink">' . $lang_ban['siterules'] . '</a></b><br>';

            $db->update('vavok_users', 'banned', 0, "id='" . $user_id . "'");
            $db->update('vavok_profil', array('bantime', 'bandesc'), array('', ''), "uid='" . $user_id . "'");
        } 
 
} else {
    header ("Location: ../");
    exit;
} 

echo '<br><a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a>';

include_once"../themes/$config_themes/foot.php";

?>