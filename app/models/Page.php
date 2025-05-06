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

        // Try to get page with user's preferred localization
        $page_data = !empty($params[0])
        ? $this->db->selectData('pages', "slug = :slug AND localization = :localization", array(
            ':slug' => 'index',
            ':localization' => $params[0]
        ))
        : '';

        // When we are trying to open localization that doesn't exist
        if (isset($params[0]) && empty($page_data)) {
            return $this->handleNoPageError();
        }

        // Page without localization
        if (empty($this->page_localization)) {
            $page_data = $this->db->selectData('pages', "slug = :slug", array(':slug' => 'index'));
        }

        // Update user's language when language is set in URL and it is different then current localization
        if (!empty($this->page_localization) && strtolower($this->page_localization) != $this->user->getPreferredLanguage($_SESSION['lang'], 'short')) {
            $this->user->changeLanguage(strtolower($this->page_localization));
        }

        // Redirect if user's language is not website default language,
        // language is not in URL, example: www.example.com
        // and page with users's language exists, example: www.example.com/de
        if ($this->configuration->getValue('default_localization') != $this->user->getUserLanguage() && empty($params[0])) {
            $this->redirection(HOMEDIR . $this->user->getPreferredLanguage($this->user->getUserLanguage(), 'short') . '/');
        }

        return $this->page_data = isset($page_data['page_title']) ? $page_data : $this->handleNoPageError();
    }

    /**
     * Dynamic loading pages from database
     * 
     * @param array $params
     */
    public function dynamic($params = [])
    {
        // Page data
        $full_data = $this->db->getMultilangPage($params[0]);

        // Requested page wil always be in the first row
        $page_data = $full_data[0];

        if (!empty($page_data) && ($page_data['published_status'] == 2 || $this->container['user']->administrator() || $this->container['user']->moderator())) {
            $this->page_data = $page_data;
        }

        // Handle when page doesn't exist
        else {
            return $this->handleNoPageError();
        }

        // Page localization
        $page_localization = $this->page_data['localization'] ?? '';

        // Update the user's localization when the page language differs from the current localization
        $this->user->updatePageLocalization($page_localization);

        return $full_data;
    }

    /**
     * Login page
     * 
     * @return array $this->page_data
     */
    public function login(): array
    {
        // Check login data while logging in
        $this->page_data = $this->user->checkAuth();

        $this->page_data['page_title'] = '{@localization[login]}}';
        $this->page_data['head_tags'] = '<meta name="robots" content="noindex">';
        $this->page_data['head_tags'] .= '<link rel="stylesheet" href="' . HOMEDIR . 'themes/templates/users/login/login.css">';

        return $this->page_data;
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
        $this->page_data['page_title'] = '{@localization[userlist]}}';

        $num_items = $this->user->countRegisteredMembers(); // no. reg. members
        $items_per_page = 10;
 
        // Start navigation
        $navigation = new Navigation($items_per_page, $num_items, HOMEDIR . 'pages/userlist/?');

        // Starting point
        $limit_start = $navigation->start()['start'];
        
        if ($num_items > 0) {
            foreach ($this->db->query("SELECT id, name FROM vavok_users ORDER BY name LIMIT $limit_start, $items_per_page") as $item) {
                $this->page_data['content'] .= '<div class="a">';
                $this->page_data['content'] .= '<a href="' . HOMEDIR . 'users/u/' . $item['id'] . '">' . $item['name'] . '</a> - joined: ' . $this->correctDate($this->user->userInfo('registration_date', $item['id']), $this->localization->showAll()['date_format']); // todo: update localization
                $this->page_data['content'] .= '</div>';
            }
        }

        $this->page_data['content'] .= $navigation->getNavigation();

        return $this->page_data;
    }

    /**
     * Statistics
     */
    public function statistics()
    {
        $this->page_data['page_title'] = '{@localization[statistics]}}';

        if ($this->configuration->getValue('show_counter') == 6 && !$this->user->administrator()) $this->redirection(HOMEDIR);

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
    
        $this->page_data['content'] .= '{@localization[temponline]}}: ';
        if ($this->configuration->getValue('show_online') == 1 || $this->user->administrator()) {
            $this->page_data['content'] .= '<a href="{@HOMEDIR}}pages/online">' . (int)$pcounter_online . '</a><br />';
        } else {
            $this->page_data['content'] .= '<b>' . (int)$pcounter_online . '</b><br />';
        }

        $this->page_data['content'] .= '<p>{@localization[registered]}}: <b>' . (int)$pcounter_reg . '</b><br />';
        $this->page_data['content'] .= '{@localization[guests]}}: <b>' . (int)$pcounter_guest . '</b></p>';
    
        $this->page_data['content'] .= '<p>{@localization[vststoday]}}: <b>' . (int)$visits_today . '</b><br />';
        $this->page_data['content'] .= '{@localization[vstpagestoday]}}: <b>' . (int)$clicks_today . '</b><br />';
        $this->page_data['content'] .= '{@localization[totvisits]}}: <b>' . (int)$total_visits . '</b><br />';
        $this->page_data['content'] .= '{@localization[totopenpages]}}: <b>' . (int)$total_clicks . '</b></p>';

        return $this->page_data;
    }

    /**
     * Users online
     */
    public function online()
    {
        $this->page_data['page_title'] = 'Online';

        if ($this->configuration->getValue('show_online') == 0 && (!$this->user->userAuthenticated() && !$this->user->administrator())) $this->redirection("../");

        // page settings
        $data_on_page = 10; // online users per page
        
        $this->page_data['content'] .= '<p><img src="{@HOMEDIR}}themes/images/img/online.gif" alt=""> <b>{@localization[whoisonline]}}</b></p>';
        
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

        $this->page_data['content'] .= $this->localization->string('totonsite') . ': <b>' . (int)$total . '</b><br />{@localization[registered]}}:  <b>' . (int)$totalreg . '</b><br /><hr>';
        
        if ($list == 'full') {
            $navigation = new Navigation($data_on_page, $total, HOMEDIR . 'pages/online/?list=full&'); // start navigation
        
            $start = $navigation->start()['start']; // starting point 
        
            $full_query = "SELECT * FROM online ORDER BY date DESC LIMIT $start, " . $data_on_page;
        
            foreach ($this->db->query($full_query) as $item) {
                $time = $this->correctDate($item['date'], 'H:i');
        
                if (($item['user'] == "0" || empty($item['user'])) && empty($item['bot'])) {
                    $this->page_data['content'] .= '<b>{@localization[guest]}}</b> ({@localization[time]}}: ' . $time . ')<br />';
                    if ($this->user->moderator() || $this->user->administrator()) {
                        $this->page_data['content'] .= '<small><font color="#CC00CC">(<a href="' . HOMEDIR . $this->configuration->getValue('admin_panel') . '/ip_information/?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
                    } 
                    $this->page_data['content'] .= '<hr />';
                } elseif (!empty($item['bot']) && ($item['user'] == "0" || empty($item['user']))) {
                    $this->page_data['content'] .= '<b>' . $item['bot'] . '</b> ({@localization[time]}}: ' . $time . ')<br />';
                    if ($this->user->moderator() || $this->user->administrator()) {
                        $this->page_data['content'] .= '<small><font color="#CC00CC">(<a href="' . HOMEDIR . $this->configuration->getValue('admin_panel') . '/ip_information/?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
                    } 
                    $this->page_data['content'] .= '<hr />';
                } else {
                    $this->page_data['content'] .= '<b><a href="' . HOMEDIR . 'users/u/' . $item['user'] . '">' . $this->user->getNickFromId($item['user']) . '</a></b> ({@localization[time]}}: ' . $time . ')<br />';
                    if ($this->user->moderator() || $this->user->administrator()) {
                        $this->page_data['content'] .= '<small><font color="#CC00CC">(<a href="' . HOMEDIR . $this->configuration->getValue('admin_panel') . '/ip_information/?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
                    }
                    $this->page_data['content'] .= '<hr />';
                }
            }
        } else {
            $total = $totalreg;
        
            if ($total < 1) {
                $this->page_data['content'] .= '<p><img src="{@HOMEDIR}}themes/images/img/reload.gif" alt=""> <b>' . $this->localization->string('noregd') . '!</b></p>';
            }

            $navigation = new Navigation($data_on_page, $total, HOMEDIR . 'online/?'); // start navigation

            $start = $navigation->start()['start']; // starting point
        
            $full_query = "SELECT * FROM online WHERE user > 0 ORDER BY date DESC LIMIT $start, " . $data_on_page;
        
            foreach ($this->db->query($full_query) as $item) {
                $time = $this->correctDate($item['date'], 'H:i');
        
                $this->page_data['content'] .= '<b><a href="' . HOMEDIR . 'users/u/' . $item['user'] . '">' . $this->user->getNickFromId($item['user']) . '</a></b> ({@localization[time]}}: ' . $time . ')<br />';

                if ($this->user->moderator() || $this->user->administrator()) {
                    $this->page_data['content'] .= '<small><font color="#CC00CC">(<a href="' . HOMEDIR . $this->configuration->getValue('admin_panel') . '/ip_information/?ip=' . $item['ip'] . '" target="_blank">' . $item['ip'] . '</a>)</font></small>';
                }

                $this->page_data['content'] .= '<hr />';
            }
        }

        $this->page_data['content'] .= $navigation->getNavigation();

        if ($list != 'full') {
            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'pages/online/?list=full', $this->localization->string('showguest'), '<p>', '</p>');
        } else {
            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'pages/online/?list=reg', $this->localization->string('hideguest'), '<p>', '</p>');
        }

        return $this->page_data;
    }

    /**
     * Cookies policy
     */
    public function cookies_policy()
    {
        $this->page_data['page_title'] = 'Cookies Policy';
        $this->page_data['homeurl'] = $this->configuration->getValue('home_address');

        return $this->page_data;
    }
}