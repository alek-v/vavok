<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class AdminpanelModel extends BaseModel {
    /**
     * Index page
     */
    public function index()
    {
        // Users data
        $data['user'] = $this->user_data;
        $data['tname'] = '{@localization[adminpanel]}}';
        $data['content'] = '';

        if (!$this->user->check_permissions('adminpanel', 'show')) $this->redirection('../?auth_error');

        if (empty($this->postAndGet('action'))) {
            // Moderator access level or bigger
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/adminchat', $this->localization->string('admchat'));
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/adminlist', $this->localization->string('modlist'));
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/unconfirmed_reg', $this->localization->string('notconf'));
            $data['content'] .= $this->sitelink('pages/userlist', $this->localization->string('userlist') . ' (' . $this->user->regmemcount() . ')');

            // Super moderator access level or bigger
            if ($this->user->moderator(103) || $this->user->moderator(105) || $this->user->administrator()) {
                if (file_exists('reports.php')) $data['content'] .= $this->sitelink('reports.php', $this->localization->string('usrcomp'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/file_upload', $this->localization->string('upload'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/uploaded_files', $this->localization->string('uplFiles'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/search_uploads', 'Search uploaded files');
            }

            // Head moderator access level or bigger
            if ($this->user->administrator() || $this->user->moderator(103)) {
                $data['content'] .= '<hr>';
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/addban', '{@localization[banunban]}}');
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/banlist', '{@localization[banlist]}}');
            }

            // Administrator access level or bigger
            if ($this->user->administrator()) {
                $data['content'] .= '<hr>';

                if (file_exists('antiword.php')) $data['content'] .= $this->sitelink('antiword.php', $this->localization->string('badword'));

                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/statistics', $this->localization->string('statistics'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/users', $this->localization->string('mngprof'));
            }

            if ($this->user->administrator() || $this->user->check_permissions('pageedit')) {
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager', $this->localization->string('mngpage'));
            }

            // Head administrator access level
            if ($this->user->administrator(101)) {
                $data['content'] .= '<hr>';

                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings', $this->localization->string('syssets'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/subscriptions', $this->localization->string('subscriptions'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/email_queue', 'Add to email queue');
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/ipban', $this->localization->string('ipbanp') . ' (' . $this->linesInFile(APPDIR . 'used/ban.dat') . ')');
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/?action=sysmng', $this->localization->string('sysmng'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/logfiles', $this->localization->string('logcheck'));
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/sitemap', 'Sitemap Generator');
            }
        }

        if ($this->postAndGet('action') == 'clear' && $this->user->administrator(101)) {
            $data['content'] .= '<p>';
            if (file_exists('delusers.php')) $data['content'] .= $this->sitelink('delusers.php', $this->localization->string('cleanusers'));
            $data['content'] .= $this->sitelink('./?action=clrmlog', $this->localization->string('cleanmodlog'));
            $data['content'] .= '</p>';
        }

        if ($this->postAndGet('action') == 'clrmlog' && $this->user->administrator(101)) {
            $this->db->query("DELETE FROM mlog");
        
            $data['content'] .= '<p><img src="../themes/images/img/open.gif" alt="" /> ' . $this->localization->string('mlogcleaned') . '</p>';
        } 
 
        if ($this->postAndGet('action') == "sysmng" && $this->user->administrator(101)) {
            $data['content'] .= '<p>';
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/systemcheck', $this->localization->string('chksystem'));
            $data['content'] .= $this->sitelink('./?action=clear', $this->localization->string('cleansys'));
        
            if (file_exists('backup.php')) $data['content'] .= $this->sitelink('backup.php', $this->localization->string('backup'));
        
            $data['content'] .= '</p>';
        }

        if ($this->postAndGet('action') == "opttbl" && $this->user->administrator(101)) {
            $alltables = mysqli_query("SHOW TABLES");

            while ($table = mysqli_fetch_assoc($alltables)) {
                foreach ($table as $db => $tablename) {
                    $sql = "OPTIMIZE TABLE `" . $tablename . "`";
                    $this->db->query($sql);
                }
            }

            $data['content'] .= '<p><img src="../themes/images/img/reload.gif" alt="" /> Optimized successfully!</p>'; // update lang
        }

        if (!empty($this->postAndGet('action'))) $data['content'] .= $this->sitelink('./', $this->localization->string('adminpanel'), '<p>', '</p>');

        $data['content'] .= $this->homelink('<p>', '</p>');

        // Pass data to the view
        return $data;
    }

    /**
     * Settings
     */
    public function settings()
    {
        // Users data
        $data['user'] = $this->user_data;

        $data['tname'] = '{@localization[settings]}}';
        $data['content'] = '';

        if (!$this->user->administrator(101)) $this->redirection('../pages/error.php?error=auth');

        $site_configuration = new Config();
        
        /**
         * Мain settings update
         */
        if ($this->postAndGet('action') == 'editone') {
            $fields = array('webtheme', 'adminNick', 'adminEmail', 'timeZone', 'title', 'homeUrl', 'siteDefaultLang', 'openReg', 'regConfirm', 'siteOff');

            $values = array(
                $this->postAndGet('conf_set2'),
                $this->postAndGet('conf_set8'),
                $this->postAndGet('conf_set9'),
                $this->postAndGet('conf_set10'),
                $this->postAndGet('conf_set11'),
                $this->postAndGet('conf_set14'),
                $this->postAndGet('conf_set47'),
                (int)$this->postAndGet('conf_set61'),
                (int)$this->postAndGet('conf_set62'),
                (int)$this->postAndGet('conf_set63')
            );
        
            /**
             * Update settings
             */
            $site_configuration->updateConfigData(array_combine($fields, $values));
        
            $this->redirection(HOMEDIR . 'adminpanel/settings/?isset=mp_yesset');
        }
        
        if ($this->postAndGet('action') == 'edittwo') {
            $fields = array(
                'showtime',
                'pageGenTime',
                'showOnline',
                'cookieConsent',
                'showCounter'
            );
        
            $values = array(
                (int)$this->postAndGet('conf_set4'),
                (int)$this->postAndGet('conf_set5'),
                (int)$this->postAndGet('conf_set7'),
                (int)$this->postAndGet('conf_set32'), // cookie consent
                (int)$this->postAndGet('conf_set74')
            );
        
            /**
             * Update settings
             */
            $site_configuration->updateConfigData(array_combine($fields, $values));
        
            $this->redirection(HOMEDIR . "adminpanel/settings/?isset=mp_yesset");
        } 
        
        if ($this->postAndGet('action') == 'editthree') {
            $fields = array(
                'bookGuestAdd',
                'maxPostChat',
                'maxPostNews',
                'subMailPacket'
            );
        
            $values = array(
                (int)$this->postAndGet('conf_set20'),
                (int)$this->postAndGet('conf_set22'),
                (int)$this->postAndGet('conf_set24'),
                (int)$this->postAndGet('conf_set56')
            );
        
            /**
             * Update settings
             */
            $site_configuration->updateConfigData(array_combine($fields, $values));
            $this->redirection(HOMEDIR . "adminpanel/settings/?isset=mp_yesset");
        
        }
        
        if ($this->postAndGet('action') == 'editfour') {
            // Update main config
            $fields = array(
                'photoFileSize',
                'maxPhotoPixels',
                'forumAccess',
                'forumChLang'
            );
        
            $values = array(
                (int)$this->postAndGet('conf_set38') * 1024,
                (int)$this->postAndGet('conf_set39'),
                (int)$this->postAndGet('conf_set49'),
                (int)$this->postAndGet('conf_set68')
            );
        
            /**
             * Update settings
             */
            $site_configuration->updateConfigData(array_combine($fields, $values));
        
            // update gallery settings
            $gallery_file = $this->getDataFile('dataconfig/gallery.dat');
            if (empty($gallery_file)) $gallery_file = '||||||||||';
            $gallery_data = explode("|", $gallery_file[0]);
        
            $gallery_data[0] = (int)$this->postAndGet('gallery_set0'); // users can upload
            $gallery_data[8] = (int)$this->postAndGet('gallery_set8'); // photos per page
            $gallery_data[5] = (int)$this->postAndGet('screen_width');
            $gallery_data[6] = (int)$this->postAndGet('screen_height');
            $gallery_data[7] = (int)$this->postAndGet('media_buttons');
        
            $gallery_text = '';
            for ($u = 0; $u < 10; $u++) {
                $gallery_text .= $gallery_data[$u] . '|';
            }
        
            if (isset($gallery_text)) {
                $this->writeDataFile('dataconfig/gallery.dat', $gallery_text);
            }
        
            $this->redirection(HOMEDIR . "adminpanel/settings/?isset=mp_yesset");
        }
        
        if ($this->postAndGet('action') == 'editfive') {
            /**
             * Update settings
             */
            $site_configuration->updateConfigData(array_combine(array('pvtLimit'), array((int)$this->postAndGet('conf_set30'))));
        
            $this->redirection(HOMEDIR . 'adminpanel/settings/?isset=mp_yesset');
        }
        
        if ($this->postAndGet('action') == 'editseven') {
            $fields = array(
                'pgFbComm',
                'refererLog',
                'showRefPage'
            );

            $values = array(
                $this->postAndGet('conf_set6'),
                (int)$this->postAndGet('conf_set51'),
                $this->postAndGet('conf_set70')
            );

            // Update settings
            $site_configuration->updateConfigData(array_combine($fields, $values));
            $this->redirection(HOMEDIR . 'adminpanel/settings/?isset=mp_yesset');
        }

        if ($this->postAndGet('action') == 'editeight') {
            $fields = array(
                'maxLogData',
                'maxBanTime'
            );

            $values = array(
                (int)$this->postAndGet('conf_set58'),
                round($this->postAndGet('conf_set76') * 1440)
            );

            // Update settings
            $site_configuration->updateConfigData(array_combine($fields, $values));

            $this->redirection(HOMEDIR . "adminpanel/settings/?isset=mp_yesset");
        }

        // Site security options
        if ($this->postAndGet('action') == 'editsecurity') {
            $fields = array('keypass', 'quarantine', 'transferProtocol', 'floodTime', 'recaptcha_sitekey', 'recaptcha_secretkey');

            $values = array(
                $this->postAndGet('conf_set1'),
                $this->postAndGet('conf_set3'),
                $this->postAndGet('conf_set21'),
                (int)$this->postAndGet('conf_set29'),
                $this->postAndGet('recaptcha_sitekey'),
                $this->postAndGet('recaptcha_secretkey')
            );

            // Update settings
            $site_configuration->updateConfigData(array_combine($fields, $values));

            $this->redirection(HOMEDIR . "adminpanel/settings/?action=security&isset=success");
        }

        if (empty($this->postAndGet('action'))) {
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings/?action=setone', $this->localization->string('mainset'));
            $data['content'] .=  $this->sitelink(HOMEDIR . 'adminpanel/settings/?action=settwo', $this->localization->string('shwinfo'));
            $data['content'] .=  $this->sitelink(HOMEDIR . 'adminpanel/settings/?action=setthree', $this->localization->string('bookchatnews'));
            $data['content'] .=  $this->sitelink(HOMEDIR . 'adminpanel/settings/?action=setfour', $this->localization->string('forumgallery'));
            $data['content'] .=  $this->sitelink(HOMEDIR . 'adminpanel/settings/?action=setseven', $this->localization->string('pagemanage'));
            $data['content'] .=  $this->sitelink(HOMEDIR . 'adminpanel/settings/?action=security', $this->localization->string('security'));
            $data['content'] .=  $this->sitelink(HOMEDIR . 'adminpanel/settings/?action=seteight', $this->localization->string('other'));
        }

        // main settings
        if ($this->postAndGet('action') == 'setone') {
            $data['content'] .=  '<h1>' . $this->localization->string('mainset') . '</h1>';

            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/settings/?action=editone');
        
            $options = '<option value="' . $this->configuration('siteDefaultLang') . '">' . $this->configuration('siteDefaultLang') . '</option>';
            $dir = opendir(APPDIR . 'include/lang');
            while ($file = readdir($dir)) {
                if (!preg_match('/[^0-9A-Za-z.\_\-]/', $file) && $file != $this->configuration('siteDefaultLang') && $file != '..' && $file != '.' && $file != "index.php" && $file != ".htaccess" && strlen($file) > 2) {
                    $options .= '<option value="' . $file . '">' . $file . '</option>';
                }
            }

            $select_lang = $this->model('ParsePage');
            $select_lang->load('forms/select');
            $select_lang->set('label_for', 'conf_set47');
            $select_lang->set('label_value', $this->localization->string('language'));
            $select_lang->set('select_id', 'conf_set47');
            $select_lang->set('select_name', 'conf_set47');
            $select_lang->set('options', $options);

            $config_themes_show = str_replace('web_', '', $this->configuration('webtheme'));
            $config_themes_show = ucfirst($config_themes_show);

            $options = '<option value="' . $this->configuration('webtheme') . '">' . $config_themes_show . '</option>';
            $dir = opendir (PUBLICDIR . 'themes');
            while ($file = readdir ($dir)) {
                if (!preg_match('/[^0-9A-Za-z.\_\-]/', $file) && $file != $this->configuration('webtheme') && $file != '..' && $file != '.' && $file != 'index.php' && $file != '.htaccess' && $file != 'templates' && $file != 'images') {
                    $nfile = str_replace("web_", "", $file);
                    $nfile = ucfirst($nfile);
                    $options .= '<option value="' . $file . '">' . $nfile . '</option>';
                }
            }

            $select_theme = $this->model('ParsePage');
            $select_theme->load('forms/select');
            $select_theme->set('label_for', 'conf_set2');
            $select_theme->set('label_value', $this->localization->string('webskin'));
            $select_theme->set('select_id', 'conf_set2');
            $select_theme->set('select_name', 'conf_set2');
            $select_theme->set('options', $options);
        
            // this will be admin username or system username
            $input8 = $this->model('ParsePage');
            $input8->load('forms/input');
            $input8->set('label_for', 'conf_set8');
            $input8->set('label_value', $this->localization->string('adminusername'));
            $input8->set('input_id', 'conf_set8');
            $input8->set('input_name', 'conf_set8');
            $input8->set('input_value', $this->configuration('adminNick'));
            $input8->set('input_maxlength', 20);
        
            $input9 = $this->model('ParsePage');
            $input9->load('forms/input');
            $input9->set('label_for', 'conf_set9');
            $input9->set('label_value', $this->localization->string('adminemail'));
            $input9->set('input_id', 'conf_set9');
            $input9->set('input_name', 'conf_set9');
            $input9->set('input_value', $this->configuration('adminEmail'));
            $input9->set('input_maxlength', 50);

            $input10 = $this->model('ParsePage');
            $input10->load('forms/input');
            $input10->set('label_for', 'conf_set10');
            $input10->set('label_value', $this->localization->string('timezone'));
            $input10->set('input_id', 'conf_set10');
            $input10->set('input_name', 'conf_set10');
            $input10->set('input_value', $this->configuration('timeZone'));
            $input10->set('input_maxlength', 3);
        
            $input11 = $this->model('ParsePage');
            $input11->load('forms/input');
            $input11->set('label_for', 'conf_set11');
            $input11->set('label_value', $this->localization->string('pagetitle'));
            $input11->set('input_id', 'conf_set11');
            $input11->set('input_name', 'conf_set11');
            $input11->set('input_value', $this->configuration('title'));
            $input11->set('input_maxlength', 100);
        
            $input14 = $this->model('ParsePage');
            $input14->load('forms/input');
            $input14->set('label_for', 'conf_set14');
            $input14->set('label_value', $this->localization->string('siteurl'));
            $input14->set('input_id', 'conf_set14');
            $input14->set('input_name', 'conf_set14');
            $input14->set('input_value', $this->configuration('homeUrl'));
            $input14->set('input_maxlength', 50);
        
            // Registration opened or closed
            $input_radio61_yes = $this->model('ParsePage');
            $input_radio61_yes->load('forms/radio_inline');
            $input_radio61_yes->set('label_for', 'conf_set61');
            $input_radio61_yes->set('label_value', $this->localization->string('yes'));
            $input_radio61_yes->set('input_id', 'conf_set61');
            $input_radio61_yes->set('input_name', 'conf_set61');
            $input_radio61_yes->set('input_value', 1);
            if ($this->configuration('openReg') == 1) {
                $input_radio61_yes->set('input_status', 'checked');
            }
        
            $input_radio61_no = $this->model('ParsePage');
            $input_radio61_no->load('forms/radio_inline');
            $input_radio61_no->set('label_for', 'conf_set61');
            $input_radio61_no->set('label_value', $this->localization->string('no'));
            $input_radio61_no->set('input_id', 'conf_set61');
            $input_radio61_no->set('input_name', 'conf_set61');
            $input_radio61_no->set('input_value', 0);
            if ($this->configuration('openReg') == 0) {
                $input_radio61_no->set('input_status', 'checked');
            }
        
            $radio_group_one = $this->model('ParsePage');
            $radio_group_one->load('forms/radio_group');
            $radio_group_one->set('description', $this->localization->string('openreg'));
            $radio_group_one->set('radio_group', $radio_group_one->merge(array($input_radio61_yes, $input_radio61_no)));
        
            // Does user need to confirm registration
            $input_radio62_yes = $this->model('ParsePage');
            $input_radio62_yes->load('forms/radio_inline');
            $input_radio62_yes->set('label_for', 'conf_set62');
            $input_radio62_yes->set('label_value', $this->localization->string('yes'));
            $input_radio62_yes->set('input_id', 'conf_set62');
            $input_radio62_yes->set('input_name', 'conf_set62');
            $input_radio62_yes->set('input_value', 1);
            if ($this->configuration('regConfirm') == 1) {
                $input_radio62_yes->set('input_status', 'checked');
            }
        
            $input_radio62_no = $this->model('ParsePage');
            $input_radio62_no->load('forms/radio_inline');
            $input_radio62_no->set('label_for', 'conf_set62');
            $input_radio62_no->set('label_value', $this->localization->string('no'));
            $input_radio62_no->set('input_id', 'conf_set62');
            $input_radio62_no->set('input_name', 'conf_set62');
            $input_radio62_no->set('input_value', 0);
            if ($this->configuration('regConfirm') == 0) {
                $input_radio62_no->set('input_status', 'checked');
            }
        
            $radio_group_two = $this->model('ParsePage');
            $radio_group_two->load('forms/radio_group');
            $radio_group_two->set('description', $this->localization->string('confregs'));
            $radio_group_two->set('radio_group', $radio_group_two->merge(array($input_radio62_yes, $input_radio62_no)));
        
            // Maintenance mode
            $input_radio63_yes = $this->model('ParsePage');
            $input_radio63_yes->load('forms/radio_inline');
            $input_radio63_yes->set('label_for', 'conf_set63');
            $input_radio63_yes->set('label_value', $this->localization->string('yes'));
            $input_radio63_yes->set('input_id', 'conf_set63');
            $input_radio63_yes->set('input_name', 'conf_set63');
            $input_radio63_yes->set('input_value', 1);
            if ($this->configuration('siteOff') == 1) {
                $input_radio63_yes->set('input_status', 'checked');
            }
        
            $input_radio63_no = $this->model('ParsePage');
            $input_radio63_no->load('forms/radio_inline');
            $input_radio63_no->set('label_for', 'conf_set63');
            $input_radio63_no->set('label_value', $this->localization->string('no'));
            $input_radio63_no->set('input_id', 'conf_set63');
            $input_radio63_no->set('input_name', 'conf_set63');
            $input_radio63_no->set('input_value', 0);
            if ($this->configuration('siteOff') == 0) {
                $input_radio63_no->set('input_status', 'checked');
            }
        
            $radio_group_three = $this->model('ParsePage');
            $radio_group_three->load('forms/radio_group');
            $radio_group_three->set('description', 'Maintenance');
            $radio_group_three->set('radio_group', $radio_group_three->merge(array($input_radio63_yes, $input_radio63_no)));
        
            $form->set('fields', $form->merge(array($select_lang, $select_theme, $input8, $input9, $input10, $input11, $input14, $radio_group_one, $radio_group_two, $radio_group_three)));
            $data['content'] .= $form->output();
        
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings/', $this->localization->string('back'), '<p>', '</p>');
        }
        
        if ($this->postAndGet('action') == "settwo") {
            $data['content'] .= '<h1>' . $this->localization->string('shwinfo') . '</h1>';
        
            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/settings/?action=edittwo');
        
            /**
             * Show clock
             */
            $_4_yes = $this->model('ParsePage');
            $_4_yes->load('forms/radio_inline');
            $_4_yes->set('label_for', 'conf_set4');
            $_4_yes->set('label_value', $this->localization->string('yes'));
            $_4_yes->set('input_id', 'conf_set4');
            $_4_yes->set('input_name', 'conf_set4');
            $_4_yes->set('input_value', 1);
            if ($this->configuration('showtime') == 1) {
                $_4_yes->set('input_status', 'checked');
            }
        
            $_4_no = $this->model('ParsePage');
            $_4_no->load('forms/radio_inline');
            $_4_no->set('label_for', 'conf_set4');
            $_4_no->set('label_value',  $this->localization->string('no'));
            $_4_no->set('input_id', 'conf_set4');
            $_4_no->set('input_name', 'conf_set4');
            $_4_no->set('input_value', 0);
            if ($this->configuration('showtime') == 0) {
                $_4_no->set('input_status', 'checked');
            }
        
            $show_clock = $this->model('ParsePage');
            $show_clock->load('forms/radio_group');
            $show_clock->set('description', $this->localization->string('showclock'));
            $show_clock->set('radio_group', $show_clock->merge(array($_4_yes, $_4_no)));
        
            /**
             * Show page generatioin time
             */
            $_5_yes = $this->model('ParsePage');
            $_5_yes->load('forms/radio_inline');
            $_5_yes->set('label_for', 'conf_set5');
            $_5_yes->set('label_value', $this->localization->string('yes'));
            $_5_yes->set('input_id', 'conf_set5');
            $_5_yes->set('input_name', 'conf_set5');
            $_5_yes->set('input_value', 1);
            if ($this->configuration('pageGenTime') == 1) {
                $_5_yes->set('input_status', 'checked');
            }
        
            $_5_no = $this->model('ParsePage');
            $_5_no->load('forms/radio_inline');
            $_5_no->set('label_for', 'conf_set5');
            $_5_no->set('label_value', $this->localization->string('no'));
            $_5_no->set('input_id', 'conf_set5');
            $_5_no->set('input_name', 'conf_set5');
            $_5_no->set('input_value', 0);
            if ($this->configuration('pageGenTime') == 0) {
                $_5_no->set('input_status', 'checked');
            }
        
            $page_gen = $this->model('ParsePage');
            $page_gen->load('forms/radio_group');
            $page_gen->set('description', $this->localization->string('pagegen'));
            $page_gen->set('radio_group', $page_gen->merge(array($_5_yes, $_5_no)));
        
            /**
             * Show online
             */
            $_7_yes = $this->model('ParsePage');
            $_7_yes->load('forms/radio_inline');
            $_7_yes->set('label_for', 'conf_set7');
            $_7_yes->set('label_value', $this->localization->string('yes'));
            $_7_yes->set('input_id', 'conf_set7');
            $_7_yes->set('input_name', 'conf_set7');
            $_7_yes->set('input_value', 1);
            if ($this->configuration('showOnline') == 1) {
                $_7_yes->set('input_status', 'checked');
            }
        
            $_7_no = $this->model('ParsePage');
            $_7_no->load('forms/radio_inline');
            $_7_no->set('label_for', 'conf_set7');
            $_7_no->set('label_value',  $this->localization->string('no'));
            $_7_no->set('input_id', 'conf_set7');
            $_7_no->set('input_name', 'conf_set7');
            $_7_no->set('input_value', 0);
            if ($this->configuration('showOnline') == 0) {
                $_7_no->set('input_status', 'checked');
            }
        
            $show_online = $this->model('ParsePage');
            $show_online->load('forms/radio_group');
            $show_online->set('description', $this->localization->string('showonline'));
            $show_online->set('radio_group', $show_online->merge(array($_7_yes, $_7_no)));
        
            /**
             * Show cookie consent
             */
            $_32_yes = $this->model('ParsePage');
            $_32_yes->load('forms/radio_inline');
            $_32_yes->set('label_for', 'conf_set32');
            $_32_yes->set('label_value', $this->localization->string('yes'));
            $_32_yes->set('input_id', 'conf_set32');
            $_32_yes->set('input_name', 'conf_set32');
            $_32_yes->set('input_value', 1);
            if ($this->configuration('cookieConsent') == 1) {
                $_32_yes->set('input_status', 'checked');
            }
        
            $_32_no = $this->model('ParsePage');
            $_32_no->load('forms/radio_inline');
            $_32_no->set('label_for', 'conf_set32');
            $_32_no->set('label_value', $this->localization->string('no'));
            $_32_no->set('input_id', 'conf_set32');
            $_32_no->set('input_name', 'conf_set32');
            $_32_no->set('input_value', 0);
            if ($this->configuration('cookieConsent') == 0) {
                $_32_no->set('input_status', 'checked');
            }
        
            $cookie_consent = $this->model('ParsePage');
            $cookie_consent->load('forms/radio_group');
            $cookie_consent->set('description', 'Cookie consent');
            $cookie_consent->set('radio_group', $cookie_consent->merge(array($_32_yes, $_32_no)));
        
            /**
             * Show counter
             */
            $incounters = array(6 => "" . $this->localization->string('dontshow') . "", 1 => "" . $this->localization->string('vsttotalvst') . "", 2 => "" . $this->localization->string('clicktotalclick') . "", 3 => "" . $this->localization->string('clickvisits') . "", 4 => "" . $this->localization->string('totclicktotvst'));
        
            $options = '<option value="' . $this->configuration('showCounter') . '">' . $incounters[$this->configuration('showCounter')] . '</option>';
            foreach($incounters as $k => $v) {
                if ($k != $this->configuration('showCounter')) {
                    $options .= '<option value="' . $k . '">' . $v . '</option>';
                }
            }
        
            $show_counter = $this->model('ParsePage');
            $show_counter->load('forms/select');
            $show_counter->set('label_for', 'conf_set74');
            $show_counter->set('label_value', $this->localization->string('countlook'));
            $show_counter->set('select_id', 'conf_set74');
            $show_counter->set('select_name', 'conf_set74');
            $show_counter->set('options', $options);
        
            $form->set('fields', $form->merge(array($show_clock, $page_gen, $show_online, $cookie_consent, $show_counter)));
            $data['content'] .= $form->output();
        
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings/', $this->localization->string('back'), '<p>', '</p>');
        }
        
        if ($this->postAndGet('action') == "setthree") {
            $data['content'] .= '<h1>' . $this->localization->string('gbnewschatset') . '</h1>';

            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/settings/?action=editthree');

            /**
             * Allow guests to write in guestbook
             */
            $_20_yes = $this->model('ParsePage');
            $_20_yes->load('forms/radio_inline');
            $_20_yes->set('label_for', 'conf_set20');
            $_20_yes->set('label_value', $this->localization->string('yes'));
            $_20_yes->set('input_id', 'conf_set20');
            $_20_yes->set('input_name', 'conf_set20');
            $_20_yes->set('input_value', 1);
            if ($this->configuration('bookGuestAdd') == 1) {
                $_20_yes->set('input_status', 'checked');
            }

            $_20_no = $this->model('ParsePage');
            $_20_no->load('forms/radio_inline');
            $_20_no->set('label_for', 'conf_set20');
            $_20_no->set('label_value', $this->localization->string('no'));
            $_20_no->set('input_id', 'conf_set20');
            $_20_no->set('input_name', 'conf_set20');
            $_20_no->set('input_value', 0);
            if ($this->configuration('bookGuestAdd') == 0) {
                $_20_no->set('input_status', 'checked');
            }

            $gb_write = $this->model('ParsePage');
            $gb_write->load('forms/radio_group');
            $gb_write->set('description', $this->localization->string('allowguestingb'));
            $gb_write->set('radio_group', $gb_write->merge(array($_20_yes, $_20_no)));
        
            /**
             * Max chat posts
             */
            $input22 = $this->model('ParsePage');
            $input22->load('forms/input');
            $input22->set('label_for', 'conf_set22');
            $input22->set('label_value', $this->localization->string('maxinchat'));
            $input22->set('input_id', 'conf_set22');
            $input22->set('input_name', 'conf_set22');
            $input22->set('input_value', $this->configuration('maxPostChat'));
            $input22->set('input_maxlength', 4);
        
            /**
             * Max news posts
             */
            $input24 = $this->model('ParsePage');
            $input24->load('forms/input');
            $input24->set('label_for', 'conf_set24');
            $input24->set('label_value', $this->localization->string('maxnews'));
            $input24->set('input_id', 'conf_set24');
            $input24->set('input_name', 'conf_set24');
            $input24->set('input_value', $this->configuration('maxPostNews'));
            $input24->set('input_maxlength', 5);
        
            /**
             * Mails in one package
             */
            $input56 = $this->model('ParsePage');
            $input56->load('forms/input');
            $input56->set('label_for', 'conf_set56');
            $input56->set('label_value', $this->localization->string('onepassmail'));
            $input56->set('input_id', 'conf_set56');
            $input56->set('input_name', 'conf_set56');
            $input56->set('input_value', $this->configuration('subMailPacket'));
            $input56->set('input_maxlength', 3);
        
            $form->set('fields', $form->merge(array($gb_write, $input22, $input24, $input56)));
            $data['content'] .= $form->output();
        
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings/', $this->localization->string('back'), '<p>', '</p>');
        }
        
        if ($this->postAndGet('action') == "setfour") {
            $kbs = $this->configuration('photoFileSize') / 1024;

            $data['content'] .= '<h1>' . $this->localization->string('forumandgalset') . '</h1>';
        
            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/settings/?action=editfour');

            // Gallery settings
            $gallery_config = $this->getDataFile('dataconfig/gallery.dat');
            if (!empty($gallery_config)) {
                $gallery_data = explode("|", $gallery_config[0]);
            } else {
                $gallery_data = explode("|", '|||||||||||||');
            }

            // Gallery photos per page
            $gallery_set8 = $this->model('ParsePage');
            $gallery_set8->load('forms/input');
            $gallery_set8->set('label_for', 'gallery_set8');
            $gallery_set8->set('label_value', $this->localization->string('photosperpg'));
            $gallery_set8->set('input_id', 'gallery_set8');
            $gallery_set8->set('input_name', 'gallery_set8');
            $gallery_set8->set('input_value', $gallery_data[8]);
            $gallery_set8->set('input_maxlength', 2);
        
            // Gallery max screen width
            $screen_width = $this->model('ParsePage');
            $screen_width->load('forms/input');
            $screen_width->set('label_for', 'screen_width');
            $screen_width->set('label_value', 'Maximum width in gallery');
            $screen_width->set('input_id', 'screen_width');
            $screen_width->set('input_name', 'screen_width');
            $screen_width->set('input_value', $gallery_data[5]);
            $screen_width->set('input_maxlength', 5);

            // Gallery max screen height
            $screen_height = $this->model('ParsePage');
            $screen_height->load('forms/input');
            $screen_height->set('label_for', 'screen_height');
            $screen_height->set('label_value', 'Maximum height in gallery');
            $screen_height->set('input_id', 'screen_height');
            $screen_height->set('input_name', 'screen_height');
            $screen_height->set('input_value', $gallery_data[6]);
            $screen_height->set('input_maxlength', 5);

            // Gallery social network buttons
            $media_buttons_yes = $this->model('ParsePage');
            $media_buttons_yes->load('forms/radio_inline');
            $media_buttons_yes->set('label_for', 'media_buttons');
            $media_buttons_yes->set('label_value', $this->localization->string('yes'));
            $media_buttons_yes->set('input_id', 'media_buttons');
            $media_buttons_yes->set('input_name', 'media_buttons');
            $media_buttons_yes->set('input_value', 1);
            if ($gallery_data[7] == 1) {
                $media_buttons_yes->set('input_status', 'checked');
            }
        
            $media_buttons_no = $this->model('ParsePage');
            $media_buttons_no->load('forms/radio_inline');
            $media_buttons_no->set('label_for', 'media_buttons');
            $media_buttons_no->set('label_value', $this->localization->string('no'));
            $media_buttons_no->set('input_id', 'media_buttons');
            $media_buttons_no->set('input_name', 'media_buttons');
            $media_buttons_no->set('input_value', 0);
            if ($gallery_data[7] == 0) {
                $media_buttons_no->set('input_status', 'checked');
            }
        
            $sn_buttons = $this->model('ParsePage');
            $sn_buttons->load('forms/radio_group');
            $sn_buttons->set('description', 'Social media like buttons in gallery');
            $sn_buttons->set('radio_group', $sn_buttons->merge(array($media_buttons_yes, $media_buttons_no)));

            // Gallery max upload size
            $conf_set38 = $this->model('ParsePage');
            $conf_set38->load('forms/input');
            $conf_set38->set('label_for', 'conf_set38');
            $conf_set38->set('label_value', $this->localization->string('photomaxkb'));
            $conf_set38->set('input_id', 'conf_set38');
            $conf_set38->set('input_name', 'conf_set38');
            $conf_set38->set('input_value', (int)$kbs);
            $conf_set38->set('input_maxlength', 8);

            // Gallery max upload pixel size
            $conf_set39 = $this->model('ParsePage');
            $conf_set39->load('forms/input');
            $conf_set39->set('label_for', 'conf_set39');
            $conf_set39->set('label_value', $this->localization->string('photopx'));
            $conf_set39->set('input_id', 'conf_set39');
            $conf_set39->set('input_name', 'conf_set39');
            $conf_set39->set('input_value', (int)$this->configuration('maxPhotoPixels'));
            $conf_set39->set('input_maxlength', 4);

            // Gallery uploads
            $gallery_set0_yes = $this->model('ParsePage');
            $gallery_set0_yes->load('forms/radio_inline');
            $gallery_set0_yes->set('label_for', 'gallery_set0');
            $gallery_set0_yes->set('label_value', $this->localization->string('yes'));
            $gallery_set0_yes->set('input_id', 'gallery_set0');
            $gallery_set0_yes->set('input_name', 'gallery_set0');
            $gallery_set0_yes->set('input_value', 1);
            if ($gallery_data[0] == 1) {
                $gallery_set0_yes->set('input_status', 'checked');
            }
        
            $gallery_set0_no = $this->model('ParsePage');
            $gallery_set0_no->load('forms/radio_inline');
            $gallery_set0_no->set('label_for', 'gallery_set0');
            $gallery_set0_no->set('label_value', $this->localization->string('no'));
            $gallery_set0_no->set('input_id', 'gallery_set0');
            $gallery_set0_no->set('input_name', 'gallery_set0');
            $gallery_set0_no->set('input_value', 0);
            if ($gallery_data[0] == 0) {
                $gallery_set0_no->set('input_status', 'checked');
            }

            $gallery_uploads = $this->model('ParsePage');
            $gallery_uploads->load('forms/radio_group');
            $gallery_uploads->set('description', 'Users can upload');
            $gallery_uploads->set('radio_group', $gallery_uploads->merge(array($gallery_set0_yes, $gallery_set0_no)));

            $form->set('fields', $form->merge(array($gallery_set8, $screen_width, $screen_height, $sn_buttons, $conf_set38, $conf_set39, $gallery_uploads)));
            $data['content'] .= $form->output();

            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings/', $this->localization->string('back'), '<p>', '</p>');
        }
        
        if ($this->postAndGet('action') == 'setseven') {
            $data['content'] .= '<h1>' . $this->localization->string('pagessets') . '</h1>';
        
            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/settings/?action=editseven');
        
            /**
             * Max referer data
             */
            $conf_set51 = $this->model('ParsePage');
            $conf_set51->load('forms/input');
            $conf_set51->set('label_for', 'referals');
            $conf_set51->set('label_value', $this->localization->string('maxrefererdata'));
            $conf_set51->set('input_id', 'referals');
            $conf_set51->set('input_name', 'conf_set51');
            $conf_set51->set('input_value', $this->configuration('refererLog'));
            $conf_set51->set('input_maxlength', 3);
        
            /**
             * Show referal page
             */
            $conf_set70yes = $this->model('ParsePage');
            $conf_set70yes->load('forms/radio_inline');
            $conf_set70yes->set('label_for', 'referal-yes');
            $conf_set70yes->set('label_value', $this->localization->string('yes'));
            $conf_set70yes->set('input_id', 'referal-yes');
            $conf_set70yes->set('input_name', 'conf_set70');
            $conf_set70yes->set('input_value', 1);
            if ($this->configuration('showRefPage') == 1) {
                $conf_set70yes->set('input_status', 'checked');
            }
        
            $conf_set70no = $this->model('ParsePage');
            $conf_set70no->load('forms/radio_inline');
            $conf_set70no->set('label_for', 'referal-no');
            $conf_set70no->set('label_value', $this->localization->string('no'));
            $conf_set70no->set('input_id', 'referal-no');
            $conf_set70no->set('input_name', 'conf_set70');
            $conf_set70no->set('input_value', 0);
            if ($this->configuration('showRefPage') == 0) {
                $conf_set70no->set('input_status', 'checked');
            }
        
            $show_refpage = $this->model('ParsePage');
            $show_refpage->load('forms/radio_group');
            $show_refpage->set('description', $this->localization->string('showrefpage'));
            $show_refpage->set('radio_group', $show_refpage->merge(array($conf_set70yes, $conf_set70no)));
        
            /**
             * Allow Facebook comments on pages
             */
            $conf_set6yes = $this->model('ParsePage');
            $conf_set6yes->load('forms/radio_inline');
            $conf_set6yes->set('label_for', 'fb_comm_yes');
            $conf_set6yes->set('label_value', $this->localization->string('yes'));
            $conf_set6yes->set('input_id', 'fb_comm_yes');
            $conf_set6yes->set('input_name', 'conf_set6');
            $conf_set6yes->set('input_value', 1);
            if ($this->configuration('pgFbComm') == 1) {
                $conf_set6yes->set('input_status', 'checked');
            }

            $conf_set6no = $this->model('ParsePage');
            $conf_set6no->load('forms/radio_inline');
            $conf_set6no->set('label_for', 'fb_comm_no');
            $conf_set6no->set('label_value', $this->localization->string('no'));
            $conf_set6no->set('input_id', 'fb_comm_no');
            $conf_set6no->set('input_name', 'conf_set6');
            $conf_set6no->set('input_value', 0);
            if ($this->configuration('pgFbComm') == 0) {
                $conf_set6no->set('input_status', 'checked');
            }

            $fb_comm = $this->model('ParsePage');
            $fb_comm->load('forms/radio_group');
            $fb_comm->set('description', 'Facebook comments on pages');
            $fb_comm->set('radio_group', $fb_comm->merge(array($conf_set6yes, $conf_set6no)));
        
            $form->set('fields', $form->merge(array($conf_set51, $show_refpage, $fb_comm)));
            $data['content'] .= $form->output();
        
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings/', $this->localization->string('back'), '<p>', '</p>');
        }

        if ($this->postAndGet('action') == "seteight") {
            $data['content'] .= '<h1>' . $this->localization->string('other') . '</h1>';
        
            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/settings/?action=editeight');

            /**
             * Max error logs in file
             */
            $conf_set58 = $this->model('ParsePage');
            $conf_set58->load('forms/input');
            $conf_set58->set('label_for', 'conf_set58');
            $conf_set58->set('label_value', $this->localization->string('maxlogfile'));
            $conf_set58->set('input_id', 'conf_set58');
            $conf_set58->set('input_name', 'conf_set58');
            $conf_set58->set('input_value', $this->configuration('maxLogData'));
            $conf_set58->set('input_maxlength', 3);

            /**
             * Max ban time
             */
            $conf_set76 = $this->model('ParsePage');
            $conf_set76->load('forms/input');
            $conf_set76->set('label_for', 'conf_set76');
            $conf_set76->set('label_value', $this->localization->string('maxbantime'));
            $conf_set76->set('input_id', 'conf_set76');
            $conf_set76->set('input_name', 'conf_set76');
            $conf_set76->set('input_value', round($this->configuration('maxBanTime') / 1440));
            $conf_set76->set('input_maxlength', 3);
        
            $form->set('fields', $form->merge(array($conf_set58, $conf_set76)));
            $data['content'] .= $form->output();
        
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings/', $this->localization->string('back'), '<p>', '</p>');
        }
        
        if ($this->postAndGet('action') == 'security') {
            $data['content'] .= '<h1>' . $this->localization->string('security') . '</h1>';
        
            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/settings/?action=editsecurity');
        
            $input29 = $this->model('ParsePage');
            $input29->load('forms/input');
            $input29->set('label_for', 'conf_set29');
            $input29->set('label_value', $this->localization->string('floodtime'));
            $input29->set('input_id', 'conf_set29');
            $input29->set('input_name', 'conf_set29');
            $input29->set('input_value', $this->configuration('floodTime'));
            $input29->set('input_maxlength', 3);
        
            $input1 = $this->model('ParsePage');
            $input1->load('forms/input');
            $input1->set('label_for', 'conf_set1');
            $input1->set('label_value', $this->localization->string('passkey'));
            $input1->set('input_id', 'conf_set1');
            $input1->set('input_name', 'conf_set1');
            $input1->set('input_value', $this->configuration('keypass'));
            $input1->set('input_maxlength', 25);
        
            // quarantine time
            $quarantine = array(0 => "" . $this->localization->string('disabled') . "", 21600 => "6 " . $this->localization->string('hours') . "", 43200 => "12 " . $this->localization->string('hours') . "", 86400 => "24 " . $this->localization->string('hours') . "", 129600 => "36 " . $this->localization->string('hours') . "", 172800 => "48 " . $this->localization->string('hours') . "");
        
            $options = '<option value="' . $this->configuration('quarantine') . '">' . $quarantine[$this->configuration('quarantine')] . '</option>';
            foreach($quarantine as $k => $v) {
                if ($k != $this->configuration('quarantine')) {
                    $options .= '<option value="' . $k . '">' . $v . '</option>';
                }
            }
        
            $select_set3 = $this->model('ParsePage');
            $select_set3->load('forms/select');
            $select_set3->set('label_for', 'conf_set3');
            $select_set3->set('label_value', $this->localization->string('quarantinetime'));
            $select_set3->set('select_id', 'conf_set3');
            $select_set3->set('select_name', 'conf_set3');
            $select_set3->set('options', $options);
        
            // transfer protocol
            $tProtocol = array('HTTPS' => 'HTTPS', 'HTTP' => 'HTTP', 'auto' => 'auto');
        
            $transfer_protocol = $this->configuration('transferProtocol');
            if (empty($this->configuration('transferProtocol'))) $transfer_protocol = 'auto';
            
            $options = '<option value="' . $transfer_protocol . '">' . $tProtocol[$transfer_protocol] . '</option>';
        
            foreach($tProtocol as $k => $v) {
                if ($k != $transfer_protocol) {
                    $options .= '<option value="' . $k . '">' . $v . '</option>';
                }
            }
        
            $select_set21 = $this->model('ParsePage');
            $select_set21->load('forms/select');
            $select_set21->set('label_for', 'conf_set21');
            $select_set21->set('label_value', 'Transfer protocol');
            $select_set21->set('select_id', 'conf_set21');
            $select_set21->set('select_name', 'conf_set21');
            $select_set21->set('options', $options);
        
            // reCAPTCHA site key
            $captcha_sitekey = $this->model('ParsePage');
            $captcha_sitekey->load('forms/input');
            $captcha_sitekey->set('label_for', 'recaptcha_sitekey');
            $captcha_sitekey->set('label_value', 'reCAPTCHA site key');
            $captcha_sitekey->set('input_id', 'recaptcha_sitekey');
            $captcha_sitekey->set('input_name', 'recaptcha_sitekey');
            $captcha_sitekey->set('input_value', $this->configuration('recaptcha_sitekey'));
            $captcha_sitekey->set('input_maxlength', 50);
        
            // reCAPTCHA secret key
            $captcha_secret = $this->model('ParsePage');
            $captcha_secret->load('forms/input');
            $captcha_secret->set('label_for', 'recaptcha_secretkey');
            $captcha_secret->set('label_value', 'reCAPTCHA secret key');
            $captcha_secret->set('input_id', 'recaptcha_secretkey');
            $captcha_secret->set('input_name', 'recaptcha_secretkey');
            $captcha_secret->set('input_value', $this->configuration('recaptcha_secretkey'));
            $captcha_secret->set('input_maxlength', 50);
        
            $form->set('fields', $form->merge(array($input29, $input1, $select_set3, $select_set21, $captcha_sitekey, $captcha_secret)));
            $data['content'] .= $form->output();
        
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings/', $this->localization->string('back'), '<p>', '</p>');
        }
        
        $data['content'] .= '<p>' . $this->sitelink(HOMEDIR . 'adminpanel', $this->localization->string('adminpanel')) . '<br />';
        $data['content'] .= $this->homelink() . '</p>';

        // Pass data to the view
        return $data;
    }

    /**
     * Admin list
     */
    public function adminlist()
    {
        // Users data
        $data['user'] = $this->user_data;
        $data['tname'] = '{@localization[modlist]}}';
        $data['content'] = '';

        if (!$this->user->check_permissions(basename(__FILE__))) $this->redirection('../?auth_error');

        $data['content'] .= '<p><img src="../themes/images/img/user.gif" alt=""> <b>' . $this->localization->string('adminlistl') . '</b></p>'; 

        $num_items = $this->user->total_admins();
        $items_per_page = 10;

        $navigation = new Navigation($items_per_page, $num_items, $this->postAndGet('page'), HOMEDIR . 'adminpanel/adminlist/?'); // start navigation

        $limit_start = $navigation->start()['start']; // starting point
        $end = $navigation->start()['end']; // ending point

        if ($num_items > 0) {
            foreach ($this->db->query("SELECT id, name, perm FROM vavok_users WHERE perm='101' OR perm='102' OR perm='103' OR perm='105' OR perm='106' ORDER BY perm LIMIT $limit_start, $items_per_page") as $item) {
                if ($item['perm'] == 101 or $item['perm'] == 102 or $item['perm'] == 103 or $item['perm'] == 105 or $item['perm'] == 106) {
                    $lnk = '<div class="a">' . $this->sitelink(HOMEDIR . 'users/u/' . $item['id'], $item['name']) . ' - ' . $this->user->user_status($item['perm']) . '</div>';
                    $data['content'] .= $lnk . '<br>';
                }
            }
        }

        $data['content'] .= $navigation->get_navigation();

        $data['content'] .= '<p>' . $this->sitelink('./', $this->localization->string('adminpanel')) . '<br>';
        $data['content'] .= $this->homelink() . '</p>';

        return $data;
    }

    public function unconfirmed_reg()
    {
        // Users data
        $data['user'] = $this->user_data;
        $data['tname'] = '{@localization[uncomfreg]}}';
        $data['content'] = '';

        if (!$this->user->check_permissions(basename(__FILE__))) $this->redirection('../?auth_error');

        if ($this->postAndGet('action') == 'conf' && !empty($this->postAndGet('usr'))) {
            $fields = array('regche', 'regkey');
            $values = array('', '');
            $this->user->update_user($fields, $values, $this->postAndGet('usr'));
        
            $vav_name = $this->user->getnickfromid($this->postAndGet('usr'));
        
            $message = $this->localization->string('hello') . " " . $vav_name . "!\r\n\r\n" . $this->localization->string('sitemod') . " " . $this->configuration('homeBase') . " " . $this->localization->string('confirmedreg') . ".\r\n" . $this->localization->string('youcanlog') . ".\r\n\r\n" . $this->localization->string('bye') . "!\r\n\r\n\r\n\r\n" . $this->user->getnickfromid($this->user->user_id()) . "\r\n" . ucfirst($this->configuration('homeBase'));
            $newMail = new Mailer;
            $newMail->queue_email($this->user->user_info('email', $this->postAndGet('usr')), $this->localization->string('msgfrmst') . " " . $this->configuration('title'), $message, '', '', 'high');
        
            $this->redirection(HOMEDIR . 'adminpanel/unconfirmed_reg/?isset=mp_ydelconf');
        }

        if (empty($this->postAndGet('action'))) {
            $noi = $this->user->total_unconfirmed();
            $num_items = $noi;
            $items_per_page = 20;
            $num_pages = ceil($num_items / $items_per_page);
        
            if (($this->postAndGet('page') > $num_pages) && $this->postAndGet('page') != 1) $page = $num_pages;
            $limit_start = ($this->postAndGet('page')-1) * $items_per_page;
            if ($limit_start < 0) {
                $limit_start = 0;
            } 

            $sql = "SELECT uid, regche, regdate, lastvst FROM vavok_profil WHERE regche='1' OR regche='2' ORDER BY regdate LIMIT $limit_start, $items_per_page";

            if ($num_items > 0) {
                foreach ($this->db->query($sql) as $item) {
                    $lnk = $this->sitelink(HOMEDIR . 'users/u/' . $item['uid'], $this->user->getnickfromid($item['uid'])) . ' (' . $this->correctDate($item['regdate'], 'd.m.Y. / H:i') . ')';
                    if ($item['regche'] == 1) {
                        $bt = $this->localization->string('notconfirmed') . '!';
                        $bym = $this->sitelink(HOMEDIR . 'adminpanel/unconfirmed_reg/?action=conf&usr=' . $item['uid'], $this->localization->string('confirms'));
                    } else {
                        $bt = 'Confirmed';
                    }

                    $data['content'] .= '<p>' . $lnk . ' IP: ' . $this->user->user_info('ipadd', $item['uid']) . ' ' . $this->localization->string('browser') . ': ' . $this->user->user_info('browser', $item['uid']) . ' ' . $bym . '</p>';
                }
            } else {
                $data['content'] .= '<p><img src="../themes/images/img/reload.gif" alt="" /> ' . $this->localization->string('emptyunconf') . '!</p>';
            }
        
            $navigation = new Navigation($items_per_page, $num_items, $this->postAndGet('page'), HOMEDIR . 'adminpanel/unconfirmed_reg/');
        
            $data['content'] .= '<div class="mt-5">';
                $data['content'] .= $navigation->get_navigation();
            $data['content'] .= '</div>';
        }

        $data['content'] .= '<p>' . $this->sitelink('./', $this->localization->string('adminpanel')) . '<br />';
        $data['content'] .= $this->homelink() . '</p>';

        return $data;
    }

    /**
     * Statistics
     */
    public function statistics()
    {
        // Users data
        $data['user'] = $this->user_data;
        $data['tname'] = '{@localization[sitestats]}}';
        $data['content'] = '';

        if (!$this->user->administrator()) $this->redirection('../?errorAuth');
  
        $data['content'] .= '<p>' . $this->sitelink(HOMEDIR . 'pages/statistics', '{@localization[visitstats]}}') . '<br />';
        $data['content'] .= $this->sitelink('../pages/online', '{@localization[usronline]}}') . '</p>';
        
        $data['content'] .= '<p>' . $this->sitelink('./', '{@localization[adminpanel]}}') . '<br>';
        $data['content'] .= $this->homelink() . '</p>';

        return $data;
    }

    /**
     * Users
     */
    public function users()
    {
        // Users data
        $data['user'] = $this->user_data;
        $data['tname'] = '{@localization[usrprofile]}}';
        $data['content'] = '';

        if (!$this->user->administrator()) $this->redirection('./?error=noauth');

        $user = $this->check($this->postAndGet('users'));
        $users_id = $this->user->getidfromnick($user);

        if (empty($this->postAndGet('action'))) {
            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/users/?action=edit');

            $input_users = $this->model('ParsePage');
            $input_users->load('forms/input');
            $input_users->set('label_for', 'users');
            $input_users->set('label_value', $this->localization->string('chooseuser') . ':');
            $input_users->set('input_name', 'users');
            $input_users->set('input_id', 'users');
            $input_users->set('input_maxlength', 20);

            $form->set('website_language[save]', $this->localization->string('showdata'));
            $form->set('fields', $input_users->output());
            $data['content'] .= $form->output();
        }

        // change profile
        if ($this->postAndGet('action') == 'edit') {
            if (!empty($user) && $this->user->username_exists($user) && $this->user->id_exists($users_id)) {
                $data['content'] .= '<img src="{@HOMEDIR}}themes/images/img/profiles.gif" alt="Profile" /> ' . $this->localization->string('usrprofile') . ' ' . $user . '<br>';

                if ($this->user->show_username() != $this->configuration('adminNick') && $user == $this->configuration('adminNick')) {
                    $data['content'] .= '<br>' . $this->localization->string('noauthtoedit') . '!<br>';
                    return $data;
                    exit;
                }

                if (($this->user->show_username() != $this->configuration('adminNick')) && ($this->user->user_info('perm', $users_id) == 101 || $this->user->user_info('perm', $users_id) == 102 || $this->user->user_info('perm', $users_id) == 103 || $this->user->user_info('perm', $users_id) == 105) && $this->user->show_username() != $user) {
                    $data['content'] .= '<br>' . $this->localization->string('noauthtoban') . '!<br>';
                    return $data;
                    exit;
                }

                $casenick = strcasecmp($user, $this->user->show_username());

                if ($casenick == 0) $data['content'] .= '<p><b><font color="red">' . $this->localization->string('myprofile') . '!</font></b></p>';

                if ($this->user->user_info('banned', $users_id) == 1) $data['content'] .= '<p><font color="#FF0000"><b>' . $this->localization->string('confban') . '</b></font></p>';
        
                if ($this->user->user_info('regche', $users_id) == 1) $data['content'] .= '<p><font color="#FF0000"><b>' . $this->localization->string('notactivated') . '</b></font></p>';
        
                $form = $this->model('ParsePage');
                $form->load('forms/form');
                $form->set('form_method', 'post');
                $form->set('form_action', HOMEDIR . 'adminpanel/users/?action=upgrade&amp;users=' . $user);
        
                $userx_access = (int)$this->user->user_info('perm', $users_id);
        
                if ($_SESSION['permissions'] == 101 && $this->user->show_username() == $this->configuration('adminNick')) {
                    $array_dostup = array(101 => $this->localization->string('access101'), 102 => $this->localization->string('access102'), 103 => $this->localization->string('access103'), 105 => $this->localization->string('access105'), 106 => $this->localization->string('access106'), 107 => $this->localization->string('access107'));
        
                    if ($userx_access == 0 || empty($userx_access)) $userx_access = 107;
        
                    $options = '<option value="' . $userx_access . '">' . $array_dostup[$userx_access] . '</option>';
                    foreach($array_dostup as $k => $v) {
                        if ($k != $userx_access) {
                            $options .= '<option value="' . $k . '">' . $v . '</option>';
                        }
                    }
                }
        
                $udd7 = $this->model('ParsePage');
                $udd7->load('forms/select');
                $udd7->set('label_for', 'udd7');
                $udd7->set('label_value', $this->localization->string('accesslevel'));
                $udd7->set('select_id', 'udd7');
                $udd7->set('select_name', 'udd7');
                $udd7->set('options', $options);
        
                $udd1 = $this->model('ParsePage');
                $udd1->load('forms/input');
                $udd1->set('label_for', 'udd1');
                $udd1->set('label_value', $this->localization->string('newpassinfo'));
                $udd1->set('input_id', 'udd1');
                $udd1->set('input_name', 'udd1');
        
                $udd2 = $this->model('ParsePage');
                $udd2->load('forms/input');
                $udd2->set('label_for', 'udd2');
                $udd2->set('label_value', $this->localization->string('city'));
                $udd2->set('input_id', 'udd2');
                $udd2->set('input_name', 'udd2');
                $udd2->set('input_value', $this->user->user_info('city', $users_id));
        
                $udd3 = $this->model('ParsePage');
                $udd3->load('forms/input');
                $udd3->set('label_for', 'udd3');
                $udd3->set('label_value', $this->localization->string('aboutyou'));
                $udd3->set('input_id', 'udd3');
                $udd3->set('input_name', 'udd3');
                $udd3->set('input_value', $this->user->user_info('about', $users_id));
        
                $udd4 = $this->model('ParsePage');
                $udd4->load('forms/input');
                $udd4->set('label_for', 'udd4');
                $udd4->set('label_value', $this->localization->string('yemail'));
                $udd4->set('input_id', 'udd4');
                $udd4->set('input_name', 'udd4');
                $udd4->set('input_value', $this->user->user_info('email', $users_id));
        
                $udd5 = $this->model('ParsePage');
                $udd5->load('forms/input');
                $udd5->set('label_for', 'udd5');
                $udd5->set('label_value', $this->localization->string('site'));
                $udd5->set('input_id', 'udd5');
                $udd5->set('input_name', 'udd5');
                $udd5->set('input_value', $this->user->user_info('site', $users_id));
        
                $udd13 = $this->model('ParsePage');
                $udd13->load('forms/input');
                $udd13->set('label_for', 'udd13');
                $udd13->set('label_value', $this->localization->string('browser'));
                $udd13->set('input_id', 'udd13');
                $udd13->set('input_name', 'udd13');
                $udd13->set('input_value', $this->user->user_info('browser', $users_id));
        
                $udd29 = $this->model('ParsePage');
                $udd29->load('forms/input');
                $udd29->set('label_for', 'udd29');
                $udd29->set('label_value', $this->localization->string('name'));
                $udd29->set('input_id', 'udd29');
                $udd29->set('input_name', 'udd29');
                $udd29->set('input_value', $this->user->user_info('firstname', $users_id));
        
                $udd40 = $this->model('ParsePage');
                $udd40->load('forms/input');
                $udd40->set('label_for', 'udd40');
                $udd40->set('label_value', $this->localization->string('perstatus'));
                $udd40->set('input_id', 'udd40');
                $udd40->set('input_name', 'udd40');
                $udd40->set('input_value', $this->user->user_info('status', $users_id));
        
                if ($this->user->user_info('subscribed', $users_id) == 1) {
                    $value = $this->localization->string('subscribed');
                } else {
                    $value = $this->localization->string('notsubed');
                }
                $subscribed = $this->model('ParsePage');
                $subscribed->load('forms/input_readonly');
                $subscribed->set('label_for', 'subscribed');
                $subscribed->set('label_value', $this->localization->string('sitenews'));
                $subscribed->set('input_id', 'subscribed');
                $subscribed->set('input_name', 'subscribed');
                $subscribed->set('input_placeholder', $value);
        
                $allban = $this->model('ParsePage');
                $allban->load('forms/input_readonly');
                $allban->set('label_for', 'allban');
                $allban->set('label_value', $this->localization->string('numbbans'));
                $allban->set('input_id', 'allban');
                $allban->set('input_placeholder', (int)$this->user->user_info('allban', $users_id));
        
                $lastvst = $this->model('ParsePage');
                $lastvst->load('forms/input_readonly');
                $lastvst->set('label_for', 'lastvst');
                $lastvst->set('label_value', $this->localization->string('lastvst'));
                $lastvst->set('input_id', 'lastvst');
                $lastvst->set('input_placeholder', $this->correctDate($this->user->user_info('lastvisit', $users_id), 'j.m.Y. / H:i'));
        
                $ip = $this->model('ParsePage');
                $ip->load('forms/input_readonly');
                $ip->set('label_for', 'ip');
                $ip->set('label_value', 'IP');
                $ip->set('input_id', 'ip');
                $ip->set('input_placeholder', $this->user->user_info('ipaddress', $users_id));
        
                $form->set('fields', $form->merge(array($udd7, $udd1, $udd2, $udd3, $udd4, $udd5, $udd13, $udd29, $udd40, $subscribed, $allban, $lastvst, $ip)));
                $data['content'] .= $form->output();
        
                $data['content'] .= '<p>';
                if ($userx_access > 106) {
                    $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/users/?action=poddel&amp;users=' . $user, $this->localization->string('deluser'), '<b>', '</b>') . '<br />';
                }
                // Website permissions for various sections
                if (file_exists('specperm.php')) {
                    $data['content'] .= $this->sitelink('specperm.php?users=' . $users_id, 'Change access permissions') . '<br />';
                }
        
                $data['content'] .= '</p>';
            } else {
                $data['content'] .= $this->localization->string('usrnoexist') . '!';
            }
        
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/users/', $this->localization->string('back'), '<p>', '</p>');
        }
        
        // update changes
        if ($this->postAndGet('action') == 'upgrade') {
            $udd1 = !empty($this->postAndGet('udd1')) ? $this->postAndGet('udd1') : '';
            $udd2 = !empty($this->postAndGet('udd2')) ? $this->postAndGet('udd2') : '';
            $udd3 = !empty($this->postAndGet('udd3')) ? $this->postAndGet('udd3') : '';
            $udd4 = !empty($this->postAndGet('udd4')) ? $this->postAndGet('udd4') : '';
            $udd5 = !empty($this->postAndGet('udd5')) ? $this->postAndGet('udd5') : '';
            $udd6 = !empty($this->postAndGet('udd6')) ? $this->postAndGet('udd6') : '';
            $udd7 = !empty($this->postAndGet('udd7')) ? $this->postAndGet('udd7') : ''; // access level
            $udd8 = !empty($this->postAndGet('udd8')) ? $this->postAndGet('udd8') : '';
            $udd9 = !empty($this->postAndGet('udd9')) ? $this->postAndGet('udd9') : '';
            $udd10 = !empty($this->postAndGet('udd10')) ? $this->postAndGet('udd10') : '';
            $udd11 = !empty($this->postAndGet('udd11')) ? $this->postAndGet('udd11') : '';
            $udd12 = !empty($this->postAndGet('udd12')) ? $this->postAndGet('udd12') : '';
            $udd13 = !empty($this->postAndGet('udd13')) ? $this->postAndGet('udd13') : '';
            $udd29 = !empty($this->postAndGet('udd29')) ? $this->postAndGet('udd29') : '';
            $udd40 = !empty($this->postAndGet('udd40')) ? $this->postAndGet('udd40') : '';
            $udd43 = !empty($this->postAndGet('udd43')) ? $this->postAndGet('udd43') : '';
        
            if ($this->user->validate_email($udd4)) {
                if (empty($udd5) || $this->validateUrl($udd5) === true) {
                    if (!empty($users_id)) {
                        if (!empty($udd6)) {
                            list($uday, $umonth, $uyear) = explode(".", $udd6);
                            $udd6 = mktime('0', '0', '0', $umonth, $uday, $uyear);
                        }

                        if (!empty($udd1)) $newpass = $this->user->password_encrypt($udd1);
        
                        // Update password
                        if (!empty($newpass)) $this->user->update_user('pass', $this->replaceNewLines($newpass), $users_id);
        
                        // Update default access permissions
                        if ($udd7 != $this->user->user_info('perm', $users_id)) $this->user->update_default_permissions($users_id, $udd7);
        
                        // Update data
                        $this->user->update_user(
                            array('city', 'about', 'email', 'site', 'rname', 'perstat', 'browsers'),
                            array($this->replaceNewLines($this->check($udd2)), $this->check($udd3), $this->replaceNewLines(htmlspecialchars(stripslashes(strtolower($udd4)))), $this->replaceNewLines($this->check($udd5)), $this->replaceNewLines($this->check($udd29)), $this->replaceNewLines($this->check($udd40)), $this->replaceNewLines($this->check($udd13))), $users_id
                        );
        
                        $data['content'] .= $this->localization->string('usrdataupd') . '!<br>';
        
                        if (!empty($udd1)) {
                            $data['content'] .= '<font color=red>' . $this->localization->string('passchanged') . ': ' . $udd1 . '</font> <br>';
                        }
        
                        $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/users/', $this->localization->string('changeotheruser')) . '<br>';
                    } else {
                        $data['content'] .= $this->localization->string('usrnoexist') . '!<br>';
                    }
                } else {
                    $data['content'] .= $this->localization->string('urlnotok') . '!<br>';
                } 
            } else {
                $data['content'] .= $this->localization->string('emailnotok') . '<br>';
            }

            $data['content'] .= '<br>' . $this->sitelink(HOMEDIR . 'adminpanel/users/?action=edit&amp;users=' . $user, $this->localization->string('back'));
        }
        
        // confirm delete
        if ($this->postAndGet('action') == 'poddel') {
            $data['content'] .= $this->localization->string('confusrdel') . ' <b>' . $user . '</b>?<br><br>';
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/users/?action=deluser&amp;users=' . $user, $this->localization->string('deluser'), '<b>', '</b>');
        
            $data['content'] .= '<br>' . $this->sitelink(HOMEDIR . 'adminpanel/users/?action=edit&amp;users=' . $user, $this->localization->string('back'));
        } 
        
        // delete user
        if ($this->postAndGet('action') == 'deluser') {
            if ($user != $this->configuration('adminNick')) {
                if ($this->user->user_info('perm', $users_id) < 101 || $this->user->user_info('perm', $users_id) > 105) {
                    $this->user->delete_user($user);
                    $data['content'] .= $this->localization->string('usrdeleted') . '!<br>';
        
                    $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/users/', $this->localization->string('changeotheruser'), '<p>', '</p>');
                } else {
                    $data['content'] .= $this->localization->string('noaccessdel') . '<br>';
                    $data['content'] .= '<br>' . $this->sitelink(HOMEDIR . 'adminpanel/users/?action=edit&amp;users=' . $user, $this->localization->string('back'));
                }
            }
        }
        
        $data['content'] .= '<p>' . $this->sitelink(HOMEDIR . 'adminpanel', $this->localization->string('adminpanel')) . '<br>';
        $data['content'] .= $this->homelink() . '</p>';

        return $data;
    }

    /**
     * IP ban
     */
    public function ipban()
    {
        // Users data
        $data['user'] = $this->user_data;
        $data['tname'] = 'IP Ban';
        $data['content'] = '';

        if (!$this->user->administrator()) $this->redirection('../?auth_error');

        if ($this->postAndGet('action') == 'zaban' && $this->user->administrator()) {
            $ips = $this->check($this->postAndGet('ips'));
        
            if (!empty($ips) && substr_count($ips, '.') == 3) $this->writeDataFile('ban.dat', "|$ips|" . PHP_EOL, 1);
        
            $data['notification'] = $this->showSuccess('IP has been banned');
        }

        if ($this->postAndGet('action') == 'razban' && $this->user->administrator()) {
            if (!empty($this->postAndGet('id')) || $this->postAndGet('id') == 0) $id = $this->postAndGet('id');
        
            if (isset($id)) {
                $file = $this->getDataFile('ban.dat');
                unset($file[$id]);
        
                $file_data = '';
                foreach ($file as $key => $value) {
                    $file_data .= $value;
                }
        
                $this->writeDataFile('ban.dat', $file_data);
            }

            // Notification
            // Update localization
            $data['notification'] = $this->showSuccess('IP ban has been removed');
        }

        if ($this->postAndGet('action') == 'delallip' && ($_SESSION['permissions'] == 101 or $_SESSION['permissions'] == 102)) {
            $this->clearFile('../used/ban.dat');

            $this->redirection(HOMEDIR . 'adminpanel/ipban');
        }

        $file = $this->getDataFile('ban.dat');
        $total = count($file);

        $navigation = new Navigation(10, $total, HOMEDIR . 'adminpanel/ipban/?'); // start navigation

        $limit_start = $navigation->start()['start']; // starting point
        
        if ($total < $limit_start + 10) {
            $end = $total;
        } else {
            $end = $limit_start + 10;
        }
        
        for ($i = $limit_start; $i < $end; $i++) {
            $file = $this->getDataFile('ban.dat');
            $file = array_reverse($file);
            $file_data = explode("|", $file[$i]);
            $i2 = round($i + 1);
        
            $num = $total - $i-1;
        
            $data['content'] .= $i2 . '. ' . $file_data[1] . ' <br>' . $this->sitelink(HOMEDIR . 'adminpanel/ipban/?action=razban&amp;id=' . $num, $this->localization->string('delban')) . '<hr>';
        } 

        if ($total < 1) {
            $data['content'] .= '<p><img src="{@HOMEDIR}}themes/images/img/reload.gif" alt="" /> ' . $this->localization->string('emptylist') . '</p>';
        }

        $data['content'] .= $navigation->get_navigation();

        $data['content'] .= '<hr>';

        $form = $this->model('ParsePage');
        $form->load('forms/form');
        $form->set('form_method', 'post');
        $form->set('form_action', HOMEDIR . 'adminpanel/ipban/?action=zaban');

        $input = $this->model('ParsePage');
        $input->load('forms/input');
        $input->set('label_for', 'ips');
        $input->set('label_value', $this->localization->string('iptoblock'));
        $input->set('input_name', 'ips');
        $input->set('input_id', 'ips');

        $form->set('website_language[save]', $this->localization->string('confirm'));
        $form->set('fields', $input->output());
        $data['content'] .= $form->output();

        $data['content'] .= '<hr>';

        $data['content'] .= '<p>' . $this->localization->string('ipbanexam') . '</p>';
        $data['content'] .= '<p>' . $this->localization->string('allbanips') . ': ' . $total . '</p>';

        if ($total > 1) {
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/ipban/?action=delallip', $this->localization->string('dellist'), '<p>', '</p>');
        }

        $data['content'] .= '<p>' . $this->sitelink(HOMEDIR . 'adminpanel', $this->localization->string('adminpanel')) . '<br>';
        $data['content'] .= $this->homelink() . '</p>';

        return $data;
    }

    /**
     * System check
     */
    public function systemcheck()
    {
        // Users data
        $data['user'] = $this->user_data;
        $data['tname'] = 'System Check';
        $data['content'] = '';

        if (!$this->user->administrator(101)) $this->redirection(HOMEDIR);

        function prev_dir($string) {
            $d1 = strrpos($string, "/");
            $d2 = substr($string, $d1, 999);
            $string = str_replace($d2, "", $string);
        
            return $string;
        }

        switch ($this->postAndGet('action')) {
            default:
                $data['content'] .= '<img src="' . HOMEDIR . 'themes/images/img/menu.gif" alt=""> ' . $this->localization->string('checksys') . '<hr>';

                $did = $this->check($this->postAndGet('did'));

                if (!empty($did) && (!is_dir(APPDIR . "used" . "$did") || !file_exists(APPDIR . "used" . "$did"))) {
                    header('Location: ' . HOMEDIR . 'adminpanel/systemcheck');
                    exit;
                }

                foreach (scandir(APPDIR . "used" . "$did") as $value) {
                    if ($value != "." && $value != ".." && $value != ".htaccess") {
                        if (is_file(APPDIR . "used" . "$did/$value")) {
                            $files[] = "$did/$value";
                        } elseif (is_dir(APPDIR . "used" . "$did/$value")) {
                            $dires[] = "$did/$value";
                        }
                    }
                }
        
                if ($did == '') {
                    if (file_exists(APPDIR . "used/.htaccess")) {
                        $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/systemcheck/?action=pod_chmod&amp;file=/.htaccess', '[Chmod - ' . $this->permissions(APPDIR . "used/.htaccess") . ']') . ' - <font color="#00FF00">' . $this->localization->string('file') . ' .htaccess ' . $this->localization->string('exist') . '</font><br>';
        
                        if (is_writeable(APPDIR . "used/.htaccess")) {
                            $data['content'] .= '<font color="#FF0000">' . $this->localization->string('wrhtacc') . '</font><br>';
                        }
                    } else {
                        $data['content'] .= '<font color="#FF0000">' . $this->localization->string('warning') . '!!! ' . $this->localization->string('file') . ' .htaccess ' . $this->localization->string('noexist') . '!<br></font>';
                    }
                }
        
                if ((count($files) + count($dires)) > 0) {
                    if (count($files) > 0) {
                        if (!empty($did)) {
                            if (file_exists(APPDIR . "used" . "$did/.htaccess")) {
                                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/systemcheck/?action=pod_chmod&amp;file=' . $did . '/.htaccess', '[CHMOD - ' . $this->permissions(APPDIR . "used" . "$did/.htaccess") . ']') . ' - <font color="#00FF00">' . $this->localization->string('file') . ' .htaccess ' . $this->localization->string('exist') . '</font><br>';
        
                                if (is_writeable(APPDIR . "used" . "$did/.htaccess")) {
                                    $data['content'] .= '<font color="#FF0000">' . $this->localization->string('wrhtacc') . '</font><br>';
                                }
                            }
                        }

                        $data['content'] .= $this->localization->string('filecheck') . ': <br />';

                        $usedfiles = 0;
                        foreach ($files as $value) {
                            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/systemcheck/?action=pod_chmod&amp;file=' . $value, '[CHMOD - ' . $this->permissions(APPDIR . "used" . "$value") . ']') . ' - used' . $value . ' (' . $this->formatSize(filesize(APPDIR . "used" . "$value")) . ') - ';
        
                            if (is_writeable(APPDIR . "used" . "$value")) {
                                $data['content'] .= '<font color="#00FF00">' . $this->localization->string('filewrit') . '</font><br>';
                            } else {
                                $data['content'] .= '<font color="#FF0000">' . $this->localization->string('filenowrit') . '</font><br>';
                            }
                            $usedfiles += filesize(APPDIR . "used" . "$value");
                        }
                        $data['content'] .= '<hr>' . $this->localization->string('filessize') . ': ' . $this->formatSize($usedfiles) . '<hr>';
                    }

                    if (count($dires) > 0) {
                        $data['content'] .= $this->localization->string('checkdirs') . ': <br>';

                        foreach ($dires as $value) {
                            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/systemcheck/?action=pod_chmod&amp;file=' . $value, '[CHMOD - ' . $this->permissions(APPDIR . "used" . "$value") . ']') . ' - ' . $this->sitelink(HOMEDIR . 'adminpanel/systemcheck/?did=' . $value, 'used' . $value) . ' (' . $this->formatSize($this->readDirectory(APPDIR . "used" . "$value")) . ') - ';
        
                            if (is_writeable(APPDIR . "used" . "$value")) {
                                $data['content'] .= '<font color="#00FF00">' . $this->localization->string('filewrit') . '</font><br>';
                            } else {
                                $data['content'] .= '<font color="#FF0000">' . $this->localization->string('filenowrit') . '</font><br>';
                            }
        
                            $useddires = $this->readDirectory(APPDIR . "used" . "$value");
                        }
                        $data['content'] .= '<hr>' . $this->localization->string('dirsize') . ': ' . $this->formatSize($useddires) . '<hr>';
                    }
                } else {
                    $data['content'] .= $this->localization->string('dirempty') . '!<hr>';
                }

                if ($did != '') {
                    if (prev_dir($did) != '') {
                        $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/systemcheck/?did=' . prev_dir($did), '<img src="' . HOMEDIR . 'themes/images/img/reload.gif" alt=""> ' . $this->localization->string('back')) . '<br>';
                    }
                    $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/systemcheck/', $this->localization->string('checksys')) . '<br>';
                }

                break; 
                // CHMOD
            case ('pod_chmod'):
                $data['content'] .= '<img src="' . HOMEDIR . 'themes/images/img/menu.gif" alt=""> ' . $this->localization->string('chchmod') . '<hr>';
        
                if ($this->postAndGet('file') && file_exists(APPDIR . "used/" . $this->postAndGet('file'))) {
                    $data['content'] .= '<form action="' . HOMEDIR . 'adminpanel/systemcheck/?action=chmod" method=post>';
                    if (is_file(APPDIR . "used/" . $this->postAndGet('file'))) {
                        $data['content'] .= $this->localization->string('file') . ': ../used' . $this->postAndGet('file') . '<br>';
                    } elseif (is_dir(APPDIR . "used/" . $this->postAndGet('file'))) {
                        $data['content'] .= $this->localization->string('folder') . ': ../used' . $this->postAndGet('file') . '<br>';
                    } 
                    $data['content'] .= 'CHMOD: <br><input type="text" name="mode" value="' . $this->permissions(APPDIR . "used/" . $this->postAndGet('file')) . '" maxlength="3" /><br>
                    <input name="file" type="hidden" value="' . $this->postAndGet('file') . '" />
                    <input type=submit value="' . $this->localization->string('save') . '"></form><hr>';
                } else {
                    $data['content'] .= 'No file name!<hr>';
                }
        
                if (!empty(prev_dir($this->postAndGet('file')))) {
                    $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/systemcheck/?did=' . prev_dir($this->postAndGet('file')), $this->localization->string('back')) . '<br>';
                }
        
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/systemcheck/', $this->localization->string('checksys')) . '<br>';
            break;
        
            case ('chmod'):
                if (!empty($this->postAndGet('file')) && !empty($this->postAndGet('mode'))) {
                    if (chmod(APPDIR . "used/" . $this->postAndGet('file'), octdec($this->postAndGet('mode'))) != false) {
                        $data['content'] .= $this->localization->string('chmodok') . '!<hr>';
                    } else {
                        $data['content'] .= $this->localization->string('chmodnotok') . '!<hr>';
                    }
                } else {
                    $data['content'] .= $this->localization->string('noneededdata') . '!<hr>';
                } 
        
                if (!empty(prev_dir($this->postAndGet('file')))) {
                    $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/systemcheck/?did=' . prev_dir($this->postAndGet('file')), $this->localization->string('back')) . '<br>';
                }
        
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/systemcheck/', $this->localization->string('checksys')) . '<br>';
            break;
        }
        
        $data['content'] .= '<p>';
        $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel', $this->localization->string('adminpanel')) . '<br />';
        $data['content'] .= $this->homelink();
        $data['content'] .= '</p>';

        return $data;
    }

    /**
     * Page search
     */
    public function pagesearch()
    {
        // Users data
        $page_data['user'] = $this->user_data;
        $page_data['tname'] = '{@localization[search]}}';
        $page_data['content'] = '';

        if (!$this->user->administrator()) $this->redirection(HOMEDIR);

        if (empty($this->postAndGet('action'))) {
            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagesearch/?action=stpc');
            $form->set('website_language[save]', $this->localization->string('search'));

            $input = $this->model('ParsePage');
            $input->load('forms/input');
            $input->set('label_for', 'stext');
            $input->set('label_value', 'Page name:');
            $input->set('input_name', 'stext');
            $input->set('input_id', 'stext');
            $input->set('input_maxlength', 30);

            $form->set('fields', $input->output());
            $page_data['content'] .= $form->output();

            $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager', $this->localization->string('back'), '<p>', '<br />');
        } elseif ($this->postAndGet('action') == 'stpc') {
            if (empty($this->postAndGet('stext'))) {
                $page_data['content'] .= '<p>Please fill all fields</p>';
            } else {
                // begin search
                $where_table = "pages";
                $cond = "pname";
                $select_fields = "*";
                $ord_fields = "pubdate DESC";
        
                $noi = $this->db->countRow($where_table, "" . $cond . " LIKE '%" . $stext . "%'");
                $items_per_page = 10;
        
                $navigation = new Navigation($items_per_page, $noi, $this->postAndGet('page'), HOMEDIR . 'adminpanel/pagesearch/?'); // start navigation
        
                $limit_start = $navigation->start()['start']; // starting point
        
                $sql = "SELECT {$select_fields} FROM {$where_table} WHERE pname LIKE '%{$stext}%' OR tname LIKE '%{$stext}%' ORDER BY {$ord_fields} LIMIT $limit_start, $items_per_page";
        
                foreach ($this->db->query($sql) as $item) {
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
                            $itemLang = ' (' . mb_strtolower($item['lang']) . ')';
                        } else {
                                $itemLang = '';
                            }

                        $tlink = $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=show&amp;file=' . $item['file'], $tname . $itemLang) . '<br />';
                    }

                    $page_data['content'] .= $tlink;
                }

                $page_data['content'] .= $navigation->get_navigation();
            }

            $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagesearch', $this->localization->string('back'), '<p>', '<br />');
        }

        $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel', $this->localization->string('adminpanel'), '', '<br />');
        $page_data['content'] .= $this->homelink('', '</p>');

        return $page_data;
    }

    /**
     * Blog category
     */
    public function blogcategory()
    {
        // Users data
        $page_data['user'] = $this->user_data;
        $page_data['tname'] = 'Blog';
        $page_data['content'] = '';

        if (!$this->user->administrator()) $this->redirection('../?auth_error');

        $action = $this->postAndGet('action');
        switch ($action) {
            case 'add-category':
                // Save category if name is sent
                if (!empty($this->postAndGet('category')) && !empty($this->postAndGet('value'))) {
                    // Category localization if choosen
                    $category_localization = !empty($this->postAndGet('lang')) ? '_' . $this->postAndGet('lang') : '';

                    // Calculate category position
                    $position = $this->db->countRow('settings', "setting_group = 'blog_category{$category_localization}'");

                    // Add category
                    $data = array('setting_group' => 'blog_category' . $category_localization, 'setting_name' => $this->postAndGet('category'), 'value' => $this->postAndGet('value'), 'options' => $position);
                    $this->db->insert('settings', $data);
        
                    // Show message if category is saved
                    $page_data['content'] .= $this->showNotification('<img src="' . HOMEDIR . 'themes/images/img/reload.gif" alt="Saved" /> Category saved</p>');
                }
        
                // Category input
                $category = $this->model('ParsePage');
                $category->load('forms/input');
                $category->set('label_value', 'Category name');
                $category->set('label_for', 'category');
                $category->set('input_name', 'category');
                $category->set('input_id', 'category');
                $category->set('input_type', 'text');
                $category->set('input_value', '');
        
                // Value input
                $value = $this->model('ParsePage');
                $value->load('forms/input');
                $value->set('label_value', 'Category value (page tag)');
                $value->set('label_for', 'value');
                $value->set('input_name', 'value');
                $value->set('input_id', 'value');
                $value->set('input_type', 'text');
                $value->set('input_value', '');

                // Language select
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

                // All fields
                $fields = array($category, $value, $select_language);

                // Create form
                $form = $this->model('ParsePage');
                $form->load('forms/form');
                $form->set('form_action', HOMEDIR . 'adminpanel/blogcategory/?action=add-category');
                $form->set('form_method', 'post');
                $form->set('fields', $form->merge($fields));

                $page_data['content'] .= $form->output();
                break;

            case 'edit-category':
                // Update category if data are sent
                if (!empty($this->postAndGet('id')) && !empty($this->postAndGet('category')) && !empty($this->postAndGet('value'))) {
                    // Update category
                    $this->db->update('settings', array('setting_name', 'value'), array($this->postAndGet('category'), $this->postAndGet('value')), "id = '{$this->postAndGet('id')}'");
        
                    // Show message if category is updated
                    $page_data['content'] .= $this->showNotification('<img src="' . HOMEDIR . 'themes/images/img/reload.gif" alt="Saved" /> Category updated</p>');
                }
        
                // Category data
                $cat_info = $this->db->getData('settings', "id='{$this->postAndGet('id')}'");
        
                // Category input
                $category = $this->model('ParsePage');
                $category->load('forms/input');
                $category->set('label_value', 'Category name');
                $category->set('label_for', 'category');
                $category->set('input_name', 'category');
                $category->set('input_id', 'category');
                $category->set('input_type', 'text');
                $category->set('input_value', $cat_info['setting_name']);
        
                // Value input
                $value = $this->model('ParsePage');
                $value->load('forms/input');
                $value->set('label_value', 'Category value (page tag)');
                $value->set('label_for', 'value');
                $value->set('input_name', 'value');
                $value->set('input_id', 'value');
                $value->set('input_type', 'text');
                $value->set('input_value', $cat_info['value']);
        
                // Category id
                $category_id = $this->model('ParsePage');
                $category_id->load('forms/input');
                $category_id->set('input_name', 'id');
                $category_id->set('input_type', 'hidden');
                $category_id->set('input_value', $this->postAndGet('id'));
        
                $fields = array($category, $value, $category_id);
        
                // Create form
                $form = $this->model('ParsePage');
                $form->load('forms/form');
                $form->set('form_action', HOMEDIR . 'adminpanel/blogcategory/?action=edit-category');
                $form->set('form_method', 'post');
                $form->set('fields', $form->merge($fields));
        
                $page_data['content'] .= $form->output();
            break;

            case 'delete':
                if ($this->db->countRow('settings', "id = {$this->postAndGet('id')}") > 0) {
                    // Update other categories with new positions
                    $category = $this->db->getData('settings', "id='{$this->postAndGet('id')}'");
                    $category_position = $category['options'];
                    $category_group = $category['setting_group'];

                    // Number of categories in this group
                    $total_in_group = $this->db->countRow('settings', "setting_group = '{$category_group}'");

                    // Calculate do we need to update other categories and update if required
                    if ($category_position < ($total_in_group - 1)) {
                        foreach($this->db->query("SELECT * FROM settings WHERE setting_group = '{$category_group}' AND options > {$category_position}") as $category_to_update) {
                            $new_position = $category_to_update['options'] - 1;
                            $id = $category_to_update['id'];

                            // Update in databse
                            $this->db->update('settings', 'options', $new_position, "id = $id");
                        }
                    }

                    // Delete category
                    $this->db->delete('settings', "id = {$this->postAndGet('id')}");
        
                    $page_data['content'] .= $this->showNotification('<img src="' . HOMEDIR . 'themes/images/img/error.gif" alt="Deleted" /> Category deleted');
                } else {
                    $page_data['content'] .= $this->showDanger('<img src="' . HOMEDIR . 'themes/images/img/error.gif" alt="Error" /> This category does not exist');
                }
            break;

            case 'move-up':
                // cat we want to update
                $cat_info = $this->db->getData('settings', "id='{$this->postAndGet('id')}'");
                $cat_group = $cat_info['setting_group'];
                $cat_position = $cat_info['options'];
                $new_position = $cat_position - 1;

                if ($cat_position != 0 && !empty($cat_position)) {
                    // Update cat with position we want to take
                    $cat_to_down = $this->db->getData('settings', "setting_group = '{$cat_group}' AND options='{$new_position}'");
                    $cat_to_down_position = $cat_to_down['options'] + 1;
                    $this->db->exec("UPDATE settings SET options='{$cat_to_down_position}' WHERE id='{$cat_to_down['id']}'");

                    // Now, update our cat
                    $this->db->exec("UPDATE settings SET options='{$new_position}' WHERE id='{$this->postAndGet('id')}'");

                    $page_data['content'] .= $this->showNotification('<img src="' . HOMEDIR . 'themes/images/img/reload.gif" alt="Updated" /> Category position updated');
                } else {
                    $page_data['content'] .= $this->showDanger('<img src="' . HOMEDIR . 'themes/images/img/error.gif" alt="Error" /> Category position not updated');
                }
            break;

            case 'move-down':                
                // cat we want to update
                $cat_info = $this->db->getData('settings', "id='{$this->postAndGet('id')}'");
                $cat_group = $cat_info['setting_group'];
                $cat_position = $cat_info['options'];
                $new_position = $cat_position + 1;
                    
                $total = $this->db->countRow('settings', "setting_group = '{$cat_group}'");

                if ($new_position < $total && (!empty($cat_position) || $cat_position == '0')) {
                    // Update cat with position we want to take
                    $cat_to_down = $this->db->getData('settings', "setting_group = '{$cat_group}' AND options='{$new_position}'");
                    $cat_to_down_position = $cat_to_down['options'] - 1;
                    $this->db->exec("UPDATE settings SET options='{$cat_to_down_position}' WHERE id='{$cat_to_down['id']}'");
                    
                    // Now, update our cat
                    $this->db->exec("UPDATE settings SET options='{$new_position}' WHERE id='{$this->postAndGet('id')}'");
        
                    $page_data['content'] .= $this->showNotification('<img src="' . HOMEDIR . 'themes/images/img/reload.gif" alt="Updated" /> Category position updated');
                } else {
                    $page_data['content'] .= $this->showDanger('<img src="' . HOMEDIR . 'themes/images/img/error.gif" alt="Error" /> Category position not updated');
                }
            break;
        
            default:
                if ($this->db->countRow('settings', "setting_group LIKE 'blog_category%'") == 0) $this->showNotification('<img src="' . HOMEDIR . 'themes/images/img/reload.gif" alt=""/> There is no any category');
        
                // Blog categories
                $current_category = '';

                foreach ($this->db->query("SELECT * FROM settings WHERE setting_group LIKE 'blog_category%' ORDER BY options") as $category) {
                     // Split categories
                     if ($current_category != $category['setting_group']) {
                        $current_category = $category['setting_group'];

                        $page_data['content'] .= '<div class="mt-5">category: ' . $current_category . '</div>';
                    }
 
                    $page_data['content'] .= '<div class="a">';
                    $page_data['content'] .= $this->sitelink(HOMEDIR . 'blog/category/' . $category['value'] . '/', $category['setting_name']) . ' ';
                    $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/blogcategory/?action=edit-category&id=' . $category['id'], '<img src="' . HOMEDIR . 'themes/images/img/edit.gif" alt="Edit" /> Edit') . ' ';
                    $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/blogcategory/?action=delete&id=' . $category['id'], '<img src="' . HOMEDIR . 'themes/images/img/error.gif" alt="Delete" /> Delete') . ' ';
                    $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/blogcategory/?action=move-up&id=' . $category['id'], '<img src="' . HOMEDIR . 'themes/images/img/ups.gif" alt="Up" /> Move up') . ' ';
                    $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/blogcategory/?action=move-down&id=' . $category['id'], '<img src="' . HOMEDIR . 'themes/images/img/downs.gif" alt="Down" /> Move down');
                    $page_data['content'] .= '</div>';
                }

                break;
        }

        $page_data['content'] .= '<p class="mt-5">';
        if ($this->postAndGet('action') !== 'add-category') $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/blogcategory/?action=add-category', 'Add category') . '<br />';
        if (!empty($this->postAndGet('action'))) $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/blogcategory', 'Blog categories') . '<br />';
        $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel', $this->localization->string('adminpanel')) . '<br />';
        $page_data['content'] .= $this->homelink();
        $page_data['content'] .= '</p>';

        return $page_data;
    }

    /**
     * Page title
     */
    public function pagetitle()
    {
        // Users data
        $page_data['user'] = $this->user_data;
        $page_data['tname'] = 'Page Title';
        $page_data['content'] = '';

        $act = $this->postAndGet('act');

        if (!$this->user->administrator()) $this->redirection('../?error');
        
        if ($act == 'addedit') {
            $tfile = $this->check($this->postAndGet('tfile'));
            $msg = $this->replaceNewLines($this->postAndGet('msg'));
        
            // get page data
            $pageData = $this->db->getData('pages', "file='{$tfile}'", 'file, headt');
        
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
            $this->db->update('pages', $fields, $values, "file='{$tfile}'");

            $this->redirection(HOMEDIR . "adminpanel/pagemanager/?action=edit&file=" . $pageData['file'] . "&isset=savedok");
        } 
        
        if ($act == 'savenew') {
            $tpage = $this->check($this->postAndGet('tpage'));
            $tpage = strtolower($tpage);
            $tpage = str_replace(' ', '-', $tpage);

            $msg = $this->replaceNewLines($this->postAndGet('msg'));

            $last_notif = $this->db->getData('pages', "pname='{$tpage}'", '`tname`, `pname`, `file`, `headt`');

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
                $this->db->insert('pages', $values);

                $PBPage = false;
            } else {
                $fields = array('tname', 'headt');
                $values = array($msg, $headData);
                $this->db->insert('pages', $fields, $values, "pname='" . $tpage . "'");
        
                $PBPage = true;
            } 

            $this->redirection(HOMEDIR . "adminpanel/pagetitle/?isset=savedok");
        }

        if ($act == 'del') {
            $tid = $this->check($this->postAndGet('tid'));
        
            $this->db->delete('pages', "pname = '{$tid}'");
        
            $this->redirection(HOMEDIR . 'adminpanel/pagetitle');
        }
        
        if (!isset($act) || empty($act)) {
            $nitems = $this->db->countRow('pages');
            $total = $nitems;
        
            if ($total < 1) {
                $page_data['content'] .= '<br /><img src="' . HOMEDIR . 'themes/images/img/reload.gif" alt=""> <b>Page titles not found!</b><br />';
            }
        
            $nitems = $this->db->countRow('pages', 'tname is not null');
            $num_items = $nitems;
        
            $items_per_page = 30;
        
            $navigation = new Navigation($items_per_page, $num_items, $this->postAndGet('page'), HOMEDIR . 'adminpanel/pagetitle/?'); // start navigation

            $limit_start = $navigation->start()['start']; // starting point

            $sql = "SELECT id, pname, tname, file FROM pages WHERE tname is not null ORDER BY pname LIMIT $limit_start, $items_per_page";
        
            if ($num_items > 0) {
                foreach ($this->db->query($sql) as $item) {
                    $lnk = $item['pname'] . ' <img src="' . HOMEDIR . 'themes/images/img/edit.gif" alt="" /> <a href="' . HOMEDIR . 'adminpanel/pagetitle/?act=edit&amp;pgfile=' . $item['file'] . '">' . $item['tname'] . '</a> | <img src="' . HOMEDIR . 'themes/images/img/edit.gif" alt="" /> <a href="' . HOMEDIR . 'adminpanel/pagemanager/?action=headtag&amp;file=' . $item['file'] . '">[Edit Meta]</a> | <img src="' . HOMEDIR . 'themes/images/img/close.gif" alt="" /> <a href="' . HOMEDIR . 'adminpanel/pagetitle/?act=del&amp;tid=' . $item['pname'] . '">[DEL]</a>'; 
                    // $page_data['content'] .= " <small>joined: $jdt</small>";
                    $page_data['content'] .= "$lnk<br />";
                }
            }

            $page_data['content'] .= $navigation->get_navigation();

            $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagetitle?act=addnew', 'Add new title', '<p>', '</p>'); // update lang
        }
        
        if ($act == 'edit') {
            $pgfile = $this->check($this->postAndGet('pgfile'));
        
            $page_title = $this->db->getData('pages', "file='{$pgfile}'", 'tname, pname');
        
            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagetitle/?act=addedit');
            $form->set('form_method', 'POST');
        
            $input = $this->model('ParsePage');
            $input->load('forms/input');
            $input->set('input_type', 'hidden');
            $input->set('input_name', 'tfile');
            $input->set('input_value', $pgfile);
        
            $input_2 = $this->model('ParsePage');
            $input_2->load('forms/input');
            $input_2->set('label_for', 'msg');
            $input_2->set('label_value', 'Page title:');
            $input_2->set('input_name', 'msg');
            $input_2->set('input_id', 'msg');
            $input_2->set('input_value', $page_title['tname']);
        
            $form->set('fields', $form->merge(array($input, $input_2)));
            $page_data['content'] .= $form->output();
        
            $page_data['content'] .= '<hr>';
        
            $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagetitle', $this->localization->string('back'), '<p>', '</p>');
        } 

        if ($act == "addnew") {
            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagetitle/?act=savenew');
            $form->set('form_method', 'POST');
        
            $input = $this->model('ParsePage');
            $input->load('forms/input');
            $input->set('label_for', 'tpage');
            $input->set('label_value', 'Page:');
            $input->set('input_type', 'text');
            $input->set('input_name', 'tpage');
            $input->set('input_id', 'tpage');
        
            $input_2 = $this->model('ParsePage');
            $input_2->load('forms/input');
            $input_2->set('label_for', 'msg');
            $input_2->set('label_value', 'Page title:');
            $input_2->set('input_type', 'text');
            $input_2->set('input_name', 'msg');
            $input_2->set('input_id', 'msg');
        
            $form->set('fields', $form->merge(array($input, $input_2)));
            $page_data['content'] .= $form->output();
        
            $page_data['content'] .= '<hr />';
        
            $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagetitle', $this->localization->string('back'), '<p>', '</p>');
        }
        
        $page_data['content'] .= '<p>';
        $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel', $this->localization->string('adminpanel')) . '<br />';
        $page_data['content'] .= $this->homelink();
        $page_data['content'] .= '</p>';

        return $page_data;
    }

    /**
     * IP information
     */
    public function ip_information()
    {
        // Users data
        $page_data['user'] = $this->user_data;
        $page_data['tname'] = 'IP information';
        $page_data['content'] = '';

        if (!$this->user->moderator() && !$this->user->administrator()) $this->redirection('../?auth_error');

        $ip = $this->postAndGet('ip');

        if (empty($ip)) exit('please set ip address');

        // Get an array with geoip-infodata
        function geo_check_ip($ip) {
            // check, if the provided ip is valid
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                throw new InvalidArgumentException("IP is not valid");
            } 

            // contact ip-server
            $response = @file_get_contents('http://ip-api.com/json/' . $ip);

            if (empty($response)) {
                throw new InvalidArgumentException("Error contacting Geo-IP-Server");
            }

            // Return result as array
            return json_decode($response, true);
        }

        $ipData = geo_check_ip($ip);

        $page_data['ip_information'] = 'IP Address: ' . $ip . '<br />';
        if (!empty($ipData) && isset($ipData['country'])) {
            $page_data['ip_information'] .= 'Country: ' . $ipData['country'] . '<br />';
            $page_data['ip_information'] .= 'State/Region: ' . $ipData['regionName'] . '<br />';
            $page_data['ip_information'] .= 'City/Town: ' . $ipData['city'] . '<br />';
        } else {
            $page_data['ip_information'] .= $this->showDanger('No data available');
        }

        $page_data['content'] = '<p>' . $this->sitelink(HOMEDIR . 'adminpanel', $this->localization->string('adminpanel')) . '<br />';
        $page_data['content'] .= $this->homelink() . '</p>';

        return $page_data;
    }    
}