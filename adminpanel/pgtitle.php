<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

$act = $vavok->post_and_get('act');

if (!$vavok->go('users')->is_administrator()) $vavok->redirect_to('../?error');

if ($act == 'addedit') {
    $tfile = $vavok->check($vavok->post_and_get('tfile'));
    $msg = $vavok->no_br($vavok->post_and_get('msg'));

    // get page data
    $pageData = $vavok->go('db')->get_data(DB_PREFIX . 'pages', "file='{$tfile}'", 'file, headt');

    $headData = $pageData['headt'];

    // remove old open graph title title and set new
    if (stripos($headData, 'property="og:title" content="')) {
    $start = stripos($headData, '<meta property="og:title"');
    for ($i = $start;$i < strlen($headData);$i++) {
        $currentChar = $headData[$i];
        $headData[$i] = '~';

        if ($currentChar == '>')
        break;
        }
    }

    $inputPosition = $start;
    $headData = str_replace('~', '', $headData);
    $headData = substr_replace($headData, '<meta property="og:title" content="' . $msg . '" />', $inputPosition, 0);

    $fields = array('tname', 'headt');
    $values = array($msg, $headData);
    $vavok->go('db')->update(DB_PREFIX . 'pages', $fields, $values, "file='{$tfile}'");


    $vavok->redirect_to("files.php?action=edit&file=" . $pageData['file'] . "&isset=savedok");
} 

if ($act == "savenew") {
    $tpage = $vavok->check($vavok->post_and_get('tpage'));
    $tpage = strtolower($tpage);
    $tpage = str_replace(' ', '-', $tpage);

    $msg = $vavok->no_br($vavok->post_and_get('msg'));

    $last_notif = $vavok->go('db')->get_data(DB_PREFIX . 'pages', "pname='" . $tpage . "'", '`tname`, `pname`, `file`, `headt`');

    $headData = $last_notif['headt'];

    // remove old open graph title title and set new
    if (stripos($headData, 'property="og:title" content="')) {
    $start = stripos($headData, '<meta property="og:title"');
    for ($i = $start;$i < strlen($headData);$i++) {
        $currentChar = $headData[$i];
        $headData[$i] = '~';

        if ($currentChar == '>')
        break;
        }
    }

    $inputPosition = $start;
    $headData = str_replace('~', '', $headData);
    $headData = trim(substr_replace($headData, '<meta property="og:title" content="' . $msg . '" />', $inputPosition, 0));

    // no data in database, insert data
    if (empty($last_notif['tname'] && $last_notif['pname'] && $last_notif['file'])) {
        $values = array(
            'pname' => $tpage,
            'tname' => $msg,
            'file' => $tpage
        );
        $vavok->go('db')->insert(DB_PREFIX . 'pages', $values);

        $PBPage = false;
    } else {
        $fields = array('tname', 'headt');
        $values = array($msg, $headData);
        $vavok->go('db')->insert(DB_PREFIX . 'pages', $fields, $values, "pname='" . $tpage . "'");

        $PBPage = true;
    } 

    $vavok->redirect_to("pgtitle.php?isset=savedok");
}

if ($act == 'del') {
    $tid = $vavok->check($vavok->post_and_get('tid'));

    $vavok->go('db')->delete(DB_PREFIX . 'pages', "pname = '{$tid}'");

    $vavok->redirect_to('pgtitle.php');
}

$vavok->require_header();

if (!isset($act) || empty($act)) {
    $nitems = $vavok->go('db')->count_row(DB_PREFIX . 'pages');
    $total = $nitems;

    if ($total < 1) {
        echo '<br /><img src="../themes/images/img/reload.gif" alt=""> <b>Page titles not found!</b><br />';
    }

    $nitems = $vavok->go('db')->count_row(DB_PREFIX . 'pages', 'tname is not null');
    $num_items = $nitems;

    $items_per_page = 30;

    $navigation = new Navigation($items_per_page, $num_items, $page, 'pgtitle.php?'); // start navigation

    $limit_start = $navigation->start()['start']; // starting point

    $sql = "SELECT id, pname, tname, file FROM " . DB_PREFIX . "pages WHERE tname is not null ORDER BY pname LIMIT $limit_start, $items_per_page";

    if ($num_items > 0) {
        foreach ($vavok->go('db')->query($sql) as $item) {
            $lnk = $item['pname'] . " <img src=\"../themes/images/img/edit.gif\" alt=\"\" /> <a href=\"pgtitle.php?act=edit&amp;pgfile=" . $item['file'] . "\">" . $item['tname'] . "</a> | <img src=\"../themes/images/img/edit.gif\" alt=\"\" /> <a href=\"files.php?action=headtag&amp;file=" . $item['file'] . "\">[Edit Meta]</a> | <img src=\"../themes/images/img/close.gif\" alt=\"\" /> <a href=\"pgtitle.php?act=del&amp;tid=" . $item['pname'] . "\">[DEL]</a>"; 
            // echo " <small>joined: $jdt</small>";
            echo "$lnk<br />";
        }
    }

    echo $navigation->get_navigation();

    echo '<br /><br /><a href="pgtitle.php?act=addnew" class="btn btn-outline-primary sitelink">Add new title</a><br /><br />'; // update lang
}

if ($act == 'edit') {
    $pgfile = $vavok->check($vavok->post_and_get('pgfile'));

    $page_title = $vavok->go('db')->get_data(DB_PREFIX . 'pages', "file='{$pgfile}'", 'tname, pname');

    $form = new PageGen('forms/form.tpl');
    $form->set('form_action', 'pgtitle.php?act=addedit');
    $form->set('form_method', 'POST');

    $input = new PageGen('forms/input.tpl');
    $input->set('input_type', 'hidden');
    $input->set('input_name', 'tfile');
    $input->set('input_value', $pgfile);

    $input_2 = new PageGen('forms/input.tpl');
    $input_2->set('label_for', 'msg');
    $input_2->set('label_value', 'Page title:');
    $input_2->set('input_name', 'msg');
    $input_2->set('input_id', 'msg');
    $input_2->set('input_value', $page_title['tname']);

    $form->set('fields', $form->merge(array($input, $input_2)));
    echo $form->output();

    echo '<hr>';

    echo '<br /><a href="pgtitle.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a><br />';
} 

if ($act == "addnew") {
    $form = new PageGen('forms/form.tpl');
    $form->set('form_action', 'pgtitle.php?act=savenew');
    $form->set('form_method', 'POST');

    $input = new PageGen('forms/input.tpl');
    $input->set('label_for', 'tpage');
    $input->set('label_value', 'Page:');
    $input->set('input_type', 'text');
    $input->set('input_name', 'tpage');
    $input->set('input_id', 'tpage');

    $input_2 = new PageGen('forms/input.tpl');
    $input_2->set('label_for', 'msg');
    $input_2->set('label_value', 'Page title:');
    $input_2->set('input_type', 'text');
    $input_2->set('input_name', 'msg');
    $input_2->set('input_id', 'msg');

    $form->set('fields', $form->merge(array($input, $input_2)));
    echo $form->output();

    echo '<hr />';

    echo '<p><a href="pgtitle.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a></p>';
}

echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('admpanel') . '</a><br />';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();

?>
