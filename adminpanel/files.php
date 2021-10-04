<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';
require_once '../include/htmlbbparser.php';

// Checking access permitions
if (!$vavok->go('users')->is_administrator() && !$vavok->go('users')->check_permissions('pageedit', 'show')) $vavok->redirect_to('../?auth_error');

$file = $vavok->check($vavok->post_and_get('file'));
$text_files = $vavok->post_and_get('text_files', true); // keep data as received so html codes will be ok
$id = $vavok->check($vavok->post_and_get('id'));

$page_editor = new Page();

// Get page id we work with
if (!empty($vavok->post_and_get('file'))) $page_id = $page_editor->get_page_id("file='{$file}'");

$config_editfiles = 20;

if ($vavok->post_and_get('action') == 'editfile') {
    // get edit mode
    if (!empty($_SESSION['edmode'])) {
        $edmode = $vavok->check($_SESSION['edmode']);
    } else {
        $edmode = 'columnist';
        $_SESSION['edmode'] = $edmode;
    }

    if (!empty($file) && !empty($text_files)) {
        $page_info = $page_editor->select_page($page_id, 'crtdby, published');

        if (!$vavok->go('users')->check_permissions('pageedit', 'show') && !$vavok->go('users')->is_administrator()) { $vavok->redirect_to("index.php?isset=ap_noaccess"); } 

        if ($page_info['crtdby'] != $vavok->go('users')->user_id && !$vavok->go('users')->check_permissions('pageedit', 'edit') && (!$vavok->go('users')->check_permissions('pageedit', 'editunpub') || $page_info['published'] != 1) && !$vavok->go('users')->is_administrator()) {
            header("Location: index.php?isset=ap_noaccess");
            exit;
        }

        // bug when magic quotes are on and '\' sign
        // if magic quotes are on we don't want ' to become \'
        if (function_exists('get_magic_quotes_gpc')) {
            // strip all slashes
            $text_files = stripslashes($text_files);
        }

        // update db data
        $page_editor->update($page_id, $text_files);
    }

    $vavok->redirect_to("files.php?action=edit&file=$file&isset=mp_editfiles");
}

if ($vavok->post_and_get('action') == 'savetags') {
    if (!$vavok->go('users')->check_permissions('pageedit', 'insert') && !$vavok->go('users')->is_administrator()) { $vavok->redirect_to("index.php?isset=ap_noaccess"); }

    $tags = !empty($vavok->post_and_get('tags')) ? $vavok->post_and_get('tags') : '';

    if (isset($tags)) { $page_editor->update_tags($id, $tags); }

    $vavok->redirect_to("files.php?action=tags&id={$id}&isset=mp_editfiles");
}

// update head tags on all pages
if ($vavok->post_and_get('action') == 'editmainhead') {
    if (!$vavok->go('users')->is_administrator(101)) $vavok->redirect_to("../?isset=ap_noaccess");

    // update header data
    $vavok->write_data_file('headmeta.dat', $text_files);

    $vavok->redirect_to('files.php?action=mainmeta&isset=mp_editfiles');
}

// update head tags on specific page
if ($vavok->post_and_get('action') == 'editheadtag') {
    // get default image link
    $image = !empty($vavok->post_and_get('image')) ? $vavok->post_and_get('image') : '';

    // update header tags
    if (!empty($file)) {
        // who created page
        $page_info = $page_editor->select_page($page_id, 'crtdby');

        // check can user see page
        if (!$vavok->go('users')->check_permissions('pageedit', 'show') && !$vavok->go('users')->is_administrator()) { $vavok->redirect_to("index.php?isset=ap_noaccess"); }

        // check can user edit page
        if (!$vavok->go('users')->check_permissions('pageedit', 'edit') && !$vavok->go('users')->is_administrator() && $page_info['crtdby'] != $vavok->go('users')->user_id) { $vavok->redirect_to("index.php?isset=ap_noaccess"); } 

        /**
         * Update data in database
         */
        $data = array(
            'headt' => $text_files,
            'default_img' => $image
        );
        $page_editor->head_data($page_id, $data);

        // redirect
        $vavok->redirect_to("files.php?action=headtag&file=$file&isset=mp_editfiles");
    } 
    // fields must not be empty
    $vavok->redirect_to("files.php?action=headtag&file=$file&isset=mp_noeditfiles");
}

/**
 * Rename page
 */
if ($vavok->post_and_get('action') == 'save_renamed') {
    $pg = $vavok->post_and_get('pg'); // new file name

    if (!empty($pg) && !empty($file)) {
        $page_info = $page_editor->select_page($page_id, 'crtdby');

        if (!$vavok->go('users')->check_permissions('pageedit', 'show') && !$vavok->go('users')->is_administrator()) {
            header("Location: index.php?isset=ap_noaccess");
            exit;
        } 
        if (!$vavok->go('users')->check_permissions('pageedit', 'edit') && !$vavok->go('users')->is_administrator() && $page_info['crtdby'] != $vavok->go('users')->user_id) {
            header("Location: index.php?isset=ap_noaccess");
            exit;
        }

        // rename page
        $page_editor->rename($pg, $page_id);

        header("Location: files.php?action=edit&file=$pg&isset=mp_editfiles");
        exit;
    } 
    header("Location: files.php?action=edit&file=$pg&isset=mp_noedit");
    exit;
}

