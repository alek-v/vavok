<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   29.07.2020. 17:55:13
*/

class Vavok {

	public function __construct() {

		/**
		 * Error reporting
		 */
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

		/**
		 * Default time zone
		 */
		date_default_timezone_set('UTC');

		/**
		 * Configuration
		 */

		define('REQUEST_URI', urldecode($_SERVER['REQUEST_URI']));
		define('SUB_SELF', substr($_SERVER['PHP_SELF'], 1));

		// Clean URL (REQUEST_URI)
		$clean_requri = explode('&fb_action_ids', REQUEST_URI)[0]; // facebook
		$clean_requri = explode('?fb_action_ids', $clean_requri)[0]; // facebook
		$clean_requri = explode('?isset', $clean_requri)[0];
		define('CLEAN_REQUEST_URI', $clean_requri);

		// For links, images and other mod rewriten directories
		if (!defined('HOMEDIR')) {
		    $path = $_SERVER['HTTP_HOST'] . CLEAN_REQUEST_URI;
		    $patharray = explode("/", $path);
		    $pathindex = "";

		    for ($i = count($patharray); $i > 2; $i--) {
		        $pathindex .= '../';
		    }

		    define("HOMEDIR", $pathindex);
		}

		// Define constants
		$enviroment = file(BASEDIR . '.env');

		for ($i=0; $i < count($enviroment); $i++) {
		    if (!empty($enviroment[$i])) $env_data = explode('=', trim($enviroment[$i]));

		    if (isset($env_data[1]) && $env_data[1] == 'null') $env_data[1] = '';

		    if (!empty($env_data[0])) define($env_data[0], $env_data[1]);
		}

	}

	// get website configuration
	public function get_configuration($data = '', $full_configuration = false) {
	    global $db;

	    $config = $db->get_data(DB_PREFIX . 'settings');

	    // Get complete configuration
	    if ($full_configuration == true) {
	        return $config;
	    }

	    if (!empty($data)) {
	        return $config[$data];
	    } else {
	        return false;
	    }
	}

	// Get message from url
	public function get_isset($msg = '') {

	    if (!empty($msg)) {
	        $isset = $msg;
	    } elseif (isset($_GET['isset'])) {
	        $isset = check($_GET['isset']);
	    }

	    include_once BASEDIR . "include/lang/" . $this->get_configuration("siteDefaultLang") . "/isset.php";

	    if (isset($isset) && !empty($issetLang[$isset])) {

	        $isset_msg = new PageGen('pages/isset.tpl');
	        $isset_msg->set('message', $issetLang[$isset]);
	        
	        return $isset_msg->output();
	        
	    }

	}

}

?>