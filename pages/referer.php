<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

// page settings
$data_on_page = 10; // referere links per page

$vavok->go('current_page')->page_title = "Referer";
$vavok->require_header();

if ($vavok->get_configuration('showRefPage') == 1 || $vavok->go('users')->is_administrator()) {
    $file = $vavok->get_data_file('referer.dat');
    $file = array_reverse($file);
    $total = count($file);

    $navigation = new Navigation($data_on_page, $total, $vavok->post_and_get('page'), 'referer.php?'); // start navigation

    $start = $navigation->start()['start']; // starting point
    $end = $navigation->start()['end']; // ending point

    for ($i = $start; $i < $end; $i++) {
        $data = explode("|", $file[$i]);
        $datime = date("H:i:s", $data[1]);

        echo '<b><a href="' . $vavok->transfer_protocol() . $data[0] . '">' . $data[0] . '</a></b> (' . $datime . ')<br />' . $vavok->go('localization')->string('visits') . ': ' . $data[3] . '<br /><hr />';
    }

    echo $navigation->get_navigation();

} else {
    echo '<p><img src="../themes/images/img/reload.gif" alt="" /> ' . $vavok->go('localization')->string('pgviewoff') . '</p>';
}

echo '<p>' . $vavok->homelink() . '<p>';

$vavok->require_footer();

?>