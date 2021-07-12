<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg()) $vavok->redirect_to('../');

$vavok_userx = $vavok->go('db')->get_data('vavok_users', "id='{$vavok->go('users')->user_id}'", 'banned');
$show_prof = $vavok->go('db')->get_data('vavok_profil', "uid='{$vavok->go('users')->user_id}'", 'bantime, bandesc, allban');

$banned = $vavok_userx['banned'];
$bantime = $show_prof['bantime'];
$bandesc = $show_prof['bandesc'];
$allban = $show_prof['allban'];

$time_ban = round($bantime - time());

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('banned');

if ($time_ban > 0) {
    // remove session - logout user
    $vavok->go('users')->logout($vavok->go('users')->user_id);

    // headers could not be send before cookies, so we load it here
    $vavok->require_header();

    echo '<img src="../images/img/error.gif" alt=""> <b>' . $vavok->go('localization')->string('banned1') . '</b><br /><br />';
    echo '<b><font color="#FF0000">' . $vavok->go('localization')->string('bandesc') . ': ' . $bandesc . '</font></b>';
    //echo '<strong>You are logged out</strong>'; TODO - update lang and show message

    echo '<br>' . $vavok->go('localization')->string('timetoend') . ' ' . $vavok->formattime($time_ban);

    echo '<br><br>' . $vavok->go('localization')->string('banno') . ': <b>' . (int)$allban . '</b><br>';
    echo $vavok->go('localization')->string('becarefnr') . '<br /><br />';
} else {
    $vavok->require_header();

    echo '<p><img src="../images/img/open.gif" alt=""> ' . $vavok->go('localization')->string('wasbanned') . '</p>';

    if (!empty($bandesc)) {
        echo '<p><b><font color="#FF0000">' . $vavok->go('localization')->string('bandesc') . ': ' . $bandesc . '</font></b></p>';
    }

    echo '<p>' . $vavok->go('localization')->string('endbanadvice') . ' <b><a href="siterules.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('siterules') . '</a></b></p>';

    $vavok->go('db')->update('vavok_users', 'banned', 0, "id='{$vavok->go('users')->user_id}'");
    $vavok->go('db')->update('vavok_profil', array('bantime', 'bandesc'), array('', ''), "uid='{$vavok->go('users')->user_id}'");
}

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>