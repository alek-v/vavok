<?php

namespace App\Classes;

class Localization {
    private array $language_data;
    private array $strings;
    private array $all;

    public function __construct()
    {
        // Use language from session
        $language = isset($_SESSION['lang']) ? $_SESSION['lang'] : '';

        // Check if language exist and set english if requested language doesn't exist
        if (!file_exists(APPDIR . 'include/lang/' . $language . '/index.php')) {
            $_SESSION['lang'] = 'english'; // Change language in session
            $language = 'english'; // Default language
        }

        require APPDIR . 'include/lang/' . $language . '/index.php';

        // Additional localization files
        $langdir = explode('/', REQUEST_URI);

        // Localization based on controller filename
        if (file_exists(APPDIR . "include/lang/" . $language . "/" . $langdir[1] . ".php")) require APPDIR . "include/lang/" . $language . "/" . $langdir[1] . ".php";

        // Localization file based on first two params from URL
        if (isset($langdir[1]) && isset($langdir[2]) && !empty($langdir[1]) && !empty($langdir[2]) && file_exists(APPDIR . "include/lang/" . $language . "/" . $langdir[1] . "/" . $langdir[2] . ".php"))  require APPDIR . "include/lang/" . $language . "/" . $langdir[1] . "/" . $langdir[2] . ".php";

        // Localization data
        $this->language_data = $language_data;
        $this->strings = $lang_home;
        $this->all = array_merge($this->language_data, $this->strings);
    }

    /**
     * Load localization files
     *
     * @param string $additional
     * @return void
     */
    public function loadAdditional(string $additional): void
    {
        // Current data with a localization
        $lang_home = $this->strings;

        // File with additional localization data
        if (!empty($additional) && file_exists(APPDIR . "include/lang/" . $this->language_data['language'] . "/" . $additional . ".php")) require APPDIR . "include/lang/" . $this->language_data['language'] . "/" . $additional . ".php";

        // Update properties with a new data
        $this->strings = $lang_home;
        $this->all = array_merge($this->language_data, $this->strings);
    }

    /**
     * Return single string
     * 
     * @param string $string
     * @return string
     */
    public function string(string $string): string
    {
        return $this->all[$string];
    }

    /**
     * Return array with astrings only, no nested arrays
     * 
     * @return array
     */
    public function getStrings(): array
    {
        return $this->strings;
    }

    /**
     * Return all data including nested arrays
     * 
     * @return array
     */
    public function showAll(): array
    {
        return $this->all;
    }

    /**
     * Current used localization
     *
     * @return string
     */
    public function currentLocalization(): string
    {
        return $this->language_data['language'];
    }
}