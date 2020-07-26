<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   26.07.2020. 17:15:45
*/

require_once"../include/startup.php";

if (!$users->is_reg()) { redirect_to("../"); } 

$vavok_userx = $db->get_data('vavok_users', "id='{$users->user_id}'", 'banned');
$show_prof = $db->get_data('vavok_profil', "uid='{$users->user_id}'", 'bantime, bandesc, allban');

$banned = $vavok_userx['banned'];
$bantime = $show_prof['bantime'];
$bandesc = $show_prof['bandesc'];
$allban = $show_prof['allban'];

$time_ban = round($bantime - time());

$my_title = $localization->string('banned');

if ($time_ban > 0) {

    // remove session - logout user
    $users->logout($users->user_id);

    // headers could not be send before cookies, so we load it here
    require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

    echo '<img src="../images/img/error.gif" alt=""> <b>' . $localization->string('banned1') . '</b><br /><br />';
    echo '<b><font color="#FF0000">' . $localization->string('bandesc') . ': ' . $bandesc . '</font></b>';
    //echo '<strong>You are logged out</strong>'; TODO - update lang and show message

    echo '<br>' . $localization->string('timetoend') . ' ' . formattime($time_ban);

    echo '<br><br>' . $localization->string('banno') . ': <b>' . (int)$allban . '</b><br>';
    echo $localization->string('becarefnr') . '<br /><br />';

} else {

    require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

    echo '<p><img src="../images/img/open.gif" alt=""> ' . $localization->string('wasbanned') . '</p>';

    if (!empty($bandesc)) {
        echo '<p><b><font color="#FF0000">' . $localization->string('bandesc') . ': ' . $bandesc . '</font></b></p>';
    }

    echo '<p>' . $localization->string('endbanadvice') . ' <b><a href="siterules.php" class="btn btn-outline-primary sitelink">' . $localization->string('siterules') . '</a></b></p>';

    $db->update('vavok_users', 'banned', 0, "id='{$users->user_id}'");
    $db->update('vavok_profil', array('bantime', 'bandesc'), array('', ''), "uid='{$users->user_id}'");

}

echo '<p><a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>