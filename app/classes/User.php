<?php
/**
 * Author:    Aleksandar Vranešević
 * URL:       https://vavok.net
 */

namespace App\Classes;

use App\Traits\Core;
use App\Traits\Validations;
use Pimple\Container;
use DateTime;
use DateInterval;

class User {
    use Core, Validations;

    protected Database $db;
    protected Config $configuration;
    public array $user_data = [
        'authenticated' => false,
        'admin_status' => 'user',
        'language' => 'english',
        'banned' => 0
    ];
    protected $visited_pages;
    protected $time_on_site;

    public function __construct(protected Container $container)
    {
        // Database connection
        $this->db = $this->container['db'];
        $this->configuration = $this->container['config'];

        // Try to authenticate user using data from the cookie
        if (empty($_SESSION['log']) && !empty($_COOKIE['cookie_login'])) {
            $cookie_token_value = $_COOKIE['cookie_login'] ?? '';

            // Search for the token and get token data
            $cookie_data = $this->db->selectData('tokens', 'token = :token', [':token' => $this->check($cookie_token_value)], 'uid, token');
            $cookie_id = $cookie_data['uid'] ?? ''; // User's id

            // Get user's data
            $users_data = $this->db->selectData('vavok_users', 'id = :id', [':id' => $cookie_id]);

            // Validate data from the cookie
            if (isset($users_data['name']) && !empty($users_data['name']) && ($_COOKIE['cookie_login'] === $cookie_token_value)) {
                // Update session with a fresh data
                $this->updateSession([
                    'log' => $users_data['name'],
                    'permissions' => $users_data['access_permission'],
                    'uid' => $users_data['id'],
                    'lang' => $users_data['localization']
                ]);
            }
            // Data from the cookie failed to validate
            else {
                // Token from cookie is not valid or it is expired, delete cookie
                setcookie('cookie_login', '', time() - 3600);
                setcookie('cookie_login', '', 1, '/', $this->cleanDomain());
            }
        }

        // Validate data from the session
        if (!empty($_SESSION['uid'])) {
            $vavok_users = empty($users_data) || !isset($cookie_id) || ($_SESSION['uid'] !== $cookie_id)  ? $this->db->selectData('vavok_users', 'id = :id', [':id' => $_SESSION['uid']]) : $users_data;
            $user_profil = $this->db->selectData('vavok_profile', 'uid = :uid', [':uid' => $_SESSION['uid']], 'registration_activated');

            // Write current session data
            $this->updateSession([
                'log' => $vavok_users['name'],
                'permissions' => $vavok_users['access_permission'],
                'uid' => $vavok_users['id'],
                'lang' => $vavok_users['localization']
            ]);

            // Set users data
            $this->setCurrentUser($vavok_users);
            $this->user_data['authenticated'] = true;

            // Update last visit
            $this->db->update('vavok_profile', 'last_visit', time(), "uid='{$this->user_data['id']}'");

            // Update IP address if changed
            if ($this->userInfo('ip_address') != $this->findIpAddress()) {
                $this->db->update('vavok_users', 'ip_address', $this->findIpAddress(), "id = '{$this->user_data['id']}'");
            }

             // Time zone
            if (!empty($vavok_users['timezone'])) {
                define('MY_TIMEZONE', $vavok_users['timezone']);
            }

            // Check if user is banned
            if (isset($vavok_users['banned']) && $vavok_users['banned'] == 1 && !strstr($_SERVER['QUERY_STRING'], 'users/ban')) {
                $this->redirection(HOMEDIR . 'users/ban');
            }

             // User need to activate the account
            if (isset($user_profil['registration_activated']) && $user_profil['registration_activated'] == 1 && !strstr($_SERVER['QUERY_STRING'], 'pages/key')) {
                setcookie('cookie_login', '');
                setcookie(session_name(), '');
                unset($_SESSION['log']);
                session_destroy();
            }
        }

        // Localization for the new visitor
        if ((empty($_SESSION['uid'])) && empty($_SESSION['lang'])) {
            $this->changeLanguage();
        }

        // Count visited pages and time on site
        if (empty($_SESSION['currs'])) {
            $_SESSION['currs'] = time();
        }
        if (empty($_SESSION['counton'])) {
            $_SESSION['counton'] = 0;
        }

        $_SESSION['counton']++;

        // Pages visited at this session
        $this->visited_pages = $_SESSION['counton'];

        // Visitor's time on the site
        $this->time_on_site = $this->makeTime(round(time() - $_SESSION['currs']));

        // If timezone is not defined use default
        if (!defined('MY_TIMEZONE')) {
            define('MY_TIMEZONE', $this->configuration->getValue('timezone'));
        }

        // Site theme
        $config_themes = $this->configuration->getValue('site_theme');

        // If theme does not exist use default theme
        // For admin panel use default theme
        if (!file_exists(APPDIR . 'views/' . $config_themes) || strpos($this->websiteHomeAddress() . $_SERVER['PHP_SELF'], $_SERVER['HTTP_HOST'] . '/adminpanel') !== false) {
            $config_themes = 'default';
        }

        define('MY_THEME', $config_themes);

        // Instantiate visit counter and online status if current request is not cronjob or ajax request
        if (!defined('DYNAMIC_REQUEST')) {
            new Counter($this->userAuthenticated(), $this->findIpAddress(), $this->userBrowser(), $this->detectBot(), $this->container);
        }

        // Admin status
        if ($this->userAuthenticated() && $this->administrator()) {
            $this->user_data['admin_status'] = 'administrator';
        }
        if ($this->userAuthenticated() && $this->moderator()) {
            $this->user_data['admin_status'] = 'moderator';
        }

        // Users language
        $this->user_data['language'] = $this->getUserLanguage();
    }

