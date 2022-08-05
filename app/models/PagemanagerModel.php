<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class PagemanagerModel extends BaseModel {
    public function index()
    {
        // Users data
        $index_data['user'] = $this->user_data;
        $index_data['tname'] = 'Page Manager';
        $index_data['content'] = '';

        // Checking access permitions
        if (!$this->user->administrator() && !$this->user->check_permissions('pageedit', 'show')) $this->redirection('../?auth_error');

        $page_editor = $this->model('Pagemanager');

        $file = $this->check($this->postAndGet('file'));
        $text_files = $this->postAndGet('text_files', true); // Keep data as received so html codes will be not filtered
        $id = $this->check($this->postAndGet('id'));
        if (!empty($this->postAndGet('file'))) $page_id = $page_editor->getPageId("file='{$file}'"); // Get page id we work with
        $config_editfiles = 20; // Files per page

        if ($this->postAndGet('action') == 'editfile') {
            if (!empty($file) && !empty($text_files)) {
                $page_info = $page_editor->selectPage($page_id, 'crtdby, published');

                if (!$this->user->check_permissions('pageedit', 'show') && !$this->user->administrator()) { $this->redirection(HOMEDIR . "?isset=ap_noaccess"); } 

                if ($page_info['crtdby'] != $this->user->user_id() && !$this->user->check_permissions('pageedit', 'edit') && (!$this->user->check_permissions('pageedit', 'editunpub') || $page_info['published'] != 1) && !$this->user->administrator()) $this->redirection(HOMEDIR . '?isset=ap_noaccess');

                // bug when magic quotes are on and '\' sign
                // if magic quotes are on we don't want ' to become \'
                if (function_exists('get_magic_quotes_gpc')) {
                    // strip all slashes
                    $text_files = stripslashes($text_files);
                }

                // update db data
                $page_editor->update($page_id, $text_files);
            }

            $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=edit&file=$file&isset=mp_editfiles");
        }

        if ($this->postAndGet('action') == 'savetags') {
            if (!$this->user->check_permissions('pageedit', 'insert') && !$this->user->administrator()) { $this->redirection(HOMEDIR . "?isset=ap_noaccess"); }

            $tags = !empty($this->postAndGet('tags')) ? $this->postAndGet('tags') : '';

            if (isset($tags)) { $page_editor->updateTags($id, $tags); }

            $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=tags&id={$id}&isset=mp_editfiles");
        }

        // update head tags on all pages
        if ($this->postAndGet('action') == 'editmainhead') {
            if (!$this->user->administrator(101)) $this->redirection("../?isset=ap_noaccess");

            // update header data
            $this->writeDataFile('headmeta.dat', $text_files);

            $this->redirection(HOMEDIR . 'adminpanel/pagemanager/?action=mainmeta&isset=mp_editfiles');
        }

        // update head tags on specific page
        if ($this->postAndGet('action') == 'editheadtag') {
            // get default image link
            $image = !empty($this->postAndGet('image')) ? $this->postAndGet('image') : '';

            // update header tags
            if (!empty($file)) {
                // who created page
                $page_info = $page_editor->selectPage($page_id, 'crtdby');

                // check can user see page
                if (!$this->user->check_permissions('pageedit', 'show') && !$this->user->administrator()) { $this->redirection(HOMEDIR . "?isset=ap_noaccess"); }

                // check can user edit page
                if (!$this->user->check_permissions('pageedit', 'edit') && !$this->user->administrator() && $page_info['crtdby'] != $this->user->user_id) { $this->redirection(HOMEDIR . "?isset=ap_noaccess"); } 

                /**
                 * Update data in database
                 */
                $data = array(
                    'headt' => $text_files,
                    'default_img' => $image
                );
                $page_editor->headData($page_id, $data);

                // redirect
                $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=headtag&file=$file&isset=mp_editfiles");
            } 
            // fields must not be empty
            $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=headtag&file=$file&isset=mp_noeditfiles");
        }

        // Rename page
        if ($this->postAndGet('action') == 'save_renamed') {
            $pg = $this->postAndGet('pg'); // new file name

            if (!empty($pg) && !empty($file)) {
                $page_info = $page_editor->selectPage($page_id, 'crtdby');

                if (!$this->user->check_permissions('pageedit', 'show') && !$this->user->administrator()) {
                    header("Location: " . HOMEDIR . "?isset=ap_noaccess");
                    exit;
                } 
                if (!$this->user->check_permissions('pageedit', 'edit') && !$this->user->administrator() && $page_info['crtdby'] != $this->user->user_id) {
                    header("Location: " . HOMEDIR . "?isset=ap_noaccess");
                    exit;
                }

                // rename page
                $page_editor->rename($pg, $page_id);

                header("Location: " . HOMEDIR . "/adminpanel/pagemanager/?action=edit&file=$pg&isset=mp_editfiles");
                exit;
            }

            header("Location: " . HOMEDIR . "/adminpanel/pagemanager/?action=edit&file=$pg&isset=mp_noedit");
            exit;
        }

        // Add new page
        if ($this->postAndGet('action') == 'addnew') {
            if (!$this->user->check_permissions('pageedit', 'insert') && !$this->user->administrator()) $this->redirection(HOMEDIR . "?isset=ap_noaccess");

            $newfile = !empty($this->postAndGet('newfile')) ? $this->postAndGet('newfile') : '';
            $type = !empty($this->postAndGet('type')) ? $this->postAndGet('type') : '';
            $allow_unicode = $this->postAndGet('allow_unicode') == 'on' ? true : false;

            // page title
            $page_title = $newfile;

            // page name in url
            if ($allow_unicode === false) {
                // remove unicode chars
                $newfile = $this->trans($newfile);
            } else {
                $newfile = $this->trans_unicode($newfile);
            }

            // page language
            if (!empty($this->postAndGet('lang'))) {
                $pagelang = $this->postAndGet('lang');

                $pagelang_file = '!.' . $pagelang . '!';
            } else {
                $pagelang = '';
                $pagelang_file = '';
            }

            if (!empty($newfile)) {
                // Check if page exists
                $includePageLang = !empty($pagelang) ? " AND lang='{$pagelang}'" : '';

                if ($page_editor->pageExists("pname='{$newfile}'" . $includePageLang, 'where')) $this->redirection(HOMEDIR . 'adminpanel/pagemanager/?action=new&isset=mp_pageexists');

                // Full page address used for meta and open graph tags
                if ($newfile == 'index') {
                    // Index page
                    $page_url = $this->websiteHomeAddress() . '/' . $pagelang;
                } elseif ($type == 'post') {
                    // Blog post
                    $page_url = $this->websiteHomeAddress() . '/blog/' . $newfile;
                } elseif ($newfile != 'index') {
                    // Page
                    $page_url = $this->websiteHomeAddress() . '/page/' . $newfile;
                }

                // page filename
                $newfiles = $newfile . $pagelang_file . '.php';

                // insert db data
                $values = array(
                'pname' => $newfile,
                'lang' => $pagelang,
                'created' => time(),
                'lastupd' => time(),
                'lstupdby' => $this->user->user_id(),
                'file' => $newfiles,
                'crtdby' => $this->user->user_id(),
                'published' => '1',
                'pubdate' => '0',
                'tname' => $page_title,
                'headt' => '<meta property="og:title" content="' . $page_title . '" />'. "\r\n" . '<meta property="og:url" content="' . $page_url . '" />' . "\r\n" . '<link rel="canonical" href="' . $page_url . '" />',
                'type' => $type,
                'default_img' => ''
                );

                // insert data
                $page_editor->insert($values);

                // file successfully created
                $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=edit&file=$newfiles&isset=mp_newfiles");
            } else {
                $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=new&isset=mp_noyesfiles");
            }
        }

        if ($this->postAndGet('action') == 'del') {
            if (!$this->user->check_permissions('pageedit', 'del') && !$this->user->administrator()) $this->redirection(HOMEDIR . "?isset=ap_noaccess");

            // delete page
            $page_editor->delete($page_id);
        
            $this->redirection(HOMEDIR . "adminpanel/pagemanager/?isset=mp_delfiles");
        }

        // publish page; page will be avaliable for visitors
        if ($this->postAndGet('action') == 'publish') {
            if (!empty($page_id)) {
                if (!$this->user->check_permissions('pageedit', 'edit') && !$this->user->administrator()) {
                    header("Location: " . HOMEDIR . "adminpanel/?isset=ap_noaccess");
                    exit;
                }

                // update db data
                $page_editor->visibility($page_id, 2);
            } 

            $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=show&file=" . $file . "&isset=mp_editfiles");
        }

        // unpublish page
        if ($this->postAndGet('action') == "unpublish") {
            if (!empty($page_id)) {

                if (!$this->user->check_permissions('pageedit', 'edit') && !$this->user->administrator()) {
                    header("Location: " . HOMEDIR . "adminpanel/?isset=ap_noaccess");
                    exit;
                }

                // update db data
                $page_editor->visibility($page_id, 1);
            } 

            $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=show&file=" . $file . "&isset=mp_editfiles");
        }

        // update page language
        if ($this->postAndGet('action') == 'pagelang') {
            if (!$this->user->administrator()) { $this->redirection("../?isset=ap_noaccess"); }

            $pageId = $this->check($this->postAndGet('id'));
            $lang = $this->postAndGet('lang');

            // update database data
            $page_editor->language($pageId, $lang);

            $pageData = $page_editor->selectPage($pageId);
            $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=show&file=" . $pageData['pname'] . "!." . $lang . "!.php&isset=mp_editfiles");

        }

        if ($page_editor->editMode() == 'visual') {
            // text editor
            $loadTextEditor = $page_editor->loadPageEditor();

            // remove fullpage plugin if exists, we dont need html header and footer tags
            $loadTextEditor = str_replace('fullpage ' , '', $loadTextEditor);

            // choose field selector
            $textEditor = str_replace('#selector', '#text_files', $loadTextEditor);

            // Add data to page header
            $index_data['headt'] = $textEditor;
        }

        // check if user can edit only pages that are made by themself or have permitions to edit all pages
        if (!$this->user->check_permissions('pageedit', 'edit') && !$this->user->administrator()) {
            $edit_only_own_pages = 'yes';
        } else {
            $edit_only_own_pages = '';
        }

        if (empty($this->postAndGet('action'))) {
            $index_data['content'] .= '<h1>' . $this->localization->string('filelist') . '</h1>';

            $total_pages = $page_editor->totalPages();

            if ($edit_only_own_pages == 'yes') {
                $total_pages = $page_editor->totalPages($this->user->user_id);
            }

            // start navigation
            $navi = new Navigation($config_editfiles, $total_pages, $this->postAndGet('page'));

            if ($edit_only_own_pages == 'yes') {
                $sql = "SELECT * FROM pages WHERE crtdby='{$this->user->user_id}' ORDER BY pname LIMIT {$navi->start()['start']}, $config_editfiles";
            } else {
                $sql = "SELECT * FROM pages ORDER BY pname LIMIT {$navi->start()['start']}, $config_editfiles";
            }

            foreach ($this->db->query($sql) as $page_info) {
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
                    $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=show&amp;file=' . $page_info['file'], $filename, '<b>', '</b>');
                    // Check for permissions to edit pages
                    if ($this->user->check_permissions('pageedit', 'edit') || $this->user->administrator() || $page_info['crtdby'] == $this->user->user_id() || ($this->user->check_permissions('pageedit', 'editunpub') && $page_info['published'] == 1)) {
                        $index_data['content'] .= '<a href="' . HOMEDIR . 'adminpanel/pagemanager/?action=edit&amp;file=' . $page_info['file'] . '" class="btn btn-outline-primary btn-sm">[Edit]</a>';
                    }

                    // Check for permissions to delete pages
                    if ($this->user->check_permissions('pageedit', 'del') || $this->user->administrator()) {
                        $index_data['content'] .= ' | <a href="' . HOMEDIR . 'adminpanel/pagemanager/?action=poddel&amp;file=' . $page_info['file'] . '" class="btn btn-outline-primary btn-sm">[Del]</a>';
                    }

                    // Check for permissions to publish and unpublish pages
                    if ($page_info['published'] == 1 && ($this->user->check_permissions('pageedit', 'edit') || $this->user->administrator())) {
                        $index_data['content'] .= ' | <a href="' . HOMEDIR . 'adminpanel/pagemanager/?action=publish&amp;file=' . $page_info['file'] . '" class="btn btn-outline-primary btn-sm">[Publish]</a>';
                    } 

                    if ($page_info['published'] != 1 && ($this->user->check_permissions('pageedit', 'edit') || $this->user->administrator())) {
                        $index_data['content'] .= ' | <a href="' . HOMEDIR . 'adminpanel/pagemanager/?action=unpublish&amp;file=' . $page_info['file'] . '" class="btn btn-outline-primary btn-sm">[Unpublish]</a>';
                    }

                    // information about page
                    $index_data['content'] .= ' ' . $this->localization->string('created') . ': ' . $this->correctDate($page_info['created'], 'd.m.y.') . ' ' . $this->localization->string('by') . ' ' . $this->user->getnickfromid($page_info['crtdby']) . ' | ' . $this->localization->string('lastupdate') . ' ' . $this->correctDate($page_info['lastupd'], 'd.m.y.') . ' ' . $this->localization->string('by') . ' ' . $this->user->getnickfromid($page_info['lstupdby']);
                    $index_data['content'] .= '<hr />';

                } else {
                    $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=show&amp;file=' . $page_info['file'], $filename, '<b>', '</b>');
                    $index_data['content'] .= '<a href="' . HOMEDIR . 'adminpanel/pagemanager/?action=edit&amp;file=' . $page_info['file'] . '" class="btn btn-outline-primary btn-sm">[Edit]</a>';
                    $index_data['content'] .= ' ' . $this->localization->string('created') . ': ' . $this->correctDate($page_info['created'], 'd.m.y.') . ' ' . $this->localization->string('by') . ' ' . $this->user->getnickfromid($page_info['crtdby']) . ' | ' . $this->localization->string('lastupdate') . ' ' . $this->correctDate($page_info['lastupd'], 'd.m.y.') . ' ' . $this->localization->string('by') . ' ' . $this->user->getnickfromid($page_info['lstupdby']);
                    $index_data['content'] .= '<hr />';
                } 
            
            unset($page_info);
            } 

            // navigation
            $navigation = new Navigation($config_editfiles, $total_pages, $this->postAndGet('page'), HOMEDIR . 'adminpanel/pagemanager/?');
            $index_data['content'] .= $navigation->get_navigation();

            $index_data['content'] .= '<p>' . $this->localization->string('totpages') . ': <b>' . (int)$total_pages . '</b></p>';
        }

        if ($this->postAndGet('action') == 'show') {
            if (!empty($page_id)) {
                $base_file = $file;

                $pageData = new Pagemanager();
                $page_info = $pageData->selectPage($page_id);

                if (!$this->user->check_permissions('pageedit', 'show') && !$this->user->administrator()) {
                    $this->redirection(HOMEDIR . "?isset=ap_noaccess");
                } 

                if ($page_info['crtdby'] != $this->user->user_id() && !$this->user->check_permissions('pageedit', 'edit') && (!$this->user->check_permissions('pageedit', 'editunpub') || $page_info['published'] != 1) && !$this->user->administrator()) {
                    $this->redirection(HOMEDIR . "?isset=ap_noaccess");
                } 

                $showname = $page_info['pname'];

                $index_data['content'] .= '<p>' . $this->localization->string('shwingpage') . ' <b>' . $showname . '</b></p>';
                $index_data['content'] .= '<p>' . $this->localization->string('created') . ': ' . $this->correctDate($page_info['created'], 'd.m.y.') . ' ' . $this->localization->string('by') . ' ' . $this->user->getnickfromid($page_info['crtdby']);
                $index_data['content'] .= ' | ' . $this->localization->string('lastupdate') . ' ' . $this->correctDate($page_info['lastupd'], 'd.m.y.') . ' ' . $this->localization->string('by') . ' ' . $this->user->getnickfromid($page_info['lstupdby']);
                
                // post type
                $post_type = !empty($page_info['type']) ? $page_info['type'] : 'page';
                $index_data['content'] .= ' | Page type: ' . $post_type;

                if ($page_info['published'] == 1 && ($this->user->check_permissions('pageedit', 'edit') || $this->user->administrator())) {
                    $index_data['content'] .= ' | <a href="' . HOMEDIR . 'adminpanel/pagemanager/?action=publish&amp;file=' . $file . '">[Publish]</a>';
                } 
                if ($page_info['published'] != 1 && ($this->user->check_permissions('pageedit', 'edit') || $this->user->administrator())) {
                    $index_data['content'] .= ' | <a href="' . HOMEDIR . 'adminpanel/pagemanager/?action=unpublish&amp;file=' . $file . '">[Unpublish]</a>';
                }
                $index_data['content'] .= '</p>';

                $index_data['content'] .= '<p>';
                $index_data['content'] .= $this->localization->string('pgaddress') . ': ';

                // Homepage (index page)
                if (preg_match('/^index(?:!\.[a-zA-Z]{2}!)?\.php$/', $file)) {
                    if (!empty($page_info['lang'])) { $url_lang = strtolower($page_info['lang']) . '/'; } else { $url_lang = ''; }
                    $index_data['content'] .= '<a href="' . $this->websiteHomeAddress() . '/' . $url_lang . '" target="_blank">' . $this->websiteHomeAddress() . '/' . $url_lang . '</a>';
                }
                // Blog post
                elseif ($post_type == 'post') {
                    $index_data['content'] .= '<br /><a href="' . $this->websiteHomeAddress() . '/blog/' . $showname . '" target="_blank">' . $this->websiteHomeAddress() . '/blog/' . $showname . '</a><br />';
                }
                // Page
                elseif ($post_type == 'page' || empty($post_type)) {
                    $index_data['content'] .= '<br /><a href="' . $this->websiteHomeAddress() . '/page/' . $showname . '" target="_blank">' . $this->websiteHomeAddress() . '/page/' . $showname . '</a><br />';
                }

                $index_data['content'] .= '</p>';

                $index_data['content'] .= '<br />' . $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=edit&file=' . $base_file, $this->localization->string('edit')) . '<br />';
                if ($this->user->check_permissions('pageedit', 'del') || $this->user->administrator()) {
                    $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=poddel&amp;file=' . $base_file, $this->localization->string('delete')) . '<br />';
                }
            }

            if (empty($edit_only_own_pages)) {
                $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagetitle?act=edit&pgfile=' . $base_file, '{@localization[pagetitle]}} ' . $showname) . '<br />';
            }
        }

        if ($this->postAndGet('action') == 'edit') {
            // Coder mode for advanced users / coders
            $edmode_name = $page_editor->editMode() == 'visual' ? 'Visual' : 'Coder';

            // Check if page exists
            if ($page_editor->pageExists($file)) {
                $page_info = $page_editor->selectPage($page_id);

                if (!$this->user->check_permissions('pageedit', 'show') && !$this->user->administrator()) $this->redirection(HOMEDIR . "?isset=ap_noaccess");

                if ($page_info['crtdby'] != $this->user->user_id() && !$this->user->check_permissions('pageedit', 'edit') && (!$this->user->check_permissions('pageedit', 'editunpub') || $page_info['published'] != 1) && !$this->user->administrator()) $this->redirection(HOMEDIR . '?isset=ap_noaccess');

                // Page name
                $show_up_file = str_replace('.php', '', $file);
                if (stristr($show_up_file, '!.')) $show_up_file = preg_replace("/(.*)!.(.*)!/", "$1", $show_up_file);

                $index_data['content'] .= '<p>Edit mode: ' . $edmode_name . '</p>';

                $form = $this->model('ParsePage');
                $form->load('forms/form');
                $form->set('form_method', 'post');
                $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/?action=edit&file=' . $file);

                $select = $this->model('ParsePage');
                $select->load('forms/select');
                $select->set('label_for', 'edmode');
                $select->set('select_id', 'edmode');
                $select->set('select_name', 'edmode');

                $options = '<option value="' . $page_editor->editMode() . '">' . $edmode_name . '</option>';

                if ($page_editor->editMode() == 'coder') {
                    $options .= '<option value="visual">Visual</option>';
                } else {
                    $options .= '<option value="coder">Coder</option>';
                }

                $select->set('options', $options);

                $form->set('fields', $select->output());
                $index_data['content'] .= $form->output();

                $index_data['content'] .= '<hr />';

                $index_data['content'] .= '<p>Updating page ' . $show_up_file . ' ' . $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=renamepg&amp;pg=' . $file, 'rename') . '</p>'; // update lang

                $form = $this->model('ParsePage');
                $form->load('forms/form');
                $form->set('form_method', 'post');
                $form->set('form_name', 'form');
                $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/?action=editfile&file=' . $file);

                $textarea = $this->model('ParsePage');
                $textarea->load('forms/textarea');
                $textarea->set('label_for', 'text_files');
                $textarea->set('textarea_id', 'text_files');
                $textarea->set('textarea_name', 'text_files');
                $textarea->set('textarea_rows', 25);
                $textarea->set('textarea_value', $page_editor->processPageContent($page_info['content']));

                $form->set('fields', $textarea->output());

                $index_data['content'] .= $form->output();

                $index_data['content'] .= '<hr>';
                $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=show&amp;file=' . $file, $show_up_file, '<p>', '</p>');

                $index_data['content'] .= '<p>';
                $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagetitle?act=edit&pgfile=' . $file, '{@localization[pagetitle]}}');
                $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=updpagelang&amp;id=' . $page_id, 'Update page language');
                $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=headtag&amp;file=' . $file, 'Head (meta) tags on this page'); // update lang
                $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=tags&amp;id=' . $page_id, 'Tags'); // update lang
                $index_data['content'] .= '</p>';
            } else {
                $index_data['content'] .= $this->showDanger($this->localization->string('file') . ' ' . $file . ' ' . $this->localization->string('noexist'));
            }
        }

        // Edit meta tags
        if ($this->postAndGet('action') == 'headtag') {
            if (!$this->user->check_permissions('pageedit', 'show') && !$this->user->administrator()) $this->redirection(HOMEDIR . '?isset=ap_noaccess');

            $page_info = $page_editor->selectPage($page_id);

            if (!$this->user->check_permissions('pageedit', 'edit') && !$this->user->administrator() && $page_info['crtdby'] != $this->user->user_id) {
                header("Location: " . HOMEDIR . "?isset=ap_noaccess");
                exit;
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

            $index_data['content'] .= '<legend>Updating file ' . $show_up_file . '</legend>'; // update lang 

            /**
             * Load form
             */
            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/?action=editheadtag&amp;file=' . $file);
            $form->set('form_method', 'POST');
            $form->set('form_name', 'form');

            /**
             * Textarea
             */
            $textarea = $this->model('ParsePage');
            $textarea->load('forms/textarea');
            $textarea->set('label_for', 'text');
            $textarea->set('textarea_name', 'text_files');
            $textarea->set('textarea_id', 'text');
            $textarea->set('textarea_value', $page_info['headt']);

            /**
             * Input field
             */
            $image_input = $this->model('ParsePage');
            $image_input->load('forms/input');
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
            $index_data['content'] .= $form->output();

            $index_data['content'] .= '<hr />';
        } 

        if ($this->postAndGet('action') == 'mainmeta') {
            if (!$this->user->administrator(101)) { $this->redirection("../?isset=ap_noaccess"); }

            $index_data['content'] .= '<img src="/themes/images/img/panel.gif" alt="" /> Edit tags in &lt;head&gt;&lt;/head&gt; on all pages<br /><br />'; // update lang

            $headtags = trim(file_get_contents(APPDIR . 'used/headmeta.dat'));

            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/?action=editmainhead');
            $form->set('form_name', 'form');
            $form->set('form_method', 'post');

            $textarea = $this->model('ParsePage');
            $textarea->load('forms/textarea');
            $textarea->set('label_for', '');
            $textarea->set('textarea_name', 'text_files');
            $textarea->set('textarea_value', $headtags);
            $textarea->set('textarea_id', '');

            $form->set('fields', $textarea->output());
            $index_data['content'] .= $form->output();

            $index_data['content'] .= '<hr>';

            $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/', $this->localization->string('back'), '<p>', '</p>');
        } 

        if ($this->postAndGet('action') == 'renamepg') {
            if (!$this->user->administrator()) {
                header("Location: ../?isset=ap_noaccess");
                exit;
            }

            $pg = $this->check($this->postAndGet('pg'));

            $index_data['content'] .= '<h1>Rename page</h1>'; // update lang

            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/?action=save_renamed');
            $form->set('form_name', 'form');
            $form->set('form_method', 'POST');

            $input_pg = $this->model('ParsePage');
            $input_pg->load('forms/input');
            $input_pg->set('label_for', '');
            $input_pg->set('input_type', 'text');
            $input_pg->set('input_name', 'pg');
            $input_pg->set('input_id', '');
            $input_pg->set('input_value', $pg);

            $input_file = $this->model('ParsePage');
            $input_file->load('forms/input');
            $input_file->set('label_for', '');
            $input_file->set('input_type', 'hidden');
            $input_file->set('input_name', 'file');
            $input_file->set('input_id', '');
            $input_file->set('input_value', $pg);

            $form->set('fields', $form->merge(array($input_pg, $input_file)));
            $index_data['content'] .= $form->output();

            $index_data['content'] .= '<hr />';

            $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=edit&amp;file=' . $pg, $this->localization->string('back'), '<p>', '</p>');
        } 

        if ($this->postAndGet('action') == 'new') {
            if (!$this->user->check_permissions('pageedit', 'insert') && !$this->user->administrator()) $this->redirection(HOMEDIR . "?isset=ap_noaccess");

            $index_data['headt'] = '
            <style>
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

            $index_data['content'] .= '<h1>' . $this->localization->string('newfile') . '</h1>';

            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/?action=addnew');

            /**
             * Page name input
             */
            $input_new_file = $this->model('ParsePage');
            $input_new_file->load('forms/input');
            $input_new_file->set('label_for', 'newfile');
            $input_new_file->set('label_value', $this->localization->string('pagename') . ':');
            $input_new_file->set('input_type', 'text');
            $input_new_file->set('input_name', 'newfile');
            $input_new_file->set('input_id', 'newfile');
            $input_new_file->set('input_maxlength', 120);

            /**
             * Language select
             */
            $languages = "SELECT * FROM languages ORDER BY lngeng";

            $options = '<option value="">Don\'t set</option>';
            foreach ($this->db->query($languages) as $lang) {
                $options .= "<option value=\"" . strtolower($lang['iso-2']) . "\">" . $lang['lngeng'] . "</option>";
            }

            $select_language = $this->model('ParsePage');
            $select_language->load('forms/select');
            $select_language->set('label_for', 'language');
            $select_language->set('label_value', $this->localization->string('language') . ' (optional):');
            $select_language->set('select_id', 'language');
            $select_language->set('select_name', 'lang');
            $select_language->set('options', $options);

            /**
             * Page type select
             */
            $select_type = $this->model('ParsePage');
            $select_type->load('forms/select');
            $select_type->set('label_for', 'type');
            $select_type->set('label_value', 'Post type:');
            $select_type->set('select_id', 'type');
            $select_type->set('select_name', 'type');
            $select_type->set('options', '<option value="page">Page</option><option value="post">Post</option>');

            /**
             * Allow unicode url checkbox
             */
            $checkbox_allow_unicode = $this->model('ParsePage');
            $checkbox_allow_unicode->load('forms/checkbox');
            $checkbox_allow_unicode->set('label_for', 'allow-unicode');
            $checkbox_allow_unicode->set('label_value', $this->localization->string('allowUnicodeUrl'));
            $checkbox_allow_unicode->set('checkbox_id', 'allow_unicode');
            $checkbox_allow_unicode->set('checkbox_name', 'allow_unicode');
            $checkbox_allow_unicode->set('checkbox_value', 'on');

            /**
             * All form fields
             */
            $fields = array($input_new_file, $select_language, $select_type, $checkbox_allow_unicode);

            /**
             * Merge fields
             */
            $form->set('fields', $form->merge($fields));

            /**
             * Show form
             */
            $form->set('website_language[save]', $this->localization->string('newpage'));
            $index_data['content'] .= $form->output();
        } 

        // confirm that you want to delete a page
        if ($this->postAndGet('action') == "poddel") {
            if (!$this->user->check_permissions('pageedit', 'del') && !$this->user->administrator()) {
                header("Location: " . HOMEDIR . "?isset=ap_noaccess");
                exit;
            }

            if (!empty($file)) {
                if ($file != "index.php") {
                    $index_data['content'] .= $this->localization->string('confdelfile') . ' <b>' . $file . '</b><br />';
                    $index_data['content'] .= '<b>' . $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=del&amp;file=' . $file, $this->localization->string('delete')) . '</b><br />';
                } else {
                    $index_data['content'] .= $this->localization->string('indexnodel') . '!<br />';
                } 
            } else {
                $index_data['content'] .= $this->localization->string('nofiletodel') . '<br />';
            } 
            $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/', $this->localization->string('back')) . '<br />';
        } 

        if ($this->postAndGet('action') == 'updpagelang') {
            if (!$this->user->administrator()) $this->redirection(HOMEDIR . '?isset=ap_noaccess');

            $id = $this->check($this->postAndGet('id'));

            // get page data
            $pageData = $page_editor->selectPage($id);

            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/?action=pagelang&amp;id=' . $pageData['id']);

            $select_language = $this->model('ParsePage');
            $select_language->load('forms/select');
            $select_language->set('label_for', 'lang');
            $select_language->set('label_value', $this->localization->string('language'));
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

            foreach ($this->db->query($languages) as $lang) {
                $options .= '<option value="' . strtolower($lang['iso-2']) . '">' . $lang['lngeng'] . '</option>';
            }

            $select_language->set('options', $options);

            $form->set('fields', $select_language->output());

            $index_data['content'] .= $form->output();

            $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/', $this->localization->string('back'), '<p>', '</p>');
        }

        if ($this->postAndGet('action') == 'tags') {
            if (!$this->user->check_permissions('pageedit', 'edit') && !$this->user->administrator() && $page_info['crtdby'] != $this->user->user_id) {
                redirection("./?isset=ap_noaccess");
            }

            /**
             * Get page data
             */
            $tag_field = $this->model('ParsePage');
            $tag_field->load('forms/input');
            $tag_field->set('label_value', 'Tags');
            $tag_field->set('label_for', 'tags');
            $tag_field->set('input_name', 'tags');
            $tag_field->set('input_id', 'tags');
            $tag_field->set('input_type', 'text');
            $tag_field->set('input_value', $page_editor->pageTags($id));

            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/?action=savetags&amp;id=' . $id);
            $form->set('form_method', 'post');
            $form->set('fields', $tag_field->output());

            $index_data['content'] .= $form->output();
        }

        $index_data['content'] .= '<p>';
        if ($this->postAndGet('action') != 'new' && ($this->user->check_permissions('pageedit', 'insert') || $this->user->administrator())) {
            $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=new', $this->localization->string('newpage')) . '<br />';
        }
        $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/blogcategory', 'Blog category management') . '<br />';
        if ($this->user->administrator(101)) {
            $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=mainmeta', 'Head tags (meta tags) on all pages') . '<br />';
        } // update lang
        if ($this->user->administrator()) {
            $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagesearch', $this->localization->string('search')) . '<br />';
        }
        if (empty($edit_only_own_pages)) {
            $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagetitle', $this->localization->string('pagetitle')) . '<br />';
        }
        if (!empty($this->postAndGet('action'))) {
            $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager', $this->localization->string('mngpage')) . '<br />';
        }
        $index_data['content'] .= '</p>';

        $index_data['content'] .= '<p>';
        $index_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel', $this->localization->string('admpanel')) . '<br />';
        $index_data['content'] .= $this->homelink();
        $index_data['content'] .= '</p>';

        return $index_data;
    }
}