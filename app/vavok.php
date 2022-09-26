<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

define('START_TIME', microtime(true));
define('VERSION', '2.3');

// Error reporting
error_reporting(E_ALL);

// Default time zone
date_default_timezone_set('UTC');

// Base directory
if (!defined('BASEDIR')) define('BASEDIR', rtrim(__DIR__, 'app'));

// Application dir
if (!defined('APPDIR')) define('APPDIR', __DIR__ . '/');

// Public directory
if (!defined('PUBLICDIR')) define('PUBLICDIR', rtrim($_SERVER['SCRIPT_FILENAME'], 'index.php'));

// Define configuration constants from .env file
if (file_exists(APPDIR . '.env')) {
    $enviroment = file(APPDIR . '.env');

    for ($i=0; $i < count($enviroment); $i++) {
        if (!empty($enviroment[$i])) $env_data = explode('=', trim($enviroment[$i]));

        // Get value
        if (isset($env_data[1]) && $env_data[1] == 'null') $env_data[1] = '';

        // Get and define constant name
        if (!empty($env_data[0])) define($env_data[0], $env_data[1]);
    }
}

/*
require_once 'classes/Core.php';
require_once 'classes/Vavok.php';
require_once 'classes/Database.php';
require_once 'classes/Controller.php';
require_once 'classes/BrowserDetection.php';
require_once 'classes/Navigation.php';
require_once 'classes/Mailer.php';
require_once 'classes/Config.php';
require_once 'classes/Counter.php';
require_once 'classes/BaseModel.php';
*/
require 'vendor/autoload.php';

$vavok = new App\Classes\Vavok();