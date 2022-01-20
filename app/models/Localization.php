<?php
/**
 * Author:    Aleksandar Vranešević
 * URL:       https://vavok.net
 */

class Localization {
	protected $language_data;
	protected $strings;
	protected $all;

    /**
     * Load localization files
     */  
	function load($language = '', $view = '')
	{
		// Use language from session
		$language = empty($language) && isset($_SESSION['lang']) ? $_SESSION['lang'] : $language;

        // Check if language exist and set english if requested language doesn't exist
		if (!file_exists(APPDIR . 'include/lang/' . $language . '/index.php')) {
            $_SESSION['lang'] = 'english'; // Change language in session
            $language = 'english'; // Default language
        }

		include APPDIR . 'include/lang/' . $language . '/index.php';

		// Additional language files
		$langdir = explode('/', REQUEST_URI);

		// Language based on filename like controller
		if (file_exists(APPDIR . "include/lang/" . $language . "/" . $langdir[1] . ".php")) include APPDIR . "include/lang/" . $language . "/" . $langdir[1] . ".php";
		// Language file based on first two params from URL
		if (isset($langdir[1]) && isset($langdir[2]) && !empty($langdir[1]) && !empty($langdir[2]) && file_exists(APPDIR . "include/lang/" . $language . "/" . $langdir[1] . "/" . $langdir[2] . ".php"))  include APPDIR . "include/lang/" . $language . "/" . $langdir[1] . "/" . $langdir[2] . ".php";		
		// Language file based on view
		if (!empty($view) && file_exists(APPDIR . "include/lang/" . $language . "/" . $view . ".php")) include APPDIR . "include/lang/" . $language . "/" . $view . ".php";		

		// Language data
		$this->language_data = $language_data;
		$this->strings = $lang_home;
		$this->all = array_merge($this->language_data, $this->strings);
	}

	/**
     * Return single string
     * 
     * @param str $string
     * @return string
     */
	public function string($string) {
		return $this->all[$string];
	}

	/**
     * Return strings only, no nested arrays
     * 
     * @return array
     */
	public function getStrings() {
		return $this->strings;
	}

	/**
     * Return all data including nested arrays
     * 
     * @return array
     */
	public function show_all() {
		return $this->all;
	}
}

?>