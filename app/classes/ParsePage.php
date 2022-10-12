<?php
/**
 * Author:    Aleksandar Vranešević
 * Site:      https://vavok.net
 */

namespace App\Classes;
use Pimple\Container;

class ParsePage {
    protected string $file;             // Template
    protected string $title;            // Page title
    protected string $content;          // Content
    protected string $lang;             // Language
    protected string $head_data;        // Meta tags
    protected string $notification;     // Notification at page
    protected array  $values = array(); // Values to replace at page template
    protected object $db;

    public function __construct(protected Container $container)
    {
        // Database connection
        $this->db = $container['db'];
    }

    /**
     * Load page
     * Redirect to https
     * 
     * @param string $file
     * @param array $data
     * @return void
     */
    public function loadPage($file, $data)
    {
        // Load template
        $this->load($file);

        // Check if we use SSL
        if ($this->container['core']->configuration('transferProtocol') == 'HTTPS' && $this->container['core']->currentConnection() == 'http://') {
            // Redirect to secure connection (HTTPS)
            $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $this->container['core']->redirection($redirect);
        }

        // Metadata of page
        // Set missing OG (open graph) tags when possible
        $this->head_data = $this->container['core']->pageHeadMetatags($data);

        $this->title = isset($data['tname']) ? $data['tname'] : '';
        $this->page_name = isset($data['pname']) ? $data['pname'] : '';
        $this->content = isset($data['content']) ? $data['content'] : '';
        $this->lang = isset($data['lang']) ? $data['lang'] : '';

        // Page views
        $this->views = !empty($data['views']) ? $data['views'] : 0;

        // Update page views
        // Update page with selected localization
        $language = is_string($this->lang) && !empty($this->lang) ? " AND lang='" . $this->lang . "'" : '';
        if (!empty($this->page_name)) $this->db->update('pages', 'views', $this->views + 1, "pname = '" . $this->page_name . "'{$language}");

        $this->notification = isset($data['show_notification']) ? $data['show_notification'] : '';
        $this->values = array_merge($this->values, $data);
    }

    /**
     * Load template, part of the page
     */
    public function load($file)
    {
        // Template location
        $this->file = file_exists(APPDIR . 'views/' . MY_THEME . '/' . $file . '.php') ? APPDIR . 'views/' . MY_THEME . '/' . $file . '.php' : APPDIR . 'views/default/' . $file . '.php';
    }
 
    /**
     * Sets a value for replacing a specific tag
     */
    public function set($key, $value)
    {
        $this->values[$key] = $value;
    }

    /**
     * Replace language variables from the view with corresponding strings
     *
     * @param string $content
     * @return string
     */
    private function parseLanguage($content, $localization)
    {
        $search = array();
        foreach($localization as $key => $val) {
            array_push($search, '{@localization[' . $key . ']}}');
        }

        return str_replace($search, $localization, $content);
    }

    /**
     * Outputs the content of the template, replacing the keys for its respective values
     */
    public function output()
    {
        /**
         * Try to verify if the file exists.
         * If it doesn't return with an error message.
         * Anything else loads the file contents and loops through the array replacing every key for its value.
         */
        if (!file_exists($this->file)) return "Error loading template file ($this->file).<br />";

        $output = file_get_contents($this->file);

        foreach ($this->values as $key => $value) {
            $tagToReplace = "{@$key}}";
            $value = isset($value) ? $value : '';

            $output = str_replace($tagToReplace, $value, $output);
        }

        return $output;
    }

    /**
     * Merge content from an array of templates and separates it with $separator.
     * 
     * @param array $templates
     * @param string $separator
     * @return string 
     */
    static public function merge($templates, $separator = "\n")
    {
        /*
         * Loop through the array concatenating the outputs from each template, separating with $separator.
         * If a type different from ParsePage is found we provide an error message.
         */
        $output = '';

        foreach ($templates as $ParsePage) {
            $content = (get_class($ParsePage) !== "App\Classes\ParsePage")
            ? "Error, incorrect type " . get_class($ParsePage) . " - expected template object."
            : $ParsePage->output();
            $output .= $content . $separator;
        } 

        return $output;
    }

    /**
     * Remove empty keys and show page
     * 
     * @return string
     */
    public function show($localization)
    {
        // Values from constants
        $this->values['HOMEDIR'] = HOMEDIR;
        $this->values['STATIC_THEMES_URL'] = STATIC_THEMES_URL;
        $this->values['STATIC_UPLOAD_URL'] = STATIC_UPLOAD_URL;

        // Page content
        $this->set('content', $this->content);

        // HTML Language
        if (!empty($this->lang)) $this->set('page_language', ' lang="' . $this->lang . '"');

        // Cookie consent
        if ($this->container['core']->configuration('cookieConsent') == 1) require APPDIR . 'include/plugins/cookie_consent/cookie_consent.php';

        // Data in <head> tag
        $this->set('head_metadata', $this->head_data);

        // Title
        $this->set('title', $this->title);

        // Notification
        if (!empty($this->notification)) $this->set('show_notification', $this->container['core']->showDanger($this->notification));

        if ($this->container['core']->configuration('showOnline') == 1) $this->set('show_online', $this->container['core']->showOnline());
        if ($this->container['core']->configuration('showCounter') != 6) $this->set('show_counter', $this->container['core']->showCounter());
        if ($this->container['core']->configuration('pageGenTime') == 1) $this->set('show_generation_time', $this->container['core']->showPageGenTime());

        // Show database queries while debugging
        if (defined('SITE_STAGE') && SITE_STAGE == 'debug') $this->set('show_debug', $this->db->showDbQueries());

        // Remove empty keys, parse language keys and return page content
        return preg_replace('/{@(.*?)}}/' , '', $this->parseLanguage($this->output(), $localization));
    }
}