    /**
     * Check if user is logged in
     *
     * @return bool
     */
    public function userAuthenticated(): bool
    {
        if ($this->user_data['authenticated'] && isset($this->user_data['id'])) {
            return true;
        }

        return false;
    }

    /**
     * Logout
     *
     * @return void
     */
    public function logout(): void
    {
        // Remove user from the online list
        $this->db->delete('online', "user = '{$this->user_data['id']}'");

        // Remove login token from database if token exists
        if (isset($_COOKIE['cookie_login']) && $this->db->countRow('tokens', "token = '{$_COOKIE['cookie_login']}'") == 1) {
            $this->db->delete('tokens', "token = '{$_COOKIE['cookie_login']}'");
        }

        // Root domain, with dot '.' session is accessible from all subdomains
        $rootDomain = '.' . $this->cleanDomain();

        // destroy cookies
        setcookie('cookie_login', '', time() - 3600);
        setcookie(session_name(), '', time() - 3600);

        // if user is logged in from root dir
        setcookie('cookie_login', '', 1, '/', $rootDomain);
        setcookie(session_name(), '', time() - 3600, $rootDomain);

        // Destroy session
        $this->destroyCurrentSession();

        // Start new session
        session_start();

        // Generate new session id
        session_regenerate_id();
    }

    /**
     * User authentication
     *
     * @return array with response data
     */
    public function checkAuth(): array
    {
        // Response data
        $data = [];

        // Login attempts
        $max_time_in_seconds = 600;
        $max_attempts = 10;

        if (!empty($this->postAndGet('log')) && !empty($this->postAndGet('pass')) && $this->postAndGet('log') != 'System') {
            if ($this->loginAttemptCount($max_time_in_seconds, $this->postAndGet('log'), $this->findIpAddress()) > $max_attempts) {
                $data['show_notification'] = "<p>I'm sorry, you've made too many attempts to log in too quickly.<br>
                Try again in " . explode(':', $this->makeTime($max_time_in_seconds))[0] . ' minutes.</p>'; // update lang
            }

            // User is logging in with email
            if ($this->validateEmail($this->postAndGet('log'))) {
                $userx_id = $this->idFromEmail($this->postAndGet('log'));
            }

            // User is logging in with username
            else {
                $userx_id = $this->getIdFromNick($this->postAndGet('log'));
            }

            // compare sent data and data from database
            if (!empty($this->userInfo('password', $userx_id)) && $this->passwordCheck($this->postAndGet('pass', true), $this->userInfo('password', $userx_id))) {
                // user want to remember login
                if ($this->postAndGet('cookietrue') == 1) {
                    // Encrypt data to save in cookie
                    $token = $this->leaveLatinLettersNumbers($this->passwordEncrypt($this->postAndGet('pass', true) . $this->generatePassword()));

                    // Set token expire time
                    $now = new DateTime();
                    $now->add(new DateInterval('P1Y'));
                    $new_time = $now->format('Y-m-d H:i:s');

                    // Save token in database
                    $this->db->insert('tokens', array('uid' => $userx_id, 'type' => 'login', 'token' => $token, 'expiration_time' => $new_time));

                    // Save cookie with token in user's device
                    SetCookie('cookie_login', $token, time() + 3600 * 24 * 365, '/', '.' . $this->cleanDomain()); // one year
                }

                $_SESSION['log'] = $this->getNickFromId($userx_id);
                $_SESSION['permissions'] = $this->userInfo('access_permission', $userx_id);
                $_SESSION['uid'] = $userx_id;

                // Use language settings from profile
                unset($_SESSION['lang']);

                // Get new session id to prevent session fixation
                session_regenerate_id();

                // Update data in profile
                $this->updateUser(
                    array('ip_address', 'browsers'),
                    array($this->findIpAddress(), $this->userBrowser()), $userx_id
                );
        
                // Redirect user to confirm registration
                if ($this->userInfo('registration_activated', $userx_id) == 1) $this->redirection(HOMEDIR . 'users/key/?log=' . $this->postAndGet('log'));
        
                // Redirect user if he is banned
                if ($this->userInfo('banned', $userx_id) == 1) $this->redirection(HOMEDIR . 'users/ban/?log=' . $this->postAndGet('log'));

                $this->redirection(HOMEDIR . $this->postAndGet('ptl'));
            }

            $data['show_notification'] = '{@localization[wronguserorpass]}}';
        }

        return $data;
    }

    /**
     * Destroy session
     */
    private function destroyCurrentSession(): void
    {
        session_unset();
        session_destroy();
        session_write_close();
        setcookie(session_name(), '', 0, '/');
    }

