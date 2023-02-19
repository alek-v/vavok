<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Navigation;

class Page extends BaseModel {
    private string $page_localization;

    /**
     * Homepage data
     * 
     * @param array $params
     * @return array
     */
    public function homepage($params = [])
    {
        // Localization from the URL
        $this->page_localization = !empty($params[0]) ? $this->user->getPreferredLanguage($params[0], 'short') : '';

        // Try to get localized page
        $data = !empty($params[0])
        ? $this->db->selectData('pages', "slug = :slug AND localization = :localization", array(
            ':slug' => 'index',
            ':localization' => $params[0]
        ))
        : '';

        // Page without localization
        if (empty($data)) $data = $this->db->selectData('pages', "slug = :slug", array(':slug' => 'index'));

        // Update user's language when language is set in URL and it is different then current localization
        if (!empty($this->page_localization) && strtolower($this->page_localization) != $this->user->getPreferredLanguage($_SESSION['lang'], 'short')) {
            $this->user->changeLanguage(strtolower($this->page_localization));
        }

        // Redirect if user's language is not website default language,
        // language is not in URL, example: www.example.com
        // and page with users's language exists, example: www.example.com/de
        if ($this->configuration('siteDefaultLang') != $this->user->getUserLanguage() && empty($params[0])) $this->redirection(HOMEDIR . $this->user->getPreferredLanguage($this->user->getUserLanguage(), 'short') . '/');

        return $data = isset($data['page_title']) ? $data : die('error404');
    }

    /**
     * Dynamic loading pages from database
     * 
     * @param array $params
     */
    public function dynamic($params = [])
    {
        // Page data
        $this_page = $this->db->selectData('pages', 'slug = :param', array(':param' => $params[0]));

        // Handle when page doesn't exist
        if (!$this_page) {
            return $this->handleNoPageError();
        }

        // Page localization
        $page_localization = isset($this_page['localization']) ? $this_page['localization'] : '';

        // Update user's localization when page's language is different then current localization
        $this->user->updatePageLocalization($page_localization);

        return $this_page;
    }

    /**
     * Login page
     * 
     * @param array $data
     * @return array $data
     */
    public function login($data = [])
    {
        // Check login data while logging in
        $data = $this->user->checkAuth();

        $data['page_title'] = '{@localization[login]}}';
        $data['head_tags'] = '<meta name="robots" content="noindex">';
        $data['head_tags'] .= '<link rel="stylesheet" href="' . HOMEDIR . 'themes/templates/users/login/login.css">';

        return $data;
    }

    /**
     * Logout
     */
    public function logout()
    {
        $this->user->logout();

        header('Location: ' . HOMEDIR);
        exit;
    }

    /**
     * List of users
     */
    public function userlist()
    {
        $data['page_title'] = '{@localization[userlist]}}';
        $data['content'] = '';

        $num_items = $this->user->countRegisteredMembers(); // no. reg. members
        $items_per_page = 10;
 
        // Start navigation
        $navigation = new Navigation($items_per_page, $num_items, HOMEDIR . 'pages/userlist/?');

        // Starting point
        $limit_start = $navigation->start()['start'];
        
        if ($num_items > 0) {
            foreach ($this->db->query("SELECT id, name FROM vavok_users ORDER BY name LIMIT $limit_start, $items_per_page") as $item) {
                $data['content'] .= '<div class="a">';
                $data['content'] .= '<a href="' . HOMEDIR . 'users/u/' . $item['id'] . '">' . $item['name'] . '</a> - joined: ' . $this->correctDate($this->user->userInfo('registration_date', $item['id']), 'd.m.Y.'); // update lang
                $data['content'] .= '</div>';
            }
        }

        $data['content'] .= $navigation->getNavigation();
        $data['content'] .= $this->homelink();

        return $data;
    }

    /**
     * Statistics
     */
    public function statistics()
    {
        $data['page_title'] = '{@localization[statistics]}}';
        $data['content'] = '';

        if ($this->configuration('showCounter') == 6 && !$this->user->administrator()) $this->redirection(HOMEDIR);

        $hour = (int)date("H", time());
        $hday = date("j", time())-1;

        $pcounter_guest = $this->db->countRow('online', "user='0'");

        $pcounter_online = $this->db->countRow('online');

        $pcounter_reg = $pcounter_online - $pcounter_guest;

        $counts = $this->db->selectData('counter');

        $clicks_today = $counts['clicks_today'];
        $total_clicks = $counts['clicks_total'];
        $visits_today = $counts['visits_today']; // visits today
        $total_visits = $counts['visits_total']; // total visits
    
        $data['content'] .= '{@localization[temponline]}}: ';
        if ($this->configuration('showOnline') == 1 || $this->user->administrator()) {
            $data['content'] .= '<a href="{@HOMEDIR}}pages/online">' . (int)$pcounter_online . '</a><br />';
        } else {
            $data['content'] .= '<b>' . (int)$pcounter_online . '</b><br />';
        }

        $data['content'] .= '{@localization[registered]}}: <b>' . (int)$pcounter_reg . '</b><br />';
        $data['content'] .= '{@localization[guests]}}: <b>' . (int)$pcounter_guest . '</b><br /><br />';
    
        $data['content'] .= '{@localization[vststoday]}}: <b>' . (int)$visits_today . '</b><br />';
        $data['content'] .= '{@localization[vstpagestoday]}}: <b>' . (int)$clicks_today . '</b><br />';
        $data['content'] .= '{@localization[totvisits]}}: <b>' . (int)$total_visits . '</b><br />';
        $data['content'] .= '{@localization[totopenpages]}}: <b>' . (int)$total_clicks . '</b><br /><br />';
        
        $data['content'] .= $this->homelink('<p>', '</p>');

        return $data;
    }

