<?php

class InstallModel extends Controller {
    protected object $db;
    protected object $user;
    protected object $localization;
	protected array  $user_data = [
		'authenticated' => false,
		'admin_status' => 'user',
		'language' => 'english'
	];
    protected bool $table_exists;

    public function __construct()
    {
        $this->db = new Database;

        // Localization
        $this->localization = $this->model('Localization');
        $this->localization->load();
    
        // Check if install is already completed
        $result      = $this->db->query("SHOW TABLES LIKE 'vavok_users'");
        $this->table_exists = $result !== false && $result->rowCount() > 0;
        if ($this->table_exists == true) {
            if ($this->db->count_row('vavok_users') > 0) die('Installation already completed');
        }
    }

    public function index()
    {
        // Users data
        $this_page['tname'] = 'Install';
        $this_page['content'] = '';
    
        if ($this->table_exists == false) {
            // import mysql data
            // Name of the file
            $filename = APPDIR . 'include/mysql/db.sql';
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
                    $this->db->query($templine);
                    // Reset temp variable to empty
                    $templine = '';
                }
            }
        }

        $this->user = $this->model('User');

        // Users data
        $this_page['user'] = $this->user_data;
        $this_page['content'] .= '<p><img src="../themes/images/img/reload.gif" alt="" /> Database successfully created!<br /></p>';
        $this_page['content'] .= '<p><a href="{@HOMEDIR}}install/register" class="btn btn-outline-primary sitelink">Next step</a></p>';

        return $this_page;
    }

    public function register()
    {
        $this->user = $this->model('User');

        // Users data
        $this_page['user'] = $this->user_data;
        $this_page['tname'] = 'Register admin';
        $this_page['content'] = '';

        return $this_page;
    }

    public function register_admin()
    {
        $this->user = $this->model('User');

        // Users data
        $this_page['user'] = $this->user_data;
        $this_page['tname'] = 'Register admin';
        $this_page['content'] = '';

        $this_page['content'] .= '<p><img src="../themes/images/img/partners.gif" alt="" /> Installation results</p>';

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
                    if ($this->user->validate_email($email)) {
                        if ($this->validateURL($osite)) {
                            $osite_name = ucfirst(str_replace("http://", "", $osite));
                            $osite_name = str_replace("https://", "", $osite_name);
    
                            // write data to config file
                            // init class
                            $myconfig = new Config;
    
                            $values = array(
                            'keypass' => $this->generate_password(),
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
    
                            // write to database
                            $this->user->register($name, $password, 0, '', 'default', $email); // register user
                            $user_id = $this->user->getidfromnick($name);
                            $this->db->update('vavok_users', 'perm', 101, "id='" . $user_id . "'");

                            $this_page['content'] .= '<p>Installation competed successfully<br></p>';

                            $this_page['content'] .= '<p><img src="../themes/images/img/reload.gif" alt="" /> <b><a href="../users/login/?log=' . $name . '&amp;pass=' . $password . '&amp;cookietrue=1">Login</a></b></p>';
                        } else {
                            $this_page['content'] .= '<p><b>Incorrect site address! (example http://sitename.domen)</b></p>';
                            $this_page['content'] .= '<p><a href="register">Back</a></p>';
                        } 
                    } else {
                        $this_page['content'] .= '<p><b>Incorrect email address! (example name@name.domain)</b></p>';
                        $this_page['content'] .= '<p><a href="register">Back</a></p>';
                    } 
                } else {
                    $this_page['content'] .= '<p><b>Passwords don\'t match! It is required to repeat the same password</b></p>';
                    $this_page['content'] .= '<p><a href="register">Back</a></p>';
                } 
     
            } else {
                $this_page['content'] .= '<p><b>Your username or your password are too short</b></p>';
                $this_page['content'] .= '<p><a href="register">Back</a></p>';
            } 
        } else {
            $this_page['content'] .= '<p><b>You didn\'t write all of the required information! Please complete all the empty fields</b></p>';
            $this_page['content'] .= '<p><a href="register">Back</a></p>';
        }

        return $this_page;
    }
}