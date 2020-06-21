<?php 
// (c) vavok.net
// todo: rewrite whole page

require_once"../include/strtup.php";
require_once"../include/htmlbbparser.php";

// checking access permitions
if (!$users->is_reg() || (!$users->is_administrator(101) && chkcpecprm('pageedit', 'show'))) {
    redirect_to("../");
} 

$table_prefix = get_configuration('tablePrefix');

// check if user can edit only pages that are made by themself and have no permitions to edit all pages
if (!chkcpecprm('pageedit', 'edit') && !$users->is_administrator(101)) {
    $editOnlyOwnPages = 'yes';
} else {
    $editOnlyOwnPages = '';
}

$action = isset($_GET['action']) ? check($_GET['action']) : '';
$page = isset($_GET['page']) ? check($_GET['page']) : '';
$file = isset($_GET['file']) ? check($_GET['file']) : '';

// init class
$pageEditor = new Page();

// get page id we work with
$page_id = $pageEditor->get_page_id("file='{$file}'");


$config_editfiles = 20;
$my_title = 'Files'; // current page title


// editing mode
// use visual mode as default
if (!empty($_SESSION['edmode'])) {
    $edmode = check($_SESSION['edmode']);
} else {
    $edmode = 'visual';
    $_SESSION['edmode'] = $edmode;
} 
if (!empty($_POST['edmode'])) {
    $edmode = check($_POST['edmode']);
    $_SESSION['edmode'] = $edmode;
}


if ($edmode == 'visual') {
    // text editor
    $loadTextEditor = $pageEditor->loadPageEditor();

    // remove fullpage plugin if exists, we dont need html header and footer tags
    $loadTextEditor = str_replace('fullpage ' , '', $loadTextEditor);

    // choose field selector
    $textEditor = str_replace('#selector', '#text_files', $loadTextEditor);

    // add to page header
    $genHeadTag = $textEditor;
}

