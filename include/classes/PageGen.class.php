<?php
// (c) vavok.net
// generate page from template


class PageGen {
    // the filename of the template to load.
    protected $file;

    // an array of values for replacing each tag on the template (the key for each value is its corresponding tag).
    protected $values = array();

    // creates a new template object and sets its associated file.
    public function __construct($file) {

        // get template
        if (file_exists(BASEDIR . 'themes/' . MY_THEME . '/templates/' . $file)) {
        	$this->file = BASEDIR . 'themes/' . MY_THEME . '/templates/' . $file;
        } else {
        	$this->file = BASEDIR . "themes/templates/" . $file;
    	}
    } 

    // sets a value for replacing a specific tag.
    public function set($key, $value) {
        $this->values{$key} = $value;
    } 

    // parse language variables from .tpl files
    public function parse_language($content) {

        // load language data
        global $lang_home;

        $search = array();
        foreach($lang_home as $key => $val) {
        array_push($search, '{@website_language[' . $key . ']}}');
        }

        $content = str_replace($search, $lang_home, $content);


        // deprecated 04.05.2020. 6:42:42
        $searchx = array(); 
        foreach($lang_home as $key => $val) {
        array_push($searchx, '{@$lang_home[\'' . $key . '\']}}');
        }

        $content = str_replace($searchx, $lang_home, $content);

        return $content;

    }

    // outputs the content of the template, replacing the keys for its respective values.
    public function output() {

        /*
        * Tries to verify if the file exists.
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

        // parse language
        $output = $this->parse_language($output);

        // remove keys that are not set
        $output = preg_replace('/{@(.*)}}/' , '', $output);

        return $output;
    }

    /*
    * Merges the content from an array of templates and separates it with $separator.
    * 
    * @param array $templates an array of Template objects to merge
    * @param string $separator the string that is used between each Template object
    * @return string 
    */
    static public function merge($templates, $separator = "\n") {
        /*
        * Loops through the array concatenating the outputs from each template, separating with $separator.
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


    function facebook_comments() {
    	$pages = new Page();
    	return '<div class="fb-comments" data-href="' . $pages->media_page_url() . '" data-width="470" data-num-posts="10"></div>';
    }


} 


?>