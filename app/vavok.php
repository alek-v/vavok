<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\Vavok;

define('START_TIME', microtime(true));
const VERSION = '4.9';

// Base directory
if (!defined('BASEDIR')) define('BASEDIR', rtrim(__DIR__, 'app'));

// Application directory
if (!defined('APPDIR')) define('APPDIR', __DIR__ . '/');

// Storage directory
if (!defined('STORAGEDIR')) define('STORAGEDIR', BASEDIR . 'storage/');

// Public directory
if (!defined('PUBLICDIR')) define('PUBLICDIR', rtrim($_SERVER['SCRIPT_FILENAME'], 'index.php'));

// Define configuration constants from .env file
if (file_exists(BASEDIR . '.env')) {
    $enviroment = file(BASEDIR . '.env');

    for ($i=0; $i < count($enviroment); $i++) {
        if (!empty($enviroment[$i])) $env_data = explode('=', trim($enviroment[$i]));

        // Get value
        if (isset($env_data[1]) && $env_data[1] == 'null') $env_data[1] = '';

        // Don't create empty constants for this names
        if (($env_data[0] == 'STATIC_UPLOAD_URL' || $env_data[0] == 'STATIC_THEMES_URL') && empty($env_data[1])) continue;

        // Get and define constant name
        if (!empty($env_data[0])) define($env_data[0], $env_data[1]);
    }
}

// Error reporting
error_reporting(E_ALL);

// Don't show errors in the production
if (defined('SITE_STAGE') && SITE_STAGE == 'prod') {
    // Don't display errors
    ini_set('display_errors', 0);

    // Write errors to log
    ini_set('log_errors', 1);
}

// Where to save error log
ini_set('error_log', STORAGEDIR . 'error_log.dat');

// Default time zone
date_default_timezone_set('UTC');

require BASEDIR . 'vendor/autoload.php';

new Vavok;