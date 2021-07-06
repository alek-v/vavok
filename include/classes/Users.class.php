<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Package:   Class for user management
 */

class Users {
	public $user_id;
	public $username;
	private $vavok;

	function __construct()
	{
		global $vavok;

		$this->vavok = $vavok;

        /**
         * With '.' session is accessible from all subdomains
         */
        $rootDomain = '.' . $this->vavok->clean_domain();

		/**
		 * Session
		 */

        /**
         * Get cookie params and set root domain
         */
        $currentCookieParams = session_get_cookie_params();
        session_set_cookie_params(
            $currentCookieParams["lifetime"],
            $currentCookieParams["path"],
            $rootDomain,
            $currentCookieParams["secure"],
            $currentCookieParams["httponly"]
        );

		session_name('sid');
		session_start();

		/**
		 * Set session from cookie data
		 */
		if (empty($_SESSION['log']) && !empty($_COOKIE['cookie_login'])) {
		    // Search for token in database and get tokend data if exists
			$cookie_data = $this->vavok->go('db')->get_data(DB_PREFIX . 'tokens', "token = '{$vavok->check($_COOKIE['cookie_login'])}'", 'uid, token');
			$cookie_id = isset($cookie_data['uid']) ? $cookie_data['uid'] : ''; // User's id
			$token_value = isset($cookie_data['token']) ? $cookie_data['token'] : '';

		    // Get user's data
			$cookie_check = $this->vavok->go('db')->get_data(DB_PREFIX . 'vavok_users', "id='{$cookie_id}'", 'name, perm, lang');

		    // If user exists write session data
		    if (isset($cookie_check['name']) && !empty($cookie_check['name']) && ($_COOKIE['cookie_login'] === $token_value)) {
		            // write current session data
		            $_SESSION['log'] = $cookie_check['name'];
		            $_SESSION['permissions'] = $cookie_check['perm'];
		            $_SESSION['uid'] = $cookie_id;
		            $_SESSION['lang'] = $cookie_check['lang'];

		            // Update ip address
		            $this->vavok->go('db')->update(DB_PREFIX . 'vavok_users', 'ipadd', $this->find_ip(), "id = '{$cookie_id}'");
		    } else {
		    	// Token from cookie is not valid or it is expired, delete cookie
		    	setcookie('cookie_login', '', time() - 3600);
		    	setcookie('cookie_login', '', 1, '/', $this->vavok->clean_domain());
		    }
		}

		$this->user_id = isset($_SESSION['log']) ? $this->getidfromnick($_SESSION['log']) : ''; // User's id
		$this->username = isset($_SESSION['log']) ? $_SESSION['log'] : '';

		// Get user data
		if (!empty($this->user_id)) {
			// Get data
		    $vavok_users = $this->vavok->go('db')->get_data(DB_PREFIX . 'vavok_users', "id='{$this->user_id}'");
		    $user_profil = $this->vavok->go('db')->get_data(DB_PREFIX . 'vavok_profil', "uid='{$this->user_id}'", 'regche');

		    /**
		     * Update last visit
		     */
		    $this->vavok->go('db')->update(DB_PREFIX . 'vavok_profil', 'lastvst', time(), "uid='{$this->user_id}'");

		 	// Time zone
		    if (!empty($vavok_users['timezone'])) define('MY_TIMEZONE', $vavok_users['timezone']);

		 	// Language
		    if (!empty($vavok_users['lang'])) {
		        // Update language in session if it is not language from prifile
		        if (empty($_SESSION['lang']) || $_SESSION['lang'] != $vavok_users['lang']) $_SESSION['lang'] = $vavok_users['lang'];
		    }

		    // Check if user is banned
		    if ($vavok_users['banned'] == 1 && !strstr($_SERVER['PHP_SELF'], 'pages/ban.php')) $this->vavok->redirect_to(BASEDIR . "pages/ban.php");

		 	// activate account
		    if ($user_profil['regche'] == 1 && !strstr($_SERVER['PHP_SELF'], 'pages/key.php')) {
		        setcookie('cookpass', '');
		        setcookie('cooklog', '');
		        setcookie(session_name(), '');
		        unset($_SESSION['log']);
		        session_destroy();
		    }
		} else {
		    if (empty($_SESSION['lang'])) $this->change_language();
		}

		/**
		 * Count visited pages and time on site
		 */

		if (empty($_SESSION['currs'])) {
		    $_SESSION['currs'] = time();
		}

		if (empty($_SESSION['counton'])) {
		    $_SESSION['counton'] = 0;
		}

		$_SESSION['counton']++;

		// pages visited at this session
		$this->visited_pages = $_SESSION['counton'];

		// visitor's time on the site
		$this->time_on_site = $this->vavok->maketime(round(time() - $_SESSION['currs']));

		/**
		 * User settings
		 */

		// If timezone is not defined use default
		if (!defined('MY_TIMEZONE')) define('MY_TIMEZONE', $this->vavok->get_configuration('timeZone'));

		/**
		 * Site theme
		 */
	    $config_themes = $this->vavok->get_configuration('webtheme');

		/**
		 * If theme does not exist use default theme
		 * For admin panel use default theme
		 */
		if (!file_exists(BASEDIR . "themes/" . $config_themes . "/index.php") || strpos($this->vavok->website_home_address() . $_SERVER['PHP_SELF'], $_SERVER['HTTP_HOST'] . '/adminpanel') !== false) $config_themes = 'default';

		define('MY_THEME', $config_themes);

		$this->vavok->add_global(array('users' => $this));
	}