if ($vavok->post_and_get('action') == 'addnew') {
    if (!$vavok->go('users')->check_permissions('pageedit', 'insert') && !$vavok->go('users')->is_administrator()) { $vavok->redirect_to("index.php?isset=ap_noaccess"); }

    $newfile = !empty($vavok->post_and_get('newfile')) ? $vavok->post_and_get('newfile') : '';
    $type = !empty($vavok->post_and_get('type')) ? $vavok->post_and_get('type') : '';
    $page_structure = !empty($vavok->post_and_get('page_structure')) ? $vavok->post_and_get('page_structure') : '';
    $allow_unicode = $vavok->post_and_get('allow_unicode') == 'on' ? true : false;

    // page title
    $page_title = $newfile;

    // page name in url
    if ($allow_unicode === false) {
        // remove unicode chars
        $newfile = $vavok->trans($newfile);
    } else {
        $newfile = $vavok->trans_unicode($newfile);
    }

    // if page structure is set
    if (!empty($page_structure)) {
        $type = $page_structure;
    }

    // page language
    if (!empty($vavok->post_and_get('lang'))) {
        $pagelang = $vavok->post_and_get('lang');

        $pagelang_file = '!.' . $pagelang . '!';
    } else {
        $pagelang = '';
        $pagelang_file = '';
    }

    if (!empty($newfile)) {
        // page filename
        $newfiles = $newfile . $pagelang_file . '.php';

        // check if page exists
        $includePageLang = !empty($pagelang) ? " AND lang='{$pagelang}'" : '';

        if ($page_editor->page_exists('', "pname='{$newfile}'" . $includePageLang)) {
            $vavok->redirect_to("files.php?action=new&isset=mp_pageexists");
        }

        // full page address
        if (!empty($page_structure) && $newfile != 'index') {
            // user's custom page structure
            $page_url = $vavok->website_home_address() . '/' . $page_structure . '/' . $newfile . '/';
        } elseif ($type == 'post') {
            // blog post
            $page_url = $vavok->website_home_address() . '/blog/' . $newfile . '/';
        } elseif ($newfile != 'index') {
            // page
            $page_url = $vavok->website_home_address() . '/page/' . $newfile . '/';
        }

        // insert db data
        $values = array(
        'pname' => $newfile,
        'lang' => $pagelang,
        'created' => time(),
        'lastupd' => time(),
        'lstupdby' => $vavok->go('users')->user_id,
        'file' => $newfiles,
        'crtdby' => $vavok->go('users')->user_id,
        'published' => '1',
        'pubdate' => '0',
        'tname' => $page_title,
        'headt' => '<meta property="og:title" content="' . $page_title . '" />'. "\r\n" . '<meta property="og:url" content="' . $page_url . '" />' . "\r\n" . '<link rel="canonical" href="' . $page_url . '" />',
        'type' => $type
        );

        // insert data
        $page_editor->insert($values);

        // file successfully created
        $vavok->redirect_to("files.php?action=edit&file=$newfiles&isset=mp_newfiles");

    } else {
        $vavok->redirect_to("files.php?action=new&isset=mp_noyesfiles");
    }
}

if ($vavok->post_and_get('action') == 'del') {
    if (!$vavok->go('users')->check_permissions('pageedit', 'del') && !$vavok->go('users')->is_administrator()) $vavok->redirect_to("index.php?isset=ap_noaccess");

    // delete page
    $page_editor->delete($page_id);
 
    $vavok->redirect_to("files.php?isset=mp_delfiles");
}

// publish page; page will be avaliable for visitors
if ($vavok->post_and_get('action') == 'publish') {
    if (!empty($page_id)) {
        if (!$vavok->go('users')->check_permissions('pageedit', 'edit') && !$vavok->go('users')->is_administrator()) {
            header("Location: index.php?isset=ap_noaccess");
            exit;
        }

        // update db data
        $page_editor->visibility($page_id, 2);
    } 

    $vavok->redirect_to("files.php?action=show&file=" . $file . "&isset=mp_editfiles");
}

// unpublish page
if ($vavok->post_and_get('action') == "unpublish") {
    if (!empty($page_id)) {

        if (!$vavok->go('users')->check_permissions('pageedit', 'edit') && !$vavok->go('users')->is_administrator()) {
            header("Location: index.php?isset=ap_noaccess");
            exit;
        }

        // update db data
        $page_editor->visibility($page_id, 1);
    } 

    $vavok->redirect_to("files.php?action=show&file=" . $file . "&isset=mp_editfiles");
}

// update page language
if ($vavok->post_and_get('action') == 'pagelang') {
    if (!$vavok->go('users')->is_administrator()) { $vavok->redirect_to("../?isset=ap_noaccess"); }

    $pageId = $vavok->check($vavok->post_and_get('id'));
    $lang = $vavok->post_and_get('lang');

    // update database data
    $page_editor->language($pageId, $lang);

    $pageData = $page_editor->select_page($pageId);
    $vavok->redirect_to("files.php?action=show&file=" . $pageData['pname'] . "!." . $lang . "!.php&isset=mp_editfiles");

}

// editing mode
// use visual mode as default
if (!empty($_SESSION['edmode'])) {
    $edmode = $vavok->check($_SESSION['edmode']);
} else {
    $edmode = 'visual';
    $_SESSION['edmode'] = $edmode;
} 
if (!empty($vavok->post_and_get('edmode'))) {
    $edmode = $vavok->post_and_get('edmode');
    $_SESSION['edmode'] = $edmode;
}