    /**
     * Count login attempts
     * 
     * @param int $seconds
     * @param string $username
     * @param string $ip
     * @return int
     */
    public function loginAttemptCount(int $seconds, string $username, string $ip): int
    {
        // First we delete old attempts from the table
        $oldest = strtotime(date("Y-m-d H:i:s") . " - " . $seconds . " seconds");
        $oldest = date("Y-m-d H:i:s", $oldest);
        $this->db->delete('login_attempts', "`datetime` < '{$oldest}'");
        
        // Next we insert this attempt into the table
        $values = array(
        'address' => $ip,
        'datetime' =>  date("Y-m-d H:i:s"),
        'username' => $username
        );
        $this->db->insert('login_attempts', $values);
        
        // Finally we count the number of recent attempts from this ip address  
        $attempts = $this->db->countRow('login_attempts', " `address` = '{$_SERVER['REMOTE_ADDR']}' AND `username` = '{$username}'");

        return $attempts;
    }

    /**
     * Register user
     * 
     * @param string $name
     * @param string $pass
     * @param string $regkeys
     * @param string $rkey
     * @param string $theme
     * @param string $mail
     * @param string @auto_message
     * @return void
     */
    public function register(string $name, string $pass, string $regkeys, string $rkey, string $theme, string $mail, string $auto_message = ''): void
    {
        $values = array(
            'name' => $name,
            'password' => $this->passwordEncrypt($pass),
            'access_permission' => '107',
            'skin' => $theme,
            'browsers' => $this->check($this->userBrowser()),
            'ip_address' => $this->findIpAddress(),
            'timezone' => 0,
            'banned' => 0,
            'localization' => $this->configuration->getValue('default_localization')
        );
        $this->db->insert('vavok_users', $values);

        $user_id = $this->db->selectData('vavok_users', 'name = :name', [':name' => $name], 'id')['id'];

        $this->db->insert('vavok_profile', array('uid' => $user_id, 'subscribed' => 0, 'registration_date' => time(), 'registration_activated' => $regkeys, 'registration_key' => $rkey, 'last_visit' => time()));
        $this->db->insert('vavok_about', array('uid' => $user_id, 'sex' => 'N', 'email' => $mail));
        $this->db->insert('notifications', array('uid' => $user_id, 'lstinb' => 0, 'type' => 'inbox'));

        // Send private message
        if (!empty($auto_message)) $this->autoMessage($auto_message, $user_id);
    }

    /**
     * Change user's language
     *
     * @param string $language
     * @return void
     */
    public function changeLanguage(string $language = ''): void
    {
        $language = $this->getPreferredLanguage($language);
        $current_session = $_SESSION['lang'] ?? '';

        // Update language if it is changed and if language is installed
        if ($current_session != $language && file_exists(APPDIR . 'include/lang/' . $language . '/index.php')) {
            // unset current language
            $_SESSION['lang'] = '';
            unset($_SESSION['lang']);

            // set new language
            $_SESSION['lang'] = $language;

            // Update language if user is registered
            if ($this->userAuthenticated()) {
                $this->db->update('vavok_users', 'localization', $language, "id = '{$_SESSION['uid']}'");
            }
        }
    }

    /**
     * Delete user
     * 
     * @param string|int $username
     * @return void
     */
    public function deleteUser(string|int $username): void
    {
        // Check if it is really user's id
        if (preg_match("/^([0-9]+)$/", $username)) {
            $users_id = $username;
        } else {
            $users_id = $this->getIdFromNick($username);
        }

        $this->db->delete('vavok_users', "id = '{$users_id}'");
        $this->db->delete('vavok_profile', "uid = '{$users_id}'");
        $this->db->delete('vavok_about', "uid = '{$users_id}'");
        $this->db->delete('inbox', "byuid = '{$users_id}' OR touid = '{$users_id}'");
        $this->db->delete('blocklist', "target = '{$users_id}' OR name = '{$users_id}'");
        $this->db->delete('buddy', "target = '{$users_id}' OR name = '{$users_id}'");
        $this->db->delete('subs', "user_id = '{$users_id}'");
        $this->db->delete('notifications', "uid = '{$users_id}'");
        if ($this->db->tableExists('group_members')) $this->db->delete('group_members', "uid = '{$users_id}'");
    }

