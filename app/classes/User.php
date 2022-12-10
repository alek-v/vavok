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
    public array $user_data = [
        'authenticated' => false,
        'admin_status' => 'user',
        'language' => 'english'
    ];

    public function __construct(protected Container $container)
    {
        // Database connection
        $this->db = $container['db'];

        // Session from cookie
        if (empty($_SESSION['log']) && !empty($_COOKIE['cookie_login'])) {
            // Search for token in database and get tokend data if exists
            $cookie_data = $this->db->selectData('tokens', 'token = :token', [':token' => $this->check($_COOKIE['cookie_login'])], 'uid, token');
            $cookie_id = isset($cookie_data['uid']) ? $cookie_data['uid'] : ''; // User's id
            $token_value = isset($cookie_data['token']) ? $cookie_data['token'] : '';

            // Get user's data
            $cookie_check = $this->db->selectData('vavok_users', 'id = :id', [':id' => $cookie_id], 'name, perm, lang');

            // If user exists write session data
            if (isset($cookie_check['name']) && !empty($cookie_check['name']) && ($_COOKIE['cookie_login'] === $token_value)) {
                    // Write current session data
                    $_SESSION['log'] = $cookie_check['name'];
                    $_SESSION['permissions'] = $cookie_check['perm'];
                    $_SESSION['uid'] = $cookie_id;
                    $_SESSION['lang'] = $cookie_check['lang'];

                    // Update ip address
                    $this->db->update('vavok_users', 'ipadd', $this->findIpAddress(), "id = '{$cookie_id}'");
            } else {
                // Token from cookie is not valid or it is expired, delete cookie
                setcookie('cookie_login', '', time() - 3600);
                setcookie('cookie_login', '', 1, '/', $this->cleanDomain());
            }
        }

        // Get user data
        if (!empty($_SESSION['uid'])) {
            $vavok_users = $this->db->selectData('vavok_users', 'id = :id', [':id' => $_SESSION['uid']]);
            $user_profil = $this->db->selectData('vavok_profil', 'uid = :uid', [':uid' => $_SESSION['uid']], 'regche');

            // Update last visit
            $this->db->update('vavok_profil', 'lastvst', time(), "uid='{$_SESSION['uid']}'");

             // Time zone
            if (!empty($vavok_users['timezone'])) define('MY_TIMEZONE', $vavok_users['timezone']);

            // Update language in session if it is not language from profile
            if (!empty($vavok_users['lang']) && (empty($_SESSION['lang']) || $_SESSION['lang'] != $vavok_users['lang'])) $_SESSION['lang'] = $vavok_users['lang'];

            // Check if user is banned
            if (isset($vavok_users['banned']) && $vavok_users['banned'] == 1 && !strstr($_SERVER['QUERY_STRING'], 'users/ban')) $this->redirection(HOMEDIR . 'users/ban');

             // activate account
            if (isset($user_profil['regche']) && $user_profil['regche'] == 1 && !strstr($_SERVER['QUERY_STRING'], 'pages/key')) {
                setcookie('cookpass', '');
                setcookie('cooklog', '');
                setcookie(session_name(), '');
                unset($_SESSION['log']);
                session_destroy();
            }
        } else {
            if (empty($_SESSION['lang'])) $this->changeLanguage();
        }

        // Count visited pages and time on site
        if (empty($_SESSION['currs'])) $_SESSION['currs'] = time();
        if (empty($_SESSION['counton'])) $_SESSION['counton'] = 0;
        $_SESSION['counton']++;

        // Pages visited at this session
        $this->visited_pages = $_SESSION['counton'];

        // Visitor's time on the site
        $this->time_on_site = $this->makeTime(round(time() - $_SESSION['currs']));

        // User settings

        // If timezone is not defined use default
        if (!defined('MY_TIMEZONE')) define('MY_TIMEZONE', $this->configuration('timeZone'));

        // Site theme
        $config_themes = $this->configuration('webtheme');

        // If theme does not exist use default theme
        // For admin panel use default theme
        if (!file_exists(APPDIR . 'views/' . $config_themes) || strpos($this->websiteHomeAddress() . $_SERVER['PHP_SELF'], $_SERVER['HTTP_HOST'] . '/adminpanel') !== false) $config_themes = 'default';

        define('MY_THEME', $config_themes);

        // Instantiate visit counter and online status if current request is not cronjob or ajax request
        if (!defined('DYNAMIC_REQUEST')) new Counter($this->userAuthenticated(), $this->findIpAddress(), $this->user_browser(), $this->detectBot(), $this->container);

        // Check if user is authenticated
        // This time we check data from database, because of this we pass parameter true
        // Next time when you use method userAuthenticated don't use parameter true and data will be used from session, no new database request
        if ($this->userAuthenticated(true)) $this->user_data['authenticated'] = true;

        // Admin status
        if ($this->administrator()) $this->user_data['admin_status'] = 'administrator';
        if ($this->moderator()) $this->user_data['admin_status'] = 'moderator';

        // Users language
        $this->user_data['language'] = $this->getUserLanguage();
    }

    /**
     * Check if user is logged in
     * When $start == true then make database request and check data from database
     *
     * @param boolean
     * @return bool
     */
    public function userAuthenticated(bool $start = false): bool
    {
        if (!empty($_SESSION['uid']) && !empty($_SESSION['permissions'])) {
            // Check data from database when parameter $start is true
            // Logout user if data from session and data from database doesn't match
            if ($start == true && $_SESSION['permissions'] == 107 && $this->userInfo('perm') != 107) {
                $this->logout($_SESSION['uid']);

                return false;
            }

            // Regular authenticated user
            if ($_SESSION['permissions'] == 107) return true;

            // Administrator, check if access permissions are changed
            if ($this->check($_SESSION['log']) == $this->userInfo('nickname') && $_SESSION['permissions'] == $this->userInfo('perm')) {
                // Everything is ok
                return true;
            } else {
                // Permissions are changed, logout user
                // When user login again new permissions will be set in session
                $this->logout($_SESSION['uid']);

                return false;
            }
        }

        return false;
    }

    /**
     * Logout
     *
     * @param integer $user_id
     * @return void
     */
    public function logout(int $user_id = null): void
    {
        if (empty($user_id)) $user_id = $_SESSION['uid'];

        // Remove user from online list
        $this->db->delete('online', "user = '{$user_id}'");

        // Remove login token from database if token exists
        if (isset($_COOKIE['cookie_login']) && $this->db->countRow('tokens', "token = '{$_COOKIE['cookie_login']}'") == 1) $this->db->delete('tokens', "token = '{$_COOKIE['cookie_login']}'");

        // Root domain, with dot '.' session is accessible from all subdomains
        $rootDomain = '.' . $this->cleanDomain();

        // destroy cookies
        setcookie('cookie_login', '', time() - 3600);
        setcookie(session_name(), '', time() - 3600);

        // if user is logged in from root dir
        setcookie('cookie_login', '', 1, '/', $rootDomain);
        setcookie(session_name(), '', time() - 3600, $rootDomain);

        // Destoy session
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
            if (!empty($this->userInfo('password', $userx_id)) && $this->password_check($this->postAndGet('pass', true), $this->userInfo('password', $userx_id))) {
                // user want to remember login
                if ($this->postAndGet('cookietrue') == 1) {
                    // Encrypt data to save in cookie
                    $token = $this->leaveLatinLettersNumbers($this->password_encrypt($this->postAndGet('pass', true) . $this->generatePassword()));

                    // Set token expire time
                    $now = new DateTime();
                    $now->add(new DateInterval('P1Y'));
                    $new_time = $now->format('Y-m-d H:i:s');

                    // Save token in database
                    $this->db->insert('tokens', array('uid' => $userx_id, 'type' => 'login', 'token' => $token, 'expiration_time' => $new_time));

                    // Save cookie with token in users's device
                    SetCookie('cookie_login', $token, time() + 3600 * 24 * 365, '/', '.' . $this->cleanDomain()); // one year
                }

                $_SESSION['log'] = $this->getNickFromId($userx_id);
                $_SESSION['permissions'] = $this->userInfo('perm', $userx_id);
                $_SESSION['uid'] = $userx_id;

                // Use language settings from profile
                unset($_SESSION['lang']);

                // Get new session id to prevent session fixation
                session_regenerate_id();

                // Update data in profile
                $this->updateUser(
                    array('ipadd', 'browsers'),
                    array($this->findIpAddress(), $this->user_browser()),
                    $userx_id);
        
                // Redirect user to confirm registration
                if ($this->userInfo('regche', $userx_id) == 1) $this->redirection(HOMEDIR . 'users/key/?log=' . $this->postAndGet('log'));
        
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
            'pass' => $this->password_encrypt($pass),
            'perm' => '107',
            'skin' => $theme,
            'browsers' => $this->check($this->user_browser()),
            'ipadd' => $this->findIpAddress(),
            'timezone' => 0,
            'banned' => 0,
            'newmsg' => 0,
            'lang' => $this->configuration('siteDefaultLang')
        );
        $this->db->insert('vavok_users', $values);

        $user_id = $this->db->selectData('vavok_users', 'name = :name', [':name' => $name], 'id')['id'];

        $this->db->insert('vavok_profil', array('uid' => $user_id, 'commadd' => 0, 'subscri' => 0, 'regdate' => time(), 'regche' => $regkeys, 'regkey' => $rkey, 'lastvst' => time(), 'chat' => 0));
        $this->db->insert('vavok_about', array('uid' => $user_id, 'sex' => 'N', 'email' => $mail));
        $this->db->insert('notif', array('uid' => $user_id, 'lstinb' => 0, 'type' => 'inbox'));

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
        $current_session = isset($_SESSION['lang']) ? $_SESSION['lang'] : '';

        // Update language if it is changed and if language is installed
        if ($current_session != $language && file_exists(APPDIR . 'include/lang/' . $language . '/index.php')) {
            // unset current language
            $_SESSION['lang'] = '';
            unset($_SESSION['lang']);

            // set new language
            $_SESSION['lang'] = $language;

            // Update language if user is registered
            if ($this->userAuthenticated()) $this->db->update('vavok_users', 'lang', $language, "id='{$_SESSION['uid']}'");
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
        $this->db->delete('vavok_profil', "uid = '{$users_id}'");
        $this->db->delete('vavok_about', "uid = '{$users_id}'");
        $this->db->delete('inbox', "byuid = '{$users_id}' OR touid = '{$users_id}'");
        $this->db->delete('blocklist', "target = '{$users_id}' OR name = '{$users_id}'");
        $this->db->delete('buddy', "target = '{$users_id}' OR name = '{$users_id}'");
        $this->db->delete('subs', "user_id = '{$users_id}'");
        $this->db->delete('notif', "uid = '{$users_id}'");
        $this->db->delete('specperm', "uid = '{$users_id}'");
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
        if (!is_array($fields)) $fields = array($fields);
        if (!is_array($values)) $values = array($values);

        // vavok_users table fields
        $vavok_users_valid_fields = array('name', 'pass', 'perm', 'skin', 'browsers', 'ipadd', 'timezone', 'banned', 'newmsg', 'lang');

        // vavok_profil table fields
        $vavok_profil_valid_fields = array('chat', 'commadd', 'subscri', 'newscod', 'perstat', 'regdate', 'regche', 'regkey', 'bantime', 'bandesc', 'lastban', 'allban', 'lastvst');

        // vavok_about table fields
        $vavok_about_valid_fields = array('birthday', 'sex', 'email', 'site', 'city', 'about', 'rname', 'surname', 'photo', 'address', 'zip', 'country', 'phone');

        // First check if there are fields to update for selected table, then update data
        if (!empty($this->filter_user_fields_values($fields, $values, $vavok_users_valid_fields, 'fields'))) $this->db->update('vavok_users', array_values($this->filter_user_fields_values($fields, $values, $vavok_users_valid_fields, 'fields')), array_values($this->filter_user_fields_values($fields, $values, $vavok_users_valid_fields, 'values')), "id='{$user_id}'");
        if (!empty($this->filter_user_fields_values($fields, $values, $vavok_profil_valid_fields, 'fields'))) $this->db->update('vavok_profil', array_values($this->filter_user_fields_values($fields, $values, $vavok_profil_valid_fields, 'fields')), array_values($this->filter_user_fields_values($fields, $values, $vavok_profil_valid_fields, 'values')), "uid='{$user_id}'");
        if (!empty($this->filter_user_fields_values($fields, $values, $vavok_about_valid_fields, 'fields'))) $this->db->update('vavok_about', array_values($this->filter_user_fields_values($fields, $values, $vavok_about_valid_fields, 'fields')), array_values($this->filter_user_fields_values($fields, $values, $vavok_about_valid_fields, 'values')), "uid='{$user_id}'");
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

        if ($data == 'fields') return $fields;

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
        $this->updateUser('perm', $permission_id, $user_id);

        $default_permissions = array(
            101 => array(),
            102 => array(),
            103 => array(),
            104 => array(),
            105 => array(),
            106 => array('adminpanel' => 'show', 'adminchat' => 'show,insert', 'adminlist' => 'show', 'reglist' => 'show'),
            107 => array()
        );

        foreach ($default_permissions[$permission_id] as $key => $value) {
            // Insert data to database if data does not exsist
            if ($this->db->countRow('specperm', "permname='{$key}' AND uid='{$user_id}'") == 0) {
                $values = array(
                    'permname' => $key,
                    'permacc' => $value,
                    'uid' => $user_id
                );

                // Insert data to database
                $this->db->insert('specperm', $values);
            }
        }
    }

    /**
     * Confirm registration with registration key
     * 
     * @param string $key
     * @return bool
     */
    public function confirmRegistration(string $key): bool
    {
        if (!$this->db->update('vavok_profil', array('regche', 'regkey'), array('', ''), "regkey='{$key}'")) return false;
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

    // send private message
    public function send_pm($pmtext, $user_id, $who) {
        $pmtext = base64_encode($pmtext);

        $time = time();

        $this->db->insert('inbox', array('text' => $pmtext, 'byuid' => $user_id, 'touid' => $who, 'timesent' => time()));

        $user_profile = $this->db->selectData('vavok_profil', 'uid = :uid', [':uid' => $who], 'lastvst');
        $last_notif = $this->db->selectData('notif', 'uid = :uid AND :type', [':uid' => $who, ':type' => 'inbox'], 'lstinb, type'); 

        // no data in database, insert data
        if (empty($last_notif['lstinb']) && empty($last_notif['type'])) {
            $this->db->insert('notif', array('uid' => $who, 'lstinb' => $time, 'type' => 'inbox'));
        }

        $notif_expired = $last_notif['lstinb'] + 864000;

        if (($user_profile['lastvst'] + 3600) < $time && $time > $notif_expired && ($inbox_notif['active'] == 1 || empty($inbox_notif['active']))) {
            $user_mail = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $who], 'email');

            $send_mail = new Mailer($this->container);
            $send_mail->queueEmail($user_mail['email'], "Message on " . $this->configuration('homeUrl'), "Hello " . $vavok->go('users')->getNickFromId($who) . "\r\n\r\nYou have new message on site " . $this->configuration('homeUrl'), '', '', 'normal'); // update lang

            $this->db->update('notif', 'lstinb', $time, "uid='" . $who . "' AND type='inbox'");
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
    public function user_id(): int
    {
        return isset($_SESSION['uid']) && !empty($_SESSION['uid']) ? $_SESSION['uid'] : 0;
    }

    /**
     * Get user nick from user id number
     *
     * @param bool $uid
     * @return string|bool
     */
    public function getNickFromId($uid): string|bool
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
     * @param int|null $users_id ID of user
     * @return string|bool
     */
    public function userInfo(string $info, ?int $users_id = null): string|bool
    {
        // If $users_id is not set use user if of logged-in user
        $users_id = empty($users_id) && isset($_SESSION['uid']) ? $_SESSION['uid'] : $users_id;

        switch ($info) {
            case 'email':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'email');
                return isset($data['email']) && !empty($data['email']) ? $data['email'] : '';
            
            case 'full_name':
                $uinfo = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'rname, surname');

                $first_name = isset($uinfo['rname']) ? $uinfo['rname'] : '';
                $last_name = isset($uinfo['surname']) ? $uinfo['surname'] : '';
                $full_name = $first_name . ' ' . $last_name;

                // Return false when there is no first and last name
                if (empty(str_replace(' ', '', $full_name))) return false;

                return $full_name;

            case 'firstname':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'rname');
                return isset($data['rname']) && !empty($data['rname']) ? $data['rname'] : '';

            case 'lastname':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'surname');
                return isset($data['surname']) && !empty($data['surname']) ? $data['surname'] : '';

            case 'city':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'city');
                return isset($data['city']) && !empty($data['city']) ? $data['city'] : '';

            case 'address':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'address');
                return isset($data['address']) && !empty($data['address']) ? $data['address'] : '';

            case 'zip':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'zip');
                return isset($data['zip']) && !empty($data['zip']) ? $data['zip'] : '';

            case 'about':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'about');
                return isset($data['about']) && !empty($data['about']) ? $data['about'] : '';

            case 'site':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'site');
                return isset($data['site']) && !empty($data['site']) ? $data['site'] : '';

            case 'birthday':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'birthday');
                return isset($data['birthday']) && !empty($data['birthday']) ? $data['birthday'] : '';

            case 'gender':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'sex');
                return isset($data['sex']) && !empty($data['sex']) ? $data['sex'] : '';

            case 'photo':
                $data = $this->db->selectData('vavok_about', 'uid = :uid', [':uid' => $users_id], 'photo');
                return isset($data['photo']) && !empty($data['photo']) ? $data['photo'] : '';

            case 'nickname':
                $data = $this->db->selectData('vavok_users', 'id = :id', [':id' => $users_id], 'name');
                return isset($data['name']) && !empty($data['name']) ? $data['name'] : '';

            case 'language':
                $data = $this->db->selectData('vavok_users', 'id = :id', [':id' => $users_id], 'lang');
                return isset($data['lang']) && !empty($data['lang']) ? $data['lang'] : '';

            case 'banned':
                $data = $this->db->selectData('vavok_users', 'id = :id', [':id' => $users_id], 'banned');
                return isset($data['banned']) && !empty($data['banned']) ? $data['banned'] : '';

            case 'password':
                $data = $this->db->selectData('vavok_users', 'id = :id', [':id' => $users_id], 'pass');
                return isset($data['pass']) && !empty($data['pass']) ? $data['pass'] : '';

            case 'perm':
                $data = $this->db->selectData('vavok_users', 'id = :id', [':id' => $users_id], 'perm');
                return isset($data['perm']) && !empty($data['perm']) ? $data['perm'] : '';

            case 'browser':
                $data = $this->db->selectData('vavok_users', 'id = :id', [':id' => $users_id], 'browsers');
                return isset($data['browsers']) && !empty($data['browsers']) ? $data['browsers'] : '';

            case 'ipaddress':
                $data = $this->db->selectData('vavok_users', 'id = :id', [':id' => $users_id], 'ipadd');
                return isset($data['ipadd']) && !empty($data['ipadd']) ? $data['ipadd'] : '';

            case 'timezone':
                $data = $this->db->selectData('vavok_users', 'id = :id', [':id' => $users_id], 'timezone');
                return isset($data['timezone']) && !empty($data['timezone']) ? $data['timezone'] : '';

            case 'bantime':
                $data = $this->db->selectData('vavok_profil', 'uid = :id', [':id' => $users_id], 'bantime');
                return isset($data['bantime']) && !empty($data['bantime']) ? $data['bantime'] : 0;

            case 'bandesc':
                $data = $this->db->selectData('vavok_profil', 'uid = :id', [':id' => $users_id], 'bandesc');
                return isset($data['bandesc']) && !empty($data['bandesc']) ? $data['bandesc'] : '';

            case 'allban':
                $data = $this->db->selectData('vavok_profil', 'uid = :id', [':id' => $users_id], 'allban');
                return isset($data['allban']) && !empty($data['allban']) ? $data['allban'] : '';

            case 'regche':
                $data = $this->db->selectData('vavok_profil', 'uid = :id', [':id' => $users_id], 'regche');
                return isset($data['regche']) && !empty($data['regche']) ? $data['regche'] : '';

            case 'status':
                $data = $this->db->selectData('vavok_profil', 'uid = :id', [':id' => $users_id], 'perstat');
                return isset($data['perstat']) && !empty($data['perstat']) ? $data['perstat'] : '';

            case 'regdate':
                $data = $this->db->selectData('vavok_profil', 'uid = :id', [':id' => $users_id], 'regdate');
                return isset($data['regdate']) && !empty($data['regdate']) ? $data['regdate'] : '';

            case 'lastvisit':
                $data = $this->db->selectData('vavok_profil', 'uid = :id', [':id' => $users_id], 'lastvst');
                return isset($data['lastvst']) && !empty($data['lastvst']) ? $data['lastvst'] : '';

            case 'subscribed':
                $data = $this->db->selectData('vavok_profil', 'uid = :id', [':id' => $users_id], 'subscri');
                return isset($data['subscri']) && !empty($data['subscri']) ? $data['subscri'] : '';

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
            return $this->userInfo('language', $_SESSION['uid']);
        } else {
            return $this->configuration('siteDefaultLang');
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
     * Check if user have a permission to see, edit, delete, etc selected part of the website
     * @param string $permname
     * @param string $needed
     * @return bool
     */
    public function checkPermissions($permname, $needed = 'show'): bool
    {
        // Check if user is logged in
        if (!$this->userAuthenticated()) return false;

        $permname = str_replace('.php', '', $permname);

        // Administrator have access to all site functions
        if ($this->administrator(101)) return true;

        if ($this->db->countRow('specperm', "uid='{$_SESSION['uid']}' AND permname='{$permname}'") == 0) return false;

        $check_data = $this->db->selectData('specperm', 'uid = :uid AND permname = :permname', [':uid' => $_SESSION['uid'], ':permname' => $permname], 'permacc');
        $perms = explode(',', $check_data['permacc']);

        if ($needed == 'show' && (in_array(1, $perms) || in_array('show', $perms))) {
            return true;
        } elseif ($needed == 'edit' && (in_array(2, $perms) || in_array('edit', $perms))) {
            return true;
        } elseif ($needed == 'del' && (in_array(3, $perms) || in_array('del', $perms))) {
            return true;
        } elseif ($needed == 'insert' && (in_array(4, $perms) || in_array('insert', $perms))) {
            return true;
        } elseif ($needed == 'editunpub' && (in_array(5, $perms) || in_array('editunpub', $perms))) {
            return true;
        }

        return false;
    }

    // Current user id
    function current_user_id($user_id = '') {
        $user_id = $_SESSION['uid'];

        if (empty($user_id)) $user_id = 0;

        return $user_id;
    }

    // number of registered members
    function regmemcount() {
        return $this->db->countRow('vavok_users');
    }

    /**
     * Return visitor's browser
     *
     * @return string
     */
    function user_browser()
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
    public function username_exists($username)
    {
        return $this->db->countRow('vavok_users', "name='{$username}'") > 0 ? true : false;
    }

    /**
     * Check if email exist in database
     * 
     * @param string $email
     * @return bool
     */
    public function email_exists($email)
    {
        return $this->db->countRow('vavok_about', "email='{$email}'") > 0 ? true : false;
    }

    /**
     * Check if users ID exist in database
     * 
     * @param string $id
     * @return bool
     */
    public function id_exists($id)
    {
        return $this->db->countRow('vavok_users', "id='{$id}'") > 0 ? true : false;
    }

    /**
     * Number of administrators
     * 
     * @return int
     */
    public function total_admins()
    {
        return $this->db->countRow('vavok_users', "perm='101' OR perm='102' OR perm='103' OR perm='105'");
    }

    /**
     * Number of banned users
     * 
     * @return int
     */
    public function total_banned()
    {
        return $this->db->countRow('vavok_users', "banned='1' OR banned='2'");
    }

    /**
     * Number of unconfirmed registrations
     * 
     * @return int
     */
    public function total_unconfirmed()
    {
        return $this->db->countRow('vavok_profil', "regche='1' OR regche='2'");
    }

    // username validation
    function validate_username($username)
    {
        if (preg_match("/^[\p{L}_.0-9]{3,15}$/ui", $username)) {
            return true;
        } else { return false; }
    }

    /**
     * Check if user is moderator
     * 
     * @param int $num
     * @param int $id
     * @return bool
     */
    function moderator($num = '', $id = '')
    {
        // Return false if user is not logged in
        if (!$this->userAuthenticated()) return false;

        if (empty($id) && !empty($_SESSION['uid'])) $id = $_SESSION['uid'];

        $permission = $this->userInfo('perm', $id);
        $perm = !empty($permission) ? intval($permission) : 0;
        
        if (!empty($num) && $perm === $num && ($perm === 103 || $perm === 105 || $perm === 106)) {
            return true;
        } elseif (empty($num) && ($perm === 103 || $perm === 105 || $perm === 106)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if user is administrator
     * 
     * @param int $num
     * @param int $id
     * @return bool
     */
    function administrator($num = '', $id = '')
    {
        // Return false if user is not logged in
        if (!$this->userAuthenticated()) return false;

        if (empty($id) && !empty($_SESSION['uid'])) $id = $_SESSION['uid'];

        $permission = $this->userInfo('perm', $id);
        $perm = !empty($permission) ? intval($permission) : 0;

        if (!empty($num) && $perm === $num && ($perm === 101 || $perm === 102)) {
            return true;
        } if (empty($num) && ($perm === 101 || $perm === 102)) {
            return true;
        } else {
            return false;
        } 
    }

    // is ignored
    function isignored($tid, $uid) {
        $ign = $this->db->countRow('blocklist', "`target`='" . $tid . "' AND `name`='" . $uid . "'");
        if ($ign > 0) {
            return true;
        }
        return false;
    }

    // ignore result
    function ignoreres($uid, $tid) {
        // 0 user can't ignore the target
        // 1 yes can ignore
        // 2 already ignored
        if ($uid == $tid) {
            return 0;
        }
        /*
        if ($vavok->go('users')->moderator($tid)) {
        //you cant ignore staff members
        return 0;
        }
        if (arebuds($tid, $uid)) {
        //why the hell would anyone ignore his bud? o.O
        return 0;
        }
        */
        if ($this->isignored($tid, $uid)) {
            return 2; // the target is already ignored by the user
        }
        return 1;
    }

    // is buddy
    function isbuddy($tid, $uid) {
        $ign = $this->db->countRow('buddy', "target='" . $tid . "' AND name='" . $uid . "'");
        if ($ign > 0) {
            return true;
        }
        return false;
    }

    /**
     * Other
     */

    // user's password encription
    function password_encrypt($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    function password_check($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public function getPreferredLanguage($language = '', $format = '')
    {
        // Get browser preferred language
        $locale = isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2) : '';

        // Use default language
        if (empty($language)) $language = $this->configuration('siteDefaultLang');

        if ($language == 'en') {
            $language = 'english';
        } elseif ($language == 'sr') {
            $language = 'serbian_latin';

            // If browser user serbian it is cyrillic
            if ($locale == 'sr') $language = 'serbian_cyrillic';

            // Check if language is available
            if ($language == 'serbian_latin' && file_exists(APPDIR . "include/lang/serbian_latin/index.php")) {
                $language = 'serbian_latin';
            }
            // Check if cyrillic scrypt is installed
            elseif (file_exists(APPDIR . "include/lang/serbian_cyrillic/index.php")) { 
                $language = 'serbian_cyrillic';
            }
            // cyrillic script not installed, use latin
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
     * @return string|boolean
     */
    public function updatePageLocalization($page_locale)
    {
        // Localization from page's data
        $page_localization = !empty($page_locale) ? $this->getPreferredLanguage($page_locale, 'short') : '';

        // Update user's language when page's language is different then current localization
        if (!empty($page_localization) && strtolower($page_localization) != $this->getPreferredLanguage($_SESSION['lang'], 'short')) {
            // Update $_SESSION['lang'] with new localization/language
            $this->changeLanguage(strtolower($page_localization));

            // Return localization we want to use now
            return $this->getPreferredLanguage($page_localization);
        }

        return false;
    }
}