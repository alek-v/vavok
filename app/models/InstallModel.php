<?php

use App\Classes\Database;
use App\Classes\BaseModel;
use App\Classes\Config;
use App\Classes\User;
use App\Traits\Validations;
use Pimple\Container;

class InstallModel extends BaseModel {
    use Validations;

    protected Container $container;
    protected Database $db;
    protected User $user;
    private bool $table_exists;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->db = $container['db'];

        // Check if install is already completed
        $result = $this->db->query("SHOW TABLES LIKE 'vavok_users'");
        $this->table_exists = $result !== false && $result->rowCount() > 0;

        // Database tables are installed and administrator is registered, make a redirection
        if ($this->table_exists && $this->db->countRow('vavok_users') > 0) {
            header('Location: ' . HOMEDIR);
            exit;
        }

        // Tables are imported into the database
        if ($this->table_exists) {
            $this->user = $container['user'];
        }
    }

    public function index()
    {
        // Users data
        $this_page['page_title'] = 'Install';
        $this_page['content'] = '';

        // Create log files
        $this->createLogFiles();

        // Import database tables into the database
        if ($this->table_exists == false) {
            // Name of the file
            $filename = APPDIR . 'database/sql/db.sql';
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

        $this_page['content'] .= '<p><img src="{@HOMEDIR}}themes/images/img/reload.gif" alt="" /> Database successfully created!<br /></p>';
        $this_page['content'] .= '<p><a href="{@HOMEDIR}}install/register" class="btn btn-outline-primary sitelink">Next step</a></p>';

        return $this_page;
    }

    public function register()
    {
        $this_page['page_title'] = 'Register admin';
        $this_page['site_address'] = $this->currentConnection() . $_SERVER['HTTP_HOST'];

        return $this_page;
    }

    public function register_admin()
    {
        $this_page['page_title'] = 'Register admin';
        $this_page['content'] = '';

        $this_page['content'] .= '<p><img src="../themes/images/img/partners.gif" alt="" /> Installation results</p>';

        $str1 = strlen($_POST['name']);
        $str2 = strlen($_POST['password']);
    
        $name = $_POST['name'];
        $password = $_POST['password'];
        $password2 = $_POST['password2'];    
        $email = $_POST['email'];
        $osite = $_POST['osite'];

        $validation_error = '';

        if (empty($name) || empty($password) || empty($email) || empty($osite)) {
            $this_page['content'] .= '<p><b>You didn\'t write all of the required information! Please complete all the empty fields</b></p>';

            $validation_error = 1;
        }

        if ($str1 < 2 || $str2 < 7) {
            $this_page['content'] .= '<p><b>Your username or your password are too short</b></p>';

            $validation_error = 1;
        }

        if ($password !== $password2) {
            $this_page['content'] .= '<p><b>Passwords don\'t match! It is required to repeat the same password</b></p>';

            $validation_error = 1;
        }

        if (!$this->validateEmail($email)) {
            $this_page['content'] .= '<p><b>Incorrect email address! (example name@name.domain)</b></p>';

            $validation_error = 1;
        }

        if (!$this->validateUrl($osite)) {
            $this_page['content'] .= '<p><b>Incorrect site address! (example http://sitename.domen)</b></p>';

            $validation_error = 1;
        }

        if ($validation_error == 1) {
            $this_page['content'] .= '<p><a href="register">Back</a></p>';

            return $this_page;
        }

        $osite_name = ucfirst(str_replace("http://", "", $osite));
        $osite_name = str_replace("https://", "", $osite_name);

        // Write configuration data
        $myconfig = new Config($this->container);

        $values = array(
        'key_password' => $this->generatePassword(),
        'site_theme' => 'default',
        'quarantine' => 0,
        'show_time' => 0,
        'page_generation_time' => 0,
        'page_facebook_comments' => 0,
        'show_online' => 0,
        'admin_username' => $name,
        'admin_email' => $email,
        'timezone' => 0,
        'title' => $osite_name,
        'home_address' => $osite,
        'transfer_protocol' => 'auto',
        'chat_max_posts' => 2000,
        'flood_time' => 10,
        'photo_file_size_limit' => 40000,
        'default_localization' => 'english',
        'admin_panel' => 'adminpanel',
        'mail_subscription_package' => 50,
        'show_counter' => 6,
        'max_ban_time' => 43200
        );

        $myconfig->updateConfigData($values);

        // Insert data into the database
        $this->user->register($name, $password, 0, '', 'default', $email); // register user
        $user_id = $this->user->getIdFromNick($name);
        $this->db->update('vavok_users', 'access_permission', 101, "id='{$user_id}'");

        $this_page['content'] .= '<p>Installation has been completed successfully</p>';
        $this_page['content'] .= '<p><img src="../themes/images/img/reload.gif" alt="" /> <strong><a href="../users/login">Login</a></strong></p>';

        return $this_page;
    }

    /**
     * Create log files
     */
    private function createLogFiles()
    {
        touch(STORAGEDIR . 'admin_chat.dat');
        touch(STORAGEDIR . 'bad_words.dat');
        touch(STORAGEDIR . 'header_meta_tags.dat');
        touch(STORAGEDIR . 'subscription_names.dat');
        touch(STORAGEDIR . 'dataconfig/gallery.dat');
        touch(STORAGEDIR . 'datalog/error.dat');
        touch(STORAGEDIR . 'datalog/error401.dat');
        touch(STORAGEDIR . 'datalog/error402.dat');
        touch(STORAGEDIR . 'datalog/error403.dat');
        touch(STORAGEDIR . 'datalog/error404.dat');
        touch(STORAGEDIR . 'datalog/error406.dat');
        touch(STORAGEDIR . 'datalog/error500.dat');
        touch(STORAGEDIR . 'datalog/error502.dat');
    }
}