    /**
     * Update users information
     * 
     * @param string|array $fields
     * @param string|array $values
     * @param int|null $user_id
     * @return void
     */
    public function updateUser(string|array $fields, string|array $values, int $user_id = null): void
    {
        $user_id = empty($user_id) ? $_SESSION['uid'] : $user_id;

        // Fields and values must be an array, we are using array_values to sort keys when any is removed while filtering
        if (!is_array($fields)) {
            $fields = array($fields);
        }

        if (!is_array($values)) {
            $values = array($values);
        }

        // vavok_users table fields
        $vavok_users_valid_fields = array('name', 'password', 'access_permission', 'skin', 'browsers', 'ip_address', 'timezone', 'banned', 'localization');

        // vavok_profile table fields
        $vavok_profile_valid_fields = array('subscribed', 'subscription_code', 'personal_status', 'registration_date', 'registration_activated', 'registration_key', 'ban_time', 'ban_description', 'last_ban', 'all_bans', 'last_visit');

        // vavok_about table fields
        $vavok_about_valid_fields = array('birthday', 'sex', 'email', 'site', 'city', 'about', 'first_name', 'last_name', 'photo', 'address', 'zip', 'country', 'phone');

        // First check if there are fields to update for selected table, then update data
        if (!empty($this->filter_user_fields_values($fields, $values, $vavok_users_valid_fields, 'fields'))) {
            $this->db->update('vavok_users', array_values($this->filter_user_fields_values($fields, $values, $vavok_users_valid_fields, 'fields')), array_values($this->filter_user_fields_values($fields, $values, $vavok_users_valid_fields, 'values')), "id='{$user_id}'");
        }

        if (!empty($this->filter_user_fields_values($fields, $values, $vavok_profile_valid_fields, 'fields'))) {
            $this->db->update('vavok_profile', array_values($this->filter_user_fields_values($fields, $values, $vavok_profile_valid_fields, 'fields')), array_values($this->filter_user_fields_values($fields, $values, $vavok_profile_valid_fields, 'values')), "uid='{$user_id}'");
        }

        if (!empty($this->filter_user_fields_values($fields, $values, $vavok_about_valid_fields, 'fields'))) {
            $this->db->update('vavok_about', array_values($this->filter_user_fields_values($fields, $values, $vavok_about_valid_fields, 'fields')), array_values($this->filter_user_fields_values($fields, $values, $vavok_about_valid_fields, 'values')), "uid='{$user_id}'");
        }
    }

    /**
     * Filter and remove fields that don't exist in table we are updating
     * 
     * @param array $fields
     * @param array $values
     * @param array $valid_fields
     * @param string $data here we choose if we want fields or values to be returned
     * @return array
     */
    private function filter_user_fields_values(array $fields, array $values, array $valid_fields, string $data): array
    {
        // Filter fields and values in array
        foreach ($fields as $key => $value) {
            // Remove field and value that don't belong to this table
            if (array_search($value, $valid_fields) === false) {
                // Find key number of value and remove value
                $value_number = array_search($value, $fields);

                // Remove value
                unset($values[$value_number]);

                // Remove field
                unset($fields[$value_number]);
            }
        }

        if ($data == 'fields') {
            return $fields;
        }

        return $values;
    }

    /**
     * Update default users permissions
     * 
     * @param int $user_id
     * @param int $permission_id
     * @return void
     */
    public function updateDefaultPermissions(int $user_id, int $permission_id): void
    {
        // Access level
        $this->updateUser('access_permission', $permission_id, $user_id);
    }

    /**
     * Confirm registration with a registration key
     * 
     * @param string $key
     * @return bool
     */
    public function confirmRegistration(string $key): bool
    {
        if (!$this->db->update('vavok_profile', array('registration_activated', 'registration_key'), array('', ''), "registration_key='{$key}'")) {
            return false;
        }

        return true;
    }

    /**
     * Number of private messages
     *
     * @param int $uid
     * @param string $view
     * @return int
     */
    public function getNumberOfMessages(int $uid, string $view = 'all'): int
    {
        if ($view == "all") {
            $nopm = $this->db->countRow('inbox', "touid='" . $uid . "' AND (deleted <> '" . $_SESSION['uid'] . "' OR deleted IS NULL)");
        } elseif ($view == "snt") {
            $nopm = $this->db->countRow('inbox', "byuid='" . $uid . "' AND (deleted <> '" . $_SESSION['uid'] . "' OR deleted IS NULL)");
        } elseif ($view == "str") {
            $nopm = $this->db->countRow('inbox', "touid='" . $uid . "' AND starred='1'");
        } elseif ($view == "urd") {
            $nopm = $this->db->countRow('inbox', "touid='" . $uid . "' AND unread='1'");
        } 
        return $nopm;
    }

    /**
     * Get number of unread pms
     * 
     * @param int $uid
     * @return int
     */
    private function unreadMessagesNumber(int $uid): int
    {
        return $this->db->countRow('inbox', "touid='{$uid}' AND unread='1'");
    }

    /**
     * Number of private msg's
     *
     * @param int $userid
     * @return string
     */
    public function user_mail(int $userid): string
    {
        $fcheck_all = $this->getNumberOfMessages($userid);
        $new_privat = $this->unreadMessagesNumber($userid);

        $all_mail = $new_privat . '/' . $fcheck_all;

        return $all_mail;
    }

    /**
     * Parse message
     *
     * @param string $text
     * @return string
     */
    public function parseMessage(string $text): string
    {
        // Decode
        $text = base64_decode($text);

        // Format message
        $text = $this->getbbcode($this->smiles($this->antiword($text)));

        // Strip slashes
        if (function_exists('get_magic_quotes_gpc')) {
            $text = stripslashes($text);
        }

        return $text;
    }

