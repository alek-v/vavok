<?php 
/*
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   21.08.2020. 22:55:46
*/

require_once"../include/startup.php";

if (!empty($_GET['action'])) {
    $action = $vavok->check($_GET["action"]);
} else {
    $action = '';
} 
if (isset($_GET['start'])) {
    $start = $vavok->check($_GET['start']);
} 

if ($users->is_reg()) {
    if ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102) {

        $current_page->page_title = "IP ban";
        require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

        echo '<img src="../images/img/menu.gif" alt=""> <b>IP ban panel</b><br><br>';

        if (empty($action)) {
            $file = $vavok->get_data_file('ban.dat');
            $total = count($file);
            if (empty($_GET['start'])) $start = 0;
            else $start = $_GET['start'];
            if ($total < $start + 10) {
                $end = $total;
            } else {
                $end = $start + 10;
            } 
            for ($i = $start; $i < $end; $i++) {
                $file = $vavok->get_data_file('ban.dat');
                $file = array_reverse($file);
                $data = explode("|", $file[$i]);
                $i2 = round($i + 1);

                $num = $total - $i-1;

                echo $i2 . '. ' . $data[1] . ' <br><a href="process.php?action=razban&amp;start=' . $start . '&amp;id=' . $num . '" class="btn btn-outline-primary sitelink">' . $localization->string('delban') . '</a><hr>';
            } 

            if ($total < 1) {
                echo'<br><img src="../images/img/reload.gif" alt="" /> ' . $localization->string('emptylist') . '<br><br>';
            } 

            if ($start != 0) {
                echo '<a href="ban.php?start=' . ($start - 10) . '" class="btn btn-outline-primary sitelink">&lt; ' . $localization->string('back') . '</a> ';
            } else {
                echo'&lt; ' . $localization->string('back') . ' ';
            } 
            echo'|';
            if ($total > $start + 10) {
                echo ' <a href="ban.php?start=' . ($start + 10) . '" class="btn btn-outline-primary sitelink">' . $localization->string('forw') . ' &gt;</a>';
            } else {
                echo' ' . $localization->string('forw') . ' &gt;';
            }

            echo '<hr>';

            $form = new PageGen('forms/form.tpl');
            $form->set('form_method', 'post');
            $form->set('form_action', 'process.php?action=zaban&amp;start=' . $start);

            $input = new PageGen('forms/input.tpl');
            $input->set('label_for', 'ips');
            $input->set('label_value', $localization->string('iptoblock'));
            $input->set('input_name', 'ips');
            $input->set('input_id', 'ips');

            $form->set('website_language[save]', $localization->string('confirm'));
            $form->set('fields', $input->output());
            echo $form->output();

            echo '<hr>';

            echo '<p>' . $localization->string('ipbanexam') . '</p>';
            echo '<p>' . $localization->string('allbanips') . ': ' . $total . '</p>';

            if ($total > 1) {
                echo'<p><a href="process.php?action=delallip" class="btn btn-outline-primary sitelink">' . $localization->string('dellist') . '</a></p>';
            }
        }

        echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $localization->string('admpanel') . '</a><br>';
        echo '<a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';
    } else {
        header ("Location: ../index.php?error");
        exit;
    } 
} else {
    header ("Location: ../index.php?error");
    exit;
} 

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>
