<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   26.07.2020. 1:29:54
*/

class Localization {

	private $language_data;
	private $strings;
	private $all;

	function __construct($language_data, $strings) {

		// Language data
		$this->language_data = $language_data;
		$this->strings = $strings;
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