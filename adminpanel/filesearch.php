<?php 
// (c) vavok.net
require_once '../include/startup.php';

if (!$vavok->go('users')->is_reg() || !$vavok->go('users')->is_administrator()) {
    header("Location: ./");
    exit;
} 

if (!empty($_GET['action'])) {
    $action = $vavok->check($_GET["action"]);
} else {
    $action = '';
} 
if (empty($action)) {
    $action = 'tpc';
} 
if (!empty($_GET['page'])) {
    $page = $vavok->check($_GET["page"]);
} else {
    $page = '';
} 

if ($action == "tpc") {
    $vavok->go('current_page')->page_title = $vavok->go('localization')->string('search');
    $vavok->require_header();

    $form = new PageGen('forms/form.tpl');
    $form->set('form_method', 'post');
    $form->set('form_action', 'filesearch.php?action=stpc');

    $input = new PageGen('forms/input.tpl');
    $input->set('label_for', 'stext');
    $input->set('label_value', 'Page name:');
    $input->set('input_name', 'stext');
    $input->set('input_id', 'stext');
    $input->set('input_maxlength', 30);

    $form->set('fields', $input->output());
    echo $form->output();

    echo '<p><a href="files.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a><br />';
    echo '<a href="./" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('admpanel') . '</a><br />';
    echo $vavok->homelink() . '</p>';

    $vavok->require_footer();
} else if ($action == "stpc") {
    $stext = $vavok->check($_POST["stext"]);

    $vavok->go('current_page')->page_title = 'Search';
    $vavok->require_header();

    if (empty($stext)) {
        echo "<br>Please fill all fields";
    } else {
        // begin search

        $where_table = "pages";
        $cond = "pname";
        $select_fields = "*";
        $ord_fields = "pubdate DESC";

        $noi = $vavok->go('db')->count_row($where_table, "" . $cond . " LIKE '%" . $stext . "%'");
        $items_per_page = 10;

        $navigation = new Navigation($items_per_page, $noi, $page, 'filesearch.php?'); // start navigation

        $limit_start = $navigation->start()['start']; // starting point

        $sql = "SELECT " . $select_fields . " FROM " . $where_table . " WHERE pname LIKE '%" . $stext . "%' OR tname LIKE '%" . $stext . "%' ORDER BY " . $ord_fields . " LIMIT $limit_start, $items_per_page";

        foreach ($vavok->go('db')->query($sql) as $item) {
            $tname = $item['tname'];
            if (empty($tname)) {
                $tname = $item['pname'];
            } 
            if (empty($item['file'])) {
            	$item['file'] = $item['pname'] . '.php';
            }
            if ($tname == "") {
                $tlink = "Unreachable<br>";
            } else {
            	if (!empty($item['lang'])) {
            		$itemLang = ' (' . mb_strtolower($item['lang']) . ')'; } else {
            			$itemLang = ''; }
                $tlink = '<a href="files.php?action=show&amp;file=' . $item['file'] . '" class="btn btn-outline-primary sitelink">' . $tname . $itemLang . '</a><br />';
            } 
            echo $tlink;
        } 

        echo $navigation->get_navigation();

    } 

    echo '<p><a href="filesearch.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a><br />';
    echo '<a href="./" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('admpanel') . '</a><br />';
    echo $vavok->homelink() . '</p>';

    $vavok->require_footer();
    exit;
} 

?>