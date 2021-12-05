<?php
/**
 * Author:    Aleksandar Vranešević
 * Site:      https://vavok.net
 */

class ParsePage extends Core {
    protected string $file;             // Template
    protected string $title;            // Page title
    protected string $content;          // Content
    protected string $lang;             // Language
    protected string $head_data;        // Meta tags
    protected string $notification;     // Notification at page
    protected array $values = array();  // Values to replace at page template

    /**
     * Load page
     * 
     * @param string $file
     * @param array $data
     * @return void
     */
    public function load_page($file, $data)
    {
        // Load template
        $this->load($file);

        // Check if we use SSL
        if ($this->get_configuration('transferProtocol') == 'HTTPS' && !$this->is_secure_connection()) {
            // Redirect to secure connection (HTTPS)
            $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $this->redirect_to($redirect);
        }

        // Metadata for all pages
        // Set missing OG tags when possible too
        $this->head_data = $this->page_head_metatags($data);
        $this->title = isset($data['tname']) ? $data['tname'] : '';
        $this->content = isset($data['content']) ? $data['content'] : '';
        $this->lang = isset($data['lang']) ? $data['lang'] : '';
        $this->notification = isset($data['show_notification']) ? $data['show_notification'] : '';
        $this->values = array_merge($this->values, $data);
    }

    /**
     * Load template
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
    public function parse_language($content, $localization)
    {
        $all = $localization;

        $search = array();
        foreach($all as $key => $val) {
            array_push($search, '{@localization[' . $key . ']}}');
        }

        return str_replace($search, $all, $content);
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
        if (!file_exists($this->file)) {
            return "Error loading template file ($this->file).<br />";
        }

        $output = file_get_contents($this->file);

        foreach ($this->values as $key => $value) {
            $tagToReplace = "{@$key}}";
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
            $content = (get_class($ParsePage) !== "ParsePage")
            ? "Error, incorrect type - expected Template."
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
        // Homedir
        $this->set('HOMEDIR', HOMEDIR);

        // Page content
        $this->set('content', $this->content);

        // HTML Language
        if (!empty($this->lang)) $this->set('page_language', ' lang="' . $this->lang . '"');

        // Cookie consent
        if ($this->get_configuration('cookieConsent') == 1) include APPDIR . 'include/plugins/cookie_consent/cookie_consent.php';

        // Data in <head> tag
        $this->set('head_metadata', $this->head_data);

        // Title
        $this->set('title', $this->title);

        // Notification
        if (!empty($this->notification)) $this->set('show_notification', $this->show_danger($this->notification));

        if ($this->get_configuration('showOnline') == 1) $this->set('show_online', $this->show_online());
        if ($this->get_configuration('showCounter') != 6) $this->set('show_counter', $this->show_counter());
        if ($this->get_configuration('pageGenTime') == 1) $this->set('show_generation_time', $this->show_gentime());

        // Show database queries while debugging
        if (defined('SITE_STAGE') && SITE_STAGE == 'debug') $this->set('show_debug', $this->db->show_db_queries());

        return preg_replace('/{@(.*?)}}/' , '', $this->parse_language($this->output(), $localization));
    }

    /**
     * Facebook comments
     *
     * @return string
     */
    function facebook_comments()
    {
    	$pages = new Page();
    	return '<div class="fb-comments" data-href="' . $pages->media_page_url() . '" data-width="470" data-num-posts="10"></div>';
    }
}