<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Navigation;
use App\Classes\Config;
use App\Classes\Mailer;
use App\Traits\Validations;
use App\Traits\Files;
use App\Traits\Notifications;

class AdminpanelModel extends BaseModel {
    use Validations, Files, Notifications;

    /**
     * Index page
     *
     * @return array
     */
    public function index(): array
    {
        $data['tname'] = '{@localization[adminpanel]}}';
        $data['content'] = '';

        if (!$this->user->checkPermissions('adminpanel', 'show')) $this->redirection('../?auth_error');

        if (empty($this->postAndGet('action'))) {
            // Moderator access level or bigger
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/adminchat', '{@localization[admin_chat]}}');
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/adminlist', '{@localization[modlist]}}');
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/unconfirmed_reg', '{@localization[notconf]}}');
            $data['content'] .= $this->sitelink('pages/userlist', '{@localization[userlist]}} (' . $this->user->regmemcount() . ')');

            // Super moderator access level or bigger
            if ($this->user->moderator(103) || $this->user->moderator(105) || $this->user->administrator()) {
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/file_upload', '{@localization[upload]}}');
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/uploaded_files', '{@localization[uplFiles]}}');
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/search_uploads', '{@localization[search_uploaded_files]}}');
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

                if (file_exists('antiword.php')) $data['content'] .= $this->sitelink('antiword.php', '{@localization[badword]}}');

                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/statistics', '{@localization[statistics]}}');
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/users', '{@localization[profile_management]}}');
            }

            if ($this->user->administrator() || $this->user->checkPermissions('pageedit')) {
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager', '{@localization[pages_management]}}');
            }

            // Head administrator access level
            if ($this->user->administrator(101)) {
                $data['content'] .= '<hr>';

                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings', '{@localization[syssets]}}');
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/subscriptions', '{@localization[subscriptions]}}');
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/email_queue', '{@localization[add_to_email_queue]}}');
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/subscription_options', '{@localization[subscription_options]}}');
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/ipban', '{@localization[ipbanp]}}' . ' (' . $this->linesInFile(STORAGEDIR . 'ban.dat') . ')');
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/logfiles', '{@localization[logcheck]}}');
                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/sitemap', '{@localization[sitemap_generator]}}');
            }
        }

        if ($this->postAndGet('action') == 'clear' && $this->user->administrator(101)) {
            $data['content'] .= '<p>';
            $data['content'] .= $this->sitelink('./?action=clrmlog', '{@localization[cleanmodlog]}}');
            $data['content'] .= '</p>';
        }

        if ($this->postAndGet('action') == 'clrmlog' && $this->user->administrator(101)) {
            $this->db->query("DELETE FROM mlog");

            $data['content'] .= '<p><img src="../themes/images/img/open.gif" alt="" /> {@localization[mlogcleaned]}}</p>';
        }

        if ($this->postAndGet('action') == 'opttbl' && $this->user->administrator(101)) {
            $alltables = mysqli_query("SHOW TABLES");

            while ($table = mysqli_fetch_assoc($alltables)) {
                foreach ($table as $db => $tablename) {
                    $sql = "OPTIMIZE TABLE `" . $tablename . "`";
                    $this->db->query($sql);
                }
            }

            $data['content'] .= '<p><img src="../themes/images/img/reload.gif" alt="" /> Optimized successfully!</p>'; // update lang
        }

        if (!empty($this->postAndGet('action'))) $data['content'] .= $this->sitelink('./', '{@localization[adminpanel]}}', '<p>', '</p>');

        $data['content'] .= $this->homelink('<p>', '</p>');

        // Pass data to the view
        return $data;
    }

    /**
     * Settings
     */
    public function settings()
    {
        $data['tname'] = '{@localization[settings]}}';
        $data['content'] = '';

        if (!$this->user->administrator(101)) $this->redirection('../pages/error.php?error=auth');

        $site_configuration = new Config($this->container);
        
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
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings/?action=setone', '{@localization[mainset]}}');
            $data['content'] .=  $this->sitelink(HOMEDIR . 'adminpanel/settings/?action=settwo', '{@localization[shwinfo]}}');
            $data['content'] .=  $this->sitelink(HOMEDIR . 'adminpanel/settings/?action=setthree', 'Chat log and email settings');
            $data['content'] .=  $this->sitelink(HOMEDIR . 'adminpanel/settings/?action=setseven', '{@localization[pagemanage]}}');
            $data['content'] .=  $this->sitelink(HOMEDIR . 'adminpanel/settings/?action=security', '{@localization[security]}}');
            $data['content'] .=  $this->sitelink(HOMEDIR . 'adminpanel/settings/?action=seteight', '{@localization[other]}}');
        }

        // main settings
        if ($this->postAndGet('action') == 'setone') {
            $data['content'] .=  '<h1>{@localization[mainset]}}</h1>';

            $form = $this->container['parse_page'];
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

            $select_lang = $this->container['parse_page'];
            $select_lang->load('forms/select');
            $select_lang->set('label_for', 'conf_set47');
            $select_lang->set('label_value', '{@localization[language]}}');
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

            $select_theme = $this->container['parse_page'];
            $select_theme->load('forms/select');
            $select_theme->set('label_for', 'conf_set2');
            $select_theme->set('label_value', '{@localization[webskin]}}');
            $select_theme->set('select_id', 'conf_set2');
            $select_theme->set('select_name', 'conf_set2');
            $select_theme->set('options', $options);
        
            // this will be admin username or system username
            $input8 = $this->container['parse_page'];
            $input8->load('forms/input');
            $input8->set('label_for', 'conf_set8');
            $input8->set('label_value', '{@localization[adminusername]}}');
            $input8->set('input_id', 'conf_set8');
            $input8->set('input_name', 'conf_set8');
            $input8->set('input_value', $this->configuration('adminNick'));
            $input8->set('input_maxlength', 20);
        
            $input9 = $this->container['parse_page'];
            $input9->load('forms/input');
            $input9->set('label_for', 'conf_set9');
            $input9->set('label_value', '{@localization[adminemail]}}');
            $input9->set('input_id', 'conf_set9');
            $input9->set('input_name', 'conf_set9');
            $input9->set('input_value', $this->configuration('adminEmail'));
            $input9->set('input_maxlength', 50);

            $input10 = $this->container['parse_page'];
            $input10->load('forms/input');
            $input10->set('label_for', 'conf_set10');
            $input10->set('label_value', '{@localization[timezone]}}');
            $input10->set('input_id', 'conf_set10');
            $input10->set('input_name', 'conf_set10');
            $input10->set('input_value', $this->configuration('timeZone'));
            $input10->set('input_maxlength', 3);
        
            $input11 = $this->container['parse_page'];
            $input11->load('forms/input');
            $input11->set('label_for', 'conf_set11');
            $input11->set('label_value', '{@localization[pagetitle]}}');
            $input11->set('input_id', 'conf_set11');
            $input11->set('input_name', 'conf_set11');
            $input11->set('input_value', $this->configuration('title'));
            $input11->set('input_maxlength', 100);
        
            $input14 = $this->container['parse_page'];
            $input14->load('forms/input');
            $input14->set('label_for', 'conf_set14');
            $input14->set('label_value', '{@localization[siteurl]}}');
            $input14->set('input_id', 'conf_set14');
            $input14->set('input_name', 'conf_set14');
            $input14->set('input_value', $this->configuration('homeUrl'));
            $input14->set('input_maxlength', 50);
        
            // Registration opened or closed
            $input_radio61_yes = $this->container['parse_page'];
            $input_radio61_yes->load('forms/radio_inline');
            $input_radio61_yes->set('label_for', 'conf_set61');
            $input_radio61_yes->set('label_value', '{@localization[yes]}}');
            $input_radio61_yes->set('input_id', 'conf_set61');
            $input_radio61_yes->set('input_name', 'conf_set61');
            $input_radio61_yes->set('input_value', 1);
            if ($this->configuration('openReg') == 1) {
                $input_radio61_yes->set('input_status', 'checked');
            }
        
            $input_radio61_no = $this->container['parse_page'];
            $input_radio61_no->load('forms/radio_inline');
            $input_radio61_no->set('label_for', 'conf_set61');
            $input_radio61_no->set('label_value', '{@localization[no]}}');
            $input_radio61_no->set('input_id', 'conf_set61');
            $input_radio61_no->set('input_name', 'conf_set61');
            $input_radio61_no->set('input_value', 0);
            if ($this->configuration('openReg') == 0) {
                $input_radio61_no->set('input_status', 'checked');
            }
        
            $radio_group_one = $this->container['parse_page'];
            $radio_group_one->load('forms/radio_group');
            $radio_group_one->set('description', '{@localization[openreg]}}');
            $radio_group_one->set('radio_group', $radio_group_one->merge(array($input_radio61_yes, $input_radio61_no)));
        
            // Does user need to confirm registration
            $input_radio62_yes = $this->container['parse_page'];
            $input_radio62_yes->load('forms/radio_inline');
            $input_radio62_yes->set('label_for', 'conf_set62');
            $input_radio62_yes->set('label_value', '{@localization[yes]}}');
            $input_radio62_yes->set('input_id', 'conf_set62');
            $input_radio62_yes->set('input_name', 'conf_set62');
            $input_radio62_yes->set('input_value', 1);
            if ($this->configuration('regConfirm') == 1) {
                $input_radio62_yes->set('input_status', 'checked');
            }
        
            $input_radio62_no = $this->container['parse_page'];
            $input_radio62_no->load('forms/radio_inline');
            $input_radio62_no->set('label_for', 'conf_set62');
            $input_radio62_no->set('label_value', '{@localization[no]}}');
            $input_radio62_no->set('input_id', 'conf_set62');
            $input_radio62_no->set('input_name', 'conf_set62');
            $input_radio62_no->set('input_value', 0);
            if ($this->configuration('regConfirm') == 0) {
                $input_radio62_no->set('input_status', 'checked');
            }
        
            $radio_group_two = $this->container['parse_page'];
            $radio_group_two->load('forms/radio_group');
            $radio_group_two->set('description', '{@localization[confregs]}}');
            $radio_group_two->set('radio_group', $radio_group_two->merge(array($input_radio62_yes, $input_radio62_no)));
        
            // Maintenance mode
            $input_radio63_yes = $this->container['parse_page'];
            $input_radio63_yes->load('forms/radio_inline');
            $input_radio63_yes->set('label_for', 'conf_set63');
            $input_radio63_yes->set('label_value', '{@localization[yes]}}');
            $input_radio63_yes->set('input_id', 'conf_set63');
            $input_radio63_yes->set('input_name', 'conf_set63');
            $input_radio63_yes->set('input_value', 1);
            if ($this->configuration('siteOff') == 1) {
                $input_radio63_yes->set('input_status', 'checked');
            }
        
            $input_radio63_no = $this->container['parse_page'];
            $input_radio63_no->load('forms/radio_inline');
            $input_radio63_no->set('label_for', 'conf_set63');
            $input_radio63_no->set('label_value', '{@localization[no]}}');
            $input_radio63_no->set('input_id', 'conf_set63');
            $input_radio63_no->set('input_name', 'conf_set63');
            $input_radio63_no->set('input_value', 0);
            if ($this->configuration('siteOff') == 0) {
                $input_radio63_no->set('input_status', 'checked');
            }
        
            $radio_group_three = $this->container['parse_page'];
            $radio_group_three->load('forms/radio_group');
            $radio_group_three->set('description', 'Maintenance');
            $radio_group_three->set('radio_group', $radio_group_three->merge(array($input_radio63_yes, $input_radio63_no)));
        
            $form->set('fields', $form->merge(array($select_lang, $select_theme, $input8, $input9, $input10, $input11, $input14, $radio_group_one, $radio_group_two, $radio_group_three)));
            $data['content'] .= $form->output();
        
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings/', '{@localization[back]}}', '<p>', '</p>');
        }
        
        if ($this->postAndGet('action') == "settwo") {
            $data['content'] .= '<h1>{@localization[shwinfo]}}</h1>';
        
            $form = $this->container['parse_page'];
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/settings/?action=edittwo');
        
            /**
             * Show clock
             */
            $_4_yes = $this->container['parse_page'];
            $_4_yes->load('forms/radio_inline');
            $_4_yes->set('label_for', 'conf_set4');
            $_4_yes->set('label_value', '{@localization[yes]}}');
            $_4_yes->set('input_id', 'conf_set4');
            $_4_yes->set('input_name', 'conf_set4');
            $_4_yes->set('input_value', 1);
            if ($this->configuration('showtime') == 1) {
                $_4_yes->set('input_status', 'checked');
            }
        
            $_4_no = $this->container['parse_page'];
            $_4_no->load('forms/radio_inline');
            $_4_no->set('label_for', 'conf_set4');
            $_4_no->set('label_value',  '{@localization[no]}}');
            $_4_no->set('input_id', 'conf_set4');
            $_4_no->set('input_name', 'conf_set4');
            $_4_no->set('input_value', 0);
            if ($this->configuration('showtime') == 0) {
                $_4_no->set('input_status', 'checked');
            }
        
            $show_clock = $this->container['parse_page'];
            $show_clock->load('forms/radio_group');
            $show_clock->set('description', '{@localization[showclock]}}');
            $show_clock->set('radio_group', $show_clock->merge(array($_4_yes, $_4_no)));
        
            /**
             * Show page generatioin time
             */
            $_5_yes = $this->container['parse_page'];
            $_5_yes->load('forms/radio_inline');
            $_5_yes->set('label_for', 'conf_set5');
            $_5_yes->set('label_value', '{@localization[yes]}}');
            $_5_yes->set('input_id', 'conf_set5');
            $_5_yes->set('input_name', 'conf_set5');
            $_5_yes->set('input_value', 1);
            if ($this->configuration('pageGenTime') == 1) {
                $_5_yes->set('input_status', 'checked');
            }
        
            $_5_no = $this->container['parse_page'];
            $_5_no->load('forms/radio_inline');
            $_5_no->set('label_for', 'conf_set5');
            $_5_no->set('label_value', '{@localization[no]}}');
            $_5_no->set('input_id', 'conf_set5');
            $_5_no->set('input_name', 'conf_set5');
            $_5_no->set('input_value', 0);
            if ($this->configuration('pageGenTime') == 0) {
                $_5_no->set('input_status', 'checked');
            }
        
            $page_gen = $this->container['parse_page'];
            $page_gen->load('forms/radio_group');
            $page_gen->set('description', '{@localization[pagegen]}}');
            $page_gen->set('radio_group', $page_gen->merge(array($_5_yes, $_5_no)));
        
            /**
             * Show online
             */
            $_7_yes = $this->container['parse_page'];
            $_7_yes->load('forms/radio_inline');
            $_7_yes->set('label_for', 'conf_set7');
            $_7_yes->set('label_value', '{@localization[yes]}}');
            $_7_yes->set('input_id', 'conf_set7');
            $_7_yes->set('input_name', 'conf_set7');
            $_7_yes->set('input_value', 1);
            if ($this->configuration('showOnline') == 1) {
                $_7_yes->set('input_status', 'checked');
            }
        
            $_7_no = $this->container['parse_page'];
            $_7_no->load('forms/radio_inline');
            $_7_no->set('label_for', 'conf_set7');
            $_7_no->set('label_value',  '{@localization[no]}}');
            $_7_no->set('input_id', 'conf_set7');
            $_7_no->set('input_name', 'conf_set7');
            $_7_no->set('input_value', 0);
            if ($this->configuration('showOnline') == 0) {
                $_7_no->set('input_status', 'checked');
            }
        
            $show_online = $this->container['parse_page'];
            $show_online->load('forms/radio_group');
            $show_online->set('description', '{@localization[showonline]}}');
            $show_online->set('radio_group', $show_online->merge(array($_7_yes, $_7_no)));
        
            /**
             * Show cookie consent
             */
            $_32_yes = $this->container['parse_page'];
            $_32_yes->load('forms/radio_inline');
            $_32_yes->set('label_for', 'conf_set32');
            $_32_yes->set('label_value', '{@localization[yes]}}');
            $_32_yes->set('input_id', 'conf_set32');
            $_32_yes->set('input_name', 'conf_set32');
            $_32_yes->set('input_value', 1);
            if ($this->configuration('cookieConsent') == 1) {
                $_32_yes->set('input_status', 'checked');
            }
        
            $_32_no = $this->container['parse_page'];
            $_32_no->load('forms/radio_inline');
            $_32_no->set('label_for', 'conf_set32');
            $_32_no->set('label_value', '{@localization[no]}}');
            $_32_no->set('input_id', 'conf_set32');
            $_32_no->set('input_name', 'conf_set32');
            $_32_no->set('input_value', 0);
            if ($this->configuration('cookieConsent') == 0) {
                $_32_no->set('input_status', 'checked');
            }
        
            $cookie_consent = $this->container['parse_page'];
            $cookie_consent->load('forms/radio_group');
            $cookie_consent->set('description', 'Cookie consent');
            $cookie_consent->set('radio_group', $cookie_consent->merge(array($_32_yes, $_32_no)));
        
            /**
             * Show counter
             */
            $incounters = array(6 => "{@localization[dontshow]}}", 1 => "{@localization[vsttotalvst]}}", 2 => "{@localization[clicktotalclick]}}", 3 => "{@localization[clickvisits]}}", 4 => "{@localization[totclicktotvst]}}");
        
            $options = '<option value="' . $this->configuration('showCounter') . '">' . $incounters[$this->configuration('showCounter')] . '</option>';
            foreach($incounters as $k => $v) {
                if ($k != $this->configuration('showCounter')) {
                    $options .= '<option value="' . $k . '">' . $v . '</option>';
                }
            }

            $show_counter = $this->container['parse_page'];
            $show_counter->load('forms/select');
            $show_counter->set('label_for', 'conf_set74');
            $show_counter->set('label_value', '{@localization[countlook]}}');
            $show_counter->set('select_id', 'conf_set74');
            $show_counter->set('select_name', 'conf_set74');
            $show_counter->set('options', $options);
        
            $form->set('fields', $form->merge(array($show_clock, $page_gen, $show_online, $cookie_consent, $show_counter)));
            $data['content'] .= $form->output();
        
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings/', '{@localization[back]}}', '<p>', '</p>');
        }

        if ($this->postAndGet('action') == "setthree") {
            $data['content'] .= '<h1>Chat log and email settings</h1>';

            $form = $this->container['parse_page'];
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/settings/?action=editthree');
        
            /**
             * Max chat posts
             */
            $input22 = $this->container['parse_page'];
            $input22->load('forms/input');
            $input22->set('label_for', 'conf_set22');
            $input22->set('label_value', '{@localization[maxinchat]}}');
            $input22->set('input_id', 'conf_set22');
            $input22->set('input_name', 'conf_set22');
            $input22->set('input_value', $this->configuration('maxPostChat'));
            $input22->set('input_maxlength', 4);

            /**
             * Mails in one package
             */
            $input56 = $this->container['parse_page'];
            $input56->load('forms/input');
            $input56->set('label_for', 'conf_set56');
            $input56->set('label_value', '{@localization[onepassmail]}}');
            $input56->set('input_id', 'conf_set56');
            $input56->set('input_name', 'conf_set56');
            $input56->set('input_value', $this->configuration('subMailPacket'));
            $input56->set('input_maxlength', 3);
        
            $form->set('fields', $form->merge(array($input22, $input56)));
            $data['content'] .= $form->output();
        
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings/', '{@localization[back]}}', '<p>', '</p>');
        }

        if ($this->postAndGet('action') == 'setseven') {
            $data['content'] .= '<h1>{@localization[pagessets]}}</h1>';
        
            $form = $this->container['parse_page'];
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/settings/?action=editseven');
        
            /**
             * Max referer data
             */
            $conf_set51 = $this->container['parse_page'];
            $conf_set51->load('forms/input');
            $conf_set51->set('label_for', 'referals');
            $conf_set51->set('label_value', '{@localization[maxrefererdata]}}');
            $conf_set51->set('input_id', 'referals');
            $conf_set51->set('input_name', 'conf_set51');
            $conf_set51->set('input_value', $this->configuration('refererLog'));
            $conf_set51->set('input_maxlength', 3);
        
            /**
             * Show referal page
             */
            $conf_set70yes = $this->container['parse_page'];
            $conf_set70yes->load('forms/radio_inline');
            $conf_set70yes->set('label_for', 'referal-yes');
            $conf_set70yes->set('label_value', '{@localization[yes]}}');
            $conf_set70yes->set('input_id', 'referal-yes');
            $conf_set70yes->set('input_name', 'conf_set70');
            $conf_set70yes->set('input_value', 1);
            if ($this->configuration('showRefPage') == 1) {
                $conf_set70yes->set('input_status', 'checked');
            }
        
            $conf_set70no = $this->container['parse_page'];
            $conf_set70no->load('forms/radio_inline');
            $conf_set70no->set('label_for', 'referal-no');
            $conf_set70no->set('label_value', '{@localization[no]}}');
            $conf_set70no->set('input_id', 'referal-no');
            $conf_set70no->set('input_name', 'conf_set70');
            $conf_set70no->set('input_value', 0);
            if ($this->configuration('showRefPage') == 0) {
                $conf_set70no->set('input_status', 'checked');
            }
        
            $show_refpage = $this->container['parse_page'];
            $show_refpage->load('forms/radio_group');
            $show_refpage->set('description', '{@localization[showrefpage]}}');
            $show_refpage->set('radio_group', $show_refpage->merge(array($conf_set70yes, $conf_set70no)));
        
            /**
             * Allow Facebook comments on pages
             */
            $conf_set6yes = $this->container['parse_page'];
            $conf_set6yes->load('forms/radio_inline');
            $conf_set6yes->set('label_for', 'fb_comm_yes');
            $conf_set6yes->set('label_value', '{@localization[yes]}}');
            $conf_set6yes->set('input_id', 'fb_comm_yes');
            $conf_set6yes->set('input_name', 'conf_set6');
            $conf_set6yes->set('input_value', 1);
            if ($this->configuration('pgFbComm') == 1) {
                $conf_set6yes->set('input_status', 'checked');
            }

            $conf_set6no = $this->container['parse_page'];
            $conf_set6no->load('forms/radio_inline');
            $conf_set6no->set('label_for', 'fb_comm_no');
            $conf_set6no->set('label_value', '{@localization[no]}}');
            $conf_set6no->set('input_id', 'fb_comm_no');
            $conf_set6no->set('input_name', 'conf_set6');
            $conf_set6no->set('input_value', 0);
            if ($this->configuration('pgFbComm') == 0) {
                $conf_set6no->set('input_status', 'checked');
            }

            $fb_comm = $this->container['parse_page'];
            $fb_comm->load('forms/radio_group');
            $fb_comm->set('description', 'Facebook comments on pages');
            $fb_comm->set('radio_group', $fb_comm->merge(array($conf_set6yes, $conf_set6no)));
        
            $form->set('fields', $form->merge(array($conf_set51, $show_refpage, $fb_comm)));
            $data['content'] .= $form->output();
        
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings/', '{@localization[back]}}', '<p>', '</p>');
        }

        if ($this->postAndGet('action') == "seteight") {
            $data['content'] .= '<h1>{@localization[other]}}</h1>';
        
            $form = $this->container['parse_page'];
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/settings/?action=editeight');

            /**
             * Max error logs in file
             */
            $conf_set58 = $this->container['parse_page'];
            $conf_set58->load('forms/input');
            $conf_set58->set('label_for', 'conf_set58');
            $conf_set58->set('label_value', '{@localization[maxlogfile]}}');
            $conf_set58->set('input_id', 'conf_set58');
            $conf_set58->set('input_name', 'conf_set58');
            $conf_set58->set('input_value', $this->configuration('maxLogData'));
            $conf_set58->set('input_maxlength', 3);

            /**
             * Max ban time
             */
            $conf_set76 = $this->container['parse_page'];
            $conf_set76->load('forms/input');
            $conf_set76->set('label_for', 'conf_set76');
            $conf_set76->set('label_value', '{@localization[maxbantime]}}');
            $conf_set76->set('input_id', 'conf_set76');
            $conf_set76->set('input_name', 'conf_set76');
            $conf_set76->set('input_value', round($this->configuration('maxBanTime') / 1440));
            $conf_set76->set('input_maxlength', 3);
        
            $form->set('fields', $form->merge(array($conf_set58, $conf_set76)));
            $data['content'] .= $form->output();
        
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings/', '{@localization[back]}}', '<p>', '</p>');
        }
        
        if ($this->postAndGet('action') == 'security') {
            $data['content'] .= '<h1>{@localization[security]}}</h1>';
        
            $form = $this->container['parse_page'];
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/settings/?action=editsecurity');
        
            $input29 = $this->container['parse_page'];
            $input29->load('forms/input');
            $input29->set('label_for', 'conf_set29');
            $input29->set('label_value', '{@localization[floodtime]}}');
            $input29->set('input_id', 'conf_set29');
            $input29->set('input_name', 'conf_set29');
            $input29->set('input_value', $this->configuration('floodTime'));
            $input29->set('input_maxlength', 3);
        
            $input1 = $this->container['parse_page'];
            $input1->load('forms/input');
            $input1->set('label_for', 'conf_set1');
            $input1->set('label_value', '{@localization[passkey]}}');
            $input1->set('input_id', 'conf_set1');
            $input1->set('input_name', 'conf_set1');
            $input1->set('input_value', $this->configuration('keypass'));
            $input1->set('input_maxlength', 25);
        
            // quarantine time
            $quarantine = array(0 => "{@localization[disabled]}}", 21600 => "6 {@localization[hours]}}", 43200 => "12 {@localization[hours]}}", 86400 => "24 {@localization[hours]}}", 129600 => "36 {@localization[hours]}}", 172800 => "48 {@localization[hours]}}");
        
            $options = '<option value="' . $this->configuration('quarantine') . '">' . $quarantine[$this->configuration('quarantine')] . '</option>';
            foreach($quarantine as $k => $v) {
                if ($k != $this->configuration('quarantine')) {
                    $options .= '<option value="' . $k . '">' . $v . '</option>';
                }
            }
        
            $select_set3 = $this->container['parse_page'];
            $select_set3->load('forms/select');
            $select_set3->set('label_for', 'conf_set3');
            $select_set3->set('label_value', '{@localization[quarantinetime]}}');
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

            $select_set21 = $this->container['parse_page'];
            $select_set21->load('forms/select');
            $select_set21->set('label_for', 'conf_set21');
            $select_set21->set('label_value', 'Transfer protocol');
            $select_set21->set('select_id', 'conf_set21');
            $select_set21->set('select_name', 'conf_set21');
            $select_set21->set('options', $options);

            // reCAPTCHA site key
            $captcha_sitekey = $this->container['parse_page'];
            $captcha_sitekey->load('forms/input');
            $captcha_sitekey->set('label_for', 'recaptcha_sitekey');
            $captcha_sitekey->set('label_value', 'reCAPTCHA site key');
            $captcha_sitekey->set('input_id', 'recaptcha_sitekey');
            $captcha_sitekey->set('input_name', 'recaptcha_sitekey');
            $captcha_sitekey->set('input_value', $this->configuration('recaptcha_sitekey'));
            $captcha_sitekey->set('input_maxlength', 50);

            // reCAPTCHA secret key
            $captcha_secret = $this->container['parse_page'];
            $captcha_secret->load('forms/input');
            $captcha_secret->set('label_for', 'recaptcha_secretkey');
            $captcha_secret->set('label_value', 'reCAPTCHA secret key');
            $captcha_secret->set('input_id', 'recaptcha_secretkey');
            $captcha_secret->set('input_name', 'recaptcha_secretkey');
            $captcha_secret->set('input_value', $this->configuration('recaptcha_secretkey'));
            $captcha_secret->set('input_maxlength', 50);

            $form->set('fields', $form->merge(array($input29, $input1, $select_set3, $select_set21, $captcha_sitekey, $captcha_secret)));
            $data['content'] .= $form->output();

            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/settings/', '{@localization[back]}}', '<p>', '</p>');
        }

        $data['content'] .= '<p>' . $this->sitelink(HOMEDIR . 'adminpanel', '{@localization[adminpanel]}}') . '<br />';
        $data['content'] .= $this->homelink() . '</p>';

        // Pass data to the view
        return $data;
    }

    /**
     * Admin list
     */
    public function adminlist()
    {
        $data['tname'] = '{@localization[modlist]}}';
        $data['content'] = '';

        if (!$this->user->checkPermissions(basename(__FILE__))) $this->redirection('../?auth_error');

        $data['content'] .= '<p><img src="../themes/images/img/user.gif" alt=""> <b>{@localization[adminlistl]}}</b></p>'; 

        $num_items = $this->user->total_admins();
        $items_per_page = 10;

        $navigation = new Navigation($items_per_page, $num_items, $this->postAndGet('page'), HOMEDIR . 'adminpanel/adminlist/?'); // start navigation

        $limit_start = $navigation->start()['start']; // starting point
        $end = $navigation->start()['end']; // ending point

        if ($num_items > 0) {
            foreach ($this->db->query("SELECT id, name, perm FROM vavok_users WHERE perm='101' OR perm='102' OR perm='103' OR perm='105' OR perm='106' ORDER BY perm LIMIT $limit_start, $items_per_page") as $item) {
                if ($item['perm'] == 101 or $item['perm'] == 102 or $item['perm'] == 103 or $item['perm'] == 105 or $item['perm'] == 106) {
                    $lnk = '<div class="a">' . $this->sitelink(HOMEDIR . 'users/u/' . $item['id'], $item['name']) . ' - ' . $this->user->userStatus($item['perm']) . '</div>';
                    $data['content'] .= $lnk . '<br>';
                }
            }
        }

        $data['content'] .= $navigation->get_navigation();

        $data['content'] .= '<p>' . $this->sitelink('./', '{@localization[adminpanel]}}') . '<br>';
        $data['content'] .= $this->homelink() . '</p>';

        return $data;
    }

    public function unconfirmed_reg()
    {
        $data['tname'] = '{@localization[uncomfreg]}}';
        $data['content'] = '';

        if (!$this->user->checkPermissions(basename(__FILE__))) $this->redirection('../?auth_error');

        if ($this->postAndGet('action') == 'conf' && !empty($this->postAndGet('usr'))) {
            $fields = array('regche', 'regkey');
            $values = array('', '');
            $this->user->updateUser($fields, $values, $this->postAndGet('usr'));

            $vav_name = $this->user->getNickFromId($this->postAndGet('usr'));

            $message = $this->localization->string('hello') . " " . $vav_name . "!\r\n
            " . $this->localization->string('sitemod') . " " . $this->configuration('homeBase') . " " . $this->localization->string('confirmedreg') . ".\r\n
            " . $this->localization->string('youcanlog') . ".\r\n\r\n
            " . $this->localization->string('bye') . "!\r\n
            " . $this->user->getNickFromId($this->user->user_id()) . "\r\n
            " . ucfirst($this->configuration('homeBase'));

            $newMail = new Mailer($this->container);
            $newMail->queueEmail($this->user->user_info('email', $this->postAndGet('usr')), $this->localization->string('msgfrmst') . " " . $this->configuration('title'), $message, '', '', 'high');

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
                    $lnk = $this->sitelink(HOMEDIR . 'users/u/' . $item['uid'], $this->user->getNickFromId($item['uid'])) . ' (' . $this->correctDate($item['regdate'], 'd.m.Y. / H:i') . ')';
                    if ($item['regche'] == 1) {
                        $bt = $this->localization->string('notconfirmed') . '!';
                        $bym = $this->sitelink(HOMEDIR . 'adminpanel/unconfirmed_reg/?action=conf&usr=' . $item['uid'], '{@localization[confirms]}}');
                    } else {
                        $bt = 'Confirmed';
                    }

                    $data['content'] .= '<p>' . $lnk . ' IP: ' . $this->user->user_info('ipadd', $item['uid']) . ' ' . $this->localization->string('browser') . ': ' . $this->user->user_info('browser', $item['uid']) . ' ' . $bym . '</p>';
                }
            } else {
                $data['content'] .= '<p><img src="../themes/images/img/reload.gif" alt="" /> ' . $this->localization->string('no_unconfirmed_registrations') . '!</p>';
            }
        
            $navigation = new Navigation($items_per_page, $num_items, $this->postAndGet('page'), HOMEDIR . 'adminpanel/unconfirmed_reg/');
        
            $data['content'] .= '<div class="mt-5">';
                $data['content'] .= $navigation->get_navigation();
            $data['content'] .= '</div>';
        }

        $data['content'] .= '<p>' . $this->sitelink('./', '{@localization[adminpanel]}}') . '<br />';
        $data['content'] .= $this->homelink() . '</p>';

        return $data;
    }

    /**
     * Statistics
     */
    public function statistics()
    {
        $data['tname'] = '{@localization[sitestats]}}';
        $data['content'] = '';

        if (!$this->user->administrator()) $this->redirection('../?errorAuth');
  
        $data['content'] .= '<p>' . $this->sitelink(HOMEDIR . 'pages/statistics', '{@localization[visitor_statistics]}}') . '<br />';
        $data['content'] .= $this->sitelink('../pages/online', '{@localization[users_online]}}') . '</p>';
        
        $data['content'] .= '<p>' . $this->sitelink('./', '{@localization[adminpanel]}}') . '<br>';
        $data['content'] .= $this->homelink() . '</p>';

        return $data;
    }

    /**
     * Users
     */
    public function users()
    {
        $data['tname'] = '{@localization[usrprofile]}}';
        $data['content'] = '';

        if (!$this->user->administrator()) $this->redirection('./?error=noauth');

        $user = $this->check($this->postAndGet('users'));
        $users_id = $this->user->getIdFromNick($user);

        if (empty($this->postAndGet('action'))) {
            $form = $this->container['parse_page'];
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/users/?action=edit');

            $input_users = $this->container['parse_page'];
            $input_users->load('forms/input');
            $input_users->set('label_for', 'users');
            $input_users->set('label_value', '{@localization[chooseuser]}}' . ':');
            $input_users->set('input_name', 'users');
            $input_users->set('input_id', 'users');
            $input_users->set('input_maxlength', 20);

            $form->set('localization[save]', '{@localization[showdata]}}');
            $form->set('fields', $input_users->output());
            $data['content'] .= $form->output();
        }

        // change profile
        if ($this->postAndGet('action') == 'edit') {
            if (!empty($user) && $this->user->username_exists($user) && $this->user->id_exists($users_id)) {
                $data['content'] .= '<img src="{@HOMEDIR}}themes/images/img/profiles.gif" alt="Profile" /> {@localization[usrprofile]}} ' . $user . '<br>';

                if ($this->user->show_username() != $this->configuration('adminNick') && $user == $this->configuration('adminNick')) {
                    $data['content'] .= '<br>{@localization[noauthtoedit]}!<br>';
                    return $data;
                    exit;
                }

                if (($this->user->show_username() != $this->configuration('adminNick')) && ($this->user->user_info('perm', $users_id) == 101 || $this->user->user_info('perm', $users_id) == 102 || $this->user->user_info('perm', $users_id) == 103 || $this->user->user_info('perm', $users_id) == 105) && $this->user->show_username() != $user) {
                    $data['content'] .= '<br>{@localization[noauthtoban]}!<br>';
                    return $data;
                    exit;
                }

                $casenick = strcasecmp($user, $this->user->show_username());

                if ($casenick == 0) $data['content'] .= '<p><b><font color="red">{@localization[myprofile]}!</font></b></p>';

                if ($this->user->user_info('banned', $users_id) == 1) $data['content'] .= '<p><font color="#FF0000"><b>{@localization[user_is_banned]}}</b></font></p>';
        
                if ($this->user->user_info('regche', $users_id) == 1) $data['content'] .= '<p><font color="#FF0000"><b>{@localization[notactivated]}}</b></font></p>';
        
                $form = $this->container['parse_page'];
                $form->load('forms/form');
                $form->set('form_method', 'post');
                $form->set('form_action', HOMEDIR . 'adminpanel/users/?action=upgrade&users=' . $user);
        
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
        
                $udd7 = $this->container['parse_page'];
                $udd7->load('forms/select');
                $udd7->set('label_for', 'udd7');
                $udd7->set('label_value', '{@localization[accesslevel]}}');
                $udd7->set('select_id', 'udd7');
                $udd7->set('select_name', 'udd7');
                $udd7->set('options', $options);
        
                $udd1 = $this->container['parse_page'];
                $udd1->load('forms/input');
                $udd1->set('label_for', 'udd1');
                $udd1->set('label_value', '{@localization[newpassinfo]}}');
                $udd1->set('input_id', 'udd1');
                $udd1->set('input_name', 'udd1');
        
                $udd2 = $this->container['parse_page'];
                $udd2->load('forms/input');
                $udd2->set('label_for', 'udd2');
                $udd2->set('label_value', '{@localization[city]}}');
                $udd2->set('input_id', 'udd2');
                $udd2->set('input_name', 'udd2');
                $udd2->set('input_value', $this->user->user_info('city', $users_id));
        
                $udd3 = $this->container['parse_page'];
                $udd3->load('forms/input');
                $udd3->set('label_for', 'udd3');
                $udd3->set('label_value', '{@localization[aboutyou]}}');
                $udd3->set('input_id', 'udd3');
                $udd3->set('input_name', 'udd3');
                $udd3->set('input_value', $this->user->user_info('about', $users_id));
        
                $udd4 = $this->container['parse_page'];
                $udd4->load('forms/input');
                $udd4->set('label_for', 'udd4');
                $udd4->set('label_value', '{@localization[yemail]}}');
                $udd4->set('input_id', 'udd4');
                $udd4->set('input_name', 'udd4');
                $udd4->set('input_value', $this->user->user_info('email', $users_id));
        
                $udd5 = $this->container['parse_page'];
                $udd5->load('forms/input');
                $udd5->set('label_for', 'udd5');
                $udd5->set('label_value', '{@localization[site]}}');
                $udd5->set('input_id', 'udd5');
                $udd5->set('input_name', 'udd5');
                $udd5->set('input_value', $this->user->user_info('site', $users_id));
        
                $udd13 = $this->container['parse_page'];
                $udd13->load('forms/input');
                $udd13->set('label_for', 'udd13');
                $udd13->set('label_value', '{@localization[browser]}}');
                $udd13->set('input_id', 'udd13');
                $udd13->set('input_name', 'udd13');
                $udd13->set('input_value', $this->user->user_info('browser', $users_id));
        
                $udd29 = $this->container['parse_page'];
                $udd29->load('forms/input');
                $udd29->set('label_for', 'udd29');
                $udd29->set('label_value', '{@localization[name]}}');
                $udd29->set('input_id', 'udd29');
                $udd29->set('input_name', 'udd29');
                $udd29->set('input_value', $this->user->user_info('firstname', $users_id));
        
                $udd40 = $this->container['parse_page'];
                $udd40->load('forms/input');
                $udd40->set('label_for', 'udd40');
                $udd40->set('label_value', '{@localization[perstatus]}}');
                $udd40->set('input_id', 'udd40');
                $udd40->set('input_name', 'udd40');
                $udd40->set('input_value', $this->user->user_info('status', $users_id));
        
                if ($this->user->user_info('subscribed', $users_id) == 1) {
                    $value = $this->localization->string('subscribed');
                } else {
                    $value = $this->localization->string('notsubed');
                }
                $subscribed = $this->container['parse_page'];
                $subscribed->load('forms/input_readonly');
                $subscribed->set('label_for', 'subscribed');
                $subscribed->set('label_value', '{@localization[sitenews]}}');
                $subscribed->set('input_id', 'subscribed');
                $subscribed->set('input_name', 'subscribed');
                $subscribed->set('input_placeholder', $value);
        
                $allban = $this->container['parse_page'];
                $allban->load('forms/input_readonly');
                $allban->set('label_for', 'allban');
                $allban->set('label_value', '{@localization[numbbans]}}');
                $allban->set('input_id', 'allban');
                $allban->set('input_placeholder', (int)$this->user->user_info('allban', $users_id));
        
                $lastvst = $this->container['parse_page'];
                $lastvst->load('forms/input_readonly');
                $lastvst->set('label_for', 'lastvst');
                $lastvst->set('label_value', '{@localization[lastvst]}}');
                $lastvst->set('input_id', 'lastvst');
                $lastvst->set('input_placeholder', $this->correctDate($this->user->user_info('lastvisit', $users_id), 'j.m.Y. / H:i'));
        
                $ip = $this->container['parse_page'];
                $ip->load('forms/input_readonly');
                $ip->set('label_for', 'ip');
                $ip->set('label_value', 'IP');
                $ip->set('input_id', 'ip');
                $ip->set('input_placeholder', $this->user->user_info('ipaddress', $users_id));
        
                $form->set('fields', $form->merge(array($udd7, $udd1, $udd2, $udd3, $udd4, $udd5, $udd13, $udd29, $udd40, $subscribed, $allban, $lastvst, $ip)));
                $data['content'] .= $form->output();
        
                $data['content'] .= '<p>';
                if ($userx_access > 106) {
                    $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/users/?action=poddel&users=' . $user, '{@localization[deluser]}}', '<b>', '</b>') . '<br />';
                }
                // Website permissions for various sections
                if (file_exists('specperm.php')) {
                    $data['content'] .= $this->sitelink('specperm.php?users=' . $users_id, 'Change access permissions') . '<br />';
                }
        
                $data['content'] .= '</p>';
            } else {
                $data['content'] .= $this->localization->string('usrnoexist') . '!';
            }
        
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/users/', '{@localization[back]}}', '<p>', '</p>');
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

            if ($this->validateEmail($udd4)) {
                if (empty($udd5) || $this->validateUrl($udd5) === true) {
                    if (!empty($users_id)) {
                        if (!empty($udd6)) {
                            list($uday, $umonth, $uyear) = explode(".", $udd6);
                            $udd6 = mktime('0', '0', '0', $umonth, $uday, $uyear);
                        }

                        if (!empty($udd1)) $newpass = $this->user->password_encrypt($udd1);
        
                        // Update password
                        if (!empty($newpass)) $this->user->updateUser('pass', $this->replaceNewLines($newpass), $users_id);
        
                        // Update default access permissions
                        if ($udd7 != $this->user->user_info('perm', $users_id)) $this->user->update_default_permissions($users_id, $udd7);
        
                        // Update data
                        $this->user->updateUser(
                            array('city', 'about', 'email', 'site', 'rname', 'perstat', 'browsers'),
                            array($this->replaceNewLines($this->check($udd2)), $this->check($udd3), $this->replaceNewLines(htmlspecialchars(stripslashes(strtolower($udd4)))), $this->replaceNewLines($this->check($udd5)), $this->replaceNewLines($this->check($udd29)), $this->replaceNewLines($this->check($udd40)), $this->replaceNewLines($this->check($udd13))), $users_id
                        );
        
                        $data['content'] .= $this->localization->string('usrdataupd') . '!<br>';
        
                        if (!empty($udd1)) {
                            $data['content'] .= '<font color=red>{@localization[passchanged]}}: ' . $udd1 . '</font> <br>';
                        }
        
                        $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/users/', '{@localization[changeotheruser]}}') . '<br>';
                    } else {
                        $data['content'] .= $this->localization->string('usrnoexist') . '!<br>';
                    }
                } else {
                    $data['content'] .= $this->localization->string('urlnotok') . '!<br>';
                } 
            } else {
                $data['content'] .= $this->localization->string('emailnotok') . '<br>';
            }

            $data['content'] .= '<br>' . $this->sitelink(HOMEDIR . 'adminpanel/users/?action=edit&users=' . $user, '{@localization[back]}}');
        }
        
        // confirm delete
        if ($this->postAndGet('action') == 'poddel') {
            $data['content'] .= $this->localization->string('confusrdel') . ' <b>' . $user . '</b>?<br><br>';
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/users/?action=deluser&users=' . $user, '{@localization[deluser]}}', '<b>', '</b>');
        
            $data['content'] .= '<br>' . $this->sitelink(HOMEDIR . 'adminpanel/users/?action=edit&users=' . $user, '{@localization[back]}}');
        } 
        
        // delete user
        if ($this->postAndGet('action') == 'deluser') {
            if ($user != $this->configuration('adminNick')) {
                if ($this->user->user_info('perm', $users_id) < 101 || $this->user->user_info('perm', $users_id) > 105) {
                    $this->user->deleteUser($user);
                    $data['content'] .= $this->localization->string('usrdeleted') . '!<br>';
        
                    $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/users/', '{@localization[changeotheruser]}}', '<p>', '</p>');
                } else {
                    $data['content'] .= $this->localization->string('noaccessdel') . '<br>';
                    $data['content'] .= '<br>' . $this->sitelink(HOMEDIR . 'adminpanel/users/?action=edit&users=' . $user, '{@localization[back]}}');
                }
            }
        }
        
        $data['content'] .= '<p>' . $this->sitelink(HOMEDIR . 'adminpanel', '{@localization[adminpanel]}}') . '<br>';
        $data['content'] .= $this->homelink() . '</p>';

        return $data;
    }

    /**
     * IP ban
     */
    public function ipban()
    {
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
            $this->clearFile(STORAGEDIR . 'ban.dat');

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
        
            $data['content'] .= $i2 . '. ' . $file_data[1] . ' <br>' . $this->sitelink(HOMEDIR . 'adminpanel/ipban/?action=razban&id=' . $num, '{@localization[delban]}}') . '<hr>';
        } 

        if ($total < 1) {
            $data['content'] .= '<p><img src="{@HOMEDIR}}themes/images/img/reload.gif" alt="" /> ' . $this->localization->string('emptylist') . '</p>';
        }

        $data['content'] .= $navigation->get_navigation();

        $data['content'] .= '<hr>';

        $form = $this->container['parse_page'];
        $form->load('forms/form');
        $form->set('form_method', 'post');
        $form->set('form_action', HOMEDIR . 'adminpanel/ipban/?action=zaban');

        $input = $this->container['parse_page'];
        $input->load('forms/input');
        $input->set('label_for', 'ips');
        $input->set('label_value', '{@localization[iptoblock]}}');
        $input->set('input_name', 'ips');
        $input->set('input_id', 'ips');

        $form->set('localization[save]', '{@localization[confirm]}}');
        $form->set('fields', $input->output());
        $data['content'] .= $form->output();

        $data['content'] .= '<hr>';

        $data['content'] .= '<p>{@localization[ipbanexam]}}</p>';
        $data['content'] .= '<p>{@localization[allbanips]}}: ' . $total . '</p>';

        if ($total > 1) {
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/ipban/?action=delallip', '{@localization[dellist]}}', '<p>', '</p>');
        }

        $data['content'] .= '<p>' . $this->sitelink(HOMEDIR . 'adminpanel', '{@localization[adminpanel]}}') . '<br>';
        $data['content'] .= $this->homelink() . '</p>';

        return $data;
    }

    /**
     * Page search
     */
    public function pagesearch()
    {
        $page_data['tname'] = '{@localization[search]}}';
        $page_data['content'] = '';

        if (!$this->user->administrator()) $this->redirection(HOMEDIR);

        if (empty($this->postAndGet('action'))) {
            $form = $this->container['parse_page'];
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagesearch/?action=stpc');
            $form->set('localization[save]', '{@localization[search]}}');

            $input = $this->container['parse_page'];
            $input->load('forms/input');
            $input->set('label_for', 'stext');
            $input->set('label_value', 'Page name:');
            $input->set('input_name', 'stext');
            $input->set('input_id', 'stext');
            $input->set('input_maxlength', 30);

            $form->set('fields', $input->output());
            $page_data['content'] .= $form->output();

            $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagemanager', '{@localization[back]}}', '<p>', '<br />');
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

                        $tlink = $this->sitelink(HOMEDIR . 'adminpanel/pagemanager/?action=show&file=' . $item['file'], $tname . $itemLang) . '<br />';
                    }

                    $page_data['content'] .= $tlink;
                }

                $page_data['content'] .= $navigation->get_navigation();
            }

            $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagesearch', '{@localization[back]}}', '<p>', '<br />');
        }

        $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel', '{@localization[adminpanel]}}', '', '<br />');
        $page_data['content'] .= $this->homelink('', '</p>');

        return $page_data;
    }

    /**
     * Blog category
     */
    public function blogcategory()
    {
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
                $category = $this->container['parse_page'];
                $category->load('forms/input');
                $category->set('label_value', 'Category name');
                $category->set('label_for', 'category');
                $category->set('input_name', 'category');
                $category->set('input_id', 'category');
                $category->set('input_type', 'text');
                $category->set('input_value', '');
        
                // Value input
                $value = $this->container['parse_page'];
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

                $select_language = $this->container['parse_page'];
                $select_language->load('forms/select');
                $select_language->set('label_for', 'language');
                $select_language->set('label_value', '{@localization[language]}}' . ' (optional):');
                $select_language->set('select_id', 'language');
                $select_language->set('select_name', 'lang');
                $select_language->set('options', $options);

                // All fields
                $fields = array($category, $value, $select_language);

                // Create form
                $form = $this->container['parse_page'];
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
                $cat_info = $this->db->selectData('settings', 'id = :id', [':id' => $this->postAndGet('id')]);
        
                // Category input
                $category = $this->container['parse_page'];
                $category->load('forms/input');
                $category->set('label_value', 'Category name');
                $category->set('label_for', 'category');
                $category->set('input_name', 'category');
                $category->set('input_id', 'category');
                $category->set('input_type', 'text');
                $category->set('input_value', $cat_info['setting_name']);
        
                // Value input
                $value = $this->container['parse_page'];
                $value->load('forms/input');
                $value->set('label_value', 'Category value (page tag)');
                $value->set('label_for', 'value');
                $value->set('input_name', 'value');
                $value->set('input_id', 'value');
                $value->set('input_type', 'text');
                $value->set('input_value', $cat_info['value']);
        
                // Category id
                $category_id = $this->container['parse_page'];
                $category_id->load('forms/input');
                $category_id->set('input_name', 'id');
                $category_id->set('input_type', 'hidden');
                $category_id->set('input_value', $this->postAndGet('id'));
        
                $fields = array($category, $value, $category_id);
        
                // Create form
                $form = $this->container['parse_page'];
                $form->load('forms/form');
                $form->set('form_action', HOMEDIR . 'adminpanel/blogcategory/?action=edit-category');
                $form->set('form_method', 'post');
                $form->set('fields', $form->merge($fields));
        
                $page_data['content'] .= $form->output();
            break;

            case 'delete':
                if ($this->db->countRow('settings', "id = {$this->postAndGet('id')}") > 0) {
                    // Update other categories with new positions
                    $category = $this->db->selectData('settings', 'id = :id', [':id' => $this->postAndGet('id')]);
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
                $cat_info = $this->db->selectData('settings', 'id = :id', [':id' => $this->postAndGet('id')]);
                $cat_group = $cat_info['setting_group'];
                $cat_position = $cat_info['options'];
                $new_position = $cat_position - 1;

                if ($cat_position != 0 && !empty($cat_position)) {
                    // Update cat with position we want to take
                    $cat_to_down = $this->db->selectData('settings', 'setting_group = :setting_group AND options = :options', [':setting_group' => $cat_group, ':options' => $new_position]);
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
                $cat_info = $this->db->selectData('settings', 'id = :id', [':id' => $this->postAndGet('id')]);
                $cat_group = $cat_info['setting_group'];
                $cat_position = $cat_info['options'];
                $new_position = $cat_position + 1;
                    
                $total = $this->db->countRow('settings', "setting_group = '{$cat_group}'");

                if ($new_position < $total && (!empty($cat_position) || $cat_position == '0')) {
                    // Update cat with position we want to take
                    $cat_to_down = $this->db->selectData('settings', 'setting_group = :setting_group AND options = :options', [':setting_group' => $cat_group, ':options' => $new_position]);
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
                $blog_categories = array();

                foreach ($this->db->query("SELECT * FROM settings WHERE setting_group LIKE 'blog_category%' ORDER BY options") as $category) {
                    // Check if category key exists and create key if it doesn't exist
                    // While creating array key put information about category group
                    if (!isset($blog_categories[$category['setting_group']])) $blog_categories[$category['setting_group']] = 'Category Group: ' . $category['setting_group'];

                    // Put data into the array key
                    $blog_categories[$category['setting_group']] .= '<div class="a">';
                    $blog_categories[$category['setting_group']] .= $this->sitelink(HOMEDIR . 'blog/category/' . $category['value'] . '/', $category['setting_name']) . ' ';
                    $blog_categories[$category['setting_group']] .= $this->sitelink(HOMEDIR . 'adminpanel/blogcategory/?action=edit-category&id=' . $category['id'], '<img src="' . HOMEDIR . 'themes/images/img/edit.gif" alt="Edit" /> Edit') . ' ';
                    $blog_categories[$category['setting_group']] .= $this->sitelink(HOMEDIR . 'adminpanel/blogcategory/?action=delete&id=' . $category['id'], '<img src="' . HOMEDIR . 'themes/images/img/error.gif" alt="Delete" /> Delete') . ' ';
                    $blog_categories[$category['setting_group']] .= $this->sitelink(HOMEDIR . 'adminpanel/blogcategory/?action=move-up&id=' . $category['id'], '<img src="' . HOMEDIR . 'themes/images/img/ups.gif" alt="Up" /> Move up') . ' ';
                    $blog_categories[$category['setting_group']] .= $this->sitelink(HOMEDIR . 'adminpanel/blogcategory/?action=move-down&id=' . $category['id'], '<img src="' . HOMEDIR . 'themes/images/img/downs.gif" alt="Down" /> Move down');
                    $blog_categories[$category['setting_group']] .= '</div>';
                }

                // Number of categories
                $count_categories = count($blog_categories);

                // Show categories
                foreach ($blog_categories as $category) {
                    // Split the view of categories
                    if ($count_categories > 1) {
                        $page_data['content'] .= '<div class="mb-5">' . $category . '</div>';
                    } else {
                        $page_data['content'] .= $category;
                    }

                    $count_categories--;
                }

                break;
        }

        $page_data['content'] .= '<p class="mt-5">';
        if ($this->postAndGet('action') !== 'add-category') $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/blogcategory/?action=add-category', 'Add category') . '<br />';
        if (!empty($this->postAndGet('action'))) $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/blogcategory', 'Blog categories') . '<br />';
        $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel', '{@localization[adminpanel]}}') . '<br />';
        $page_data['content'] .= $this->homelink();
        $page_data['content'] .= '</p>';

        return $page_data;
    }

    /**
     * Page title
     */
    public function pagetitle()
    {
        $page_data['tname'] = 'Page Title';
        $page_data['content'] = '';

        $act = $this->postAndGet('act');

        if (!$this->user->administrator()) $this->redirection('../?error');
        
        if ($act == 'addedit') {
            $tfile = $this->check($this->postAndGet('tfile'));
            $msg = $this->replaceNewLines($this->postAndGet('msg'));
        
            // Get page data
            $pageData = $this->db->selectData('pages', 'file = :file', [':file' => $tfile], 'file, headt');
        
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

            $last_notif = $this->db->selectData('pages', 'pname = :pname', [':pname' => $tpage], '`tname`, `pname`, `file`, `headt`');

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
                    $lnk = $item['pname'] . ' <img src="' . HOMEDIR . 'themes/images/img/edit.gif" alt="" /> <a href="' . HOMEDIR . 'adminpanel/pagetitle/?act=edit&pgfile=' . $item['file'] . '">' . $item['tname'] . '</a> | <img src="' . HOMEDIR . 'themes/images/img/edit.gif" alt="" /> <a href="' . HOMEDIR . 'adminpanel/pagemanager/?action=headtag&file=' . $item['file'] . '">[Edit Meta]</a> | <img src="' . HOMEDIR . 'themes/images/img/close.gif" alt="" /> <a href="' . HOMEDIR . 'adminpanel/pagetitle/?act=del&tid=' . $item['pname'] . '">[DEL]</a>'; 
                    // $page_data['content'] .= " <small>joined: $jdt</small>";
                    $page_data['content'] .= "$lnk<br />";
                }
            }

            $page_data['content'] .= $navigation->get_navigation();

            $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagetitle?act=addnew', 'Add new title', '<p>', '</p>'); // update lang
        }
        
        if ($act == 'edit') {
            $pgfile = $this->check($this->postAndGet('pgfile'));
        
            $page_title = $this->db->selectData('pages', 'file = :file', ['file' => $pgfile], 'tname, pname');
        
            $form = $this->container['parse_page'];
            $form->load('forms/form');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagetitle/?act=addedit');
            $form->set('form_method', 'POST');
        
            $input = $this->container['parse_page'];
            $input->load('forms/input');
            $input->set('input_type', 'hidden');
            $input->set('input_name', 'tfile');
            $input->set('input_value', $pgfile);
        
            $input_2 = $this->container['parse_page'];
            $input_2->load('forms/input');
            $input_2->set('label_for', 'msg');
            $input_2->set('label_value', 'Page title:');
            $input_2->set('input_name', 'msg');
            $input_2->set('input_id', 'msg');
            $input_2->set('input_value', $page_title['tname']);
        
            $form->set('fields', $form->merge(array($input, $input_2)));
            $page_data['content'] .= $form->output();
        
            $page_data['content'] .= '<hr>';
        
            $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagetitle', '{@localization[back]}}', '<p>', '</p>');
        } 

        if ($act == "addnew") {
            $form = $this->container['parse_page'];
            $form->load('forms/form');
            $form->set('form_action', HOMEDIR . 'adminpanel/pagetitle/?act=savenew');
            $form->set('form_method', 'POST');
        
            $input = $this->container['parse_page'];
            $input->load('forms/input');
            $input->set('label_for', 'tpage');
            $input->set('label_value', 'Page:');
            $input->set('input_type', 'text');
            $input->set('input_name', 'tpage');
            $input->set('input_id', 'tpage');
        
            $input_2 = $this->container['parse_page'];
            $input_2->load('forms/input');
            $input_2->set('label_for', 'msg');
            $input_2->set('label_value', 'Page title:');
            $input_2->set('input_type', 'text');
            $input_2->set('input_name', 'msg');
            $input_2->set('input_id', 'msg');
        
            $form->set('fields', $form->merge(array($input, $input_2)));
            $page_data['content'] .= $form->output();
        
            $page_data['content'] .= '<hr />';
        
            $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/pagetitle', '{@localization[back]}}', '<p>', '</p>');
        }
        
        $page_data['content'] .= '<p>';
        $page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel', '{@localization[adminpanel]}}') . '<br />';
        $page_data['content'] .= $this->homelink();
        $page_data['content'] .= '</p>';

        return $page_data;
    }

    /**
     * IP information
     */
    public function ip_information()
    {
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

        $page_data['content'] = '<p>' . $this->sitelink(HOMEDIR . 'adminpanel', '{@localization[adminpanel]}}') . '<br />';
        $page_data['content'] .= $this->homelink() . '</p>';

        return $page_data;
    }    
}