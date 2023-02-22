<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

namespace App\Classes;

use App\Traits\Core;

class Vavok {
    use Core;

    protected string $currentController = 'Pages';
    protected string $currentMethod = 'index';
    protected array  $params = [];

    public function __construct()
    {
        // With '.' session is accessible from all subdomains
        $rootDomain = '.' . $_SERVER['SERVER_NAME'];

        // Get cookie params and set root domain
        $currentCookieParams = session_get_cookie_params();
        session_set_cookie_params(
            $currentCookieParams["lifetime"],
            $currentCookieParams["path"],
            $rootDomain,
            $currentCookieParams["secure"],
            $currentCookieParams["httponly"]
        );

        // Start session
        session_start();

        define('REQUEST_URI', urldecode($_SERVER['REQUEST_URI']));
        define('CLEAN_REQUEST_URI', $this->cleanRequestUri(REQUEST_URI));

        // For links, images and other mod rewritten directories
        if (!defined('HOMEDIR')) {
            $path = $_SERVER['HTTP_HOST'] . CLEAN_REQUEST_URI;
            $patharray = explode('/', $path);
            $pathindex = './';

            for ($i = count($patharray); $i > 2; $i--) {
                $pathindex .= '../';
            }

            define('HOMEDIR', $pathindex);
        }

        // Cookie-free domain for uploaded files
        if (!defined('STATIC_UPLOAD_URL')) define('STATIC_UPLOAD_URL', $this->currentConnection() . $_SERVER['HTTP_HOST'] . '/fls');

        // Cookie-free domain for themes
        if (!defined('STATIC_THEMES_URL')) define('STATIC_THEMES_URL', $this->currentConnection() . $_SERVER['HTTP_HOST'] . '/themes');

        // Parameters from the URL
        $url = $this->paramsFromUrl();

        // Find a controller to use
        if (isset($url[0]) && file_exists(APPDIR . 'controllers/' . ucwords($url[0]). '.php')) {
            // If exists, set as controller
            $this->currentController = ucwords($url[0]);
            // Unset 0 Index
            unset($url[0]);
        }
        // Site pages
        elseif (isset($url[0]) && $url[0] == 'page' && isset($url[1])) {
            // Page name
            $url[2] = $url[1];
            // Method to call
            $url[1] = 'dynamic';
            // Unset 0 index (page)
            unset($url[0]);
        }
        // Error page 404
        elseif (isset($url[0]) && strlen($url[0]) != 2) {
            // Set as controller
            $this->currentController = 'Errors';
            // Set dynamic page
            $url[2] = 'error_404';
            // Set method
            $url[1] = 'error_404';
            // Unset 0 index (page)
            unset($url[0]);
        }

        // Require the controller
        require APPDIR . 'controllers/'. $this->currentController . '.php';

        $controller = new $this->currentController;

        // Check for second part of url
        if (isset($url[1])) {
            // Check if method exists in the controller
            if (method_exists($controller, $url[1])) {
                $this->currentMethod = $url[1];
                // Unset 1 index
                unset($url[1]);
            }
        }

        // Get params
        $this->params = $url ? array_values($url) : [];

        // Method of the controller to call
        $method_to_call = $this->currentMethod;

        // Call a method of the controller and show the page
        // This method will have a contact with model and the view
        $controller->$method_to_call($this->params);
    }
}