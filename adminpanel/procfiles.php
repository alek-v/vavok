<?php 
// (c) vavok.net - Aleksandar Vranešević
// modified: 26.07.2020. 2:33:09
// todo: rewrite whole page

require_once"../include/startup.php";
require_once"../include/htmlbbparser.php";

if (!$users->is_reg() || (!$users->is_administrator() && !$users->check_permissions('pageedit', 'show'))) { redirect_to("../?isset=ap_noaccess"); }

// init page editor
$pageEditor = new Page;

$action = isset($_GET['action']) ? check($_GET['action']) : '';

if (isset($_GET['file'])) {
    $file = check($_GET['file']);

    // get page id we work with
    $page_id = $pageEditor->get_page_id("file='{$file}'");
} elseif (isset($_POST['file'])) {
    $file = check($_POST['file']);

    // get page id we work with
    $page_id = $pageEditor->get_page_id("file='{$file}'");
} else {
    $file = '';
}

$text_files = isset($_POST['text_files']) ? $_POST['text_files'] : ''; // keep data as received so html codes will be ok

$config_editfiles = 10;

if ($action == "editfile") {
    // get edit mode
    if (!empty($_SESSION['edmode'])) {
        $edmode = check($_SESSION['edmode']);
    } else {
        $edmode = 'columnist';
        $_SESSION['edmode'] = $edmode;
    } 

    if (!empty($file) && !empty($text_files)) {
        $page_info = $pageEditor->select_page($page_id, 'crtdby, published');

        if (!$users->check_permissions('pageedit', 'show') && !$users->is_administrator()) { redirect_to("index.php?isset=ap_noaccess"); } 

        if ($page_info['crtdby'] != $users->user_id && !$users->check_permissions('pageedit', 'edit') && (!$users->check_permissions('pageedit', 'editunpub') || $page_info['published'] != 1) && !$users->is_administrator()) {
            header("Location: index.php?isset=ap_noaccess");
            exit;
        }

        // bug when magic quotes are on and '\' sign
        // if magic quotes are on we don't want ' to become \'
        if (get_magic_quotes_gpc()) {
            // strip all slashes
            $text_files = stripslashes($text_files);
        }

        // update db data
        $pageEditor->update($page_id, $text_files);
    } 

    redirect_to("files.php?action=edit&file=$file&isset=mp_editfiles");

}

// update head tags on all pages
if ($action == 'editmainhead') {
    if (!$users->is_administrator(101)) {
        redirect_to("../?isset=ap_noaccess");
    } 

    // update header data
    file_put_contents("../used/headmeta.dat", $text_files);

    redirect_to("files.php?action=mainmeta&isset=mp_editfiles");
}

// update head tags on specific page
if ($action == "editheadtag") {

    // get default image link
    $image = !empty($_POST['image']) ? check($_POST['image']) : '';

    // update header tags
    if (!empty($file)) {

        // who created page
        $page_info = $pageEditor->select_page($page_id, 'crtdby');

        // check can user see page
        if (!$users->check_permissions('pageedit', 'show') && !$users->is_administrator()) { redirect_to("index.php?isset=ap_noaccess"); }

        // check can user edit page
        if (!$users->check_permissions('pageedit', 'edit') && !$users->is_administrator() && $page_info['crtdby'] != $users->user_id) { redirect_to("index.php?isset=ap_noaccess"); } 

        // update db data
        $data = array(
            'headt' => $text_files,
            'default_img' => $image
        );
        $pageEditor->head_data($page_id, $data);

        // redirect
        redirect_to("files.php?action=headtag&file=$file&isset=mp_editfiles");

    } 
    // fields must not be empty
    redirect_to("files.php?action=headtag&file=$file&isset=mp_noeditfiles");
}

// rename page
if ($action == "renamepg") {
    $pg = check($_POST['pg']); // new file name

    if (!empty($pg) && !empty($file)) {
        $page_info = $pageEditor->select_page($page_id, 'crtdby');

        if (!$users->check_permissions('pageedit', 'show') && !$users->is_administrator()) {
            header("Location: index.php?isset=ap_noaccess");
            exit;
        } 
        if (!$users->check_permissions('pageedit', 'edit') && !$users->is_administrator() && $page_info['crtdby'] != $users->user_id) {
            header("Location: index.php?isset=ap_noaccess");
            exit;
        } 

        // rename page
        $pageEditor->rename($pg, $page_id);

        header("Location: files.php?action=edit&file=$pg&isset=mp_editfiles");
        exit;
    } 
    header("Location: files.php?action=edit&file=$pg&isset=mp_noedit");
    exit;
}