if ($edmode == 'visual') {
    // text editor
    $loadTextEditor = $page_editor->loadPageEditor();

    // remove fullpage plugin if exists, we dont need html header and footer tags
    $loadTextEditor = str_replace('fullpage ' , '', $loadTextEditor);

    // choose field selector
    $textEditor = str_replace('#selector', '#text_files', $loadTextEditor);

    // add to page header
    $vavok->go('current_page')->append_head_tags($textEditor);
}

// check if user can edit only pages that are made by themself or have permitions to edit all pages
if (!$vavok->go('users')->check_permissions('pageedit', 'edit') && !$vavok->go('users')->is_administrator()) {
    $edit_only_own_pages = 'yes';
} else {
    $edit_only_own_pages = '';
}

$vavok->go('current_page')->page_title = 'Files'; // current page title

if (empty($vavok->post_and_get('action'))) {

    $vavok->require_header();

    echo '<h1>' . $vavok->go('localization')->string('filelist') . '</h1>';

    $total_pages = $page_editor->total_pages();

    if ($edit_only_own_pages == 'yes') {
        $total_pages = $page_editor->total_pages($vavok->go('users')->user_id);
    } 

    // start navigation
    $navi = new Navigation($config_editfiles, $total_pages, $vavok->post_and_get('page'));


    if ($edit_only_own_pages == 'yes') {
        $sql = "SELECT * FROM " . DB_PREFIX . "pages WHERE crtdby='{$vavok->go('users')->user_id}' ORDER BY pname LIMIT {$navi->start()['start']}, $config_editfiles";
    } else {
        $sql = "SELECT * FROM " . DB_PREFIX . "pages ORDER BY pname LIMIT {$navi->start()['start']}, $config_editfiles";
    }

    foreach ($vavok->go('db')->query($sql) as $page_info) {

        if (empty($page_info['file'][0]) || $page_info['file'][0] == '/') {
            continue;
        }

        if (!empty($page_info['lang'])) {
            $file_lang = '(' . strtoupper($page_info['lang']) . ')';
        } else {
            $file_lang = '';
        } 

        $filename = preg_replace("/!.(.*)!.php/", "$2", $page_info['file']);
        $filename = str_replace(".php", "", $filename);

        $filename = $filename . ' ' . strtoupper($file_lang) . '';

        if (empty($edit_only_own_pages)) {

            echo $vavok->sitelink('files.php?action=show&amp;file=' . $page_info['file'], $filename, '<b>', '</b>');
            // Check for permissions to edit pages
            if ($vavok->go('users')->check_permissions('pageedit', 'edit') || $vavok->go('users')->is_administrator() || $page_info['crtdby'] == $vavok->go('users')->user_id || ($vavok->go('users')->check_permissions('pageedit', 'editunpub') && $page_info['published'] == 1)) {
                echo '<a href="files.php?action=edit&amp;file=' . $page_info['file'] . '" class="btn btn-outline-primary btn-sm">[Edit]</a>';
            }

            // Check for permissions to delete pages
            if ($vavok->go('users')->check_permissions('pageedit', 'del') || $vavok->go('users')->is_administrator()) {
                echo ' | <a href="files.php?action=poddel&amp;file=' . $page_info['file'] . '" class="btn btn-outline-primary btn-sm">[Del]</a>';
            }

            // Check for permissions to publish and unpublish pages
            if ($page_info['published'] == 1 && ($vavok->go('users')->check_permissions('pageedit', 'edit') || $vavok->go('users')->is_administrator())) {
                echo ' | <a href="files.php?action=publish&amp;file=' . $page_info['file'] . '" class="btn btn-outline-primary btn-sm">[Publish]</a>';
            } 

            if ($page_info['published'] != 1 && ($vavok->go('users')->check_permissions('pageedit', 'edit') || $vavok->go('users')->is_administrator())) {
                echo ' | <a href="files.php?action=unpublish&amp;file=' . $page_info['file'] . '" class="btn btn-outline-primary btn-sm">[Unpublish]</a>';
            }

            // Informations about page
            echo ' ' . $vavok->go('localization')->string('created') . ': ' . $vavok->date_fixed($page_info['created'], 'd.m.y.') . ' ' . $vavok->go('localization')->string('by') . ' ' . $vavok->go('users')->getnickfromid($page_info['crtdby']) . ' | ' . $vavok->go('localization')->string('lastupdate') . ' ' . $vavok->date_fixed($page_info['lastupd'], 'd.m.y.') . ' ' . $vavok->go('localization')->string('by') . ' ' . $vavok->go('users')->getnickfromid($page_info['lstupdby']);
            echo '<hr />';

        } else {
            echo $vavok->sitelink('files.php?action=show&amp;file=' . $page_info['file'], $filename, '<b>', '</b>');
            echo '<a href="files.php?action=edit&amp;file=' . $page_info['file'] . '" class="btn btn-outline-primary btn-sm">[Edit]</a>';
            echo ' ' . $vavok->go('localization')->string('created') . ': ' . $vavok->date_fixed($page_info['created'], 'd.m.y.') . ' ' . $vavok->go('localization')->string('by') . ' ' . $vavok->go('users')->getnickfromid($page_info['crtdby']) . ' | ' . $vavok->go('localization')->string('lastupdate') . ' ' . $vavok->date_fixed($page_info['lastupd'], 'd.m.y.') . ' ' . $vavok->go('localization')->string('by') . ' ' . $vavok->go('users')->getnickfromid($page_info['lstupdby']);
            echo '<hr />';
        } 
    
    unset($page_info);

    } 

    // navigation
    $navigation = new Navigation($config_editfiles, $total_pages, $vavok->post_and_get('page'), 'files.php?');
    echo $navigation->get_navigation();

    echo '<br />' . $vavok->go('localization')->string('totpages') . ': <b>' . (int)$total_pages . '</b><br />';
    echo '<div>&nbsp;</div>';
    if (empty($edit_only_own_pages)) {
        echo '<a href="pgtitle.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('pagetitle') . '</a><br />';
    } 
} 

