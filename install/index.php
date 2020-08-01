<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   01.08.2020. 19:11:08
*/

if (isset($_GET['step'])) {
	$step = $_GET['step'];
} elseif (isset($_POST['step'])) {
	$step = $_POST['step'];
} else {
	$step = '';
}

$my_title = 'Install';
include_once "include/header.php";

// first step
if (empty($step)) {
    ?>
    <p>By installing this software you agree to <a href="../LICENSE">license</a><br /><br /></p>

    <form method="post" action="index.php?step=first_end">
    <fieldset>
    <legend>Installation: first step</legend>
    <label for="dbhost">Database host (mostly localhost):</label><br />
    <input name="dbhost" id="dbhost" maxlength="30" /><br />
    <label for="database">Database name:</label><br />
    <input name="database" id="database" maxlength="30" /><br />
    <label for="dbusername">Database username:</label><br />
    <input name="dbusername" id="dbusername" maxlength="30" /><br />
    <label for="dbpass">Database password:</label><br />
    <input name="dbpass" id="dbpass" maxlength="30" /><br />
    <label for="select">Language:</label>
    <select name="language" id="language">
    <option name="english" value="english">English</option>
    </select>
    <input value="Continue..." type="submit" />
    </fieldset>
    </form>
    <hr />

    <?php
}

if ($step == 'first_end') {
    /**
     * Root dir for including system files
     */
    if (!defined('BASEDIR')) {
        $folder_level = "";
        while (!file_exists($folder_level . "robots.txt")) {
            $folder_level .= "../";
        } 
        define("BASEDIR", $folder_level);
    }

    include "../include/classes/Config.class.php";

    $values = array(
	    'DB_HOST' => $_POST['dbhost'],
	    'DB_USERNAME' => $_POST['dbusername'],
	    'DB_PASSWORD' => $_POST['dbpass'],
	    'DB_DATABASE' => $_POST['database']
    );

    $myconfig = new Config();
    $myconfig->update_config_file($values);

    // Create local files
    include "include/main_data.php";

    // prepare to import mysql data
    // MySQL host
    $mysql_host = $_POST['dbhost'];
    // MySQL username
    $mysql_username = $_POST['dbusername'];
    // MySQL password
    $mysql_password = $_POST['dbpass'];
    // database name
    $mysql_database = $_POST['database'];

    echo '<p><img src="../images/img/partners.gif" alt="" /> Second step<br></p>';

    echo '<p><img src="../images/img/reload.gif" alt="" /> Data successfully saved!</p>';
    echo '<p><a href="finish.php?step=second&amp;host=' . $mysql_host . '&amp;user=' . $mysql_username . '&amp;pass=' . $mysql_password . '&amp;db=' . $mysql_database . '">Second step</a> - Creating database</p>';

}

include_once"include/footer.php";
?>