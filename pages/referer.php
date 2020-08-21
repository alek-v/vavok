<?php 
/**
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   21.08.2020. 23:06:57
*/

require_once"../include/startup.php";

// page settings
$data_on_page = 10; // referere links per page

$current_page->page_title = "Referer";
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

if (empty($_GET['page']) || $_GET['page'] < 1) {
    $page = 1;
} else {
    $page = $vavok->check($_GET['page']);
}

if ($vavok->get_configuration('showRefPage') == 1 || $users->is_administrator()) {

    $file = $vavok->get_data_file('referer.dat');
    $file = array_reverse($file);
    $total = count($file);

    $navigation = new Navigation($data_on_page, $total, $page, 'referer.php?'); // start navigation

    $start = $navigation->start()['start']; // starting point
    $end = $navigation->start()['end']; // ending point

    for ($i = $start; $i < $end; $i++) {

        $data = explode("|", $file[$i]);
        $datime = date("H:i:s", $data[1]);

        echo '<b><a href="' . $vavok->transfer_protocol() . $data[0] . '">' . $data[0] . '</a></b> (' . $datime . ')<br />' . $localization->string('visits') . ': ' . $data[3] . '<br /><hr />';

    } 

    echo $navigation->get_navigation();

} else {
    echo '<p><img src="../images/img/reload.gif" alt="" /> ' . $localization->string('pgviewoff') . '<br></p>';
} 

echo '<p><a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>