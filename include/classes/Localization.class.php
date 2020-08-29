<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   03.08.2020. 9:20:52
*/

class Localization {

	private $language_data;
	private $strings;
	private $all;
	private $users;

	function __construct() {
		global $users;

		$this->users = $users;

		/**
		 * Load localization files
		 */
		if (!file_exists(BASEDIR . "include/lang/" . $this->users->get_user_language() . "/index.php")) { $_SESSION['lang'] = 'english'; }

		include_once BASEDIR . "include/lang/" . $this->users->get_user_language() . "/index.php";

		// Language data
		$this->language_data = $language_data;
		$this->strings = $lang_home;
		$this->all = array_merge($this->language_data, $this->strings);

	}

	// Return single string
	public function string($string) {
		return $this->all[$string];
	}

	// Return strings only
	public function show_strings() {
		return $this->strings;
	}

	// Return all data
	public function show_all() {
		return $this->all;
	}

}

?>