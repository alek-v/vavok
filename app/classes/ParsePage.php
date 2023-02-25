<?php
/**
 * Author:    Aleksandar Vranešević
 * Site:      https://vavok.net
 */

namespace App\Classes;
use App\Traits\Core;
use App\Traits\Notifications;
use Pimple\Container;

class ParsePage {
    use Core, Notifications;

    protected string $file;             // Template
    protected string $title;            // Page title
    protected string $content;          // Content
    protected string $localization;     // Localization
    protected string $head_data;        // Meta tags
    protected string $notification;     // Notification on the page
    protected array  $values = array(); // Values to replace on the page template
    protected object $db;

    public function __construct(protected Container $container)
    {
        $this->db = $this->container['db'];
        $this->configuration = $this->container['config'];
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
        if ($this->configuration->getValue('transfer_protocol') == 'HTTPS' && $this->currentConnection() == 'http://') {
            // Redirect to secure connection (HTTPS)
            $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $this->redirection($redirect);
        }

        // Metadata of page
        // Set missing OG (open graph) tags when possible
        $this->head_data = $this->pageHeadMetatags($data);

        $this->title = isset($data['page_title']) ? $data['page_title'] : '';
        $this->page_name = isset($data['slug']) ? $data['slug'] : '';
        $this->content = isset($data['content']) ? $data['content'] : '';
        $this->localization = isset($data['localization']) ? $data['localization'] : '';

        // Page views
        $this->views = !empty($data['views']) ? $data['views'] : 0;

        // Update page views
        // Update page with selected localization
        $language = is_string($this->localization) && !empty($this->localization) ? " AND localization='" . $this->localization . "'" : '';
        if (!empty($this->page_name)) $this->db->update('pages', 'views', $this->views + 1, "slug = '" . $this->page_name . "'{$language}");

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
     * 
     * @return string $output
     */
    public function output(): string
    {
        /**
         * Try to verify if the file exists.
         * If it doesn't return with an error message.
         * Anything else loads the file contents and loops through the array replacing every key for its value.
         */
        if (!file_exists($this->file)) return "Error loading template file ($this->file).<br />";

        $output = file_get_contents($this->file);

        foreach ($this->values as $key => $value) {
            $keyToReplace = "{@$key}}";

            // Validate value that will replace the key
            $value = isset($value) && !is_array($value) ? $value : '';

            $output = str_replace($keyToReplace, $value, $output);
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
        if (!empty($this->localization)) $this->set('page_language', " lang=\"{$this->localization}\"");

        // Cookie consent
        if ($this->configuration->getValue('cookie_consent') == 1) require APPDIR . 'include/plugins/cookie_consent/cookie_consent.php';

        // Data in <head> tag
        $this->set('head_metadata', $this->head_data);

        // Title
        $this->set('title', $this->title);

        // Notification
        if (!empty($this->notification)) $this->set('show_notification', $this->showDanger($this->notification));

        if ($this->configuration->getValue('show_online') == 1) $this->set('show_online', $this->showOnline());
        if ($this->configuration->getValue('show_counter') != 6) $this->set('show_counter', $this->showCounter());
        if ($this->configuration->getValue('page_generation_time') == 1) $this->set('show_generation_time', $this->showPageGenTime());

        // Show database queries while debugging
        if (defined('SITE_STAGE') && SITE_STAGE == 'debug') $this->set('show_debug', $this->db->showDbQueries());

        // Remove empty keys, parse language keys and return page content
        return preg_replace('/{@(.*?)}}/' , '', $this->parseLanguage($this->output(), $localization));
    }
}