	/**
	 * Check if user is registered
	 *
	 * @return bool
	 */
	public function is_reg()
	{
	    if (!empty($_SESSION['uid']) && !empty($_SESSION['permissions'])) {
	        if (!empty($this->user_id)) {
	            $show_user = $this->vavok->go('db')->get_data(DB_PREFIX . 'vavok_users', "id='{$this->user_id}'", 'name, perm');
	            // Check if permissions are changed
	            if ($this->vavok->check($_SESSION['log']) == $show_user['name'] && $_SESSION['permissions'] == $show_user['perm']) {
	                return true; // everything is ok
	            } else {
	            	$this->logout($this->user_id);

	                return false;
	            }
	        } else {
	            return false;
	        }
	    } else {
	    	return false;
	    }
	}

	/**
	 * Logout
	 *
	 * @param integer $user_id
	 * @return void
	 */
	public function logout($user_id)
	{
		// Remove user from online list
	    $this->vavok->go('db')->delete(DB_PREFIX . 'online', "user = '{$this->user_id}'");

	    // Remove login token from database if token exists
	    if ($this->vavok->go('db')->count_row(DB_PREFIX . 'tokens', "token = '{$_COOKIE['cookie_login']}'") == 1) $this->vavok->go('db')->delete(DB_PREFIX . 'tokens', "token = '{$_COOKIE['cookie_login']}'");

        /**
         * Root domain, with dot '.' session is accessible from all subdomains
         */
        $rootDomain = '.' . $this->vavok->clean_domain();

	    // destroy cookies
	    setcookie('cookie_login', '', time() - 3600);
	    setcookie(session_name(), '', time() - 3600);

	    // if user is logged in from root dir
	    setcookie('cookie_login', '', 1, '/', $rootDomain);
	    setcookie(session_name(), '', time() - 3600, $rootDomain);

	    // Destoy session
	    $this->destroy_current_session();

	    // Start new session
	    session_start();

	    // Generate new session id
	    session_regenerate_id();
	}

	/**
	 * Destroy session
	 */
	public function destroy_current_session()
	{
        session_unset();
        session_destroy();
        session_write_close();
        setcookie(session_name(), '', 0, '/');
	}

	// count login attempts
	public function login_attempt_count($seconds, $username, $ip)
	{
	    try {
	        // First we delete old attempts from the table
	        $oldest = strtotime(date("Y-m-d H:i:s") . " - " . $seconds . " seconds");
	        $oldest = date("Y-m-d H:i:s", $oldest);
	        $this->vavok->go('db')->delete(DB_PREFIX . 'login_attempts', "`datetime` < '{$oldest}'");
	        
	        // Next we insert this attempt into the table
	        $values = array(
	        'address' => $ip,
	        'datetime' =>  date("Y-m-d H:i:s"),
	        'username' => $username
	        );
	        $this->vavok->go('db')->insert_data(DB_PREFIX . 'login_attempts', $values);
	        
	        // Finally we count the number of recent attempts from this ip address  
	        $attempts = $this->vavok->go('db')->count_row('login_attempts', " `address` = '" . $_SERVER['REMOTE_ADDR'] . "' AND `username` = '" . $username . "'");

	        return $attempts;
	    } catch (PDOEXCEPTION $e) {
	        echo "Error: " . $e;
	    }
	}