    /**
     * Send private message
     *
     * @param string $pmtext
     * @param int $user_id
     * @param int $who
     * @return void
     */
    public function sendMessage(string $pmtext, int $user_id, int $who): void
    {
        $pmtext = base64_encode($pmtext);

        $time = time();

        $this->db->insert('inbox', array('text' => $pmtext, 'byuid' => $user_id, 'touid' => $who, 'timesent' => time()));

        $user_profile = $this->db->selectData('vavok_profile', 'uid = :uid', [':uid' => $who], 'last_visit');
        $last_notif = $this->db->selectData('notifications', 'uid = :uid AND :type', [':uid' => $who, ':type' => 'inbox'], 'lstinb, type'); 

        // no data in database, insert data
        if (empty($last_notif['lstinb']) && empty($last_notif['type'])) {
            $this->db->insert('notifications', array('uid' => $who, 'lstinb' => $time, 'type' => 'inbox'));
        }

        $notif_expired = $last_notif['lstinb'] + 864000;

        if (($user_profile['last_visit'] + 3600) < $time && $time > $notif_expired && ($last_notif['active'] == 1 || empty($last_notif['active']))) {
            $user_mail = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $who], 'email');

            $send_mail = new Mailer($this->container);
            $send_mail->queueEmail($user_mail['email'], "Message on " . $this->configuration->getValue('home_address'), "Hello " . $this->getNickFromId($who) . "\r\n\r\nYou have new message on site " . $this->configuration->getValue('home_address'), '', '', 'normal'); // update lang

