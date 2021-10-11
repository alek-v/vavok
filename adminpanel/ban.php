<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_administrator()) $vavok->redirect_to('../?auth_error');

if ($vavok->post_and_get('action') == 'zaban' && $vavok->go('users')->is_administrator()) {
    $ips = $vavok->check($vavok->post_and_get('ips'));

    if (!empty($ips) && substr_count($ips, '.') == 3) {
        $vavok->write_data_file('ban.dat', "|$ips|" . PHP_EOL, 1);
    }

    $vavok->redirect_to('ban.php');
}

if ($vavok->post_and_get('action') == 'razban' && $vavok->go('users')->is_administrator()) {
    if (!empty($vavok->post_and_get('id')) || $vavok->post_and_get('id') == 0) $id = $vavok->post_and_get('id');

    if (isset($id)) {
        $file = $vavok->get_data_file('ban.dat');
        unset($file[$id]);

        $data = '';
        foreach ($file as $key => $value) {
            $data .= $value;
        }

        $vavok->write_data_file('ban.dat', $data);
    }

    $vavok->redirect_to('ban.php');
}

if ($vavok->post_and_get('action') == 'delallip' && ($_SESSION['permissions'] == 101 or $_SESSION['permissions'] == 102)) {
    $vavok->clear_files('../used/ban.dat');

    $vavok->redirect_to('ban.php');
}

$vavok->go('current_page')->page_title = 'IP ban';
$vavok->require_header();

echo '<p><img src="../themes/images/img/menu.gif" alt="" /> <b>IP ban panel</b></p>';

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

    echo $i2 . '. ' . $data[1] . ' <br>' . $vavok->sitelink('ban.php?action=razban&amp;id=' . $num, $vavok->go('localization')->string('delban')) . '<hr>';
} 

if ($total < 1) {
    echo '<p><img src="../themes/images/img/reload.gif" alt="" /> ' . $vavok->go('localization')->string('emptylist') . '</p>';
}

echo $navigation->get_navigation();

echo '<hr>';

$form = new PageGen('forms/form.tpl');
$form->set('form_method', 'post');
$form->set('form_action', 'ban.php?action=zaban');

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
    echo $vavok->sitelink('ban.php?action=delallip', $vavok->go('localization')->string('dellist'), '<p>', '</p>');
}

echo '<p>' . $vavok->sitelink('./', $vavok->go('localization')->string('admpanel')) . '<br>';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();

?>