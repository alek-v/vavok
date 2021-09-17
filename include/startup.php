<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

define('START_TIME', microtime(true));
define('VERSION', '1.5.13');

/**
 * Root dir for including system files
 */
if (!defined('BASEDIR')) define('BASEDIR', __DIR__ . '/../');

/**
 * Autoload classes
 */
spl_autoload_register(function($class) {
    include BASEDIR . "include/classes/{$class}.class.php";
});

$vavok = new Vavok();
new Db();
new Users();

/**
 * Stop application here for system requests
 */
if (!strstr($_SERVER['PHP_SELF'], '/cronjob/')) {
    new Page();
    new Localization();
    new Counter();
    new Manageip();
    new Referer();
}

?>