            $this->db->update('notifications', 'lstinb', $time, "uid='" . $who . "' AND type='inbox'");
        }
    }

    /**
     *  Private message by system
     * 
     * @param string $msg
     * @param int $who
     * @param int|null $sender_id
     * @return void
     */
    public function autoMessage(string $msg, int $who, int $sender_id = null): void
    {
        $sender = !empty($sender_id) ? $sender_id : 0;

         $values = array(
         'text' => base64_encode($msg),
         'byuid' => $sender,
         'touid' => $who,
         'unread' => '1',
         'timesent' => time()
        );

        $this->db->insert('inbox', $values);
    }

    /**
     * Username
     * 
     * @return string
     */
    public function showUsername(): string
    {
        return isset($_SESSION['log']) && !empty($_SESSION['log']) ? $_SESSION['log'] : '';
    }

    /**
     * Users id
     * 
     * @return int
     */
    public function userIdNumber(): int
    {
        return isset($_SESSION['uid']) && !empty($_SESSION['uid']) ? $_SESSION['uid'] : 0;
    }

    /**
     * Get user nick from user id number
     *
     * @param int $uid
     * @return string|bool
     */
    public function getNickFromId(int $uid): string|bool
    {
        $unick = $this->userInfo('nickname', $uid);
        return !empty($unick) ? $unick : false;
    }

    /**
     * Get vavok_users user id from nickname
     * 
     * @param string $nick
     * @return int
     */
    public function getIdFromNick(string $nick): int
    {
        $uid = $this->db->selectData('vavok_users', 'name = :name', [':name' => $nick], 'id');
        return !empty($uid['id']) ? $uid['id'] : 0;
    }

    /**
     * Get users id by email address
     * 
     * @param string $email
     * @return int|bool
     */
    public function idFromEmail(string $email): int|bool
    {
        $id = $this->db->selectData('vavok_about', 'email = :email', [':email' => $email], 'uid');
        return !empty($id['uid']) ? $id['uid'] : false;
    }

    /**
     * Calculate age
     *
     * @param string $strdate
     * @return int
     */
    public function calculateAge(string $strdate): int
    {
        $dob = explode(".", $strdate);
        if (count($dob) != 3) {
            return 0;
        } 
        $y = $dob[2];
        $m = $dob[1];
        $d = $dob[0];
        if (strlen($y) != 4) {
            return 0;
        } 
        if (strlen($m) != 2) {
            return 0;
        } 
        if (strlen($d) != 2) {
            return 0;
        } 

        $y += 0;
        $m += 0;
        $d += 0;

        if ($y == 0) return 0;
        $rage = date("Y") - $y;
        if (date("m") < $m) {
            $rage -= 1;
        } else {
            if ((date("m") == $m) && (date("d") < $d)) {
                $rage -= 1;
            } 
        } 
        return $rage;
    }

    /**
     * Get information about user
     *
     * @param string $info data that method need to return
     * @param int|null $user_id ID of the user
     * @return string|bool
     */
    public function userInfo(string $info, ?int $user_id = null): string|bool
    {
        // If $user_id is not set use user id of logged-in user
        $users_id = empty($user_id) && isset($_SESSION['uid']) ? $_SESSION['uid'] : $user_id;

        if (empty($user_id) && !$this->userAuthenticated()) {
            return false;
        }

        switch ($info) {
            case 'email':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'email');
                return isset($data['email']) && !empty($data['email']) ? $data['email'] : '';

            case 'full_name':
                $uinfo = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'first_name, last_name');

                $first_name = $uinfo['first_name'] ?? '';
                $last_name = $uinfo['last_name'] ?? '';
                $full_name = $first_name . ' ' . $last_name;

                // Return false when there is no first and last name
                if (empty(str_replace(' ', '', $full_name))) return false;

                return $full_name;

            case 'first_name':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'first_name');
                return isset($data['first_name']) && !empty($data['first_name']) ? $this->securityCheck($data['first_name']) : '';

            case 'last_name':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'last_name');
                return isset($data['last_name']) && !empty($data['last_name']) ? $this->securityCheck($data['last_name']) : '';

            case 'city':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'city');
                return isset($data['city']) && !empty($data['city']) ? $this->securityCheck($data['city']) : '';

            case 'address':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'address');
                return isset($data['address']) && !empty($data['address']) ? $this->securityCheck($data['address']) : '';

            case 'zip':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'zip');
                return isset($data['zip']) && !empty($data['zip']) ? $this->securityCheck($data['zip']) : '';

            case 'about':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'about');
                return isset($data['about']) && !empty($data['about']) ? $this->securityCheck($data['about']) : '';

            case 'site':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'site');
                return isset($data['site']) && !empty($data['site']) ? $this->securityCheck($data['site']) : '';

            case 'birthday':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'birthday');
                return isset($data['birthday']) && !empty($data['birthday']) ? $this->securityCheck($data['birthday']) : '';

            case 'gender':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'sex');
                return isset($data['sex']) && !empty($data['sex']) ? $this->securityCheck($data['sex']) : '';

            case 'photo':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'photo');
                return isset($data['photo']) && !empty($data['photo']) ? $this->securityCheck($data['photo']) : '';

            case 'nickname':
                $data = isset($user_id) ? $this->db->selectData('vavok_users', 'id = :id', [':id' => $users_id], 'name') : '';

                return isset($data['name']) && !empty($data['name']) ? $this->securityCheck($data['name']) : $this->securityCheck($this->user_data['name']);

            case 'language':
                $data = isset($user_id) ? $this->db->selectData('vavok_users', 'id = :id', [':id' => $users_id], 'localization') : '';

                return isset($data['localization']) && !empty($data['localization']) ? $this->securityCheck($data['localization']) : $this->securityCheck($this->user_data['localization']);

            case 'banned':
                $data = isset($user_id) ? $this->db->selectData('vavok_users', 'id = :id', [':id' => $users_id], 'banned') : '';

                return isset($data['banned']) && !empty($data['banned']) ? (int)$data['banned'] : (int)$this->user_data['banned'];

            case 'password':
                $data = isset($user_id) ? $this->db->selectData('vavok_users', 'id = :id', [':id' => $users_id], 'password') : '';

                return isset($data['password']) && !empty($data['password']) ? $data['password'] : $this->user_data['password'];

            case 'access_permission':
                $data = isset($user_id) ? $this->db->selectData('vavok_users', 'id = :id', [':id' => $users_id], 'access_permission') : '';

                return isset($data['access_permission']) && !empty($data['access_permission']) ? $data['access_permission'] : '';

            case 'browser':
                $data = isset($user_id) ? $this->db->selectData('vavok_users', 'id = :id', [':id' => $users_id], 'browsers') : '';

                return isset($data['browsers']) && !empty($data['browsers']) ? $this->securityCheck($data['browsers']) : $this->securityCheck($this->user_data['browser']);

            case 'ip_address':
                $data = isset($user_id) ? $this->db->selectData('vavok_users', 'id = :id', [':id' => $users_id], 'ip_address') : '';

                return isset($data['ip_address']) && !empty($data['ip_address']) ? $this->securityCheck($data['ip_address']) : $this->securityCheck($this->user_data['ip_address']);

            case 'timezone':
                $data = isset($user_id) ? $this->db->selectData('vavok_users', 'id = :id', [':id' => $users_id], 'timezone') : '';

                return isset($data['timezone']) && !empty($data['timezone']) ? $this->securityCheck($data['timezone']) : $this->securityCheck($this->user_data['timezone']);

            case 'ban_time':
                $data = $this->db->selectData('vavok_profile', 'uid = :id', [':id' => $users_id], 'ban_time');
                return isset($data['ban_time']) && !empty($data['ban_time']) ? $data['ban_time'] : 0;

            case 'ban_description':
                $data = $this->db->selectData('vavok_profile', 'uid = :id', [':id' => $users_id], 'ban_description');
                return isset($data['ban_description']) && !empty($data['ban_description']) ? $this->securityCheck($data['ban_description']) : '';

            case 'all_bans':
                $data = $this->db->selectData('vavok_profile', 'uid = :id', [':id' => $users_id], 'all_bans');
                return isset($data['all_bans']) && !empty($data['all_bans']) ? $data['all_bans'] : 0;

            case 'registration_activated':
                $data = $this->db->selectData('vavok_profile', 'uid = :id', [':id' => $users_id], 'registration_activated');
                return isset($data['registration_activated']) && !empty($data['registration_activated']) ? $data['registration_activated'] : '';

            case 'status':
                $data = $this->db->selectData('vavok_profile', 'uid = :id', [':id' => $users_id], 'personal_status');
                return isset($data['personal_status']) && !empty($data['personal_status']) ? $this->securityCheck($data['personal_status']) : '';

            case 'registration_date':
                $data = $this->db->selectData('vavok_profile', 'uid = :id', [':id' => $users_id], 'registration_date');
                return isset($data['registration_date']) && !empty($data['registration_date']) ? $data['registration_date'] : '';

            case 'last_visit':
                $data = $this->db->selectData('vavok_profile', 'uid = :id', [':id' => $users_id], 'last_visit');
                return isset($data['last_visit']) && !empty($data['last_visit']) ? $data['last_visit'] : '';

            case 'subscribed':
                $data = $this->db->selectData('vavok_profile', 'uid = :id', [':id' => $users_id], 'subscribed');
                return isset($data['subscribed']) && !empty($data['subscribed']) ? $data['subscribed'] : '';

            case 'last_ban':
                $data = $this->db->selectData('vavok_profile', 'uid = :id', [':id' => $users_id], 'last_ban');
                return isset($data['last_ban']) && !empty($data['last_ban']) ? $data['last_ban'] : '';

            default:
                return false;
        }
    }

    /**
     * User's language
     *
     * @return string
     */
    public function getUserLanguage(): string
    {
        // Use language from session if exists
        if (isset($_SESSION['lang']) && !empty($_SESSION['lang'])) return $_SESSION['lang'];

        if ($this->userAuthenticated()) {
            return $this->userInfo('language', $this->user_data['id']);
        } else {
            return $this->configuration->getValue('default_localization');
        }
    }

    /**
     * Find user's IP address
     *
     * @return string
     */
    public function findIpAddress(): string
    {
        if (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = preg_replace("/[^0-9.]/", "", $_SERVER['REMOTE_ADDR']);
        }
        return htmlspecialchars(stripslashes($ip));
    }

    /**
     * Check if user is online
     *
     * @param string $login
     * @return string
     */
    public function userOnline(string $login): string
    {
        $xuser = $this->getIdFromNick($login);
        $statwho = '<font color="#CCCCCC">[Off]</font>';

        $result = $this->db->countRow('online', "user='{$xuser}'");

        if ($result > 0 && $xuser > 0) $statwho = '<font color="#00FF00">[On]</font>';

        return $statwho;
    }

    /**
     * Administrator status name
     * 
     * @param int $message
     * @return string
     */
    public function userStatus(int $message): string
    {
        $message = str_replace(101, '{@localization[access101]}}', $message);
        $message = str_replace(102, '{@localization[access102]}}', $message);
        $message = str_replace(103, '{@localization[access103]}}', $message);
        $message = str_replace(105, '{@localization[access105]}}', $message);
        $message = str_replace(106, '{@localization[access106]}}', $message);
        return str_replace(107, '{@localization[access107]}}', $message);
    }

    /**
     * Number of registered members
     *
     * @return int
     */
    function countRegisteredMembers(): int
    {
        return $this->db->countRow('vavok_users');
    }

    /**
     * Return visitor's browser
     *
     * @return string
     */
    function userBrowser(): string
    {
        $detectBrowser = new BrowserDetection();
        $userBrowser = rtrim($detectBrowser->detect()->getBrowser() . ' ' . $detectBrowser->getVersion());

        $userBrowser = !empty($userBrowser) ? $userBrowser : 'Browser not detected';

        return $userBrowser;
    }

    /**
     * Check if username exist in database
     * 
     * @param string $username
     * @return bool
     */
    public function usernameExists(string $username): bool
    {
        return $this->db->countRow('vavok_users', "name='{$username}'") > 0 ? true : false;
    }

    /**
     * Check if email exist in database
     * 
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email): bool
    {
        return $this->db->countRow('vavok_about', "email='{$email}'") > 0 ? true : false;
    }

    /**
     * Check if users ID exist in database
     * 
     * @param int $id
     * @return bool
     */
    public function idExists(int $id): bool
    {
        return $this->db->countRow('vavok_users', "id='{$id}'") > 0 ? true : false;
    }

    /**
     * Number of administrators
     * 
     * @return int
     */
    public function totalAdmins(): int
    {
        return $this->db->countRow('vavok_users', "access_permission='101' OR access_permission='102' OR access_permission='103' OR access_permission='105'");
    }

    /**
     * Number of banned users
     * 
     * @return int
     */
    public function totalBanned(): int
    {
        return $this->db->countRow('vavok_users', "banned='1' OR banned='2'");
    }

    /**
     * Number of unconfirmed registrations
     * 
     * @return int
     */
    public function totalUnconfirmed(): int
    {
        return $this->db->countRow('vavok_profile', "registration_activated='1' OR registration_activated='2'");
    }

    /**
     * Username validation
     *
     * @param string $username
     * @return bool
     */
    function validateUsername(string $username): bool
    {
        if (preg_match("/^[\p{L}_.0-9]{3,15}$/ui", $username)) return true;

        return false;
    }

    /**
     * Check if user is moderator
     * 
     * @param ?int $permission_id
     * @param ?int $user_id
     * @return bool
     */
    function moderator(?int $permission_id = null, ?int $user_id = null): bool
    {
        if (empty($permission_id) && (!empty($_SESSION['permissions']) && $_SESSION['permissions'] > 0)) {
            $session_permission = $_SESSION['permissions'];
        }

        $user_id = empty($user_id) && (!empty($_SESSION['uid']) && $_SESSION['uid'] > 0) ? $_SESSION['uid'] : $user_id;

        $permission = $session_permission ?? $this->userInfo('access_permission', $user_id);

        $permission = !empty($permission) ? intval($permission) : 0;

        if (!empty($permission_id) && $permission === $permission_id && ($permission === 103 || $permission === 105 || $permission === 106)) {
            return true;
        } elseif (empty($permission_id) && ($permission === 103 || $permission === 105 || $permission === 106)) {
            return true;
        }

        return false;
    }

    /**
     * Check if user is administrator
     * 
     * @param ?int $permission_id
     * @param ?int $user_id
     * @return bool
     */
    function administrator(?int $permission_id = null, ?int $user_id = null): bool
    {
        if (empty($permission_id) && (!empty($_SESSION['permissions']) && $_SESSION['permissions'] > 0)) {
            $session_permission = $_SESSION['permissions'];
        }

        $user_id = empty($user_id) && (!empty($_SESSION['uid']) && $_SESSION['uid'] > 0) ? $_SESSION['uid'] : $user_id;

        $permission = $session_permission ?? $this->userInfo('access_permission', $user_id);

        $permission = !empty($permission) ? intval($permission) : 0;

        if (!empty($permission_id) && $permission === $permission_id && ($permission === 101 || $permission === 102)) {
            return true;
        } elseif (empty($permission_id) && ($permission === 101 || $permission === 102)) {
            return true;
        }

        return false;
    }

    /**
     * Check if user is on the block list
     *
     * @param int $tid
     * @param int $uid
     * @return bool
     */
    public function isUserBlocked(int $tid, int $uid): bool
    {
        $ign = $this->db->countRow('blocklist', "target='{$tid}' AND name='{$uid}'");
        if ($ign > 0) {
            return true;
        }
        return false;
    }

    /**
     * Block check result
     *
     * @param int $uid
     * @param int $tid
     * @return int
     */
    function blockCheckResult(int $uid, int $tid): int
    {
        // 0 user can't ignore the target
        // 1 yes can ignore
        // 2 already ignored
        if ($uid == $tid) {
            return 0;
        }

        if ($this->isUserBlocked($tid, $uid)) {
            return 2; // the target is already ignored by the user
        }

        return 1;
    }

    /**
     * Check if use is in the contact list
     *
     * @param int $tid
     * @param int $uid
     * @return bool
     */
    function checkContact(int $tid, int $uid): bool
    {
        $ign = $this->db->countRow('buddy', "target='{$tid}' AND name='{$uid}'");

        if ($ign > 0) return true;

        return false;
    }

    /**
     * Password encryption
     *
     * @param string $password
     * @return string
     */
    function passwordEncrypt(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verify password
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    function passwordCheck(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Return user's preferred language
     *
     * @param string $language
     * @param string $format
     * @return string
     */
    public function getPreferredLanguage(string $language = '', string $format = ''): string
    {
        // Get browser preferred language
        $locale = isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2) : '';

        // Use default language
        if (empty($language)) $language = $this->configuration->getValue('default_localization');

        if ($language == 'en') {
            $language = 'english';
        } elseif ($language == 'sr') {
            $language = 'serbian_latin';

            // If browser user Serbian it is cyrillic
            if ($locale == 'sr') $language = 'serbian_cyrillic';

            // Check if language is available
            if ($language == 'serbian_latin' && file_exists(APPDIR . "include/lang/serbian_latin/index.php")) {
                $language = 'serbian_latin';
            }
            // Check if cyrillic scrypt is installed
            elseif (file_exists(APPDIR . "include/lang/serbian_cyrillic/index.php")) { 
                $language = 'serbian_cyrillic';
            }
            // Cyrillic script not installed, use latin
            else {
                $language = 'serbian_latin'; 
            }
        }

        // Return short version
        if ($format == 'short') {
            if ($language == 'english') $language = 'en'; // Short code for English
            if ($language == 'serbian_latin' || $language == 'serbian_cyrillic') $language = 'sr'; // Short code for Serbian
        }

        return strtolower($language);
    }

    /**
     * Detect page's language and change user's language to page's language
     * 
     * @param string $page_locale
     * @return string|bool
     */
    public function updatePageLocalization(string $page_locale): string|bool
    {
        // Localization from page's data
        $page_localization = !empty($page_locale) ? $this->getPreferredLanguage($page_locale, 'short') : '';

        // Update user's language when page's language is different from current localization
        if (!empty($page_localization) && strtolower($page_localization) != $this->getPreferredLanguage($_SESSION['lang'], 'short')) {
            // Update $_SESSION['lang'] with new localization/language
            $this->changeLanguage(strtolower($page_localization));

            // Return localization we want to use now
            return $this->getPreferredLanguage($page_localization);
        }

        return false;
    }

    /**
     * Set users data
     *
     * @param array $data
     * @return void
     */
    private function setCurrentUser(array $data): void
    {
        foreach ($data as $property => $value) {
            $this->user_data[$property] = $value;
        }
    }

    /**
     * Update data in the session
     *
     * @param array $data
     * @return bool
     */
    private function updateSession(array $data): bool
    {
        $return_value = false;

        foreach ($data as $key => $value) {
            // Update if value has been changed
            if (!isset($_SESSION[$key]) || $_SESSION[$key] !== $value) {
                $_SESSION[$key] = $value;

                $return_value = true;
            }
        }

        return $return_value;
    }
}