if (empty($action)) {
    include_once"../themes/$config_themes/index.php";

    if (isset($_GET['isset'])) {
        $isset = check($_GET['isset']);
        echo '<div align="center"><b><font color="#FF0000">';
        echo get_isset();
        echo '</font></b></div>';
    } 

    echo '<h1>' . $lang_home['filelist'] . '</h1>';

    $total_pages = $pageEditor->total_pages();

    if ($editOnlyOwnPages == 'yes') {
        $total_pages = $pageEditor->total_pages($user_id);
    } 

    // start navigation
    $navi = new Navigation($config_editfiles, $total_pages, $page);


    if ($editOnlyOwnPages == 'yes') {
        $sql = "SELECT * FROM {$table_prefix}pages WHERE crtdby='{$user_id}' ORDER BY pname LIMIT {$navi->start()['start']}, $config_editfiles";
    } else {
        $sql = "SELECT * FROM {$table_prefix}pages ORDER BY pname LIMIT {$navi->start()['start']}, $config_editfiles";
    } 
    foreach ($db->query($sql) as $page_info) {
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

        // permitions to edit home page of the site
        if ($page_info['file'] === "index.php" && empty($editOnlyOwnPages)) {
            echo '<b><a href="files.php?action=show&amp;file=' . $page_info['file'] . '" class="btn btn-primary sitelink"><font color="#FF0000">' . $filename . '</font></a></b> ' . $lang_home['created'] . ': ' . date_fixed($page_info['created'], 'd.m.y.') . ' ' . $lang_home['by'] . ' ' . getnickfromid($page_info['crtdby']) . ' | ' . $lang_home['lastupdate'] . ' ' . date_fixed($page_info['lastupd'], 'd.m.y.') . ' ' . $lang_home['by'] . ' ' . getnickfromid($page_info['lstupdby']) . '<br />';
            if (chkcpecprm('pageedit', 'edit') || $users->is_administrator(101)) {
                echo '<a href="files.php?action=edit&amp;file=' . $page_info['file'] . '" class="btn btn-outline-primary btn-sm">[Edit]</a><hr>';
            } 
        } else {
            if (empty($editOnlyOwnPages) || (chkcpecprm('pageedit', 'editunpub') && $page_info['published'] == 1)) {
                echo '<b><a href="files.php?action=show&amp;file=' . $page_info['file'] . '" class="btn btn-primary sitelink">' . $filename . '</a></b> ' . $lang_home['created'] . ': ' . date_fixed($page_info['created'], 'd.m.y.') . ' ' . $lang_home['by'] . ' ' . getnickfromid($page_info['crtdby']) . ' | ' . $lang_home['lastupdate'] . ' ' . date_fixed($page_info['lastupd'], 'd.m.y.') . ' ' . $lang_home['by'] . ' ' . getnickfromid($page_info['lstupdby']) . '<br />';
                if (chkcpecprm('pageedit', 'edit') || $users->is_administrator(101) || $page_info['crtdby'] == $user_id || (chkcpecprm('pageedit', 'editunpub') && $page_info['published'] == 1)) {
                    echo '<a href="files.php?action=edit&amp;file=' . $page_info['file'] . '" class="btn btn-outline-primary btn-sm">[Edit]</a>';
                } 
                if (chkcpecprm('pageedit', 'del') || $users->is_administrator(101)) {
                    echo ' | <a href="files.php?action=poddel&amp;file=' . $page_info['file'] . '" class="btn btn-outline-primary btn-sm">[Del]</a>';
                } 
                if ($page_info['published'] == 1 && (chkcpecprm('pageedit', 'publish') || $users->is_administrator(101))) {
                    echo ' | <a href="procfiles.php?action=publish&amp;file=' . $page_info['file'] . '" class="btn btn-outline-primary btn-sm">[Publish]</a>';
                } 
                if ($page_info['published'] != 1 && (chkcpecprm('pageedit', 'publish') || $users->is_administrator(101))) {
                    echo ' | <a href="procfiles.php?action=unpublish&amp;file=' . $page_info['file'] . '" class="btn btn-outline-primary btn-sm">[Unpublish]</a>';
                } 
                echo '<hr />';
            } elseif ($editOnlyOwnPages == 'yes' && $page_info['crtdby'] == $user_id) {
                echo '<b><a href="files.php?action=show&amp;file=' . $page_info['file'] . '" class="btn btn-primary sitelink">' . $filename . '</a></b> ' . $lang_home['created'] . ': ' . date_fixed($page_info['created'], 'd.m.y.') . ' ' . $lang_home['by'] . ' ' . getnickfromid($page_info['crtdby']) . ' | ' . $lang_home['lastupdate'] . ' ' . date_fixed($page_info['lastupd'], 'd.m.y.') . ' ' . $lang_home['by'] . ' ' . getnickfromid($page_info['lstupdby']) . '<br />';
                echo '<a href="files.php?action=edit&amp;file=' . $page_info['file'] . '" class="btn btn-outline-primary btn-sm">[Edit]</a>';
                echo '<hr />';
            } 
        } 
        unset($page_info);
    } 

    // navigation
    $navigation = new Navigation($config_editfiles, $total_pages, $page, 'files.php?');
    echo $navigation->get_navigation();

    echo '<br />' . $lang_home['totpages'] . ': <b>' . (int)$total_pages . '</b><br />';
    echo '<div>&nbsp;</div>';
    if (empty($editOnlyOwnPages)) {
        echo '<a href="pgtitle.php" class="btn btn-outline-primary sitelink">' . $lang_home['pagetitle'] . '</a><br />';
    } 
} 

