<?php
// (c) vavok.net

// set cookie
    if (empty($_SESSION['log']) && empty($_SESSION['pass']) && !empty($_COOKIE['cookpass']) && !empty($_COOKIE['cooklog'])) {
        // decode username from cookie
        $unlog = xoft_decode($_COOKIE['cooklog'], $config["keypass"]);
        // decode password from cookie
        $unpar = xoft_decode($_COOKIE['cookpass'], $config["keypass"]);
        // encode to md5
        $unparmd5 = md5($unpar);
        // search for username provided in cookie
		$cookie_id = $users->getidfromnick($unlog);
		$cookie_check = $db->select('vavok_users', "id='" . $cookie_id . "'", '', 'name, pass');

        // if user exists
        if (!empty($cookie_check['name'])) {
            // check is password correct
            if ($unparmd5 == $cookie_check['pass'] && $unlog == $cookie_check['name'] && !empty($unlog) && !empty($unparmd5)) {

                $pr_ip = explode(".", $ip);
                $my_ip = $pr_ip[0] . $pr_ip[1] . $pr_ip[2];


                // write current session data
                $_SESSION['log'] = $unlog;
                $_SESSION['pass'] = $unpar;
                $_SESSION['my_ip'] = $my_ip;
                $_SESSION['my_brow'] = $users->user_browser();
                
                // update ip address and last visit time
                $db->update('vavok_users', 'ipadd', $ip, "id = '" . $cookie_id . "'");
                $db->update('vavok_profil', 'lastvst', time(), "uid = '" . $cookie_id . "'");
            }
        } 
    }

?>