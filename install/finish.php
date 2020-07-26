<?php 
// (c) vavok.net - Aleksandar Vranesevic

// /install/install.php have disabled connection to db in startup.php, so I manipulate with db in this page

require_once"../include/startup.php";
include_once BASEDIR . "lang/" . get_configuration('language') . "/index.php";
require_once "../lang/" . get_configuration('language') . "/installinstall.php";

$my_title = 'Install';
require_once"include/header.php";

 

if (isset($_GET['step'])) {
    $step = $_GET['step'];
} else {
    $step = $_POST['step'];
} 

$sitetime = time();
$datex = date("d.m.y");
$timex = date("H:i");

if ($step == 'second') {
	if ($db->table_exists('vavok_users') > 0) {
		echo '<p>Data in selected database already exists, please select another database for this website.<br />
		If you are configuring crossdomain website continue to third step of installation.</p>';
		echo '<p>
		<a href="finish.php?step=third">Continue to next step</a>
		</p>';

		require_once "include/footer.php";
		exit;
	}

    // import mysql data
    // Name of the file
    $filename = 'mysql/mysql_main.sql';
    // MySQL host
    $mysql_host = $_GET['host'];
    // MySQL username
    $mysql_username = $_GET['user'];
    // MySQL password
    $mysql_password = $_GET['pass'];
    // Database name
    $mysql_database = $_GET['db']; 
    // Temporary variable, used to store current query
    $templine = '';
    // Read in entire file
    $lines = file($filename);
    // Loop through each line
    foreach ($lines as $line) {
        // Skip it if it's a comment
        if (substr($line, 0, 2) == '--' || $line == '')
            continue; 
        // Add this line to the current segment
        $templine .= $line; 
        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1, 1) == ';') {
            // Perform the query
            $db->query($templine); 
            // Reset temp variable to empty
            $templine = '';
        } 
    } 

    echo '<p><img src="../images/img/partners.gif" alt="" /> ' . $lang_install['secondstep'] . ' - ' . $lang_install['inserttint'] . '<br></p>';

    echo '<p><img src="../images/img/reload.gif" alt="" /> ' . $lang_install['dataadded'] . '!<br /></p>';
    echo '<p><a href="finish.php?step=third" class="btn btn-outline-primary sitelink">' . $lang_install['thirdstep'] . '</a></p>';
} 