if ($action == "show") {

    if (!empty($page_id)) {
        $base_file = $file;

        $pageData = new Page();
        $page_info = $pageData->select_page($page_id);

        if (!chkcpecprm('pageedit', 'show') && !$users->is_administrator(101)) {
            redirect_to("index.php?isset=ap_noaccess");
        } 

        if ($page_info['crtdby'] != $user_id && !chkcpecprm('pageedit', 'edit') && (!chkcpecprm('pageedit', 'editunpub') || $page_info['published'] != 1) && !$users->is_administrator(101)) {
            redirect_to("index.php?isset=ap_noaccess");
        } 

        $showname = $page_info['pname'];

        include_once"../themes/$config_themes/index.php";

        if (isset($_GET['isset'])) {
            $isset = check($_GET['isset']);
            echo '<div align="center"><b><font color="#FF0000">';
            echo get_isset();
            echo '</font></b></div>';
        } 

        echo '<p>' . $lang_home['shwingpage'] . ' <b>' . $showname . '</b></p>';
        echo '<p>' . $lang_home['created'] . ': ' . date_fixed($page_info['created'], 'd.m.y.') . ' ' . $lang_home['by'] . ' ' . getnickfromid($page_info['crtdby']);
        echo ' | ' . $lang_home['lastupdate'] . ' ' . date_fixed($page_info['lastupd'], 'd.m.y.') . ' ' . $lang_home['by'] . ' ' . getnickfromid($page_info['lstupdby']);
        
        // post type
        $post_type = !empty($page_info['type']) ? $page_info['type'] : 'page';
        echo ' | Page type: ' . $post_type;

        if ($page_info['published'] == 1 && (chkcpecprm('pageedit', 'publish') || $users->is_administrator(101))) {
            echo ' | <a href="procfiles.php?action=publish&amp;file=' . $file . '">[Publish]</a>';
        } 
        if ($page_info['published'] != 1 && (chkcpecprm('pageedit', 'publish') || $users->is_administrator(101))) {
            echo ' | <a href="procfiles.php?action=unpublish&amp;file=' . $file . '">[Unpublish]</a>';
        }
        echo '</p>';

        echo '<p>';
        echo $lang_home['pgaddress'] . ': ';

        // if it is index doesnt show this page like other pages
        if (preg_match('/^index(?:!\.[a-zA-Z]{2}!)?\.php$/', $file)) {
            // this is index page
        	if (!empty($page_info['lang'])) { $url_lang = strtolower($page_info['lang']) . '/'; } else { $url_lang = ''; }

        	echo '<a href="' . website_home_address() . '/' . $url_lang . '" target="_blank">' . website_home_address() . '/' . $url_lang . '</a>';

 		} elseif ($post_type == 'post') {
            // this is blog post
            echo '<br /><a href="' . website_home_address() . '/blog/' . $showname . '/" target="_blank">' . website_home_address() . '/blog/' . $showname . '/</a><br />';

        } elseif ($post_type == 'page' || empty($post_type)) {
            // this is page
	        echo '<br /><a href="' . website_home_address() . '/page/' . $showname . '/" target="_blank">' . website_home_address() . '/page/' . $showname . '/</a><br />';
        } else {
            // this is custom page structure
            echo '<br /><a href="' . website_home_address() . '/' . $post_type . '/' . $showname . '/" target="_blank">' . website_home_address() . '/' . $post_type . '/' . $showname . '/</a><br />';
        }

        echo '</p>';


        echo '<br /><a href="files.php?action=edit&amp;file=' . $base_file . '" class="btn btn-outline-primary sitelink">' . $lang_home['edit'] . '</a><br />';
        if (chkcpecprm('pageedit', 'del') || $users->is_administrator(101)) {
        echo '<a href="files.php?action=poddel&amp;file=' . $base_file . '" class="btn btn-outline-primary sitelink">' . $lang_home['delete'] . '</a><br />';
        }
    } 

if (empty($editOnlyOwnPages)) {
echo '<a href="pgtitle.php?act=edit&amp;pgfile=' . $base_file . '" class="btn btn-outline-primary sitelink">' . $lang_home['pagetitle'] . '</a><br />';
} 
} 

