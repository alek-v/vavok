<?php

namespace App\Classes;

class BrowserDetection {
    private string $_user_agent;
    private string $_name = '';
    private string $_version = '';
    private string $_platform = '';

    private $_basic_browser = array (
        'Trident\/7.0' => 'Internet Explorer 11',
        'Beamrise' => 'Beamrise',
        'Opera' => 'Opera',
        'OPR' => 'Opera',
        'Shiira' => 'Shiira',
        'Chimera' => 'Chimera',
        'Phoenix' => 'Phoenix',
        'Firebird' => 'Firebird',
        'Camino' => 'Camino',
        'Netscape' => 'Netscape',
        'OmniWeb' => 'OmniWeb',
        'Konqueror' => 'Konqueror',
        'icab' => 'iCab',
        'Lynx' => 'Lynx',
        'Links' => 'Links',
        'hotjava' => 'HotJava',
        'amaya' => 'Amaya',
        'IBrowse' => 'IBrowse',
        'iTunes' => 'iTunes',
        'Silk' => 'Silk',
        'Dillo' => 'Dillo', 
        'Maxthon' => 'Maxthon',
        'Arora' => 'Arora',
        'Galeon' => 'Galeon',
        'Iceape' => 'Iceape',
        'Iceweasel' => 'Iceweasel',
        'Midori' => 'Midori',
        'QupZilla' => 'QupZilla',
        'Namoroka' => 'Namoroka',
        'NetSurf' => 'NetSurf',
        'BOLT' => 'BOLT',
        'EudoraWeb' => 'EudoraWeb',
        'shadowfox' => 'ShadowFox',
        'Swiftfox' => 'Swiftfox',
        'Uzbl' => 'Uzbl',
        'UCBrowser' => 'UCBrowser',
        'Kindle' => 'Kindle',
        'wOSBrowser' => 'wOSBrowser',
        'Epiphany' => 'Epiphany', 
        'SeaMonkey' => 'SeaMonkey',
        'Avant Browser' => 'Avant Browser',
        'Firefox' => 'Firefox',
        'Chrome' => 'Chrome',
        'MSIE' => 'Internet Explorer',
        'Internet Explorer' => 'Internet Explorer',
        'Safari' => 'Safari',
        'Mozilla' => 'Mozilla'  
    );

    private $_basic_platform = array(
        'windows' => 'Windows', 
        'iPad' => 'iPad', 
        'iPod' => 'iPod', 
        'iPhone' => 'iPhone', 
        'mac' => 'Apple', 
        'android' => 'Android', 
        'linux' => 'Linux',
        'BlackBerry' => 'BlackBerry',
        'FreeBSD' => 'FreeBSD',
        'OpenBSD' => 'OpenBSD',
        'NetBSD' => 'NetBSD',
        'UNIX' => 'UNIX',
        'DragonFly' => 'DragonFlyBSD',
        'OpenSolaris' => 'OpenSolaris',
        'SunOS' => 'SunOS', 
        'OS\/2' => 'OS/2',
        'BeOS' => 'BeOS',
        'win' => 'Windows',
        'Dillo' => 'Linux',
        'PalmOS' => 'PalmOS',
        'RebelMouse' => 'RebelMouse'   
    ); 

    public function __construct($ua = '')
    {
        // Get user agent
        if(empty($ua)) {
            $this->_user_agent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : getenv('HTTP_USER_AGENT'));
        } else {
            $this->_user_agent = $ua;
        }
    }

    /**
     * Detect user's browser and operating system
     * 
     * @return object
     */
    public function detect(): object
    {
        $this->detectBrowser();
        $this->detectPlatform();

        return $this;
    }

    /**
     * Detect user's browser and browser's version
     * 
     * @return void
     */
    private function detectBrowser(): void
    {
        foreach($this->_basic_browser as $pattern => $name) {
            if (preg_match("/".$pattern."/i", $this->_user_agent, $match)) {
                // Browser name
                $this->_name = $name;

                // Get version number
                $known = array('Version', $pattern, 'other');
                $pattern_version = '#(?<browser>' . join('|', $known).')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
                if (!preg_match_all($pattern_version, $this->_user_agent, $matches)) {
                    // we have no matching number just continue
                }
                // see how many we have
                $i = count($matches['browser']);
                if ($i != 1) {
                    //we will have two since we are not using 'other' argument yet
                    //see if version is before or after the name
                    if (strripos($this->_user_agent, "Version") < strripos($this->_user_agent, $pattern)) {
                        $this->_version = isset($matches['version'][0]) ? $matches['version'][0] : '';
                    }
                    else {
                        $this->_version = isset($matches['version'][1]) ? $matches['version'][1] : '';
                    }
                }
                else {
                    $this->_version = isset($matches['version'][0]) ? $matches['version'][0] : '';
                }

                break;
            }
        }
    }

    /**
     * Detect platform
     * 
     * @return void
     */
    private function detectPlatform(): void
    {
        foreach($this->_basic_platform as $key => $platform) {
            if (stripos($this->_user_agent, $key) !== false) {
                $this->_platform = $platform;
                break;
            }
        }
    }

    /**
     * Return browser name
     * 
     * @return string
     */
    public function getBrowser(): string
    {
        return $this->_name;
    }

    /**
     * Return version number
     * 
     * @return string
     */
    public function getVersion(): string
    {
        return $this->_version;
    }

    /**
     * Return the platform
     * 
     * @return string
     */
    public function getPlatform(): string
    {
        return $this->_platform;
    }

    /**
     * Return user's agent
     * 
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->_user_agent;
    }

    /**
     * Return user's device
     * 
     * @return string phone|computer
     */
    public static function userDevice(): string
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) { $user_agents = $_SERVER['HTTP_USER_AGENT']; } else { $user_agents = ''; }

        if (stristr($user_agents, 'symbian') == true || stristr($user_agents, 'midp') == true || stristr($user_agents, 'android') == true || stristr($user_agents, 'mobi') == true) {
            return 'phone';
        }

        return 'computer';
    }
}