if ($vavok->post_and_get('action') == "show") {
    if (!empty($page_id)) {
        $base_file = $file;

        $pageData = new Page();
        $page_info = $pageData->select_page($page_id);

        if (!$vavok->go('users')->check_permissions('pageedit', 'show') && !$vavok->go('users')->is_administrator()) {
            $vavok->redirect_to("index.php?isset=ap_noaccess");
        } 

        if ($page_info['crtdby'] != $vavok->go('users')->user_id && !$vavok->go('users')->check_permissions('pageedit', 'edit') && (!$vavok->go('users')->check_permissions('pageedit', 'editunpub') || $page_info['published'] != 1) && !$vavok->go('users')->is_administrator()) {
            $vavok->redirect_to("index.php?isset=ap_noaccess");
        } 

        $showname = $page_info['pname'];

        $vavok->require_header(); 

        echo '<p>' . $vavok->go('localization')->string('shwingpage') . ' <b>' . $showname . '</b></p>';
        echo '<p>' . $vavok->go('localization')->string('created') . ': ' . $vavok->date_fixed($page_info['created'], 'd.m.y.') . ' ' . $vavok->go('localization')->string('by') . ' ' . $vavok->go('users')->getnickfromid($page_info['crtdby']);
        echo ' | ' . $vavok->go('localization')->string('lastupdate') . ' ' . $vavok->date_fixed($page_info['lastupd'], 'd.m.y.') . ' ' . $vavok->go('localization')->string('by') . ' ' . $vavok->go('users')->getnickfromid($page_info['lstupdby']);
        
        // post type
        $post_type = !empty($page_info['type']) ? $page_info['type'] : 'page';
        echo ' | Page type: ' . $post_type;

        if ($page_info['published'] == 1 && ($vavok->go('users')->check_permissions('pageedit', 'edit') || $vavok->go('users')->is_administrator())) {
            echo ' | <a href="files.php?action=publish&amp;file=' . $file . '">[Publish]</a>';
        } 
        if ($page_info['published'] != 1 && ($vavok->go('users')->check_permissions('pageedit', 'edit') || $vavok->go('users')->is_administrator())) {
            echo ' | <a href="files.php?action=unpublish&amp;file=' . $file . '">[Unpublish]</a>';
        }
        echo '</p>';

        echo '<p>';
        echo $vavok->go('localization')->string('pgaddress') . ': ';

        // if it is index doesnt show this page like other pages
        if (preg_match('/^index(?:!\.[a-zA-Z]{2}!)?\.php$/', $file)) {
            // this is index page
        	if (!empty($page_info['lang'])) { $url_lang = strtolower($page_info['lang']) . '/'; } else { $url_lang = ''; }

        	echo '<a href="' . $vavok->website_home_address() . '/' . $url_lang . '" target="_blank">' . $vavok->website_home_address() . '/' . $url_lang . '</a>';

 		} elseif ($post_type == 'post') {
            // this is blog post
            echo '<br /><a href="' . $vavok->website_home_address() . '/blog/' . $showname . '/" target="_blank">' . $vavok->website_home_address() . '/blog/' . $showname . '/</a><br />';

        } elseif ($post_type == 'page' || empty($post_type)) {
            // this is page
	        echo '<br /><a href="' . $vavok->website_home_address() . '/page/' . $showname . '/" target="_blank">' . $vavok->website_home_address() . '/page/' . $showname . '/</a><br />';
        } else {
            // this is custom page structure
            echo '<br /><a href="' . $vavok->website_home_address() . '/' . $post_type . '/' . $showname . '/" target="_blank">' . $vavok->website_home_address() . '/' . $post_type . '/' . $showname . '/</a><br />';
        }

        echo '</p>';


        echo '<br /><a href="files.php?action=edit&amp;file=' . $base_file . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('edit') . '</a><br />';
        if ($vavok->go('users')->check_permissions('pageedit', 'del') || $vavok->go('users')->is_administrator()) {
        echo '<a href="files.php?action=poddel&amp;file=' . $base_file . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('delete') . '</a><br />';
        }
    } 

    if (empty($edit_only_own_pages)) {
        echo '<a href="pgtitle.php?act=edit&amp;pgfile=' . $base_file . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('pagetitle') . '</a><br />';
    }

} 

