<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Package:   Class for user management
* Updated:   20.07.2020. 4:16:14
*/


class Users {

	function __construct() {
		global $db;

		$this->db = $db;
		$this->user_id = isset($_SESSION['log']) ? $this->getidfromnick(check($_SESSION['log'])) : '';
	}

	// check current session if user is registered
	public function is_reg() {

	    if (!empty($_SESSION['log']) && !empty($_SESSION['permissions'])) {

	        if (!empty($this->user_id)) {

	            $show_user = $this->db->get_data('vavok_users', "id='" . $this->user_id . "'", 'name, perm');

	            // Check if permissions are changed
	            if (check($_SESSION['log']) == $show_user['name'] && $_SESSION['permissions'] == $show_user['perm']) {
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

	// Logout
	function logout($user_id) {

	    $this->db->delete('online', "user = '{$this->user_id}'");

	    // destroy cookies
	    setcookie('cooklog', "", time() - 3600);
	    setcookie('cookpass', "", time() - 3600);
	    setcookie(session_name(), "", time() - 3600);

	    // if user is logged in from root dir
	    setcookie("cooklog", '', 1, '/');
	    setcookie("cookpass", '', 1, '/');

	    // destoy session
	    session_destroy();

	}

	// count login attempts
	function login_attempt_count($seconds, $username, $ip, $db) {
	    try {
	        // First we delete old attempts from the table
	        $oldest = strtotime(date("Y-m-d H:i:s") . " - " . $seconds . " seconds");
	        $oldest = date("Y-m-d H:i:s", $oldest);
	        $db->delete('login_attempts', "`datetime` < '" . $oldest . "'");
	        
	        // Next we insert this attempt into the table
	        $values = array(
	        'address' => $ip,
	        'datetime' =>  date("Y-m-d H:i:s"),
	        'username' => $username
	        );
	        $db->insert_data('login_attempts', $values);
	        
	        // Finally we count the number of recent attempts from this ip address  
	        $attempts = $db->count_row('login_attempts', " `address` = '" . $_SERVER['REMOTE_ADDR'] . "' AND `username` = '" . $username . "'");

	        return $attempts;
	    } catch (PDOEXCEPTION $e) {
	        echo "Error: " . $e;
	    }
	}

	// register user
	function register($name, $pass, $regkeys, $rkey, $theme, $mail) {
	    global $lang_home, $config, $db;
	    
	    $values = array(
	        'name' => $name,
	        'pass' => $this->password_encrypt($pass),
	        'perm' => '107',
	        'skin' => $theme,
	        'browsers' => check($this->user_browser()),
	        'ipadd' => $this->find_ip(),
	        'timezone' => 0,
	        'banned' => 0,
	        'newmsg' => 0,
	        'lang' => $config["language"]
	    );
	    $db->insert_data('vavok_users', $values);

	    $user_id = $db->get_data('vavok_users', "name='{$name}'", 'id')['id'];

	    $db->insert_data('vavok_profil', array('uid' => $user_id, 'opentem' => 0, 'commadd' => 0, 'subscri' => 0, 'regdate' => time(), 'regche' => $regkeys, 'regkey' => $rkey, 'lastvst' => time(), 'forummes' => 0, 'chat' => 0));
	    $db->insert_data('page_setting', array('uid' => $user_id, 'newsmes' => 5, 'forummes' => 5, 'forumtem' => 10, 'privmes' => 5));
	    $db->insert_data('vavok_about', array('uid' => $user_id, 'sex' => 'N', 'email' => $mail));
	    $db->insert_data('notif', array('uid' => $user_id, 'lstinb' => 0, 'type' => 'inbox'));

	    // send private message
	    $msg = $lang_home['autopmreg'];
	    $this->autopm($msg, $user_id);

	}

	/*

	Update informations about user

	*/

	// Change user's language
	public function change_language($language) {

		// unset current language
		$_SESSION['lang'] = "";
		unset($_SESSION['lang']);

		// set new language
		$_SESSION['lang'] = $language;

		// Update language if user is registered
		if ($this->is_reg()) { $this->db->update('vavok_users', 'lang', $language, "id='{$this->user_id}'"); }

	}

	// delete user from database
	function delete_user($users) {

	    // check if it is really user's id
	    if (preg_match("/^([0-9]+)$/", $users)) {
	        $users_id = $users;
	    } else {
	        $users_id = $this->getidfromnick($users);
	    }

	    $this->db->delete("vavok_users", "id = '{$users_id}'");
	    $this->db->delete("vavok_profil", "uid = '{$users_id}'");
	    $this->db->delete("vavok_about", "uid = '{$users_id}'");
	    $this->db->delete("inbox", "byuid = '{$users_id}' OR touid = '{$users_id}'");
	    $this->db->delete("ignore", "target = '{$users_id}' OR name = '{$users_id}'");
	    $this->db->delete("buddy", "target = '{$users_id}' OR name = '{$users_id}'");
	    $this->db->delete("subs", "user_id = '{$users_id}'");
	    $this->db->delete("notif", "uid = '{$users_id}'");
	    $this->db->delete("specperm", "uid = '{$users_id}'");

	    return $users;

	}

	/*

	Inbox

	*/

	// private messages
	function getpmcount($uid, $view = "all") {
	    if ($view == "all") {
	        $nopm = $this->db->count_row('inbox', "touid='" . $uid . "' AND (deleted <> '" . $this->user_id . "' OR deleted IS NULL)");
	    } elseif ($view == "snt") {
	        $nopm = $this->db->count_row('inbox', "byuid='" . $uid . "' AND (deleted <> '" . $this->user_id . "' OR deleted IS NULL)");
	    } elseif ($view == "str") {
	        $nopm = $this->db->count_row('inbox', "touid='" . $uid . "' AND starred='1'");
	    } elseif ($view == "urd") {
	        $nopm = $this->db->count_row('inbox', "touid='" . $uid . "' AND unread='1'");
	    } 
	    return $nopm;
	} 

	// get number of unread pms
	function getunreadpm($uid) {
	    return $this->db->count_row('inbox', "touid='" . $uid . "' AND unread='1'")[0];
	}

	// number of private msg's
	function user_mail($userid) {
	    $fcheck_all = $this->getpmcount($userid);
	    $new_privat = $this->getunreadpm($userid);

	    $all_mail = $new_privat . '/' . $fcheck_all;

	    return $all_mail;
	}

	function isstarred($pmid) {
	    $strd = $this->db->get_data('inbox', "id='" . $pmid . "'", 'starred');
	    if ($strd['starred'] == "1") {
	        return true;
	    } else {
	        return false;
	    } 
	} 

	function parsepm($text) {
		
		// decode
		$text = base64_decode($text);

		// format message
	    $text = getbbcode(smiles(antiword($text)));

	    // strip slashes
	    if (get_magic_quotes_gpc()) {
	        $text = stripslashes($text);
	    } 

	    return $text;
	} 

	// send private message
	function send_pm($pmtext, $user_id, $who) {

		$pmtext = base64_encode($pmtext);

		$time = time();

        $this->db->insert_data('inbox', array('text' => $pmtext, 'byuid' => $user_id, 'touid' => $who, 'timesent' => time()));

        $user_profile = $this->db->get_data('vavok_profil', "uid='{$who}'", 'lastvst');
        $last_notif = $this->db->get_data('notif', "uid='{$who}' AND type='inbox'", 'lstinb, type'); 
        // no data in database, insert data
        if (empty($last_notif['lstinb']) && empty($last_notif['type'])) {
            $this->db->insert_data('notif', array('uid' => $who, 'lstinb' => $time, 'type' => 'inbox'));
        } 
        $notif_expired = $last_notif['lstinb'] + 864000;

        if (($user_profile['lastvst'] + 3600) < $time && $time > $notif_expired && ($inbox_notif['active'] == 1 || empty($inbox_notif['active']))) {
            $user_mail = $this->db->get_data('vavok_about', "uid='{$who}'", 'email');

            $send_mail = new Mailer();
            $send_mail->send($user_mail['email'], "Message on " . $config["homeUrl"], "Hello " . $users->getnickfromid($who) . "\r\n\r\nYou have new message on site " . $config["homeUrl"]); // update lang

            $this->db->update('notif', 'lstinb', $time, "uid='" . $who . "' AND type='inbox'");
        }

	}

	// Private message by system
	function autopm($msg, $who, $sys = '') {

	    if (!empty($sys)) {
	        $sysid = $sys;
	    } else {
	        $sysid = $this->getidfromnick('System');
	    }

	 	$values = array(
	 	'text' => base64_encode($msg),
	 	'byuid' => $sysid,
	 	'touid' => $who,
	 	'unread' => '1',
	 	'timesent' => time()
		);

		$this->db->insert_data('inbox', $values);
	}

	/*

	Informations about users

	*/

	// get user nick from user id number
	function getnickfromid($uid) {
	    $unick = $this->db->get_data('vavok_users', "id='{$uid}'", 'name');
	    return $unick['name'];
	}

	// get vavok_users user id from nickname
	function getidfromnick($nick) {
	    $uid = $this->db->get_data('vavok_users', "name='{$nick}'", 'id');
	    return $uid['id'];
	}

	// get vavok_users user id from email
	function get_id_from_mail($mail) {
	    $uid = $this->db->get_data('vavok_about', "email='{$mail}'", 'uid');
	    return $uid['uid'];
	}

	// Calculate age
	function get_age($strdate) {
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
	        $uinfo = $this->db->get_data('vavok_about', "uid='{$users_id}'", 'email');
	        return $uinfo['email'];
	    } elseif ($info == 'full_name') {
	    	$uinfo = $this->db->get_data('vavok_about', "uid='{$users_id}'", 'rname, surname');

	    	$full_name = !empty($uinfo['rname'] . $uinfo['surname']) ? rtrim($uinfo['rname'] . ' ' . $uinfo['surname']) : false;

	    	return $full_name;
	    } else { return false; }

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

	// user online status
	function user_online($login) {
	    $xuser = $this->getidfromnick($login);
	    $statwho = '<font color="#CCCCCC">[Off]</font>';

	    $result = $this->db->count_row('online', 'user="' . $xuser . '"');

	    if ($result > 0 && $xuser > 0) {
	        $statwho = '<font color="#00FF00">[On]</font>';
	    } 

	    return $statwho;
	}

	// check permissions for admin panel
	// check if user have permitions to see, edit, delete, etc selected part of the website
	function check_permissions($permname, $needed = 'show') {

	    $permname = str_replace('.php', '', $permname);

	    if ($this->is_administrator()) {
	        return true;
	    }

	    $check = $this->db->count_row(get_configuration('tablePrefix') . 'specperm', "uid='{$this->user_id}' AND permname='{$permname}'");

	    if ($check > 0) {
	    	
	        $check_data = $this->db->get_data(get_configuration('tablePrefix') . 'specperm', "uid='{$this->user_id}' AND permname='{$permname}'", 'permacc');
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
	    $rmc = $this->db->count_row('vavok_users');
	    return $rmc;
	}

	function user_device() {
    	return BrowserDetection::userDevice();
	}


	// visitor's browser
	function user_browser() {

		if(ini_get("browscap")) {
			$userBrowser = get_browser(null, true);
		} else {
			$detectBrowser = new BrowserDetection();
			$userBrowser = rtrim($detectBrowser->detect()->getBrowser() . ' ' . $detectBrowser->getVersion());
		}
		if (empty($userBrowser)) { $userBrowser = 'Not detected'; }

		return $userBrowser;
	}

	/*

	Validations

	*/

	// username validation
	function validate_username($username) {

		if (preg_match("/^[\p{L}_.0-9]{3,15}$/ui", $username)) {
			return true;
		} else { return false; }

	}

	// email validation with support for unicode emails
	function validate_email($email) {

		// check unicode email
		if (is_unicode($email)) {

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

	// check if user is moderator
	function is_moderator($num = '', $id = '') {
	    if (empty($id) && !empty($this->user_id)) {
	        $id = $this->user_id;
	    }

	    $chk_adm = $this->db->get_data('vavok_users', "id='{$id}'", 'perm');
	    $perm = intval($chk_adm['perm']);
	    
	    if ($perm === $num) {
	        return true;
	    } elseif (empty($num) && ($perm === 103 || $perm === 105 || $perm === 106)) {
	        return true;
	    } else {
	        return false;
	    } 
	}

	// check if user is administrator
	function is_administrator($num = '', $id = '') {
	    if (empty($id) && !empty($this->user_id)) {
	        $id = $this->user_id;
	    }

	    $chk_adm = $this->db->get_data('vavok_users', "id='{$id}'", 'perm');
	    $perm = intval($chk_adm['perm']);

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
	    $ign = $this->db->count_row('`ignore`', "`target`='" . $tid . "' AND `name`='" . $uid . "'");
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
	  if ($users->is_moderator($tid)) {
	    //you cant ignore staff members
	    return 0;
	  }
	  if (arebuds($tid, $uid)) {
	    //why the hell would anyone ignore his bud? o.O
	    return 0;
	  }
	  */
	    if (isignored($tid, $uid)) {
	        return 2; // the target is already ignored by the user
	    } 
	    return 1;
	} 

	// is buddy
	function isbuddy($tid, $uid) {
	    $ign = $this->db->count_row('buddy', "target='" . $tid . "' AND name='" . $uid . "'");
	    if ($ign > 0) {
	        return true;
	    } 
	    return false;
	}

	/*

	Other

	*/

	// user's password encription
	function password_encrypt($password) {

		return password_hash($password, PASSWORD_BCRYPT);

	}

	function password_check($password, $hash) {

		return password_verify($password, $hash);

	}


}

?>