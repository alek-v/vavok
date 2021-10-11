<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_administrator()) $vavok->redirect_to('./');

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('search');
$vavok->require_header();

if (empty($vavok->post_and_get('action'))) {
    $form = new PageGen('forms/form.tpl');
    $form->set('form_method', 'post');
    $form->set('form_action', 'filesearch.php?action=stpc');
    $form->set('website_language[save]', $vavok->go('localization')->string('search'));

    $input = new PageGen('forms/input.tpl');
    $input->set('label_for', 'stext');
    $input->set('label_value', 'Page name:');
    $input->set('input_name', 'stext');
    $input->set('input_id', 'stext');
    $input->set('input_maxlength', 30);

    $form->set('fields', $input->output());
    echo $form->output();

    echo $vavok->sitelink('files.php', $vavok->go('localization')->string('back'), '<p>', '<br />');
} else if ($vavok->post_and_get('action') == 'stpc') {
    $stext = $vavok->check($vavok->post_and_get('stext'));

    if (empty($stext)) {
        echo '<p>Please fill all fields</p>';
    } else {
        // begin search

        $where_table = "pages";
        $cond = "pname";
        $select_fields = "*";
        $ord_fields = "pubdate DESC";

        $noi = $vavok->go('db')->count_row($where_table, "" . $cond . " LIKE '%" . $stext . "%'");
        $items_per_page = 10;

        $navigation = new Navigation($items_per_page, $noi, $vavok->post_and_get('page'), 'filesearch.php?'); // start navigation

        $limit_start = $navigation->start()['start']; // starting point

        $sql = "SELECT {$select_fields} FROM {$where_table} WHERE pname LIKE '%{$stext}%' OR tname LIKE '%{$stext}%' ORDER BY {$ord_fields} LIMIT $limit_start, $items_per_page";

        foreach ($vavok->go('db')->query($sql) as $item) {
            $tname = $item['tname'];
            if (empty($tname)) {
                $tname = $item['pname'];
            } 
            if (empty($item['file'])) {
            	$item['file'] = $item['pname'] . '.php';
            }
            if (empty($tname)) {
                $tlink = 'Unreachable<br>';
            } else {
            	if (!empty($item['lang'])) {
            		$itemLang = ' (' . mb_strtolower($item['lang']) . ')'; } else {
            			$itemLang = ''; }
                $tlink = $vavok->sitelink('files.php?action=show&amp;file=' . $item['file'], $tname . $itemLang) . '<br />';
            }
            echo $tlink;
        }
        echo $navigation->get_navigation();
    }

    echo $vavok->sitelink('filesearch.php', $vavok->go('localization')->string('back'), '<p>', '<br />');
}

echo $vavok->sitelink('./', $vavok->go('localization')->string('admpanel'), '', '<br />');
echo $vavok->homelink('', '</p>');

$vavok->require_footer();

?>