if ($vavok->post_and_get('action') == "edit") {
    // check if page exists
    $checkPage = $page_editor->page_exists($file);

    // coder mode for advanced users / coders
    if ($edmode == 'coder') {
        $edmode_name = 'Coder';
    } 
    if ($edmode == 'visual') {
        $edmode_name = 'Visual';
    } 

    if ($checkPage == true) {
        $page_info = $page_editor->select_page($page_id);

        if (!$vavok->go('users')->check_permissions('pageedit', 'show') && !$vavok->go('users')->is_administrator()) { $vavok->redirect_to("index.php?isset=ap_noaccess"); } 

        if ($page_info['crtdby'] != $vavok->go('users')->user_id && !$vavok->go('users')->check_permissions('pageedit', 'edit') && (!$vavok->go('users')->check_permissions('pageedit', 'editunpub') || $page_info['published'] != 1) && !$vavok->go('users')->is_administrator()) {
            header("Location: index.php?isset=ap_noaccess");
            exit;
        } 

        $vavok->require_header();

        $datamainfile = htmlspecialchars($page_info['content']);

        // show page name
        $show_up_file = str_replace('.php', '', $file);
        if (stristr($show_up_file, '!.')) {
            $show_up_file = preg_replace("/(.*)!.(.*)!/", "$1", $show_up_file);
        } 

        echo '<p>Edit mode: ' . $edmode_name . '</p>';

		$form = new PageGen('forms/form.tpl');
		$form->set('form_method', 'post');
		$form->set('form_action', 'files.php?action=edit&amp;file=' . $file);

		$select = new PageGen('forms/select.tpl');
		$select->set('label_for', 'edmode');
		$select->set('select_id', 'edmode');
		$select->set('select_name', 'edmode');

		$options = '<option value="' . $edmode . '">' . $edmode_name . '</option>';

		if ($edmode == 'coder') {
			$options .= '<option value="visual">Visual</option>';
		} else {
			$options .= '<option value="coder">Coder</option>';
		}

		$select->set('options', $options);

		$form->set('fields', $select->output());
		echo $form->output();

        echo '<hr />';

        echo '<p>Updating page ' . $show_up_file . ' | <a href="files.php?action=renamepg&amp;pg=' . $file . '" class="btn btn-outline-primary sitelink">rename</a></p>'; // update lang

        $form = new PageGen('forms/form.tpl');
        $form->set('form_method', 'post');
        $form->set('form_name', 'form');
        $form->set('form_action', 'files.php?action=editfile&amp;file=' . $file);

        $textarea = new PageGen('forms/textarea.tpl');
        $textarea->set('label_for', 'text_files');
        $textarea->set('textarea_id', 'text_files');
        $textarea->set('textarea_name', 'text_files');
        $textarea->set('textarea_rows', '3');
        $textarea->set('textarea_value', $datamainfile);

        $form->set('fields', $textarea->output());

        echo $form->output();

        echo '<hr>';
        echo '<p><a href="files.php?action=show&amp;file=' . $file . '" class="btn btn-outline-primary sitelink">' . $show_up_file . '</a></p>';

        echo '<p><a href="pgtitle.php?act=edit&amp;pgfile=' . $file . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('pagetitle') . '</a>';
        echo '<a href="files.php?action=updpagelang&amp;id=' . $page_id . '" class="btn btn-outline-primary sitelink">Update page language</a>';
        echo '<a href="files.php?action=headtag&amp;file=' . $file . '" class="btn btn-outline-primary sitelink">Head (meta) tags on this page</a>'; // update lang
        echo '<a href="files.php?action=tags&amp;id=' . $page_id . '" class="btn btn-outline-primary sitelink">Tags</a></p>'; // update lang

    } else {
        $vavok->require_header();
        echo '<p>' . $vavok->go('localization')->string('file') . ' ' . $file . ' ' . $vavok->go('localization')->string('noexist') . '</p>';
    } 

} 
// edit meta tags
if ($vavok->post_and_get('action') == "headtag") {
    if (!$vavok->go('users')->check_permissions('pageedit', 'show') && !$vavok->go('users')->is_administrator()) {
        $vavok->redirect_to("index.php?isset=ap_noaccess");
    }

    $page_info = $page_editor->select_page($page_id);

    if (!$vavok->go('users')->check_permissions('pageedit', 'edit') && !$vavok->go('users')->is_administrator() && $page_info['crtdby'] != $vavok->go('users')->user_id) {
        header("Location: index.php?isset=ap_noaccess");
        exit;
    } 

    $vavok->require_header();

    ?>

<style type="text/css">
.x_meta_buttons {
	-moz-box-shadow:inset 0px 0px 0px 1px #bbdaf7;
	-webkit-box-shadow:inset 0px 0px 0px 1px #bbdaf7;
	box-shadow:inset 0px 0px 0px 1px #bbdaf7;
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #95D7ED), color-stop(1, #378de5) );
	background:-moz-linear-gradient( center top, #95D7ED 5%, #378de5 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#95D7ED', endColorstr='#378de5');
	background-color:#95D7ED;
	-webkit-border-top-left-radius:10px;
	-moz-border-radius-topleft:10px;
	border-top-left-radius:10px;
	-webkit-border-top-right-radius:16px;
	-moz-border-radius-topright:10px;
	border-top-right-radius:10px;
	-webkit-border-bottom-right-radius:10px;
	-moz-border-radius-bottomright:10px;
	border-bottom-right-radius:10px;
	-webkit-border-bottom-left-radius:10px;
	-moz-border-radius-bottomleft:10px;
	border-bottom-left-radius:10px;
	border:1px solid #84bbf3;
	display:inline-block;
	color:#ffffff;
	font-family:Arial;
	font-size:13px;
	font-weight:bold;
	font-style:normal;
	padding: 2px;
	text-decoration:none;
	text-align:center;
}
.x_meta_buttons:link,
.x_meta_buttons:visited {
    color: #000;
}
.x_meta_buttons:hover {
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #378de5), color-stop(1, #95D7ED) );
	background:-moz-linear-gradient( center top, #378de5 5%, #95D7ED 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#378de5', endColorstr='#79bbff');
	background-color:#378de5;
}
.x_meta_buttons:active {
	position:relative;
	top:1px;
}
</style>

<?php

    // show page name
    if (!stristr($file, '/')) {
        $show_up_file = str_replace('.php', '', $file);
        if (stristr($show_up_file, '!.')) {
            $show_up_file = preg_replace("/(.*)!.(.*)!/", "$1", $show_up_file);
        } 
    } else {
        $show_up_file = $file;
    }

    ?>

	<!-- add tags using javascript -->
	<script language="JavaScript">
	<!--
	  function tag(text1, text2) 
	  { 
	     if ((document.selection)) 
	     { 
	       document.form.text_files.focus(); 
	       document.form.document.selection.createRange().text = text1+document.form.document.selection.createRange().text+text2; 
	     } else if(document.forms['form'].elements['text_files'].selectionStart != undefined) { 
	         var element    = document.forms['form'].elements['text_files']; 
	         var str     = element.value; 
	         var start    = element.selectionStart; 
	         var length    = element.selectionEnd - element.selectionStart; 
	         element.value = str.substr(0, start) + text1 + str.substr(start, length) + text2 + str.substr(start + length); 
	     } else document.form.text_files.value += text1+text2; 
	  }	
	//--> 
	</script>

	<p>
		<a class="x_meta_buttons" href=# onClick="javascript:tag('<meta name=&quot;description&quot; content=&quot;', '&quot; />'); return false;">&lt;description&gt;</a>
		<a class="x_meta_buttons" href=# onClick="javascript:tag('<meta name=&quot;keywords&quot; content=&quot;', '&quot; />'); return false;">&lt;keywords&gt;</a>
		<a class="x_meta_buttons" href=# onClick="javascript:tag('<meta name=&quot;author&quot; content=&quot;', '&quot; />'); return false;">&lt;author&gt;</a>
		<a class="x_meta_buttons" href=# onClick="javascript:tag('<meta property=&quot;og:image&quot; content=&quot;', '&quot; />'); return false;">&lt;og:image&gt;</a>
		<a class="x_meta_buttons" href=# onClick="javascript:tag('<meta property=&quot;og:title&quot; content=&quot;', '&quot; />'); return false;">&lt;og:title&gt;</a>
		<a class="x_meta_buttons" href=# onClick="javascript:tag('<meta property=&quot;og:url&quot; content=&quot;', '&quot; />'); return false;">&lt;og:url&gt;</a>
		<a class="x_meta_buttons" href=# onClick="javascript:tag('<meta property=&quot;og:description&quot; content=&quot;', '&quot; />'); return false;">&lt;og:description&gt;</a>
	</p>


<?php

    echo '<legend>Updating file ' . $show_up_file . '</legend>'; // update lang 

    /**
     * Load form
     */
	$form = new PageGen('forms/form.tpl');
	$form->set('form_action', 'files.php?action=editheadtag&amp;file=' . $file);
	$form->set('form_method', 'POST');
	$form->set('form_name', 'form');

    /**
     * Textarea
     */
	$textarea = new PageGen('forms/textarea.tpl');
	$textarea->set('label_for', 'text');
	$textarea->set('textarea_name', 'text_files');
	$textarea->set('textarea_id', 'text');
	$textarea->set('textarea_value', $page_info['headt']);

    /**
     * Input field
     */
	$image_input = new PageGen('forms/input.tpl');
	$image_input->set('label_for', 'image');
	$image_input->set('label_value', 'Default image:');
	$image_input->set('input_type', 'text');
	$image_input->set('input_name', 'image');
	$image_input->set('input_id', 'image');
	$image_input->set('input_value', $page_info['default_img']);

    /**
     * Insert fields
     */
	$form->set('fields', $form->merge(array($textarea, $image_input)));

    /**
     * Show form
     */
	echo $form->output();

	echo '<hr />';
} 

if ($vavok->post_and_get('action') == 'mainmeta') {
    if (!$vavok->go('users')->is_administrator(101)) { $vavok->redirect_to("../?isset=ap_noaccess"); }

    $vavok->require_header();

    echo '<img src="/themes/images/img/panel.gif" alt="" /> Edit tags in &lt;head&gt;&lt;/head&gt; on all pages<br /><br />'; // update lang

    $headtags = trim(file_get_contents(BASEDIR . 'used/headmeta.dat'));

    $form = new PageGen('forms/form.tpl');
    $form->set('form_action', 'files.php?action=editmainhead');
    $form->set('form_name', 'form');
    $form->set('form_method', 'post');

    $textarea = new PageGen('forms/textarea.tpl');
    $textarea->set('label_for', '');
    $textarea->set('textarea_name', 'text_files');
    $textarea->set('textarea_value', $headtags);
    $textarea->set('textarea_id', '');

    $form->set('fields', $textarea->output());
    echo $form->output();

    echo '<hr>';

    echo '<br /><a href="files.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a><br />';
} 

if ($vavok->post_and_get('action') == 'renamepg') {
    if (!$vavok->go('users')->is_administrator()) {
        header("Location: ../?isset=ap_noaccess");
        exit;
    }

    $pg = $vavok->check($vavok->post_and_get('pg'));

    $vavok->require_header();

    echo '<h1>Rename page</h1>'; // update lang

    $form = new PageGen('forms/form.tpl');
    $form->set('form_action', 'files.php?action=save_renamed');
    $form->set('form_name', 'form');
    $form->set('form_method', 'POST');

    $input_pg = new PageGen('forms/input.tpl');
    $input_pg->set('label_for', '');
    $input_pg->set('input_type', 'text');
    $input_pg->set('input_name', 'pg');
    $input_pg->set('input_id', '');
    $input_pg->set('input_value', $pg);

    $input_file = new PageGen('forms/input.tpl');
    $input_file->set('label_for', '');
    $input_file->set('input_type', 'hidden');
    $input_file->set('input_name', 'file');
    $input_file->set('input_id', '');
    $input_file->set('input_value', $pg);

    $form->set('fields', $form->merge(array($input_pg, $input_file)));
    echo $form->output();

    echo '<hr />';

    echo '<p><a href="files.php?action=edit&amp;file=' . $pg . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a></p>';
} 

if ($vavok->post_and_get('action') == "new") {
    if (!$vavok->go('users')->check_permissions('pageedit', 'insert') && !$vavok->go('users')->is_administrator()) {
        $vavok->redirect_to("index.php?isset=ap_noaccess");
    }

    $vavok->go('current_page')->append_head_tags('
	<style type="text/css">
		.tooltip {
			border-bottom: 1px dotted #000000; color: #000000; outline: none;
			cursor: help; text-decoration: none;
			position: relative;
		}
		.tooltip span {
			margin-left: -999em;
			position: absolute;
		}
		.tooltip:hover span {
			border-radius: 5px 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; 
			box-shadow: 5px 5px 5px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 5px 5px rgba(0, 0, 0, 0.1); -moz-box-shadow: 5px 5px rgba(0, 0, 0, 0.1);
			font-family: Calibri, Tahoma, Geneva, sans-serif;
			position: absolute; left: 1em; top: 2em; z-index: 99;
			margin-left: 0; width: 250px;
		}
		.tooltip:hover img {
			border: 0; margin: -10px 0 0 -55px;
			float: left; position: absolute;
		}
		.tooltip:hover em {
			font-family: Candara, Tahoma, Geneva, sans-serif; font-size: 1.2em; font-weight: bold;
			display: block; padding: 0.2em 0 0.6em 0;
		}
		.classic { padding: 0.8em 1em; }
		.custom { padding: 0.5em 0.8em 0.8em 2em; }
		* html a:hover { background: transparent; }
		.classic {background: #FFFFAA; border: 1px solid #FFAD33; }
		.critical { background: #FFCCAA; border: 1px solid #FF3334;	}
		.help { background: #9FDAEE; border: 1px solid #2BB0D7;	}
		.info { background: #9FDAEE; border: 1px solid #2BB0D7;	padding: 20px;}
		.warning { background: #FFFFAA; border: 1px solid #FFAD33; }
	</style>
    ');

    $vavok->require_header();

    echo '<h1>' . $vavok->go('localization')->string('newfile') . '</h1>';

    $form = new PageGen('forms/form.tpl');
    $form->set('form_method', 'post');
    $form->set('form_action', 'files.php?action=addnew');

    /**
     * Page name input
     */
    $input_new_file = new PageGen('forms/input.tpl');
    $input_new_file->set('label_for', 'newfile');
    $input_new_file->set('label_value', $vavok->go('localization')->string('pagename') . ':');
    $input_new_file->set('input_type', 'text');
    $input_new_file->set('input_name', 'newfile');
    $input_new_file->set('input_id', 'newfile');
    $input_new_file->set('input_maxlength', 120);

    /**
     * Language select
     */
    $languages = "SELECT * FROM languages ORDER BY lngeng";

    $options = '<option value="">Don\'t set</option>';
    foreach ($vavok->go('db')->query($languages) as $lang) {
        $options .= "<option value=\"" . strtolower($lang['iso-2']) . "\">" . $lang['lngeng'] . "</option>";
    }

    $select_language = new PageGen('forms/select.tpl');
    $select_language->set('label_for', 'language');
    $select_language->set('label_value', $vavok->go('localization')->string('language') . ' (optional):');
    $select_language->set('select_id', 'language');
    $select_language->set('select_name', 'lang');
    $select_language->set('options', $options);

    /**
     * Page type select
     */
    $select_type = new PageGen('forms/select.tpl');
    $select_type->set('label_for', 'type');
    $select_type->set('label_value', 'Post type:');
    $select_type->set('select_id', 'type');
    $select_type->set('select_name', 'type');
    $select_type->set('options', '<option value="page">Page</option><option value="post">Post</option>');

    /**
     * Custom page structure
     */
    if (!empty($vavok->get_configuration('customPages'))) {

	    $select_structure = new PageGen('forms/select.tpl');
	    $select_structure->set('label_for', 'page_structure');
	    $select_structure->set('label_value', 'Page structure:');
	    $select_structure->set('select_id', 'page_structure');
	    $select_structure->set('select_name', 'page_structure');
	    $select_structure->set('options', '<option value="">/page/new-page/</option>
	    <option value="' . $vavok->get_configuration('customPages') . '">/' . $vavok->get_configuration('customPages') . '/' . $vavok->go('localization')->string('new-page') . '/</option>');

    } else { $select_structure = ''; }

    /**
     * Allow unicode url checkbox
     */
    $checkbox_allow_unicode = new PageGen('forms/checkbox.tpl');
    $checkbox_allow_unicode->set('label_for', 'allow-unicode');
    $checkbox_allow_unicode->set('label_value', $vavok->go('localization')->string('allowUnicodeUrl'));
    $checkbox_allow_unicode->set('checkbox_id', 'allow_unicode');
    $checkbox_allow_unicode->set('checkbox_name', 'allow_unicode');
    $checkbox_allow_unicode->set('checkbox_value', 'on');


    /**
     * All form fields
     */
    $fields = array($input_new_file, $select_language, $select_type, $select_structure, $checkbox_allow_unicode);

    /**
     * Remove field is it is empty
     */
    if (empty($select_structure)) unset($fields[3]);

    /**
     * Merge fields
     */
    $form->set('fields', $form->merge($fields));

    /**
     * Show form
     */
    $form->set('website_language[save]', $vavok->go('localization')->string('newpage'));
    echo $form->output();

} 

// confirm that you want to delete a page
if ($vavok->post_and_get('action') == "poddel") {
    if (!$vavok->go('users')->check_permissions('pageedit', 'del') && !$vavok->go('users')->is_administrator()) {
        header("Location: index.php?isset=ap_noaccess");
        exit;
    } 

    $vavok->require_header();

    if (!empty($file)) {
        if ($file != "index.php") {
            echo $vavok->go('localization')->string('confdelfile') . ' <b>' . $file . '</b><br />';
            echo '<b><a href="files.php?action=del&amp;file=' . $file . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('delete') . '</a></b><br />';
        } else {
            echo $vavok->go('localization')->string('indexnodel') . '!<br />';
        } 
    } else {
        echo $vavok->go('localization')->string('nofiletodel') . '<br />';
    } 
    echo '<a href="files.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a><br />';
} 

if ($vavok->post_and_get('action') == 'updpagelang') {
    if (!$vavok->go('users')->is_administrator()) $vavok->redirect_to('index.php?isset=ap_noaccess');

    $id = $vavok->check($vavok->post_and_get('id'));

    // get page data
    $pageData = $page_editor->select_page($id);

    $vavok->require_header();

	$form = new PageGen('forms/form.tpl');
	$form->set('form_method', 'post');
	$form->set('form_action', 'files.php?action=pagelang&amp;id=' . $pageData['id']);

	$select_language = new PageGen('forms/select.tpl');
	$select_language->set('label_for', 'lang');
	$select_language->set('label_value', $vavok->go('localization')->string('language'));
	$select_language->set('select_name', 'lang');
	$select_language->set('select_id', 'lang');

	$options = '';
	if (!empty($pageData['lang'])) {
		$options .= '<option value="' . $pageData['lang'] . '">' . $pageData['lang'] . '</option>';
		$options .= '<option value="">Leave empty</option>'; // update language
	} else {
		$options .= '<option value="">Leave empty</option>'; // update language
	}

	$languages = "SELECT * FROM languages ORDER BY lngeng";

	foreach ($vavok->go('db')->query($languages) as $lang) {
	    $options .= '<option value="' . strtolower($lang['iso-2']) . '">' . $lang['lngeng'] . '</option>';
	}

	$select_language->set('options', $options);

	$form->set('fields', $select_language->output());

	echo $form->output();

    echo '<p><a href="files.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a></p>';
}

if ($vavok->post_and_get('action') == 'tags') {
    if (!$vavok->go('users')->check_permissions('pageedit', 'edit') && !$vavok->go('users')->is_administrator() && $page_info['crtdby'] != $vavok->go('users')->user_id) {
        redirect_to("./?isset=ap_noaccess");
    }

    $vavok->require_header();

    /**
     * Get page data
     */
    $tag_field = new PageGen('forms/input.tpl');
    $tag_field->set('label_value', 'Tags');
    $tag_field->set('label_for', 'tags');
    $tag_field->set('input_name', 'tags');
    $tag_field->set('input_id', 'tags');
    $tag_field->set('input_type', 'text');
    $tag_field->set('input_value', $page_editor->page_tags($id));

    $form = new PageGen('forms/form.tpl');
    $form->set('form_action', 'files.php?action=savetags&amp;id=' . $id);
    $form->set('form_method', 'post');
    $form->set('fields', $tag_field->output());

    echo $form->output();

}

echo '<p>';
if ($vavok->post_and_get('action') != "new" && ($vavok->go('users')->check_permissions('pageedit', 'insert') || $vavok->go('users')->is_administrator())) {
    echo '<a href="files.php?action=new" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('newpage') . '</a>';
}
if ($vavok->go('users')->is_administrator(101)) {
    echo '<a href="files.php?action=mainmeta" class="btn btn-outline-primary sitelink">Head (meta) tags on all pages</a>';
} // update lang
if ($vavok->go('users')->is_administrator()) {
    echo '<a href="filesearch.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('search') . '</a>';
} 
if (!empty($vavok->post_and_get('action'))) {
    echo '<a href="files.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('mngpage') . '</a>';
}

echo '</p>';

echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('admpanel') . '</a><br />';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();

?>