	// register user
	public function register($name, $pass, $regkeys, $rkey, $theme, $mail)
	{	    
	    $values = array(
	        'name' => $name,
	        'pass' => $this->password_encrypt($pass),
	        'perm' => '107',
	        'skin' => $theme,
	        'browsers' => $this->vavok->check($this->user_browser()),
	        'ipadd' => $this->find_ip(),
	        'timezone' => 0,
	        'banned' => 0,
	        'newmsg' => 0,
	        'lang' => $this->vavok->get_configuration('siteDefaultLang')
	    );
	    $this->vavok->go('db')->insert_data(DB_PREFIX . 'vavok_users', $values);

	    $user_id = $this->vavok->go('db')->get_data(DB_PREFIX . 'vavok_users', "name='{$name}'", 'id')['id'];

	    $this->vavok->go('db')->insert_data(DB_PREFIX . 'vavok_profil', array('uid' => $user_id, 'opentem' => 0, 'commadd' => 0, 'subscri' => 0, 'regdate' => time(), 'regche' => $regkeys, 'regkey' => $rkey, 'lastvst' => time(), 'forummes' => 0, 'chat' => 0));
	    $this->vavok->go('db')->insert_data(DB_PREFIX . 'vavok_about', array('uid' => $user_id, 'sex' => 'N', 'email' => $mail));
	    $this->vavok->go('db')->insert_data(DB_PREFIX . 'notif', array('uid' => $user_id, 'lstinb' => 0, 'type' => 'inbox'));

	    // send private message
	    $msg = $this->vavok->go('localization')->string('autopmreg');
	    $this->autopm($msg, $user_id);
	}

	/**
	 * Update informations about user
	 */

	/**
	 * Change user's language
	 *
	 * @param string $language
	 * @return void
	 */
	public function change_language($language = '')
	{
		$language = $this->get_prefered_language($language);
		$current_session = isset($_SESSION['lang']) ? $_SESSION['lang'] : '';

		// Update language if it is changed and if language is installed
		if ($current_session != $language && file_exists(BASEDIR . 'include/lang/' . $language . '/index.php')) {
			// unset current language
			$_SESSION['lang'] = '';
			unset($_SESSION['lang']);

			// set new language
			$_SESSION['lang'] = $language;

			// Update language if user is registered
			if ($this->is_reg()) { $this->vavok->go('db')->update(DB_PREFIX . 'vavok_users', 'lang', $language, "id='{$this->user_id}'"); }
		}
	}

	// delete user from database
	public function delete_user($users)
	{
	    // check if it is really user's id
	    if (preg_match("/^([0-9]+)$/", $users)) {
	        $users_id = $users;
	    } else {
	        $users_id = $this->getidfromnick($users);
	    }

	    $this->vavok->go('db')->delete(DB_PREFIX . "vavok_users", "id = '{$users_id}'");
	    $this->vavok->go('db')->delete(DB_PREFIX . "vavok_profil", "uid = '{$users_id}'");
	    $this->vavok->go('db')->delete(DB_PREFIX . "vavok_about", "uid = '{$users_id}'");
	    $this->vavok->go('db')->delete(DB_PREFIX . "inbox", "byuid = '{$users_id}' OR touid = '{$users_id}'");
	    $this->vavok->go('db')->delete(DB_PREFIX . "ignore", "target = '{$users_id}' OR name = '{$users_id}'");
	    $this->vavok->go('db')->delete(DB_PREFIX . "buddy", "target = '{$users_id}' OR name = '{$users_id}'");
	    $this->vavok->go('db')->delete(DB_PREFIX . "subs", "user_id = '{$users_id}'");
	    $this->vavok->go('db')->delete(DB_PREFIX . "notif", "uid = '{$users_id}'");
	    $this->vavok->go('db')->delete(DB_PREFIX . "specperm", "uid = '{$users_id}'");
	    if ($this->vavok->go('db')->table_exists(DB_PREFIX . 'group_members')) $this->vavok->go('db')->delete(DB_PREFIX . 'group_members', "uid = '{$users_id}'");

	    return $users;
	}

	/**
	 * Inbox
	 */

