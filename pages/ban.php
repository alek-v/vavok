<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg()) $vavok->redirect_to('../');

// Ban description
$bandesc = $vavok->go('users')->user_info('bandesc');

// Ban time
$time_ban = round($vavok->go('users')->user_info('bantime') - time());

// Page title
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

    echo '<br><br>' . $vavok->go('localization')->string('banno') . ': <b>' . (int)$vavok->go('users')->user_info('allban') . '</b><br>';
    echo $vavok->go('localization')->string('becarefnr') . '<br /><br />';
} else {
    $vavok->require_header();

    echo '<p><img src="../images/img/open.gif" alt=""> ' . $vavok->go('localization')->string('wasbanned') . '</p>';

    if (!empty($bandesc)) {
        echo '<p><b><font color="#FF0000">' . $vavok->go('localization')->string('bandesc') . ': ' . $bandesc . '</font></b></p>';
    }

    echo '<p>' . $vavok->go('localization')->string('endbanadvice') . ' <b><a href="siterules.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('siterules') . '</a></b></p>';

    $vavok->go('users')->update_user('banned', 0);
    $vavok->go('users')->update_user(array('bantime', 'bandesc'), array('', ''));
}

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>