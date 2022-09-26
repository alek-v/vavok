<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Navigation;

class BanModel extends BaseModel {
    /**
     * Add ban
     */
    public function addban()
    {
        // Users data
        $data['user'] = $this->user_data;
        $data['tname'] = '{@localization[banning]}}';
        $data['content'] = '';

        $user = $this->postAndGet('users');

        if (!$this->user->administrator(101) && !$this->user->administrator(102) && !$this->user->moderator(103)) $this->redirection('../?auth_error');
        
        $data['content'] .= '<p><img src="../themes/images/img/partners.gif" alt=""> <b>' . $this->localization->string('banunban') . '</b></p>';
        
        if (empty($this->postAndGet('action'))) {
            $form = $this->model('ParsePage');
            $form->load('forms/form');
            $form->set('form_method', 'post');
            $form->set('form_action', HOMEDIR . 'adminpanel/addban/?action=edit');

            $input = $this->model('ParsePage');
            $input->load('forms/input');
            $input->set('label_for', 'users');
            $input->set('label_value', $this->localization->string('chooseuser'));
            $input->set('input_type', 'text');
            $input->set('input_name', 'users');
            $input->set('input_id', 'users');
            $input->set('maxlength', 20);
        
            $form->set('website_language[save]', $this->localization->string('confirm'));
            $form->set('fields', $input->output());
        
            $data['content'] .= $form->output();
        
            $data['content'] .= '<hr>';
        }

        // edit profile
        if ($this->postAndGet('action') == 'edit') {
            if (!empty($user)) {
                if (ctype_digit($user) === false) {
                    $userx_id = $this->user->getidfromnick($user);
                    $users_nick = $this->user->getnickfromid($userx_id);
                } else {
                    $userx_id = $user;
                    $users_nick = $this->user->getnickfromid($user);
                }

                $user = $this->check($user);
                if (!empty($userx_id) && !empty($users_nick)) {
                    $data['content'] .= '<img src="../themes/images/img/profiles.gif" alt=""> <b>Profile of member ' . $users_nick . '</b><br /><br />'; // update lang
                    $data['content'] .= 'Bans: <b>' . (int)$this->user->user_info('allban', $userx_id) . '</b><br />'; // update lang
                    if (ctype_digit($this->user->user_info('lastban', $userx_id))) {
                        $data['content'] .= $this->localization->string('lastban') . ': ' . $this->correctDate($this->check($this->user->user_info('lastban', $userx_id)), "j.m.y/H:i") . '<br />';
                    }

                    $data['content'] .= '<br />';
        
                    if ($this->user->user_info('perm', $userx_id) >= 101 && $this->user->user_info('perm', $userx_id) <= 105 && $user != $this->user->show_username()) {
                        $data['content'] .= $this->localization->string('noauthtoban') . '<br /><br />';
                    } else {
                        if ($user == $this->user->show_username()) {
                            $data['content'] .= '<b><font color="#FF0000">' . $this->localization->string('myprofile') . '!</font></b><br /><br />';
                        }

                        if ($this->user->user_info('bantime', $userx_id) > 0) {
                            $ost_time = round($this->user->user_info('bantime', $userx_id) - time());
                        } else {
                            $ost_time = time();
                        }

                        if ($this->user->user_info('banned', $userx_id) < 1 || $this->user->user_info('bantime', $userx_id) < time()) {
                            $form = $this->model('ParsePage');
                            $form->load('forms/form');
                            $form->set('form_method', 'post');
                            $form->set('form_action', HOMEDIR . 'adminpanel/addban/?action=banuser&users=' . $users_nick);
        
                            $input_duration = $this->model('ParsePage');
                            $input_duration->load('forms/input');
                            $input_duration->set('label_for', 'duration');
                            $input_duration->set('label_value', $this->localization->string('banduration') . ':');
                            $input_duration->set('input_id', 'duration');
                            $input_duration->set('input_name', 'duration');
        
                            $input_radio_1 = $this->model('ParsePage');
                            $input_radio_1->load('forms/radio');
                            $input_radio_1->set('label_for', 'bform');
                            $input_radio_1->set('label_value', $this->localization->string('minutes'));
                            $input_radio_1->set('input_id', 'bform');
                            $input_radio_1->set('input_name', 'bform');
                            $input_radio_1->set('input_value', 'min');
                            $input_radio_1->set('input_status', 'checked');
        
                            $input_radio_2 = $this->model('ParsePage');
                            $input_radio_2->load('forms/radio');
                            $input_radio_2->set('label_for', 'bform');
                            $input_radio_2->set('label_value', $this->localization->string('hours'));
                            $input_radio_2->set('input_id', 'bform');
                            $input_radio_2->set('input_name', 'bform');
                            $input_radio_2->set('input_value', 'chas');
        
                            $input_radio_3 = $this->model('ParsePage');
                            $input_radio_3->load('forms/radio');
                            $input_radio_3->set('label_for', 'bform');
                            $input_radio_3->set('label_value', $this->localization->string('days'));
                            $input_radio_3->set('input_id', 'bform');
                            $input_radio_3->set('input_name', 'bform');
                            $input_radio_3->set('input_value', 'sut');
        
                            $input_textarea = $this->model('ParsePage');
                            $input_textarea->load('forms/textarea');
                            $input_textarea->set('label_for', 'udd39');
                            $input_textarea->set('label_value', $this->localization->string('bandesc'));
                            $input_textarea->set('textarea_id', 'udd39');
                            $input_textarea->set('textarea_name', 'udd39');
        
                            $form->set('website_language[save]', $this->localization->string('confirm'));
                            $form->set('fields', $form->merge(array($input_duration, $input_radio_1, $input_radio_2, $input_radio_3, $input_textarea)));
                            $data['content'] .= $form->output();
        
                            $data['content'] .= '<hr>';
        
                            $data['content'] .= $this->localization->string('maxbantime') . ' ' . $this->formatTime(round($this->configuration('maxBanTime') * 60)) . '<br />';
                            $data['content'] .= $this->localization->string('bandesc1') . '<br />';
                        } else {
                            $data['content'] .= '<b><font color="#FF0000">' . $this->localization->string('confban') . '</font></b><br />';
                            if (ctype_digit($this->user->user_info('lastban', $userx_id))) {
                                $data['content'] .= $this->localization->string('bandate') . ': ' . $this->correctDate($this->user->user_info('lastban', $userx_id)) . '<br />';
                            }
                            $data['content'] .= $this->localization->string('banend') . ' ' . $this->formatTime($ost_time) . '<br />';
                            $data['content'] .= $this->localization->string('bandesc') . ': ' . $this->check($this->user->user_info('bandesc', $userx_id)) . '<br />'; 
                            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/addban/?action=deleteban&amp;users=' . $user, $this->localization->string('delban')) . '<hr>';
                        }
                    }
                } else {
                    $data['content'] .= $this->localization->string('usrnoexist') . '!<br />';
                }
            } else {
                $data['content'] .= $this->localization->string('nousername') . '!<br />';
            }
        }

        if ($this->postAndGet('action') == 'banuser') {
            $bform = $this->check($this->postAndGet('bform'));
            $udd38 = $this->check($this->postAndGet('duration'));
            $users_id = $this->user->getidfromnick($user);
            $udd39 = $this->check($this->postAndGet('udd39'));

            if (!empty($users_id)) {
                if ($bform == "min") $ban_time = $udd38;
                if ($bform == "chas") $ban_time = round($udd38 * 60);
                if ($bform == "sut") $ban_time = round($udd38 * 60 * 24);

                if (!empty($ban_time)) {
                    if ($ban_time <= $this->configuration('maxBanTime')) {
                        if (!empty($udd39)) {
                            $newbantime = round(time() + ($ban_time * 60));
                            $newbandesc = $this->replaceNewLines($this->check($udd39), ' ');
                            $newlastban = time();
        
                            $newallban = $this->user->user_info('allban', $users_id) + 1;

                            // Update users data
                            $this->user->update_user('banned', 1, $users_id);

                            $fields = array('bantime', 'bandesc', 'lastban', 'allban');
                            $values = array($newbantime, $newbandesc, $newlastban, $newallban);
                            $this->user->update_user($fields, $values, $users_id);
        
                            $data['content'] .= $this->localization->string('usrdata') . ' ' . $user . ' ' . $this->localization->string('edited') . '!<br />';
                            $data['content'] .= '<b><font color="FF0000">' . $this->localization->string('confban') . '</font></b><br /><br />';
                        } else {
                            $data['content'] .= $this->localization->string('noreason') . '!<br />';
                        } 
                    } else {
                        $data['content'] .= $this->localization->string('maxbantimeare') . ' ' . round($this->configuration('maxBanTime') / 1440) . ' ' . $this->localization->string('days') . '!<br />';
                    } 
                } else {
                    $data['content'] .= $this->localization->string('nobantime') . '!<br />';
                }
            } else {
                $data['content'] .= $this->localization->string('usrnoexist') . '!<br />';
            }
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/addban/?action=edit&users=' . $user, $this->localization->string('back'), '<p>', '</p>');
        }

        if ($this->postAndGet('action') == 'deleteban') {
            $users_id = $this->user->getidfromnick($user);

            if (!empty($users_id)) {
                // update changes
                $newallban = $this->user->user_info('allban', $users_id);

                if ($newallban > 0) $newallban = $newallban--;

                $this->user->update_user('banned', 0, $users_id);

                $fields = array('bantime', 'bandesc', 'allban');
                $values = array(0, '', $newallban);
                $this->user->update_user($fields, $values, $users_id);

                $data['content'] .= $this->localization->string('usrdata') . ' ' . $user . ' ' . $this->localization->string('edited') . '!<br />';
                $data['content'] .= '<b><font color="00FF00">' . $this->localization->string('confUnBan') . '</font></b><br /><br />';

                $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/addban', $this->localization->string('changeotheruser')) . '<br />';
            } else {
                $data['content'] .= '<p>' . $this->localization->string('usrnoexist') . '!</p>';
            }

            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/addban/?action=edit&users=' . $user, $this->localization->string('back'), '<p>', '</p>');
        }

        // delete user
        if ($this->postAndGet('action') == 'deluser') {
            $user = $this->check($user);
            $this->user->delete_user($user);

            $data['content'] .= '<p>' . $this->localization->string('usrdeleted') . '!</p>';

            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/addban', $this->localization->string('back'), '<p>', '</p>');
        } 

        $data['content'] .= '<p>' . $this->sitelink(HOMEDIR . 'adminpanel', $this->localization->string('adminpanel')) . '<br />';
        $data['content'] .= $this->homelink() . '</p>';

        return $data;
    }