if ($action == "edit") {
    // check if page exists
    $checkPage = $pageEditor->page_exists($file);

    // coder mode for advanced users / coders
    if ($edmode == 'coder') {
        $edmode_name = 'Coder';
    } 
    if ($edmode == 'visual') {
        $edmode_name = 'Visual';
    } 

    if ($checkPage == true) {
        $page_info = $pageEditor->select_page($page_id);

        if (!chkcpecprm('pageedit', 'show') && !$users->is_administrator(101)) {
            header("Location: index.php?isset=ap_noaccess");
            exit;
        } 

        if ($page_info['crtdby'] != $user_id && !chkcpecprm('pageedit', 'edit') && (!chkcpecprm('pageedit', 'editunpub') || $page_info['published'] != 1) && !$users->is_administrator(101)) {
            header("Location: index.php?isset=ap_noaccess");
            exit;
        } 

        include_once"../themes/$config_themes/index.php";

        if (isset($_GET['isset'])) {
            $isset = check($_GET['isset']);
            echo '<div align="center"><b><font color="#FF0000">';
            echo get_isset();
            echo '</font></b></div>';
        } 

        $datamainfile = $page_info['content'];

        $datamainfile = str_replace('<?php echo \'<a href="\' . BASEDIR . \'pages/inbox.php">\' . $lang_home[\'inbox\'] . \'</a>\'; ?>', '{INBOX}', $datamainfile);
        $datamainfile = str_replace('<?php echo \'(\' . $users->user_mail($user_id) . \')\'; ?>', '{INBOXMSGS}', $datamainfile);
        $datamainfile = str_replace('<?php echo \'<a href="\' . BASEDIR . \'pages/input.php?action=exit">\' . $lang_home[\'logout\'] . \'</a>\'; ?>', '{LOGOUT}', $datamainfile);

        $datamainfile = str_replace('<?=BASEDIR?>', '{BASEDIR}', $datamainfile);
        $datamainfile = str_replace('<?php echo BASEDIR; ?>', '{BASEDIR}', $datamainfile);

        $datamainfile = htmlspecialchars($datamainfile); 

        // show page name
        $show_up_file = str_replace('.php', '', $file);
        if (stristr($show_up_file, '!.')) {
            $show_up_file = preg_replace("/(.*)!.(.*)!/", "$1", $show_up_file);
        } 

        echo '<p>Edit mode: ' . $edmode_name . '</p>
        <form method="post" action="files.php?action=edit&amp;file=' . $file . '">
		<select name="edmode" >
		<option value="' . $edmode . '">' . $edmode_name . '</option>';
		if ($edmode == 'coder') {
			echo '<option value="visual">Visual</option>';
		} else {
		echo '<option value="coder">Coder</option>';
		}
		
		echo '</select>
		<input type="submit" name="submit_button" value="Go">
		</form><br />';

        echo '<hr /><p>Updating page ' . $show_up_file . ' | <a href="files.php?action=renamepg&amp;pg=' . $file . '" class="btn btn-outline-primary sitelink">rename</a></p><br />'; // update lang

        echo '<form action="procfiles.php?action=editfile&amp;file=' . $file . '" name="form" method="POST">';

        echo '<textarea id="text_files" name="text_files">';
        echo $datamainfile;
        echo '</textarea>';

        echo '<br /><br />';

        echo '<br /><button type="submit" class="btn btn-primary">' . $lang_home['save'] . '</button></form><hr>';
        echo '<p><a href="files.php?action=show&amp;file=' . $file . '" class="btn btn-outline-primary sitelink">' . $show_up_file . '</a></p>';

        echo '<a href="pgtitle.php?act=edit&amp;pgfile=' . $file . '" class="btn btn-outline-primary sitelink">' . $lang_home['pagetitle'] . '</a>';
        echo '<a href="files.php?action=headtag&amp;file=' . $file . '" class="btn btn-outline-primary sitelink">Head (meta) tags on this page</a>'; // update lang
        
    } else {
        include_once"../themes/$config_themes/index.php";
        echo $lang_admin['file'] . ' ' . $file . ' ' . $lang_admin['noexist'] . '<br />';
    } 

} 
// edit meta tags
if ($action == "headtag") {

    if (!chkcpecprm('pageedit', 'show') && !$users->is_administrator(101)) {
        redirect_to("index.php?isset=ap_noaccess");
    }

    $page_info = $pageEditor->select_page($page_id);

    if (!chkcpecprm('pageedit', 'edit') && !$users->is_administrator(101) && $page_info['crtdby'] != $user_id) {
        header("Location: index.php?isset=ap_noaccess");
        exit;
    } 

    include_once"../themes/$config_themes/index.php";

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

    if (isset($_GET['isset'])) {
        $isset = check($_GET['isset']);
        echo '<div align="center"><b><font color="#FF0000">';
        echo get_isset();
        echo '</font></b></div>';
    } 
    // show page name
    if (!stristr($file, '/')) {
        $show_up_file = str_replace('.php', '', $file);
        if (stristr($show_up_file, '!.')) {
            $show_up_file = preg_replace("/(.*)!.(.*)!/", "$1", $show_up_file);
        } 
    } else {
        $show_up_file = $file;
    } 

    echo '<legend>Updating file ' . $show_up_file . '</legend>'; // update lang 

    echo '<form action="procfiles.php?action=editheadtag&amp;file=' . $file . '" name="form" method="POST">';

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
	echo '<label for="text"></label>';
    echo '<textarea cols="80" rows="30" name="text_files" id="text">';
    echo $page_info['headt'];
    echo '</textarea>';

	echo '<label for="image">Default image:</label>';
    echo '<input type=text" name="image" id="image" value="' . $page_info['default_img'] . '">';

    echo '<br />
            <button type="submit" class="btn btn-primary">' . $lang_home['save'] . '</button>

            </form><hr />';
} 

