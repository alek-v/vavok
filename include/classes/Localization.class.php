<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   02.09.2020. 21:16:09
 */

class Localization {
	private $language_data;
	private $strings;
	private $all;
	private $vavok;

	function __construct() {
		global $vavok;

		$this->vavok = $vavok;

		/**
		 * Load localization files
		 */
		if (!file_exists(BASEDIR . "include/lang/" . $this->vavok->go('users')->get_user_language() . "/index.php")) { $_SESSION['lang'] = 'english'; }

		include_once BASEDIR . "include/lang/" . $this->vavok->go('users')->get_user_language() . "/index.php";

		// Language data
		$this->language_data = $language_data;
		$this->strings = $lang_home;
		$this->all = array_merge($this->language_data, $this->strings);

        $vavok->add_global(array('localization' => $this));
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