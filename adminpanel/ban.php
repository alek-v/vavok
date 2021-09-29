<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg() || ($_SESSION['permissions'] != 101 && $_SESSION['permissions'] != 102)) { $vavok->redirect_to('../?auth_error'); }

$vavok->go('current_page')->page_title = 'IP ban';
$vavok->require_header();

echo '<p><img src="../images/img/menu.gif" alt="" /> <b>IP ban panel</b></p>';

if (empty($vavok->post_and_get('action'))) {
    $file = $vavok->get_data_file('ban.dat');
    $total = count($file);

    $navigation = new Navigation(10, $total, $vavok->post_and_get('page'), 'ban.php?'); // start navigation

    $limit_start = $navigation->start()['start']; // starting point

    if ($total < $limit_start + 10) {
        $end = $total;
    } else {
        $end = $limit_start + 10;
    }

    for ($i = $limit_start; $i < $end; $i++) {
        $file = $vavok->get_data_file('ban.dat');
        $file = array_reverse($file);
        $data = explode("|", $file[$i]);
        $i2 = round($i + 1);

        $num = $total - $i-1;

        echo $i2 . '. ' . $data[1] . ' <br><a href="process.php?action=razban&amp;id=' . $num . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('delban') . '</a><hr>';
    } 

    if ($total < 1) {
        echo '<p><img src="../images/img/reload.gif" alt="" /> ' . $vavok->go('localization')->string('emptylist') . '</p>';
    } 

    echo $navigation->get_navigation();

    echo '<hr>';

    $form = new PageGen('forms/form.tpl');
    $form->set('form_method', 'post');
    $form->set('form_action', 'process.php?action=zaban&amp;start=' . $start);

    $input = new PageGen('forms/input.tpl');
    $input->set('label_for', 'ips');
    $input->set('label_value', $vavok->go('localization')->string('iptoblock'));
    $input->set('input_name', 'ips');
    $input->set('input_id', 'ips');

    $form->set('website_language[save]', $vavok->go('localization')->string('confirm'));
    $form->set('fields', $input->output());
    echo $form->output();

    echo '<hr>';

    echo '<p>' . $vavok->go('localization')->string('ipbanexam') . '</p>';
    echo '<p>' . $vavok->go('localization')->string('allbanips') . ': ' . $total . '</p>';

    if ($total > 1) {
        echo '<p><a href="process.php?action=delallip" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('dellist') . '</a></p>';
    }
}

echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('admpanel') . '</a><br>';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();

?>