if ($action == 'mainmeta') {
    if (!$users->is_administrator(101)) {
        header("Location: ../?isset=ap_noaccess");
        exit;
    } 
    include_once"../themes/$config_themes/index.php";
    if (isset($_GET['isset'])) {
        $isset = check($_GET['isset']);
        echo '<div align="center"><b><font color="#FF0000">';
        echo get_isset();
        echo '</font></b></div>';
    } 

    echo '<img src="/images/img/panel.gif" alt="" /> Edit tags in &lt;head&gt;&lt;/head&gt; on all pages<br /><br />'; // update lang
    $headtags = file_get_contents('../used/headmeta.dat');

    echo '<form action="procfiles.php?action=editmainhead" name="form" method="POST">';

    echo '<textarea cols="80" rows="30" name="text_files">';
    echo $headtags;
    echo '</textarea>';

    echo '<br /><input type="submit" value="' . $lang_home['save'] . '"></form><hr>';

    echo '<br /><a href="files.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br />';
} 

if ($action == 'renamepg') {
    if (!$users->is_administrator(101)) {
        header("Location: ../?isset=ap_noaccess");
        exit;
    }

    $pg = check($_GET['pg']);

    include_once"../themes/$config_themes/index.php";

    if (isset($_GET['isset'])) {
        $isset = check($_GET['isset']);

        echo '<div align="center"><b><font color="#FF0000">';
        echo get_isset();
        echo '</font></b></div>';
    } 

    echo '<img src="/images/img/panel.gif" alt="" /> Rename page<br /><br />'; // update lang
    echo '<form action="procfiles.php?action=renamepg" name="form" method="POST">';
    echo '<input type="text" name="pg" value="' . $pg . '">';
    echo '<input type="hidden" name="file" value="' . $pg . '">';
    echo '<br /><input type="submit" value="' . $lang_home['save'] . '"></form><hr /><br />';

    echo '<a href="files.php?action=edit&amp;file=' . $pg . '" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br />';
} 

if ($action == "new") {

    if (!chkcpecprm('pageedit', 'insert') && !$users->is_administrator(101)) {
        redirect_to("index.php?isset=ap_noaccess");
    }

    $genHeadTag = '
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
    ';

    include_once"../themes/$config_themes/index.php";

    if (isset($_GET['isset'])) {
        $isset = check($_GET['isset']);
        echo '<div align="center"><b><font color="#FF0000">';
        echo get_isset();
        echo '</font></b></div>';
    } 

    echo '<div>
    <h1><img src="../images/img/edit.gif" alt="" /> ' . $lang_home['newfile'] . '</h1></div>';

    echo '<div class="form-group">
    		<form method="post" action="procfiles.php?action=addnew">';
    echo '<label for="newfile">' . $lang_home['pagename'] . ':</label>';
	    echo '<input class="form-control" type="text" name="newfile" id="newfile" maxlength="120" />
    </div>'; 


    // language
    $languages = "SELECT * FROM languages ORDER BY lngeng";

    echo '<div class="form-group">
    <label for="language">' . $lang_home['language'] . ' (optional):</label>';
    echo '<select class="form-control" id="language" name="lang">';

    echo '<option value="">Don\'t set</option>';
    foreach ($db->query($languages) as $lang) {
        echo "<option value=\"" . strtolower($lang['iso-2']) . "\">" . $lang['lngeng'] . "</option>";
    } 
    echo "</select>
    </div>";
    ?>

    <div class="form-group">
    <label for="type">Post type:</label>
    <select class="form-control" id="type" name="type">
        <option value="page">Page</option>
        <option value="post">Post</option>
    </select>
    </div>

    <?php
    if (!empty(get_configuration('customPages'))) {

    echo '<div class="form-group">
    <label for="page_structure">Page structure:</label>
    <select class="form-control" id="page_structure" name="page_structure">
        <option value="">/page/new-page/</option>
        <option value="' . get_configuration('customPages') . '">/' . get_configuration('customPages') . '/' . $lang_home['new-page'] . '/</option>
    </select>
    </div>';

    }
    ?>

    <div class="form-group form-check">
      <input class="form-check-input" type="checkbox" value="" name="allow_unicode" id="allow-unicode">
      <label class="form-check-label" for="allow-unicode">
        <?php echo $lang_home['allowUnicodeUrl']; ?>
      </label>
    </div>

    <?php
    echo '<div class="form-group">
    <button class="btn btn-primary" type="submit" />' . $lang_home['newpage'] . '</button>
    </div>
    </form>';
} 

