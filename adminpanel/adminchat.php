<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg() || !$vavok->go('users')->check_permissions(basename(__FILE__))) $vavok->redirect_to('../?auth_error');

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('adminchat');

$vavok->require_header();

echo '<img src="../images/img/menu.gif" alt=""> <b>' . $vavok->go('localization')->string('adminchat') . '</b><br><br>';

if (empty($vavok->post_and_get('action'))) {
    echo '<a href="#down"><img src="../images/img/downs.gif" alt=""></a> ';
    echo '<a href="adminchat.php?r=' . rand(100, 999) . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('refresh') . '</a><br>';

    echo'<hr><form action="process.php?action=acadd" method="post"><b>' . $vavok->go('localization')->string('message') . '</b><br>';
    echo'<textarea cols="80" rows="5" name="msg"></textarea><br>';

    echo'<input type="submit" value="' . $vavok->go('localization')->string('save') . '" /></form><hr>';

    $file = $vavok->get_data_file('adminchat.dat');
    $file = array_reverse($file);
    $total = count($file);

    if ($total < 1) {
        echo'<br><img src="../images/img/reload.gif" alt=""> <b>' . $vavok->go('localization')->string('nomsgs') . '</b><br>';
    }

    $navigation = new Navigation(10, $total, $vavok->post_and_get('page'), 'adminchat.php?'); // start navigation

    $limit_start = $navigation->start()['start']; // starting point

    if ($total < $limit_start + 10) {
        $end = $total;
    } else {
        $end = $limit_start + 10;
    }

    for ($i = $limit_start; $i < $end; $i++) {
        $data = explode("|", $file[$i]); 

        $statwho = $vavok->go('users')->user_online($data[1]); 

        $data_text = $vavok->getbbcode($data[0]);

        echo '<div class=b><b><a href="../pages/user.php?uz=' . $data[1] . '" class="btn btn-outline-primary sitelink"> ' . $data[1] . ' </a></b> ' . $statwho;

        if (date('d.m.y') == $data[2]) {
            $data[2] = '<font color="#FF0000">' . $vavok->go('localization')->string('today') . '</font>';
        }

        echo'<small> (' . $data[2] . ' / ' . $data[3] . ')</small></div>' . $data_text . '<br><small><font color="#CC00CC">[' . $data[4] . ', ' . $data[5] . ']</font></small>';
        echo'<br>';
    }

    echo '<hr>';
    echo $navigation->get_navigation();

    echo '<br><br>';

    echo '<a href="../pages/smiles.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('smile') . '</a>';
}

if ($vavok->post_and_get('action') == 'prodel') {
    echo '<br>' . $vavok->go('localization')->string('delacmsgs') . '?<br>';
    echo '<b><a href="process.php?action=acdel" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('yessure') . '!</a></b><br>';

    echo '<br><a href="adminchat.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a>';
} 

if (isset($total) && $total > 0 && ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102)) {
    echo '<br><a href="adminchat.php?action=prodel" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('cleanchat') . '</a>';
}

echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('admpanel') . '</a><br>';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();

?>