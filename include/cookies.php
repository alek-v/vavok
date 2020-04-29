<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       http://vavok.net
* Updated:   29.04.2020. 9:03:54
*/

// set cookie
if (empty($_SESSION['log']) && !empty($_COOKIE['cookpass']) && !empty($_COOKIE['cooklog'])) {

    // decode username from cookie
    $unlog = xoft_decode($_COOKIE['cooklog'], $config["keypass"]);

    // decode password from cookie
    $unpar = xoft_decode($_COOKIE['cookpass'], $config["keypass"]);
    
    // search for username provided in cookie
	$cookie_id = $users->getidfromnick($unlog);

    // get user's data
	$cookie_check = $db->get_data('vavok_users', "id='" . $cookie_id . "'", 'name, pass, perm');

    // if user exists
    if (!empty($cookie_check['name'])) {

        // check is password correct
        if ($users->password_check($unpar, $cookie_check['pass']) && $unlog == $cookie_check['name']) {

            $pr_ip = explode(".", $ip);
            $my_ip = $pr_ip[0] . $pr_ip[1] . $pr_ip[2];


            // write current session data
            $_SESSION['log'] = $unlog;
            $_SESSION['permissions'] = $cookie_check['perm'];
            $_SESSION['my_ip'] = $my_ip;
            $_SESSION['my_brow'] = $users->user_browser();
            
            // update ip address and last visit time
            $db->update('vavok_users', 'ipadd', $ip, "id = '{$cookie_id}'");
            $db->update('vavok_profil', 'lastvst', time(), "uid = '{$cookie_id}'");
        }
    } 
}

?>