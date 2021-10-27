<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

define('START_TIME', microtime(true));
define('VERSION', '1.6.0.b1');

/**
 * Root dir for including system files
 */
if (!defined('BASEDIR')) define('BASEDIR', rtrim(__DIR__, 'include'));

/**
 * Autoload classes
 */
spl_autoload_register(function($class) {
    include BASEDIR . "include/classes/{$class}.class.php";
});

$vavok = new Vavok();
new Db();
new Users();
new Page();
new Localization();
new Manageip();
new Referer();
if (!strstr($_SERVER['PHP_SELF'], '/cronjob/')) new Counter(); // We don't need visitor counter for cron job

?>