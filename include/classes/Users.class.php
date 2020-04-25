<?php
// (c) vavok.net
// class for user management

class Users {

	function __construct() {
		global $db;

		$this->db = $db;
	}

	// get user nick from user id number
	function getnickfromid($uid) {
	    $unick = $this->db->get_data('vavok_users', "id='" . $uid . "'", 'name');
	    return $unick['name'];
	}

	// get vavok_users user id from nickname
	function getidfromnick($nick) {
	    $uid = $this->db->get_data('vavok_users', "name='" . $nick . "'", 'id');
	    return $uid['id'];
	}

	// delete user from database
	function delete_user($users) {
	    // check is it really user's id
	    if (preg_match("/^([0-9]+)$/", $users)) {
	        $users_id = $users;
	    } else {
	        $users_id = $this->getidfromnick($users);
	    }

	    $this->db->delete("vavok_users", "id = '" . $users_id . "'");
	    $this->db->delete("vavok_profil", "uid = '" . $users_id . "'");
	    $this->db->delete("page_setting", "uid = '" . $users_id . "'");
	    $this->db->delete("vavok_about", "uid = ''" . $users_id . "'");
	    $this->db->delete("inbox", "byuid = " . $users_id . "' OR touid` = '" . $users_id . "'");
	    $this->db->delete("ignore", "target = ''" . $users_id . " OR name = '" . $users_id . "'");
	    $this->db->delete("buddy", "target = '" . $users_id . "' OR name = '" . $users_id . "'");
	    $this->db->delete("subs", "user_id = '" . $users_id . "'");
	    $this->db->delete("notif", "uid = '" . $users_id . "'");
	    $this->db->delete("specperm", "uid = '" . $users_id . "'");

	    return $users;
	}

	// check if user is moderator
	function is_moderator($num = '', $id = '') {
	    if (empty($id) && !empty(current_user_id())) {
	        $id = current_user_id();
	    }

	    $chk_adm = $this->db->get_data('vavok_users', "id='" . $id . "'", 'perm');
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
	    if (empty($id) && !empty(current_user_id())) {
	        $id = current_user_id();
	    }

	    $chk_adm = $this->db->get_data('vavok_users', "id='" . $id . "'", 'perm');
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
	  if (ismod($tid)) {
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

	// private messages
	function getpmcount($uid, $view = "all") {
	    if ($view == "all") {
	        $nopm = $this->db->count_row('inbox', "touid='" . $uid . "' AND (deleted <> '" . current_user_id() . "' OR deleted IS NULL)");
	    } elseif ($view == "snt") {
	        $nopm = $this->db->count_row('inbox', "byuid='" . $uid . "' AND (deleted <> '" . current_user_id() . "' OR deleted IS NULL)");
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

	// number of registered members
	function regmemcount() {
	    $rmc = $this->db->count_row('vavok_users');
	    return $rmc;
	}

	function isstarred($pmid) {
	    $strd = $this->db->select('inbox', "id='" . $pmid . "'", '', 'starred');
	    if ($strd['starred'] == "1") {
	        return true;
	    } else {
	        return false;
	    } 
	} 

	function parsepm($text) {
	    $text = antiword($text);
	    $text = smiles($text);
	    $text = getbbcode($text);
	    if (get_magic_quotes_gpc()) {
	        $text = stripslashes($text);
	    } 

	    return $text;
	} 

	// check current session if user is registered
	function is_reg() {
	    if (!empty($_SESSION['log']) && !empty($_SESSION['pass'])) {
	        $isuser_check = $this->getidfromnick(check($_SESSION['log']));
	        if (!empty($isuser_check)) {
	            $show_user = $this->db->get_data('vavok_users', "id='" . $isuser_check . "'", 'name, pass');
	            if (check($_SESSION['log']) == $show_user['name'] && md5($_SESSION['pass']) == $show_user['pass']) {
	                return true;
	            } else {
	                session_destroy();
	                return false;
	            }
	        } else {
	            session_destroy();
	            return false;
	        }
	    }
	}

	// get info about user
	function get_user_info($xuser_id, $info) {
	    if ($info == 'email') {
	        $uinfo = $this->db->select('vavok_about', "uid='" . $xuser_id . "'", '', 'email');
	        return $uinfo['email'];
	    } 
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

}

?>