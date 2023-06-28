<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Navigation;
use App\Traits\Notifications;

class BanModel extends BaseModel {
    use Notifications;

    /**
     * Add ban
     *
     * @return array
     */
    public function addban(): array
    {
        $this->page_data['page_title'] = '{@localization[banning]}}';

        $user = $this->postAndGet('users');

        if (!$this->user->administrator(101) && !$this->user->administrator(102) && !$this->user->moderator(103)) {
            $this->redirection('../?auth_error');
        }
        
        $this->page_data['content'] .= '<h1><img src="../themes/images/img/partners.gif" alt=""> <b>{@localization[banunban]}}</b></h1>';
        
        if (empty($this->postAndGet('action'))) {
            $form = $this->container['parse_page'];
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/addban/?action=edit');

            $input = $this->container['parse_page'];
            $input->load('forms/input');
            $input->set('label_for', 'users');
            $input->set('label_value', $this->localization->string('chooseuser'));
            $input->set('input_type', 'text');
            $input->set('input_name', 'users');
            $input->set('input_id', 'users');
            $input->set('maxlength', 20);
        
            $form->set('localization[save]', $this->localization->string('confirm'));
            $form->set('fields', $input->output());
        
            $this->page_data['content'] .= $form->output();
        
            $this->page_data['content'] .= '<hr>';
        }

        // edit profile
        if ($this->postAndGet('action') == 'edit') {
            if (empty($user)) {
                $this->page_data['content'] .= $this->localization->string('no_username');

                return $this->page_data;
            }

            if (ctype_digit($user) === false) {
                $userx_id = $this->user->getIdFromNick($user);
                $users_nick = $this->user->getNickFromId($userx_id);
            } else {
                $userx_id = $user;
                $users_nick = $this->user->getNickFromId($user);
            }

            if (empty($userx_id) || empty($users_nick)) {
                $this->page_data['content'] .= $this->localization->string('user_does_not_exist');

                return $this->page_data;
            }

            $this->page_data['content'] .= '<p><img src="../themes/images/img/profiles.gif" alt="">Profile of the member ' . $users_nick . '</p>'; // todo: update locale
            $this->page_data['content'] .= '<p>Bans: <b>' . (int)$this->user->userInfo('all_bans', $userx_id) . '</b></p>'; // todo: update locale

            if (!empty($this->user->userInfo('last_ban', $userx_id))) {
                $this->page_data['content'] .= '<p>' . $this->localization->string('lastban') . ': ' . $this->correctDate($this->check($this->user->userInfo('last_ban', $userx_id)), "j.m.y / H:i") . '</p>';
            }

            if ($this->user->userInfo('access_permission', $userx_id) == 101) {
                $this->page_data['content'] .= '<p>' . $this->localization->string('noauthtoban') . '</p>';

                return $this->page_data;
            }

            if ($user == $this->user->showUsername()) {
                $this->page_data['content'] .= '<p><b><font color="#FF0000">{@localization[you_are_changing_your_profile]}}</font></b></p>';
            }

            if ($this->user->userInfo('ban_time', $userx_id) > 0) {
                $ost_time = round($this->user->userInfo('ban_time', $userx_id) - time());
            } else {
                $ost_time = time();
            }

            if ($this->user->userInfo('banned', $userx_id) < 1 || $this->user->userInfo('ban_time', $userx_id) < time()) {
                $form = $this->container['parse_page'];
                $form->load('forms/form');
                $form->set('form_method', 'post');
                $form->set('form_action', HOMEDIR . 'adminpanel/addban/?action=banuser&users=' . $users_nick);

                $input_duration = $this->container['parse_page'];
                $input_duration->load('forms/input');
                $input_duration->set('label_for', 'duration');
                $input_duration->set('label_value', $this->localization->string('banduration') . ':');
                $input_duration->set('input_id', 'duration');
                $input_duration->set('input_name', 'duration');

                $input_radio_1 = $this->container['parse_page'];
                $input_radio_1->load('forms/radio');
                $input_radio_1->set('label_for', 'bform');
                $input_radio_1->set('label_value', $this->localization->string('minutes'));
                $input_radio_1->set('input_id', 'bform');
                $input_radio_1->set('input_name', 'bform');
                $input_radio_1->set('input_value', 'min');
                $input_radio_1->set('input_status', 'checked');

                $input_radio_2 = $this->container['parse_page'];
                $input_radio_2->load('forms/radio');
                $input_radio_2->set('label_for', 'bform');
                $input_radio_2->set('label_value', $this->localization->string('hours'));
                $input_radio_2->set('input_id', 'bform');
                $input_radio_2->set('input_name', 'bform');
                $input_radio_2->set('input_value', 'chas');

                $input_radio_3 = $this->container['parse_page'];
                $input_radio_3->load('forms/radio');
                $input_radio_3->set('label_for', 'bform');
                $input_radio_3->set('label_value', $this->localization->string('days'));
                $input_radio_3->set('input_id', 'bform');
                $input_radio_3->set('input_name', 'bform');
                $input_radio_3->set('input_value', 'sut');

                $input_textarea = $this->container['parse_page'];
                $input_textarea->load('forms/textarea');
                $input_textarea->set('label_for', 'udd39');
                $input_textarea->set('label_value', $this->localization->string('ban_reason'));
                $input_textarea->set('textarea_id', 'udd39');
                $input_textarea->set('textarea_name', 'udd39');

                $form->set('localization[save]', $this->localization->string('confirm'));
                $form->set('fields', $form->merge(array($input_duration, $input_radio_1, $input_radio_2, $input_radio_3, $input_textarea)));
                $this->page_data['content'] .= $form->output();

                $this->page_data['content'] .= '<hr>';

                $this->page_data['content'] .= '<p>' . $this->localization->string('maxbantime') . ' ' . $this->formatTime(round($this->configuration->getValue('max_ban_time') * 60)) . '</p>';
                $this->page_data['content'] .= '<p>' . $this->localization->string('please_state_ban_description') . '</p>';
            } else {
                $this->page_data['content'] .= '<p><b><font color="#FF0000">{@localization[user_is_banned]}}</font></b></p>';

                if (!empty($this->user->userInfo('last_ban', $userx_id))) {
                    $this->page_data['content'] .= '<p>' . $this->localization->string('bandate') . ': ' . $this->correctDate($this->user->userInfo('last_ban', $userx_id)) . '</p>';
                }

                $this->page_data['content'] .= '<p>' . $this->localization->string('banend') . ' ' . $this->formatTime($ost_time) . '</p>';
                $this->page_data['content'] .= '<p>' . $this->localization->string('ban_reason') . ': ' . $this->check($this->user->userInfo('ban_description', $userx_id)) . '</p>';
                $this->page_data['content'] .= '<p>' . $this->sitelink(HOMEDIR . 'adminpanel/addban/?action=deleteban&users=' . $user, $this->localization->string('delban')) . '</p><hr>';
            }
        }

        if ($this->postAndGet('action') == 'banuser') {
            $bform = $this->check($this->postAndGet('bform'));
            $udd38 = $this->check($this->postAndGet('duration'));
            $users_id = $this->user->getIdFromNick($user);
            $udd39 = $this->check($this->postAndGet('udd39'));

            if (empty($users_id)) {
                $this->page_data['content'] .= '<p>' . $this->localization->string('user_does_not_exist') . '</p>';
            }

            if ($bform == "min") $ban_time = $udd38;
            if ($bform == "chas") $ban_time = round($udd38 * 60);
            if ($bform == "sut") $ban_time = round($udd38 * 60 * 24);

            if (empty($ban_time)) {
                $this->page_data['content'] .= '<p>' . $this->localization->string('nobantime') . '</p>';

                return $this->page_data;
            }

            if ($ban_time <= $this->configuration->getValue('max_ban_time')) {
                $this->page_data['content'] .= '<p>' . $this->localization->string('maxbantimeare') . ' ' . round($this->configuration->getValue('max_ban_time') / 1440) . ' {@localization[days]}}</p>';
            }

            if (empty($udd39)) {
                $this->page_data['content'] .= '<p>' . $this->localization->string('noreason') . '</p>';

                return $this->page_data;
            }

            $newbantime = round(time() + ($ban_time * 60));
            $newbandesc = $this->replaceNewLines($this->check($udd39), ' ');
            $newlastban = time();

            $newallban = $this->user->userInfo('all_bans', $users_id) + 1;

            // Update users data
            $this->user->updateUser('banned', 1, $users_id);

            $fields = array('ban_time', 'ban_description', 'last_ban', 'all_bans');
            $values = array($newbantime, $newbandesc, $newlastban, $newallban);
            $this->user->updateUser($fields, $values, $users_id);

            $this->page_data['content'] .= '<p>' . $this->localization->string('usrdata') . ' ' . $user . ' {@localization[edited]}}!<br />';
            $this->page_data['content'] .= '<b><font color="FF0000">{@localization[user_is_banned]}}</font></b></p>';

            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/addban/?action=edit&users=' . $user, $this->localization->string('back'), '<p>', '</p>');
        }

        if ($this->postAndGet('action') == 'deleteban') {
            $users_id = $this->user->getIdFromNick($user);

            if (!empty($users_id)) {
                // update changes
                $newallban = $this->user->userInfo('all_bans', $users_id);

                if ($newallban > 0) $newallban = $newallban--;

                $this->user->updateUser('banned', 0, $users_id);

                $fields = array('ban_time', 'ban_description', 'all_bans');
                $values = array(0, '', $newallban);
                $this->user->updateUser($fields, $values, $users_id);

                $this->page_data['content'] .= $this->localization->string('usrdata') . ' ' . $user . ' {@localization[edited]}}!<br />';
                $this->page_data['content'] .= '<b><font color="00FF00">{@localization[confUnBan]}}</font></b><br /><br />';

                $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/addban', $this->localization->string('changeotheruser')) . '<br />';
            } else {
                $this->page_data['content'] .= '<p>{@localization[user_does_not_exist]}}!</p>';
            }

            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/addban/?action=edit&users=' . $user, $this->localization->string('back'), '<p>', '</p>');
        }

        // Delete the user
        if ($this->postAndGet('action') == 'deluser') {
            $user = $this->check($user);
            $this->user->deleteUser($user);

            $this->page_data['content'] .= '<p>{@localization[usrdeleted]}}!</p>';

            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/addban', $this->localization->string('back'), '<p>', '</p>');
        }

        return $this->page_data;
    }