// confirm that you want to delete a page
if ($action == "poddel") {
    if (!chkcpecprm('pageedit', 'del') && !$users->is_administrator(101)) {
        header("Location: index.php?isset=ap_noaccess");
        exit;
    } 

    include_once"../themes/$config_themes/index.php";

    if (!empty($file)) {
        if ($file != "index.php") {
            echo $lang_home['confdelfile'] . ' <b>' . $file . '</b><br />';
            echo '<b><a href="procfiles.php?action=del&amp;file=' . $file . '" class="btn btn-outline-primary sitelink">' . $lang_home['delete'] . '</a></b><br />';
        } else {
            echo $lang_home['indexnodel'] . '!<br />';
        } 
    } else {
        echo $lang_home['nofiletodel'] . '<br />';
    } 
    echo '<a href="files.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br />';
} 

if ($action == "pagelang") {
    if (!$users->is_administrator(101)) {
        redirect_to("index.php?isset=ap_noaccess");
    } 

    $id = check($_GET['id']);

    // get page data
    $pageData = $pageEditor->select_page($id);

    include_once"../themes/$config_themes/index.php";

    echo '<div class="form-group">';

	    echo '<form method="post" action="procfiles.php?action=pagelang&amp;id=' . $pageData['id'] . '">'; 

		    echo '<label for="lang">' . $lang_home['language'] . '</label>';

		    echo '<select name="lang" id="lang" class="custom-select custom-select-lg mb-3">';

		    if (!empty($pageData['lang'])) {

		    	echo '<option value="' . $pageData['lang'] . '">' . $pageData['lang'] . '</option>';

		    } else {

		    	echo '<option value="">Leave empty</option>'; // update language

			}

		    $languages = "SELECT * FROM languages ORDER BY lngeng";

		    foreach ($db->query($languages) as $lang) {
		        echo '<option value="' . strtolower($lang['iso-2']) . '">' . $lang['lngeng'] . '</option>';
		    }

		    echo '</select>';

		    echo '<button type="submit" class="btn btn-primary">' . $lang_home['save'] . '</button>

	    </form>';

    echo '</div>';

    echo '<a href="files.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br />';
} 

if ($action != "new" && (chkcpecprm('pageedit', 'insert') || $users->is_administrator(101))) {
    echo '<a href="files.php?action=new" class="btn btn-outline-primary sitelink">' . $lang_home['newpage'] . '</a>';
} 
if ($users->is_administrator(101) && ($action == 'edit' || $action == 'show')) {
    echo '<a href="files.php?action=pagelang&amp;id=' . $page_id . '" class="btn btn-outline-primary sitelink">Update page language</a>';
} 
if ($users->is_administrator(101)) {
    echo '<a href="files.php?action=mainmeta" class="btn btn-outline-primary sitelink">Head (meta) tags on all pages</a>';
} // update lang
if ($users->is_administrator()) {
    echo '<a href="filesearch.php" class="btn btn-outline-primary sitelink">Search</a>';
} 
if (!empty($action)) {
    echo '<a href="files.php" class="btn btn-outline-primary sitelink">' . $lang_home['mngpage'] . '</a>';
} 
if ($action != "faq") {
    // echo '<br /><img src="../images/img/faq.gif" alt=""> <a href="files.php?action=faq">' . $lang_home['faq'] . '</a>';
}
echo '<p><a href="index.php" class="btn btn-outline-primary sitelink">' . $lang_home['admpanel'] . '</a><br />';
echo '<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';

include_once"../themes/$config_themes/foot.php";

?>