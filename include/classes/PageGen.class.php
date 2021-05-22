<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Package:   Class for managing pages
 */

class PageGen {
    protected $file;
    protected $values = array();
    private $vavok;

    public function __construct($file)
    {
        global $vavok;

        $this->vavok = $vavok;

        /**
         * Get template
         */
        if (file_exists(BASEDIR . 'themes/' . MY_THEME . '/templates/' . $file)) {
        	$this->file = BASEDIR . 'themes/' . MY_THEME . '/templates/' . $file;
        } else {
        	$this->file = BASEDIR . "themes/templates/" . $file;
    	}
    } 

    /**
     * Sets a value for replacing a specific tag
     */
    public function set($key, $value)
    {
        $this->values[$key] = $value;
    } 

    /**
     * Replace language variables from .tpl files with corresponding strings
     *
     * @param string $content
     * @return string
     */
    public function parse_language($content)
    {
        $all = $this->vavok->go('localization')->show_strings();

        $search = array();
        foreach($all as $key => $val) {
            array_push($search, '{@website_language[' . $key . ']}}');
        }

        $content = str_replace($search, $all, $content);

        return $content;
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

        /**
         * Replace strings
         */
        $output = $this->parse_language($output);

        /**
         * Remove keys that are not set
         */
        $output = preg_replace('/{@(.*?)}}/' , '', $output);

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
         * If a type different from PageGen is found we provide an error message.
         */
        $output = "";

        foreach ($templates as $PageGen) {
            $content = (get_class($PageGen) !== "PageGen")
            ? "Error, incorrect type - expected Template."
            : $PageGen->output();
            $output .= $content . $separator;
        } 

        return $output;
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


?>