    /**
     * Users online
     */
    public function online()
    {
        $data['page_title'] = 'Online';
        $data['content'] = '';

        if ($this->configuration('showOnline') == 0 && (!$this->user->userAuthenticated() && !$this->user->administrator())) $this->redirection("../");

        // page settings
        $data_on_page = 10; // online users per page
        
        $data['content'] .= '<p><img src="{@HOMEDIR}}themes/images/img/online.gif" alt=""> <b>{@localization[whoisonline]}}</b></p>';
        
        $total = $this->db->countRow('online');
        $totalreg = $this->db->countRow('online', "user > 0");
        
        if (!empty($this->postAndGet('list'))) {
            $list = $this->check($this->postAndGet('list'));
        } else {
            if ($totalreg > 0) {
                $list = 'reg';
            } else {
                $list = 'full';
            } 
        } 
        if ($list != 'full' && $list != 'reg') {
            $list = 'full';
        }

        $data['content'] .= $this->localization->string('totonsite') . ': <b>' . (int)$total . '</b><br />{@localization[registered]}}:  <b>' . (int)$totalreg . '</b><br /><hr>';
        
        if ($list == 'full') {
            $navigation = new Navigation($data_on_page, $total, HOMEDIR . 'pages/online/?list=full&'); // start navigation
        
            $start = $navigation->start()['start']; // starting point 
        
            $full_query = "SELECT * FROM online ORDER BY date DESC LIMIT $start, " . $data_on_page;
        
            foreach ($this->db->query($full_query) as $item) {
                $time = $this->correctDate($item['date'], 'H:i');
        
                if (($item['user'] == "0" || empty($item['user'])) && empty($item['bot'])) {
                    $data['content'] .= '<b>{@localization[guest]}}</b> ({@localization[time]}}: ' . $time . ')<br />';
                    if ($this->user->moderator() || $this->user->administrator()) {
                        $data['content'] .= '<small><font color="#CC00CC">(<a href="' . HOMEDIR . $this->configuration('mPanel') . '/ip_information/?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
                    } 
                    $data['content'] .= '<hr />';
                } elseif (!empty($item['bot']) && ($item['user'] == "0" || empty($item['user']))) {
                    $data['content'] .= '<b>' . $item['bot'] . '</b> ({@localization[time]}}: ' . $time . ')<br />';
                    if ($this->user->moderator() || $this->user->administrator()) {
                        $data['content'] .= '<small><font color="#CC00CC">(<a href="' . HOMEDIR . $this->configuration('mPanel') . '/ip_information/?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
                    } 
                    $data['content'] .= '<hr />';
                } else {
                    $data['content'] .= '<b><a href="' . HOMEDIR . 'users/u/' . $item['user'] . '">' . $this->user->getNickFromId($item['user']) . '</a></b> ({@localization[time]}}: ' . $time . ')<br />';
                    if ($this->user->moderator() || $this->user->administrator()) {
                        $data['content'] .= '<small><font color="#CC00CC">(<a href="' . HOMEDIR . $this->configuration('mPanel') . '/ip_information/?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
                    }
                    $data['content'] .= '<hr />';
                }
            }
        } else {
            $total = $totalreg;
        
            if ($total < 1) {
                $data['content'] .= '<p><img src="{@HOMEDIR}}themes/images/img/reload.gif" alt=""> <b>' . $this->localization->string('noregd') . '!</b></p>';
            }

            $navigation = new Navigation($data_on_page, $total, HOMEDIR . 'online/?'); // start navigation

            $start = $navigation->start()['start']; // starting point
        
            $full_query = "SELECT * FROM online WHERE user > 0 ORDER BY date DESC LIMIT $start, " . $data_on_page;
        
            foreach ($this->db->query($full_query) as $item) {
                $time = $this->correctDate($item['date'], 'H:i');
        
                $data['content'] .= '<b><a href="' . HOMEDIR . 'users/u/' . $item['user'] . '">' . $this->user->getNickFromId($item['user']) . '</a></b> ({@localization[time]}}: ' . $time . ')<br />';

                if ($this->user->moderator() || $this->user->administrator()) {
                    $data['content'] .= '<small><font color="#CC00CC">(<a href="' . HOMEDIR . $this->configuration('mPanel') . '/ip_information/?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
                }

                $data['content'] .= '<hr />';
            }
        }

        $data['content'] .= $navigation->getNavigation();

        if ($list != 'full') {
            $data['content'] .= $this->sitelink(HOMEDIR . 'pages/online/?list=full', $this->localization->string('showguest'), '<p>', '</p>');
        } else {
            $data['content'] .= $this->sitelink(HOMEDIR . 'pages/online/?list=reg', $this->localization->string('hideguest'), '<p>', '</p>');
        }

        $data['content'] .= $this->homelink('<p>', '</p>');

        return $data;
    }

    /**
     * Cookies policy
     */
    public function cookies_policy()
    {
        $this_page['page_title'] = 'Cookies Policy';
        $this_page['homeurl'] = $this->configuration('homeUrl');

        return $this_page;
    }
}