    /**
     * List of the banned users
     */
    public function banlist()
    {
        $this->page_data['page_title'] = '{@localization[banlist]}}';

        if (!$this->user->administrator() && !$this->user->moderator(103)) $this->redirection('../index.php?error');

        // Number of banned users
        $noi = $this->user->totalBanned();
        $items_per_page = 10;

        $navigation = new Navigation($items_per_page, $noi, 'banlist?'); // start navigation
        $limit_start = $navigation->start()['start']; // starting point

        $sql = "SELECT id, name, banned FROM vavok_users WHERE banned='1' OR banned='2' ORDER BY banned LIMIT $limit_start, $items_per_page";

        if ($noi > 0) {
            foreach ($this->db->query($sql) as $item) {
                if ($item['banned'] == 1) {
                    $this->page_data['content'] .= '<div class="a"><p>' . $this->sitelink(HOMEDIR . 'users/u/' . $item['name'], $item['name']) . ' <small>{@localization[banduration]}}: ' . $this->correctDate($this->user->userInfo('ban_time', $item['id']), 'd.m.y.') . ' | {@localization[ban_reason]}}: ' . $this->user->userInfo('ban_description', $item['id']) . '</small></p></div>';
                }
            }
        } else {
            $this->page_data['content'] .= $this->showNotification('<img src="../themes/images/img/reload.gif" alt="" /> ' . $this->localization->string('noentry'));
        }

        $this->page_data['navigation'] = $navigation->getNavigation();

        return $this->page_data;
    }
}