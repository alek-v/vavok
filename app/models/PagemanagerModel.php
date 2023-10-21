<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Navigation;
use App\Classes\PageManager;
use App\Traits\Files;
use App\Traits\Notifications;

class PagemanagerModel extends BaseModel {
    use Files, Notifications;

    /**
     * Main page
     *
     * @return array
     */
    public function index(): array
    {
        // Check access permissions
        if (!$this->user->administrator()) {
            $this->redirection('../?auth_error');
        }

        $this->page_data['page_title'] = 'Page Manager';

        $page_editor = new PageManager($this->container);

        $file = $this->check($this->postAndGet('file'));

        // Keep data as received so html codes will be not filtered
        $text_files = $this->postAndGet('text_files', true);

        $id = $this->check($this->postAndGet('id'));

        // Files per page
        $config_editfiles = 20;

        // Save page changes
        if ($this->postAndGet('action') == 'edit_file') {
            if (!empty($id) && !empty($text_files)) {
                $page_info = $page_editor->selectPage($id, 'created_by, published_status');

                // Update DB data
                $page_editor->update($id, $text_files);
            }

            $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=edit&id=$id&isset=mp_editfiles");
        }

        if ($this->postAndGet('action') == 'savetags') {
            $tags = !empty($this->postAndGet('tags')) ? $this->postAndGet('tags') : '';

            if (isset($tags)) {
                $page_editor->updateTags($id, $tags);
            }

            $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=tags&id={$id}&isset=mp_editfiles");
        }

        // Update head tags on all pages
        if ($this->postAndGet('action') == 'editmainhead') {
            if (!$this->user->administrator(101)) {
                $this->redirection("../?isset=ap_noaccess");
            }

            // update header data
            $this->writeDataFile('header_meta_tags.dat', $text_files);

            $this->redirection(HOMEDIR . 'adminpanel/pagemanager/?action=mainmeta&isset=mp_editfiles');
        }

        // Update head tags on the page
        if ($this->postAndGet('action') == 'save_head_tags') {
            // Get default image link
            $image = !empty($this->postAndGet('image')) ? $this->postAndGet('image') : '';

            // Update header tags
            if (!empty($id)) {
                // Who created the page
                $page_info = $page_editor->selectPage($id, 'created_by');

                // Update data in database
                $data = array(
                    'head_tags' => $text_files,
                    'default_img' => $image
                );
                $page_editor->headData($id, $data);

                // Redirect
                $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=page_head_tags&id=$id&isset=mp_editfiles");
            }

            // Fields must be filled
            $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=page_head_tags&id=$id&isset=mp_noeditfiles");
        }

        // Rename the page
        if ($this->postAndGet('action') == 'save_renamed') {
            $pg = $this->postAndGet('pg');

            if (!empty($id) && !empty($pg)) {
                // Rename the page
                $page_editor->rename($pg, $id);

                $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=edit&id=$id&isset=mp_editfiles");
            }

            $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=edit&id=$id&isset=mp_noedit");
        }

        // Add a new page
        if ($this->postAndGet('action') == 'add_new_page') {
            $new_file = !empty($this->postAndGet('newfile')) ? $this->postAndGet('newfile') : '';
            $type = !empty($this->postAndGet('type')) ? $this->postAndGet('type') : '';
            $allow_unicode = $this->postAndGet('allow_unicode') == 'on' ? true : false;

            // page title
            $page_title = $new_file;

            // page name in url
            if ($allow_unicode === false) {
                // remove unicode chars
                $new_file = $this->trans($new_file);
            } else {
                $new_file = $this->translateUnicode($new_file);
            }

            // page language
            if (!empty($this->postAndGet('localization'))) {
                $page_localization = $this->postAndGet('localization');

                $page_localization_file = '_' . $page_localization;
            } else {
                $page_localization = '';
                $page_localization_file = '';
            }

            if (!empty($new_file)) {
                // Check if page exists
                $include_where = !empty($page_localization) ? ' AND localization = :localization' : '';
                $include_bind = !empty($page_localization) ? [':localization' => $page_localization] : '';

                $page_bind = [':slug' => $new_file];
                if (!empty($include_bind)) $page_bind = array_merge($page_bind, $include_bind);
                if ($page_editor->pageExists('slug = :slug' . $include_where, 'where', $page_bind)) {
                    $this->redirection(HOMEDIR . 'adminpanel/pagemanager/?action=new&isset=mp_pageexists');
                }

                // Full page address used for meta and open graph tags
                if ($new_file == 'index') {
                    // Index page
                    $page_url = $this->websiteHomeAddress() . '/' . $page_localization;
                } elseif ($type == 'post') {
                    // Blog post
                    $page_url = $this->websiteHomeAddress() . '/blog/' . $new_file;
                } elseif ($new_file != 'index') {
                    // Page
                    $page_url = $this->websiteHomeAddress() . '/page/' . $new_file;
                }

                // page filename
                $new_files = $new_file . $page_localization_file . '.php';

                // insert db data
                $values = array(
                'slug' => $new_file,
                'localization' => $page_localization,
                'date_created' => time(),
                'date_updated' => time(),
                'updated_by' => $this->user->userIdNumber(),
                'file' => $new_files,
                'created_by' => $this->user->userIdNumber(),
                'published_status' => '1',
                'date_published' => '0',
                'page_title' => $page_title,
                'head_tags' => '<meta property="og:title" content="' . $page_title . '" />'. "\r\n" . '<meta property="og:url" content="' . $page_url . '" />' . "\r\n" . '<link rel="canonical" href="' . $page_url . '" />',
                'type' => $type,
                'default_img' => ''
                );

                // Insert data
                $last_id = $page_editor->insert($values);

                // Page successfully created
                $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=edit&id=$last_id&isset=mp_newfiles");
            } else {
                $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=new&isset=mp_noyesfiles");
            }
        }

        if ($this->postAndGet('action') == 'confirmed_page_delete') {
            // Delete the page
            $page_editor->delete($id);

            $this->redirection(HOMEDIR . "adminpanel/pagemanager/?isset=mp_delfiles");
        }

        // Publish the page. Page will be shown to visitors
        if ($this->postAndGet('action') == 'publish') {
            if (empty($id)) {
                $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=show&id=" . $id);
            }

            // Update DB data
            $page_editor->visibility($id, 2);

            $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=show&id=" . $id . "&isset=mp_editfiles");
        }

        // Unpublish page
        if ($this->postAndGet('action') == 'unpublish') {
            if (empty($id)) {
                $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=show&id=" . $id);
            }

            // Update DB data
            $page_editor->visibility($id, 1);

            $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=show&id=" . $id . "&isset=mp_editfiles");
        }

        // Update page language
        if ($this->postAndGet('action') == 'save_page_localization') {
            $localization = $this->postAndGet('localization');

            // Update database data
            $page_editor->language($id, $localization);

            $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=show&id=" . $id . "&isset=mp_editfiles");
        }

        if ($page_editor->editMode() == 'visual') {
            // Text editor
            $loadTextEditor = $page_editor->loadPageEditor();

            // Remove fullpage plugin if exists, we don't need html header and footer tags
            $loadTextEditor = str_replace('fullpage ' , '', $loadTextEditor);

            // Choose field selector
            $textEditor = str_replace('#selector', '#text_files', $loadTextEditor);

            // Add data to page header
            $this->page_data['head_tags'] = $textEditor;
        }

        if (empty($this->postAndGet('action'))) {
            $this->page_data['content'] .= '<h1>Pages</h1>'; // todo: update localization

            $total_pages = $page_editor->totalPages();

            // start navigation
            $navi = new Navigation($config_editfiles, $total_pages);

            $sql = "SELECT * FROM pages ORDER BY slug LIMIT {$navi->start()['start']}, $config_editfiles";

            foreach ($this->db->query($sql) as $page_info) {
                if (!empty($page_info['localization'])) {
                    $file_localization = '(' . $page_info['localization'] . ')';
                } else {
                    $file_localization = '';
                }

                // Page name
                $filename = $page_info['slug'];
                $filename = $filename . ' ' . strtoupper($file_localization);

                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=show&id=' . $page_info['id'], $filename, '<b>', '</b>');
                $this->page_data['content'] .= '<a href="' . HOMEDIR . 'adminpanel/pagemanager/?action=edit&id=' . $page_info['id'] . '" class="btn btn-outline-primary btn-sm">[Edit]</a>';
                $this->page_data['content'] .= ' | <a href="' . HOMEDIR . 'adminpanel/pagemanager/?action=delete_page&id=' . $page_info['id'] . '" class="btn btn-outline-primary btn-sm">[Del]</a>';

                // Check for permissions to publish and unpublish pages
                if ($page_info['published_status'] == 1) {
                    $this->page_data['content'] .= ' | <a href="' . HOMEDIR . 'adminpanel/pagemanager/?action=publish&id=' . $page_info['id'] . '" class="btn btn-outline-primary btn-sm">[Publish]</a>';
                }

                if ($page_info['published_status'] != 1) {
                    $this->page_data['content'] .= ' | <a href="' . HOMEDIR . 'adminpanel/pagemanager/?action=unpublish&id=' . $page_info['id'] . '" class="btn btn-outline-primary btn-sm">[Unpublish]</a>';
                }

                // Information about the page
                $this->page_data['content'] .= ' {@localization[created]}}: ' . $this->correctDate($page_info['date_created'], 'd.m.y.') . ' {@localization[by]}} ' . $this->user->getNickFromId($page_info['created_by']) . ' | {@localization[lastupdate]}} ' . $this->correctDate($page_info['date_updated'], 'd.m.y.') . ' {@localization[by]}} ' . $this->user->getNickFromId($page_info['updated_by']);
                $this->page_data['content'] .= '<hr />';

                unset($page_info);
            }

            // Navigation
            $navigation = new Navigation($config_editfiles, $total_pages, HOMEDIR . 'adminpanel/pagemanager/?');
            $this->page_data['content'] .= $navigation->getNavigation();

            $this->page_data['content'] .= '<p>{@localization[total_pages]}}: <b>' . (int)$total_pages . '</b></p>';
        }

        if ($this->postAndGet('action') == 'show') {
            if (empty($id)) {
                $this->redirection(HOMEDIR);
            }

            $pageData = new PageManager($this->container);
            $page_info = $pageData->selectPage($id);

            $showname = $page_info['slug'];

            $this->page_data['content'] .= '<p>' . $this->localization->string('shwingpage') . ' <b>' . $showname . '</b></p>';
            $this->page_data['content'] .= '<p>{@localization[created]}}: ' . $this->correctDate($page_info['date_created'], 'd.m.y.') . ' {@localization[by]}} ' . $this->user->getNickFromId($page_info['created_by']);
            $this->page_data['content'] .= ' | {@localization[lastupdate]}} ' . $this->correctDate($page_info['date_updated'], 'd.m.y.') . ' {@localization[by]}} ' . $this->user->getNickFromId($page_info['updated_by']);
            
            // Post type
            $post_type = !empty($page_info['type']) ? $page_info['type'] : 'page';
            $this->page_data['content'] .= ' | Page type: ' . $post_type;

            if ($page_info['published_status'] == 1) {
                $this->page_data['content'] .= ' | <a href="' . HOMEDIR . 'adminpanel/pagemanager/?action=publish&id=' . $id . '">[Publish]</a>';
            } else {
                $this->page_data['content'] .= ' | <a href="' . HOMEDIR . 'adminpanel/pagemanager/?action=unpublish&id=' . $id . '">[Unpublish]</a>';
            }

            $this->page_data['content'] .= '</p>';
            $this->page_data['content'] .= '<p>';
            $this->page_data['content'] .= $this->localization->string('pgaddress') . ': ';

            // Homepage (index page)
            if (preg_match('/^index(?:!\.[a-zA-Z]{2}!)?\.php$/', $file)) {
                if (!empty($page_info['localization'])) {
                    $url_localization = strtolower($page_info['localization']) . '/';
                } else {
                    $url_localization = '';
                }

                $this->page_data['content'] .= '<a href="' . $this->websiteHomeAddress() . '/' . $url_localization . '" target="_blank">' . $this->websiteHomeAddress() . '/' . $url_localization . '</a>';
            }
            // Blog post
            elseif ($post_type == 'post') {
                $this->page_data['content'] .= '<br /><a href="' . $this->websiteHomeAddress() . '/blog/' . $showname . '" target="_blank">' . $this->websiteHomeAddress() . '/blog/' . $showname . '</a><br />';
            }
            // Page
            elseif ($post_type == 'page' || empty($post_type)) {
                $this->page_data['content'] .= '<br /><a href="' . $this->websiteHomeAddress() . '/page/' . $showname . '" target="_blank">' . $this->websiteHomeAddress() . '/page/' . $showname . '</a><br />';
            }

            $this->page_data['content'] .= '</p>';
            $this->page_data['content'] .= '<br />' . $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=edit&id=' . $id, $this->localization->string('edit')) . '<br />';
            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=delete_page&id=' . $id, $this->localization->string('delete')) . '<br />';
            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagetitle?act=edit&id=' . $id, '{@localization[pagetitle]}}') . '<br />';
        }

