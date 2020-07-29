<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   29.07.2020. 18:09:58
*/

if (isset($_GET['step'])) {
    $step = $_GET['step'];
} else {
    $step = $_POST['step'];
}

$my_title = 'Install';

if ($step == 'second') {

    require_once "include/header.php";

    $con = mysqli_connect($_GET['host'], $_GET['user'], $_GET['pass'], $_GET['db']);

	if (mysqli_num_rows(mysqli_query($con, "SHOW TABLES LIKE 'vavok_users'"))) {


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
            mysqli_query($con, $templine);
            // Reset temp variable to empty
            $templine = '';
        }
    }

    echo '<p><img src="../images/img/partners.gif" alt="" /> Second step - Creating database<br></p>';

    echo '<p><img src="../images/img/reload.gif" alt="" /> Database successfully created!<br /></p>';
    echo '<p><a href="finish.php?step=third" class="btn btn-outline-primary sitelink">Third step</a></p>';
} 

if ($step == 'third') {

    require_once "../include/startup.php";
    require_once "include/header.php";

    echo '<p><img src="../images/img/partners.gif" alt="" /> This will make you register as an administrator of this website.<br>After successful registration, delete folder "install"<br></p>';
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
    <legend>Third step</legend>
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

    require_once "../include/startup.php";
    require_once "include/header.php";

    echo '<p><img src="../images/img/partners.gif" alt="" /> Installation results</p>';

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
                        		echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">Back</a></p>';

                        		exit;
                        	}
                    	}

                    	// write data to config file
                        // init class
                        $myconfig = new Config;

                        $values = array(
                        'keypass' => generate_password(),
                        'webtheme' => 'default',
                        'quarantine' => 0,
                        'showtime' => 0,
                        'pageGenTime' => 0,
                        'pgFbComm' => 0,
                        'showOnline' => 0,
                        'adminNick' => $name,
                        'adminEmail' => $email,
                        'timeZone' => 0, // time zone
                        'title' => $osite_name,
                        'homeUrl' => $osite,
                        'bookGuestAdd' => 0,
                        'transferProtocol' => 'auto',
                        'maxPostChat' => 2000,
                        'maxPostNews' => 10000,
                        'floodTime' => 10,
                        'photoList' => 5,
                        'photoFileSize' => 40000,
                        'maxPhotoPixels' => 640,
                        'siteDefaultLang' => 'english',
                        'mPanel' => 'adminpanel',
                        'subMailPacket' => 50,
                        'dosLimit' => 480,
                        'showCounter' => 6,
                        'maxBanTime' => 43200
                        );

                        $myconfig->update_config_data($values);

                        // insert data to database if it is not crossdomain
                        if ($crossDomainInstall == 0) {
                            // write to database
                            $users->register($name, $password, 0, '', 'default', $email); // register user
                            $user_id = $users->getidfromnick($name);
                            $db->update('vavok_users', 'perm', 101, "id='" . $user_id . "'");
                    	}

                        echo '<p>Installation competed successfully<br></p>';

                        echo '<p><img src="../images/img/reload.gif" alt="" /> <b><a href="../pages/input.php?log=' . $name . '&amp;pass=' . $password . '&amp;cookietrue=1">Login</a></b></p>';
                    } else {
                        echo '<p><b>Incorrect site address! (example http://sitename.domen)</b></p>';
                        echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">Back</a></p>';
                    } 
                } else {
                    echo '<p><b>Incorrect email address! (example name@name.domain)</b></p>';
                    echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">Back</a></p>';
                } 
            } else {
                echo '<p><b>Passwords don\'t match! It is required to repeat the same password</b></p>';
                echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">Back</a></p>';
            } 
 
        } else {
            echo '<p><b>Your username or your password are too short</b></p>';
            echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">Back</a></p>';
        } 
    } else {
        echo '<p><b>You didn\'t write all of the required information! Please complete all the empty fields</b></p>';
        echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">Back</a></p>';
    } 
}

require_once "include/footer.php";
?>