    /**
     * List of banned users
     */
    public function banlist()
    {
        // Users data
        $data['user'] = $this->user_data;
        $data['tname'] = '{@localization[banlist]}}';
        $data['content'] = '';

        if (!$this->user->administrator() && !$this->user->moderator(103)) $this->redirection('../index.php?error');

        // Number of banned users
        $noi = $this->user->total_banned();
        $items_per_page = 10;
        
        $navigation = new Navigation($items_per_page, $noi, $this->postAndGet('page'), 'banlist.php?'); // start navigation
        $limit_start = $navigation->start()['start']; // starting point
        
        $sql = "SELECT id, name, banned FROM vavok_users WHERE banned='1' OR banned='2' ORDER BY banned LIMIT $limit_start, $items_per_page";
        
        if ($noi > 0) {
            foreach ($this->db->query($sql) as $item) {
                if ($item['banned'] == 1) $data['content'] .= '<div class="a"><p>' . $this->sitelink(HOMEDIR . 'users/u/' . $item['name'], $item['name']) . ' <small>' . $this->localization->string('banduration') . ': ' . $this->correctDate($this->user->user_info('bantime', $item['id']), 'd.m.y.') . ' | ' . $this->localization->string('bandesc') . ': ' . $this->user->user_info('bandesc', $item['id']) . '</small></p></div>';
            }
        } else {
            $data['content'] .= $this->showNotification('<img src="../themes/images/img/reload.gif" alt="" /> ' . $this->localization->string('noentry'));
        }

        $data['navigation'] = $navigation->get_navigation();

        $data['bottom_links'] = $this->sitelink('./', $this->localization->string('adminpanel')) . '<br />';
        $data['bottom_links'] .= $this->homelink();

        return $data;
    }
}