if ($action == "addnew") {

    if (!$users->check_permissions('pageedit', 'insert') && !$users->is_administrator()) { redirect_to("index.php?isset=ap_noaccess"); }

    $newfile = isset($_POST['newfile']) ? check($_POST['newfile']) : '';
    $type = isset($_POST['type']) ? check($_POST['type']) : '';
    $page_structure = isset($_POST['page_structure']) ? check($_POST['page_structure']) : '';
    $allow_unicode = isset($_POST['allow_unicode']) ? true : false;

    // page title
    $page_title = $newfile;

    // page name in url
    if ($allow_unicode === false) {
        // remove unicode chars
        $newfile = trans($newfile);
    } else {
        $newfile = trans_unicode($newfile);
    }

    // if page structure is set
    if (!empty($page_structure)) {
        $type = $page_structure;
    }

    // page language
    if (isset($_POST['lang']) && !empty($_POST['lang'])) {

        $pagelang = check($_POST['lang']);

        $pagelang_file = '!.' . $pagelang . '!';

    } else {

        $pagelang = '';

    }

    if (!empty($newfile)) {
        // page filename
        $newfiles = $newfile . $pagelang_file . '.php';

        // check if page exists
        $includePageLang = !empty($pagelang) ? " AND lang='{$pagelang}'" : '';

        if ($pageEditor->page_exists('', "pname='{$newfile}'" . $includePageLang)) {
            redirect_to("files.php?action=new&isset=mp_pageexists");
        }

        // full page address
        if (!empty($page_structure)) {
            // user's custom page structure
            $page_url = website_home_address() . '/' . $page_structure . '/' . $newfile . '/';
        } elseif ($type == 'post') {
            // blog post
            $page_url = website_home_address() . '/blog/' . $newfile . '/';
        } else {
            // page
            $page_url = website_home_address() . '/page/' . $newfile . '/';
        }

        // insert db data
        $values = array(
        'pname' => $newfile,
        'lang' => $pagelang,
        'created' => time(),
        'lastupd' => time(),
        'lstupdby' => $users->user_id,
        'file' => $newfiles,
        'crtdby' => $users->user_id,
        'published' => '1',
        'pubdate' => '0',
        'tname' => $page_title,
        'headt' => '<meta property="og:title" content="' . $page_title . '" />'. "\r\n" . '<meta property="og:url" content="' . $page_url . '" />' . "\r\n" . '<link rel="canonical" href="' . $page_url . '" />',
        'type' => $type
        );

        // insert data
        $pageEditor->insert($values);

        // file successfully created
        redirect_to("files.php?action=edit&file=$newfiles&isset=mp_newfiles");

    } else {
        redirect_to("files.php?action=new&isset=mp_noyesfiles");
    }

}

if ($action == "del") {

    if (!$users->check_permissions('pageedit', 'del') && !$users->is_administrator()) {
        redirect_to("index.php?isset=ap_noaccess");
    }

    // delete page
    $pageEditor->delete($page_id);
 
    redirect_to("files.php?isset=mp_delfiles");
}

// publish page; page will be avaliable for visitors
if ($action == "publish") {
    if (!empty($page_id)) {

        if (!$users->check_permissions('pageedit', 'edit') && !$users->is_administrator()) {
            header("Location: index.php?isset=ap_noaccess");
            exit;
        }

        // update db data
        $pageEditor->visibility($page_id, 2);
    } 

    redirect_to("files.php?action=show&file=" . $file . "&isset=mp_editfiles");
}

// unpublish page
if ($action == "unpublish") {

    if (!empty($page_id)) {

        if (!$users->check_permissions('pageedit', 'edit') && !$users->is_administrator()) {
            header("Location: index.php?isset=ap_noaccess");
            exit;
        }

        // update db data
        $pageEditor->visibility($page_id, 1);
    } 

    redirect_to("files.php?action=show&file=" . $file . "&isset=mp_editfiles");
}

// update page language
if ($action == 'pagelang') {
    if (!$users->is_administrator()) { redirect_to("../?isset=ap_noaccess"); }

    $pageId = check($_GET['id']);
    $lang = check($_POST['lang']);

    // update database data
    $pageEditor->language($pageId, $lang);

    $pageData = $pageEditor->select_page($pageId);
    redirect_to("files.php?action=show&file=" . $pageData['pname'] . "!." . $lang . "!.php&isset=mp_editfiles");

} 

?>