        if ($this->postAndGet('action') == 'edit') {
            // Coder mode for advanced users / coders
            $edmode_name = $page_editor->editMode() == 'visual' ? 'Visual' : 'Coder';

            // Page data
            $page_info = $page_editor->selectPage((int)$this->postAndGet('id'));

            if (!empty($page_info)) {
                $page_id = $page_info['id'];

                $this->page_data['content'] .= '<p>Edit mode: ' . $edmode_name . '</p>';

                $form = $this->container['parse_page'];
                $form->load('forms/form');
                $form->set('form_method', 'post');
                $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/?action=edit&id=' . $id);

                $select = $this->container['parse_page'];
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
                $this->page_data['content'] .= $form->output();

                $this->page_data['content'] .= '<hr />';

                // Todo: Update localization
                $this->page_data['content'] .= '<p>Updating page ' . $page_info['slug'] . ' ' . $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=rename_page&id=' . $id, 'rename') . '</p>'; // update lang

                $form = $this->container['parse_page'];
                $form->load('forms/form');
                $form->set('form_method', 'post');
                $form->set('form_name', 'form');
                $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/?action=edit_file&id=' . $page_id);

                $textarea = $this->container['parse_page'];
                $textarea->load('forms/textarea');
                $textarea->set('label_for', 'text_files');
                $textarea->set('textarea_id', 'text_files');
                $textarea->set('textarea_name', 'text_files');
                $textarea->set('textarea_rows', 25);
                $textarea->set('textarea_value', $page_editor->processPageContent($page_info['content']));

                $form->set('fields', $textarea->output());

                $this->page_data['content'] .= $form->output();

                $this->page_data['content'] .= '<hr>';
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=show&id=' . $id, $page_info['slug'], '<p>', '</p>');

                $this->page_data['content'] .= '<p>';
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagetitle?act=edit&id=' . $id, '{@localization[pagetitle]}}');
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=update_page_localization&id=' . $id, 'Update page language');
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=page_head_tags&id=' . $id, 'Head (meta) tags on this page'); // TODO: Update lang
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=tags&id=' . $page_id, 'Tags'); // TODO: Update lang
                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/thumbnail/' . $page_id, 'Thumbnail');
                $this->page_data['content'] .= '</p>';
            } else {
                $this->page_data['content'] .= $this->showDanger($this->localization->string('file') . ' ' . $file . ' ' . $this->localization->string('noexist'));
            }
        }

        // Edit meta tags
        if ($this->postAndGet('action') == 'page_head_tags') {
            $page_info = $page_editor->selectPage($id);

            $this->page_data['content'] .= '<legend>Updating page ' . $page_info['page_title'] . '</legend>'; // update lang

            // Load form
            $form = $this->container['parse_page'];
            $form->load('forms/form');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/?action=save_head_tags&id=' . $id);
            $form->set('form_method', 'POST');
            $form->set('form_name', 'form');

            // Textarea
            $textarea = $this->container['parse_page'];
            $textarea->load('forms/textarea');
            $textarea->set('label_for', 'text');
            $textarea->set('textarea_name', 'text_files');
            $textarea->set('textarea_id', 'text');
            $textarea->set('textarea_value', $page_info['head_tags']);

            // Input field
            $image_input = $this->container['parse_page'];
            $image_input->load('forms/input');
            $image_input->set('label_for', 'image');
            $image_input->set('label_value', 'Default image:');
            $image_input->set('input_type', 'text');
            $image_input->set('input_name', 'image');
            $image_input->set('input_id', 'image');
            $image_input->set('input_value', $page_info['default_img']);

            // Insert fields
            $form->set('fields', $form->merge(array($textarea, $image_input)));

            // Show form
            $this->page_data['content'] .= $form->output();

            $this->page_data['content'] .= '<hr />';
        }

        if ($this->postAndGet('action') == 'mainmeta') {
            $this->page_data['content'] .= '<img src="/themes/images/img/panel.gif" alt="" /> Edit tags in &lt;head&gt;&lt;/head&gt; on all pages<br /><br />'; // update lang

            $headtags = trim(file_get_contents(STORAGEDIR . 'header_meta_tags.dat'));

            $form = $this->container['parse_page'];
            $form->load('forms/form');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/?action=editmainhead');
            $form->set('form_name', 'form');
            $form->set('form_method', 'post');

            $textarea = $this->container['parse_page'];
            $textarea->load('forms/textarea');
            $textarea->set('label_for', '');
            $textarea->set('textarea_name', 'text_files');
            $textarea->set('textarea_value', $headtags);
            $textarea->set('textarea_id', '');

            $form->set('fields', $textarea->output());
            $this->page_data['content'] .= $form->output();

            $this->page_data['content'] .= '<hr>';

            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/', $this->localization->string('back'), '<p>', '</p>');
        } 

        if ($this->postAndGet('action') == 'rename_page') {
            $page_info = $page_editor->selectPage($id);

            $this->page_data['content'] .= '<h1>Rename page</h1>'; // update lang

            $form = $this->container['parse_page'];
            $form->load('forms/form');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/?action=save_renamed');
            $form->set('form_name', 'form');
            $form->set('form_method', 'POST');

            $input_pg = $this->container['parse_page'];
            $input_pg->load('forms/input');
            $input_pg->set('label_for', '');
            $input_pg->set('input_type', 'text');
            $input_pg->set('input_name', 'pg');
            $input_pg->set('input_id', '');
            $input_pg->set('input_value', $page_info['slug']);

            $input_file = $this->container['parse_page'];
            $input_file->load('forms/input');
            $input_file->set('label_for', '');
            $input_file->set('input_type', 'hidden');
            $input_file->set('input_name', 'id');
            $input_file->set('input_id', '');
            $input_file->set('input_value', $id);

            $form->set('fields', $form->merge(array($input_pg, $input_file)));
            $this->page_data['content'] .= $form->output();

            $this->page_data['content'] .= '<hr />';

            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=edit&id=' . $id, $this->localization->string('back'), '<p>', '</p>');
        } 

        if ($this->postAndGet('action') == 'new') {
            $this->page_data['head_tags'] = '
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
                .critical { background: #FFCCAA; border: 1px solid #FF3334;    }
                .help { background: #9FDAEE; border: 1px solid #2BB0D7;    }
                .info { background: #9FDAEE; border: 1px solid #2BB0D7;    padding: 20px;}
                .warning { background: #FFFFAA; border: 1px solid #FFAD33; }
            </style>
            ';

            $this->page_data['content'] .= '<h1>New page</h1>'; // todo: update localization

            $form = $this->container['parse_page'];
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/?action=add_new_page');

            // Page name input
            $input_new_file = $this->container['parse_page'];
            $input_new_file->load('forms/input');
            $input_new_file->set('label_for', 'newfile');
            $input_new_file->set('label_value', $this->localization->string('pagename') . ':');
            $input_new_file->set('input_type', 'text');
            $input_new_file->set('input_name', 'newfile');
            $input_new_file->set('input_id', 'newfile');
            $input_new_file->set('input_maxlength', 120);

            // Language select
            $languages = "SELECT * FROM languages ORDER BY lngeng";

            $options = '<option value="">Don\'t set</option>';
            foreach ($this->db->query($languages) as $localization) {
                $options .= "<option value=\"" . strtolower($localization['iso-2']) . "\">" . $localization['lngeng'] . "</option>";
            }

            $select_language = $this->container['parse_page'];
            $select_language->load('forms/select');
            $select_language->set('label_for', 'language');
            $select_language->set('label_value', $this->localization->string('language') . ' (optional):');
            $select_language->set('select_id', 'language');
            $select_language->set('select_name', 'localization');
            $select_language->set('options', $options);

            // Page type select
            $select_type = $this->container['parse_page'];
            $select_type->load('forms/select');
            $select_type->set('label_for', 'type');
            $select_type->set('label_value', 'Post type:');
            $select_type->set('select_id', 'type');
            $select_type->set('select_name', 'type');
            $select_type->set('options', '<option value="page">Page</option><option value="post">Post</option>');

            // Allow unicode url checkbox
            $checkbox_allow_unicode = $this->container['parse_page'];
            $checkbox_allow_unicode->load('forms/checkbox');
            $checkbox_allow_unicode->set('label_for', 'allow-unicode');
            $checkbox_allow_unicode->set('label_value', $this->localization->string('allowUnicodeUrl'));
            $checkbox_allow_unicode->set('checkbox_id', 'allow_unicode');
            $checkbox_allow_unicode->set('checkbox_name', 'allow_unicode');
            $checkbox_allow_unicode->set('checkbox_value', 'on');

            // All form fields
            $fields = array($input_new_file, $select_language, $select_type, $checkbox_allow_unicode);

            // Merge fields
            $form->set('fields', $form->merge($fields));

            // Show form
            $form->set('localization[save]', $this->localization->string('save'));
            $this->page_data['content'] .= $form->output();
        } 

        // Confirm that you want to delete the page
        if ($this->postAndGet('action') == 'delete_page') {
            // Page data
            $page_data = $page_editor->selectPage($id);

            if (!empty($id)) {
                $this->page_data['content'] .= '<p>' . $this->localization->string('confdelfile') . ' <b>' . $page_data['page_title'] . '</b></p>';
                $this->page_data['content'] .= '<p><b>' . $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=confirmed_page_delete&id=' . $id, $this->localization->string('delete')) . '</b></p>';
            } else {
                $this->page_data['content'] .= '<p>' . $this->localization->string('nofiletodel') . '</p>';
            }

            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/', $this->localization->string('back')) . '<br />';
        }

        if ($this->postAndGet('action') == 'update_page_localization') {
            // get page data
            $pageData = $page_editor->selectPage($id);

            $form = $this->container['parse_page'];
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/?action=save_page_localization&id=' . $pageData['id']);

            $select_language = $this->container['parse_page'];
            $select_language->load('forms/select');
            $select_language->set('label_for', 'localization');
            $select_language->set('label_value', $this->localization->string('language'));
            $select_language->set('select_name', 'localization');
            $select_language->set('select_id', 'localization');

            $options = '';

            if (!empty($pageData['localization'])) {
                $options .= '<option value="' . $pageData['localization'] . '">' . $pageData['localization'] . '</option>';
                $options .= '<option value="">Leave empty</option>'; // update language
            } else {
                $options .= '<option value="">Leave empty</option>'; // update language
            }

            $languages = "SELECT * FROM languages ORDER BY lngeng";

            foreach ($this->db->query($languages) as $localization) {
                $options .= '<option value="' . strtolower($localization['iso-2']) . '">' . $localization['lngeng'] . '</option>';
            }

            $select_language->set('options', $options);

            $form->set('fields', $select_language->output());

            $this->page_data['content'] .= $form->output();

            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/', $this->localization->string('back'), '<p>', '</p>');
        }

        if ($this->postAndGet('action') == 'tags') {
            // Get page data
            $tag_field = $this->container['parse_page'];
            $tag_field->load('forms/input');
            $tag_field->set('label_value', 'Tags');
            $tag_field->set('label_for', 'tags');
            $tag_field->set('input_name', 'tags');
            $tag_field->set('input_id', 'tags');
            $tag_field->set('input_type', 'text');
            $tag_field->set('input_value', $page_editor->pageTags($id));

            $form = $this->container['parse_page'];
            $form->load('forms/form');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/?action=savetags&id=' . $id);
            $form->set('form_method', 'post');
            $form->set('fields', $tag_field->output());

            $this->page_data['content'] .= $form->output();
        }

        $this->page_data['content'] .= '<p>';

        $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=new', $this->localization->string('newpage')) . '<br />';
        $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/blogcategory', 'Blog category management') . '<br />';

        // Todo: Update localization
        if ($this->user->administrator(101)) {
            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=mainmeta', 'Head tags (meta tags) on all pages') . '<br />';
        }

        $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagesearch', $this->localization->string('search')) . '<br />';

        if (!empty($this->postAndGet('action'))) {
            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager', $this->localization->string('pages_management')) . '<br />';
        }

        $this->page_data['content'] .= '</p>';

        return $this->page_data;
    }

    public function thumbnail(array $params)
    {
        // Check access permissions
        if (!$this->user->administrator()) {
            $this->redirection('../?auth_error');
        }

        $page_id = $params[1] ?? 0;

        $this->page_data['page_title'] = 'Thumbnail';

        $page_editor = new PageManager($this->container);

        $page_details = $page_editor->selectPage($page_id);

        if (empty($page_details)) {
            return $this->handleNoPageError();
        }

        // Create form
        $thumbnail_field = $this->container['parse_page'];
        $thumbnail_field->load('forms/input');
        $thumbnail_field->set('label_value', 'Thumbnail');
        $thumbnail_field->set('label_for', 'thumbnail');
        $thumbnail_field->set('input_name', 'thumbnail');
        $thumbnail_field->set('input_id', 'thumbnail');
        $thumbnail_field->set('input_placeholder', 'Address of the thumbnail');
        $thumbnail_field->set('input_type', 'text');
        $thumbnail_field->set('input_value', $page_details['thumbnail']);

        $form = $this->container['parse_page'];
        $form->load('forms/form');
        $form->set('form_action', HOMEDIR . 'adminpanel/pagemanager/update_thumbnail/' . $page_id);
        $form->set('form_method', 'post');
        $form->set('fields', $thumbnail_field->output());

        $this->page_data['content'] .= $form->output();

        return $this->page_data;
    }

    public function update_thumbnail(array $params)
    {
        // Check access permissions
        if (!$this->user->administrator()) {
            $this->redirection('../?auth_error');
        }

        $page_id = $params[1] ?? 0;

        $page_editor = new PageManager($this->container);

        $page_details = $page_editor->selectPage($page_id);

        if (empty($page_details)) {
            return $this->handleNoPageError();
        }

        $page_editor->updateThumbnail($page_id, $this->postAndGet('thumbnail'));

        $this->redirection(HOMEDIR . 'adminpanel/pagemanager/thumbnail/' . $page_id);
    }
}