	// private messages
	public function getpmcount($uid, $view = "all") {
	    if ($view == "all") {
	        $nopm = $this->vavok->go('db')->count_row(DB_PREFIX . 'inbox', "touid='" . $uid . "' AND (deleted <> '" . $this->user_id . "' OR deleted IS NULL)");
	    } elseif ($view == "snt") {
	        $nopm = $this->vavok->go('db')->count_row(DB_PREFIX . 'inbox', "byuid='" . $uid . "' AND (deleted <> '" . $this->user_id . "' OR deleted IS NULL)");
	    } elseif ($view == "str") {
	        $nopm = $this->vavok->go('db')->count_row(DB_PREFIX . 'inbox', "touid='" . $uid . "' AND starred='1'");
	    } elseif ($view == "urd") {
	        $nopm = $this->vavok->go('db')->count_row(DB_PREFIX . 'inbox', "touid='" . $uid . "' AND unread='1'");
	    } 
	    return $nopm;
	}

	/**
	 * Get number of unread pms
	 * 
	 * @param integer $uid
	 * @return integer
	 */
	function getunreadpm($uid)
	{
	    return $this->vavok->go('db')->count_row(DB_PREFIX . 'inbox', "touid='{$uid}' AND unread='1'");
	}

	// number of private msg's
	public function user_mail($userid) {
	    $fcheck_all = $this->getpmcount($userid);
	    $new_privat = $this->getunreadpm($userid);

	    $all_mail = $new_privat . '/' . $fcheck_all;

	    return $all_mail;
	}

	public function isstarred($pmid) {
	    $strd = $this->vavok->go('db')->get_data(DB_PREFIX . 'inbox', "id='{$pmid}'", 'starred');
	    if ($strd['starred'] == 1) {
	        return true;
	    } else {
	        return false;
	    }
	}

	public function parsepm($text) {
		// decode
		$text = base64_decode($text);

		// format message
	    $text = $this->vavok->getbbcode($this->vavok->smiles($this->vavok->antiword($text)));

	    // strip slashes
	    if (function_exists('get_magic_quotes_gpc')) {
	        $text = stripslashes($text);
	    }

	    return $text;
	}

	// send private message
	public function send_pm($pmtext, $user_id, $who) {
		$pmtext = base64_encode($pmtext);

		$time = time();

        $this->vavok->go('db')->insert_data(DB_PREFIX . 'inbox', array('text' => $pmtext, 'byuid' => $user_id, 'touid' => $who, 'timesent' => time()));

        $user_profile = $this->vavok->go('db')->get_data(DB_PREFIX . 'vavok_profil', "uid='{$who}'", 'lastvst');
        $last_notif = $this->vavok->go('db')->get_data(DB_PREFIX . 'notif', "uid='{$who}' AND type='inbox'", 'lstinb, type'); 
        // no data in database, insert data
        if (empty($last_notif['lstinb']) && empty($last_notif['type'])) {
            $this->vavok->go('db')->insert_data(DB_PREFIX . 'notif', array('uid' => $who, 'lstinb' => $time, 'type' => 'inbox'));
        } 
        $notif_expired = $last_notif['lstinb'] + 864000;

        if (($user_profile['lastvst'] + 3600) < $time && $time > $notif_expired && ($inbox_notif['active'] == 1 || empty($inbox_notif['active']))) {
            $user_mail = $this->vavok->go('db')->get_data(DB_PREFIX . 'vavok_about', "uid='{$who}'", 'email');

            $send_mail = new Mailer();
            $send_mail->queue_email($user_mail['email'], "Message on " . $this->vavok->get_configuration('homeUrl'), "Hello " . $vavok->go('users')->getnickfromid($who) . "\r\n\r\nYou have new message on site " . $this->vavok->get_configuration('homeUrl'), '', '', 'normal'); // update lang

            $this->vavok->go('db')->update(DB_PREFIX . 'notif', 'lstinb', $time, "uid='" . $who . "' AND type='inbox'");
        }
	}

	/**
	 *  Private message by system
	 * 
	 * @param string $msg
	 * @param integer $who
	 * @param integer $sender_id
	 * @return void
	 */
	public function autopm($msg, $who, $sender_id = '')
	{
		$sender = !empty($sender_id) ? $sender_id : 0;

	 	$values = array(
	 	'text' => base64_encode($msg),
	 	'byuid' => $sender,
	 	'touid' => $who,
	 	'unread' => '1',
	 	'timesent' => time()
		);

		$this->vavok->go('db')->insert_data(DB_PREFIX . 'inbox', $values);
	}