if ($step == 'third') {

    echo '<p><img src="../images/img/partners.gif" alt="" /> ' . $lang_install['installinfo'] . '<br></p>';
        // check if website and admin already exists (crossdomain website)
    if ($users->regmemcount() > 1) {
		echo '<p>It seems that you are configuring crossdomain website.<br />
		Please enter main website admin username and password</p>';
    }

    echo '<hr/>';
    // echo 'Please use only alphanumeric characters<br>';
    ?>

    <form method="post" action="finish.php?step=regadmin">
    <fieldset>
    <legend><?php echo $lang_install['thirdstep'];
    ?></legend>
    <label for="name">Username (max20):</label><br />
    <input name="name" id="name" maxlength="20" /><br />
    <label for="password">Password (max20):</label><br />
    <input name="password" id="password" type="password" maxlength="20" /><br />
    <label for="password2">Password again:</label><br />
    <input name="password2" id="password2" type="password" maxlength="20" /><br>
    <label for="email">Email:</label><br />
    <input name="email" id="email" maxlength="100" /><br />
    <label for="osite">Site address:</label><br />
    <input name="osite" id="osite" value="<?php echo transfer_protocol() . $_SERVER['HTTP_HOST']; ?>" maxlength="100" /><br />
    <input value="Continue..." type="submit" />
    </fieldset>
    </form>
    <hr />

<?php
} 
// instalation results
if ($step == "regadmin") {

    echo '<p><img src="../images/img/partners.gif" alt="" /> ' . $lang_install['installresults'] . '<br></p>';

    // check if website and admin already exists (crossdomain website)
    $crossDomainInstall = 0;
    if ($users->regmemcount() > 1) {
    	$crossDomainInstall = 1;
    }

    $str1 = strlen($_POST['name']);
    $str2 = strlen($_POST['password']);

    $name = $_POST['name'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];    
    $email = $_POST['email'];
    $osite = $_POST['osite'];


    if ($name != "" && $password != "" && $email != "" && $osite != "") {
        if ($str1 < 21 && $str1 > 2 && $str2 < 21 && $str2 > 2) {
            if ($password == $password2) {
                if ($users->validate_email($email)) {
                    if (validateURL($osite)) {
                        $osite_name = ucfirst(str_replace("http://", "", $osite));
                        $osite_name = str_replace("https://", "", $osite_name);

                    	// check is everything ok if this is crossdomain website
                    	if ($crossDomainInstall == 1) {
                        	$adminId = $users->getidfromnick($name);

                        	$adminPass = $db->get_data('vavok_users', "id='" . $adminId . "'", 'pass');

                        	if (!$users->is_administrator(101, $adminId) || !$users->password_check($password, $adminPass['pass'])) {
                        		echo '<p>You are configuring cross-domain website.<br />
                        		Please enter main website administrator username and password</p>';
                        		echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">' . $localization->string('back') . '</a></p>';

                        		exit;
                        	}
                    	}

                    	// write data to config file
                        // init class
                        $myconfig = new Config;

                        $values = array(
                        0 => '0',
                        1 => generate_password(),
                        2 => 'default',
                        3 => '0',
                        4 => '0',
                        5 => '0',
                        6 => '0',
                        7 => '0',
                        8 => $name,
                        9 => $email,
                        10 => '0', // time zone
                        11 => $osite_name,
                        12 => 'default',
                        14 => $osite,
                        20 => '0',
                        21 => 'auto', // force HTTPS
                        22 => 200,
                        24 => 100,
                        25 => 1000,
                        29 => 10,
                        32 => '0', // cookie consent
                        33 => 4,
                        34 => 100,
                        35 => '0',
                        36 => '0',
                        37 => 5,
                        38 => 40000,
                        39 => 320,
                        40 => 5,
                        41 => 600,
                        42 => 5,
                        44 => 5,
                        45 => 15,
                        46 => 5,
                        47 => 'english',
                        48 => 'adminpanel',
                        49 => '0',
                        50 => 1,
                        51 => 100,
                        52 => 1,
                        53 => '0',
                        54 => 5,
                        55 => 6,
                        56 => 50,
                        57 => 400,
                        58 => 200,
                        59 => '0',
                        60 => 5,
                        61 => 1,
                        62 => '0',
                        63 => '0',
                        64 => '0',
                        65 => '0',
                        66 => 15,
                        67 => '0',
                        68 => '0',
                        69 => 1,
                        70 => '0',
                        72 => '0',
                        74 => 6,
                        76 => 43200
                        );
                        $myconfig->update($values);


                        // insert data to database if it is not crossdomain
                        if ($crossDomainInstall == 0) {
                            // write to database
                            $users->register($name, $password, 0, '', 'default', $email); // register user
                            $user_id = $users->getidfromnick($name);
                            $db->update('vavok_users', 'perm', 101, "id='" . $user_id . "'");
                    	}

                        echo '<p>' . $lang_install['installok'] . '.<br></p>';

                        echo '<p><img src="../images/img/reload.gif" alt="" /> <b><a href="../pages/input.php?log=' . $name . '&amp;pass=' . $password . '&amp;cookietrue=1">' . $lang_install['logintosite'] . '</a></b></p>';
                    } else {
                        echo '<p><b>' . $lang_install['siteaddressbad'] . '</b></p>';
                        echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">' . $localization->string('back') . '</a></p>';
                    } 
                } else {
                    echo '<p><b>' . $lang_install['bademail'] . '</b></p>';
                    echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">' . $localization->string('back') . '</a></p>';
                } 
            } else {
                echo '<p><b>' . $lang_install['badagainbass'] . '.</b></p>';
                echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">' . $localization->string('back') . '</a></p>';
            } 
 
        } else {
            echo '<p><b>' . $lang_install['shortuserpass'] . '</b></p>';
            echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">' . $localization->string('back') . '</a></p>';
        } 
    } else {
        echo '<p><b>' . $lang_install['fillfields'] . '.</b></p>';
        echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">' . $localization->string('back') . '</a></p>';
    } 
}

require_once "include/footer.php";
?>