	/**
	 * Informations about users
	 */

	// Show username
	public function show_username() {
		return isset($_SESSION['log']) ? $_SESSION['log'] : '';
	}

	/**
	 * Get user nick from user id number
	 *
	 * @param bool $uid
	 * @return str $unick
	 */
	public function getnickfromid($uid)
	{
	    $unick = $this->vavok->go('db')->get_data(DB_PREFIX . 'vavok_users', "id='{$uid}'", 'name');
	    return $unick = !empty($unick['name']) ? $unick['name'] : '';
	}

	/**
	 * Get vavok_users user id from nickname
	 * 
	 * @param string $nick
	 * @return int
	 */
	public function getidfromnick($nick)
	{
	    $uid = $this->vavok->go('db')->get_data(DB_PREFIX . 'vavok_users', "name='{$nick}'", 'id');
	    $id = !empty($uid['id']) ? $uid['id'] : 0;

	    return $id;
	}

	// get vavok_users user id from email
	public function get_id_from_mail($mail) {
	    $uid = $this->vavok->go('db')->get_data(DB_PREFIX . 'vavok_about', "email='{$mail}'", 'uid');
	    return $uid['uid'];
	}

	// Calculate age
	public function get_age($strdate) {
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

	// Get informations about user from database
	public function get_user_info($users_id, $info) {

	    if ($info == 'email') {
	        return $this->vavok->go('db')->get_data(DB_PREFIX . 'vavok_about', "uid='{$users_id}'", 'email')['email'];
	    } elseif ($info == 'full_name') {
	    	$uinfo = $this->vavok->go('db')->get_data(DB_PREFIX . 'vavok_about', "uid='{$users_id}'", 'rname, surname');

	    	$full_name = !empty($uinfo['rname'] . $uinfo['surname']) ? rtrim($uinfo['rname'] . ' ' . $uinfo['surname']) : false;

	    	return $full_name;
	    } elseif ('language') {
	    	return $this->vavok->go('db')->get_data(DB_PREFIX . 'vavok_users', "id='{$users_id}'", 'lang')['lang'];
	    } else { return false; }
	}

	// User's language
	public function get_user_language() {
		if ($this->is_reg()) {
			return $this->get_user_info($this->user_id, 'language');
		} else {
			// Use language from session if exists
			if (!empty($_SESSION['lang'])) { return $_SESSION['lang']; }
			else {
				return $this->vavok->get_configuration('siteDefaultLang');
			}
		}
	}

	// Find user's IP address
	public function find_ip() {
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
	public function user_online($login)
	{
	    $xuser = $this->getidfromnick($login);
	    $statwho = '<font color="#CCCCCC">[Off]</font>';

	    $result = $this->vavok->go('db')->count_row(DB_PREFIX . 'online', "user='{$xuser}'");

	    if ($result > 0 && $xuser > 0) $statwho = '<font color="#00FF00">[On]</font>';

	    return $statwho;
	}

	// Administrator status name
	function user_status($message) {
	    $message = str_replace('101', $this->vavok->go('localization')->string('access101'), $message);
	    $message = str_replace('102', $this->vavok->go('localization')->string('access102'), $message);
	    $message = str_replace('103', $this->vavok->go('localization')->string('access103'), $message);
	    $message = str_replace('105', $this->vavok->go('localization')->string('access105'), $message);
	    $message = str_replace('106', $this->vavok->go('localization')->string('access106'), $message);
	    $message = str_replace('107', $this->vavok->go('localization')->string('access107'), $message);
	    return $message;
	}

	// check permissions for admin panel
	// check if user have permitions to see, edit, delete, etc selected part of the website
	function check_permissions($permname, $needed = 'show') {
	    $permname = str_replace('.php', '', $permname);

	    if ($this->is_administrator()) {
	        return true;
	    }

	    $check = $this->vavok->go('db')->count_row(DB_PREFIX . 'specperm', "uid='{$this->user_id}' AND permname='{$permname}'");

	    if ($check > 0) {
	    	
	        $check_data = $this->vavok->go('db')->get_data(DB_PREFIX . 'specperm', "uid='{$this->user_id}' AND permname='{$permname}'", 'permacc');
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
	        } else {
	            return false;
	        }
	    } else {
	        return false;
	    }
	}

	// Current user id
	function current_user_id($user_id = '') {
	    $user_id = $this->user_id;

	    if (empty($user_id)) {
	        $user_id = 0;
	    }

	    return $user_id;
	}

	// number of registered members
	function regmemcount() {
	    $rmc = $this->vavok->go('db')->count_row(DB_PREFIX . 'vavok_users');
	    return $rmc;
	}

	function user_device() {
    	return BrowserDetection::userDevice();
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
	 * Validations
	 */

	// username validation
	function validate_username($username)
	{
		if (preg_match("/^[\p{L}_.0-9]{3,15}$/ui", $username)) {
			return true;
		} else { return false; }
	}

	// email validation with support for unicode emails
	function validate_email($email) {
		// check unicode email
		if ($this->vavok->is_unicode($email)) {
		    if (preg_match("/^(?!\.)((?!.*\.{2})[a-zA-Z0-9\x{0080}-\x{00FF}\x{0100}-\x{017F}\x{0180}-\x{024F}\x{0250}-\x{02AF}\x{0300}-\x{036F}\x{0370}-\x{03FF}\x{0400}-\x{04FF}\x{0500}-\x{052F}\x{0530}-\x{058F}\x{0590}-\x{05FF}\x{0600}-\x{06FF}\x{0700}-\x{074F}\x{0750}-\x{077F}\x{0780}-\x{07BF}\x{07C0}-\x{07FF}\x{0900}-\x{097F}\x{0980}-\x{09FF}\x{0A00}-\x{0A7F}\x{0A80}-\x{0AFF}\x{0B00}-\x{0B7F}\x{0B80}-\x{0BFF}\x{0C00}-\x{0C7F}\x{0C80}-\x{0CFF}\x{0D00}-\x{0D7F}\x{0D80}-\x{0DFF}\x{0E00}-\x{0E7F}\x{0E80}-\x{0EFF}\x{0F00}-\x{0FFF}\x{1000}-\x{109F}\x{10A0}-\x{10FF}\x{1100}-\x{11FF}\x{1200}-\x{137F}\x{1380}-\x{139F}\x{13A0}-\x{13FF}\x{1400}-\x{167F}\x{1680}-\x{169F}\x{16A0}-\x{16FF}\x{1700}-\x{171F}\x{1720}-\x{173F}\x{1740}-\x{175F}\x{1760}-\x{177F}\x{1780}-\x{17FF}\x{1800}-\x{18AF}\x{1900}-\x{194F}\x{1950}-\x{197F}\x{1980}-\x{19DF}\x{19E0}-\x{19FF}\x{1A00}-\x{1A1F}\x{1B00}-\x{1B7F}\x{1D00}-\x{1D7F}\x{1D80}-\x{1DBF}\x{1DC0}-\x{1DFF}\x{1E00}-\x{1EFF}\x{1F00}-\x{1FFF}\x{20D0}-\x{20FF}\x{2100}-\x{214F}\x{2C00}-\x{2C5F}\x{2C60}-\x{2C7F}\x{2C80}-\x{2CFF}\x{2D00}-\x{2D2F}\x{2D30}-\x{2D7F}\x{2D80}-\x{2DDF}\x{2F00}-\x{2FDF}\x{2FF0}-\x{2FFF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{3100}-\x{312F}\x{3130}-\x{318F}\x{3190}-\x{319F}\x{31C0}-\x{31EF}\x{31F0}-\x{31FF}\x{3200}-\x{32FF}\x{3300}-\x{33FF}\x{3400}-\x{4DBF}\x{4DC0}-\x{4DFF}\x{4E00}-\x{9FFF}\x{A000}-\x{A48F}\x{A490}-\x{A4CF}\x{A700}-\x{A71F}\x{A800}-\x{A82F}\x{A840}-\x{A87F}\x{AC00}-\x{D7AF}\x{F900}-\x{FAFF}\.!#$%&'*+-\/=?^_`{|}~\-\d]+)@(?!\.)([a-zA-Z0-9\x{0080}-\x{00FF}\x{0100}-\x{017F}\x{0180}-\x{024F}\x{0250}-\x{02AF}\x{0300}-\x{036F}\x{0370}-\x{03FF}\x{0400}-\x{04FF}\x{0500}-\x{052F}\x{0530}-\x{058F}\x{0590}-\x{05FF}\x{0600}-\x{06FF}\x{0700}-\x{074F}\x{0750}-\x{077F}\x{0780}-\x{07BF}\x{07C0}-\x{07FF}\x{0900}-\x{097F}\x{0980}-\x{09FF}\x{0A00}-\x{0A7F}\x{0A80}-\x{0AFF}\x{0B00}-\x{0B7F}\x{0B80}-\x{0BFF}\x{0C00}-\x{0C7F}\x{0C80}-\x{0CFF}\x{0D00}-\x{0D7F}\x{0D80}-\x{0DFF}\x{0E00}-\x{0E7F}\x{0E80}-\x{0EFF}\x{0F00}-\x{0FFF}\x{1000}-\x{109F}\x{10A0}-\x{10FF}\x{1100}-\x{11FF}\x{1200}-\x{137F}\x{1380}-\x{139F}\x{13A0}-\x{13FF}\x{1400}-\x{167F}\x{1680}-\x{169F}\x{16A0}-\x{16FF}\x{1700}-\x{171F}\x{1720}-\x{173F}\x{1740}-\x{175F}\x{1760}-\x{177F}\x{1780}-\x{17FF}\x{1800}-\x{18AF}\x{1900}-\x{194F}\x{1950}-\x{197F}\x{1980}-\x{19DF}\x{19E0}-\x{19FF}\x{1A00}-\x{1A1F}\x{1B00}-\x{1B7F}\x{1D00}-\x{1D7F}\x{1D80}-\x{1DBF}\x{1DC0}-\x{1DFF}\x{1E00}-\x{1EFF}\x{1F00}-\x{1FFF}\x{20D0}-\x{20FF}\x{2100}-\x{214F}\x{2C00}-\x{2C5F}\x{2C60}-\x{2C7F}\x{2C80}-\x{2CFF}\x{2D00}-\x{2D2F}\x{2D30}-\x{2D7F}\x{2D80}-\x{2DDF}\x{2F00}-\x{2FDF}\x{2FF0}-\x{2FFF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{3100}-\x{312F}\x{3130}-\x{318F}\x{3190}-\x{319F}\x{31C0}-\x{31EF}\x{31F0}-\x{31FF}\x{3200}-\x{32FF}\x{3300}-\x{33FF}\x{3400}-\x{4DBF}\x{4DC0}-\x{4DFF}\x{4E00}-\x{9FFF}\x{A000}-\x{A48F}\x{A490}-\x{A4CF}\x{A700}-\x{A71F}\x{A800}-\x{A82F}\x{A840}-\x{A87F}\x{AC00}-\x{D7AF}\x{F900}-\x{FAFF}\-\.\d]+)((\.([a-zA-Z\x{0080}-\x{00FF}\x{0100}-\x{017F}\x{0180}-\x{024F}\x{0250}-\x{02AF}\x{0300}-\x{036F}\x{0370}-\x{03FF}\x{0400}-\x{04FF}\x{0500}-\x{052F}\x{0530}-\x{058F}\x{0590}-\x{05FF}\x{0600}-\x{06FF}\x{0700}-\x{074F}\x{0750}-\x{077F}\x{0780}-\x{07BF}\x{07C0}-\x{07FF}\x{0900}-\x{097F}\x{0980}-\x{09FF}\x{0A00}-\x{0A7F}\x{0A80}-\x{0AFF}\x{0B00}-\x{0B7F}\x{0B80}-\x{0BFF}\x{0C00}-\x{0C7F}\x{0C80}-\x{0CFF}\x{0D00}-\x{0D7F}\x{0D80}-\x{0DFF}\x{0E00}-\x{0E7F}\x{0E80}-\x{0EFF}\x{0F00}-\x{0FFF}\x{1000}-\x{109F}\x{10A0}-\x{10FF}\x{1100}-\x{11FF}\x{1200}-\x{137F}\x{1380}-\x{139F}\x{13A0}-\x{13FF}\x{1400}-\x{167F}\x{1680}-\x{169F}\x{16A0}-\x{16FF}\x{1700}-\x{171F}\x{1720}-\x{173F}\x{1740}-\x{175F}\x{1760}-\x{177F}\x{1780}-\x{17FF}\x{1800}-\x{18AF}\x{1900}-\x{194F}\x{1950}-\x{197F}\x{1980}-\x{19DF}\x{19E0}-\x{19FF}\x{1A00}-\x{1A1F}\x{1B00}-\x{1B7F}\x{1D00}-\x{1D7F}\x{1D80}-\x{1DBF}\x{1DC0}-\x{1DFF}\x{1E00}-\x{1EFF}\x{1F00}-\x{1FFF}\x{20D0}-\x{20FF}\x{2100}-\x{214F}\x{2C00}-\x{2C5F}\x{2C60}-\x{2C7F}\x{2C80}-\x{2CFF}\x{2D00}-\x{2D2F}\x{2D30}-\x{2D7F}\x{2D80}-\x{2DDF}\x{2F00}-\x{2FDF}\x{2FF0}-\x{2FFF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{3100}-\x{312F}\x{3130}-\x{318F}\x{3190}-\x{319F}\x{31C0}-\x{31EF}\x{31F0}-\x{31FF}\x{3200}-\x{32FF}\x{3300}-\x{33FF}\x{3400}-\x{4DBF}\x{4DC0}-\x{4DFF}\x{4E00}-\x{9FFF}\x{A000}-\x{A48F}\x{A490}-\x{A4CF}\x{A700}-\x{A71F}\x{A800}-\x{A82F}\x{A840}-\x{A87F}\x{AC00}-\x{D7AF}\x{F900}-\x{FAFF}]){2,63})+)$/u", $email)) {
				return true; 
			} else {
				return false; 
			}
		} else {

			if (filter_var($email, FILTER_VALIDATE_EMAIL)) { // non-unicode email
		        return true;
		    } else {
		        return false;
		    }
		}
	}

	/**
	 * Check if user is moderator
	 * 
	 * @param int $num
	 * @param int $id
	 * @return bool
	 */
	function is_moderator($num = '', $id = '')
	{
	    if (empty($id) && !empty($this->user_id)) {
	        $id = $this->user_id;
	    }

	    $chk_adm = $this->vavok->go('db')->get_data(DB_PREFIX . 'vavok_users', "id='{$id}'", 'perm');
	    $perm = isset($chk_adm['perm']) ? intval($chk_adm['perm']) : 0;
	    
	    if ($perm === $num) {
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
	function is_administrator($num = '', $id = '')
	{
	    if (empty($id) && !empty($this->user_id)) {
	        $id = $this->user_id;
	    }

	    $chk_adm = $this->vavok->go('db')->get_data(DB_PREFIX . 'vavok_users', "id='{$id}'", 'perm');
	    $perm = isset($chk_adm['perm']) ? intval($chk_adm['perm']) : 0;

	    if ($perm === $num) {
	        return true;
	    } if (empty($num) && ($perm === 101 || $perm === 102)) {
	        return true;
	    } else {
	        return false;
	    } 
	}

	// is ignored
	function isignored($tid, $uid) {
	    $ign = $this->vavok->go('db')->count_row(DB_PREFIX . '`ignore`', "`target`='" . $tid . "' AND `name`='" . $uid . "'");
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
		if ($vavok->go('users')->is_moderator($tid)) {
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
	    $ign = $this->vavok->go('db')->count_row(DB_PREFIX . 'buddy', "target='" . $tid . "' AND name='" . $uid . "'");
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

	public function get_prefered_language($language = '', $format = '') {
		// Get browser preferred language
		$locale = isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2) : '';

		// Use default language
		if (empty($language)) { $language = $this->vavok->get_configuration('siteDefaultLang'); }


		if ($language == 'en') {
			$language = 'english';
		} elseif ($language == 'sr') {
			$language = 'serbian_latin';

			// If browser user serbian it is cyrillic
			if ($locale == 'sr') {
				$language = 'serbian_cyrillic';
			}

			// check if language is available
			if ($language == 'serbian_latin' && file_exists(BASEDIR . "include/lang/serbian_latin/index.php")) {
				$language = 'serbian_latin';
			} elseif (file_exists(BASEDIR . "include/lang/serbian_cyrillic/index.php")) { // check if cyrillic scrypt is installed
				$language = 'serbian_cyrillic';
			} else {
				$language = 'serbian_latin'; // cyrillic script not installed, use latin
			}
		}

		// Return showr version
		if ($format == 'short') {
			if ($language == 'english') { $language = 'en'; }

			if ($language == 'serbian_latin' || $language == 'serbian_cyrillic') {
				$language = 'sr';
			}
		}

		return strtolower($language);
	}

}

?>