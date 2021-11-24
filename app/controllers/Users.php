<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class Users extends Controller {
    /**
     * Default page
     */
    public function index()
    {
        die('There is nothing to show here');
    }

    /**
     * Login
     */
    public function login()
    {
        // Load page model
        $model = $this->model('Page');

        // Pass page to the view
        $this->view('users/login', $model->login());
    }

    /**
     * Logout
     */
    public function logout()
    {
        // Load page model
        $model = $this->model('Page');
        $model->logout();
    }

    /**
     * Register
     */
    public function register()
    {
        // Load page model
        $model = $this->model('UsersModel');
        $model->register();
    }

    /**
     * Confirm registration key
     */
    public function confirmkey()
    {
        // Load model
        $model = $this->model('UsersModel');

        // Pass page to the view
        $this->view('users/register/confirmkey', $model->confirmkey());
    }

    /**
     * Confirm registration key
     */
    public function key()
    {
        // Load model
        $model = $this->model('UsersModel');

        // Pass page data to the view
        $this->view('users/register/key', $model->key());
    }

    /**
     * Resend registration key
     */
    public function resendkey()
    {
        // Load model
        $model = $this->model('UsersModel');

        // Pass page data to the view
        $this->view('users/register/resendkey', $model->resendkey());
    }

    /**
     * Lost password
     */
    public function lostpassword()
    {
        // Load model
        $model = $this->model('UsersModel');

        // Pass page data to the view
        $this->view('users/lost_password', $model->lostpassword());
    }

    /**
     * Change language
     */
    public function changelang()
    {
        // Load model
        $model = $this->model('UsersModel');

        // Pass page data to the view
        $model->changelang();
    }

    /**
     * Ignore list
     */
    public function ignore()
    {
        // Load model
        $model = $this->model('UsersModel');

        // Pass page to the view
        $this->view('users/ignore', $model->ignore());
    }

    /**
     * Contact list
     */
    public function contacts()
    {
        // Load page model
        $model = $this->model('UsersModel');

        // Pass page to the view
        $this->view('users/contacts', $model->contacts());
    }

    public function mymenu()
    {
        // Load page model
        $model = $this->model('UsersModel');

        // Pass page to the view
        $this->view('users/mymenu', $model->mymenu());
    }

    /**
     * Settings
     */
    public function settings($params = [])
    {
        // Load page model
        $model = $this->model('UsersModel');

        // Pass page to the view
        $this->view('users/settings', $model->settings($params));
    }

    /**
     * Ban
     */
    public function ban()
    {
        // Load page model
        $model = $this->model('UsersModel');

        // Pass page to the view
        $this->view('users/settings', $model->ban());
    }

    /**
     * Users profile
     */
    public function u($params = [])
    {
        // Load page model
        $model = $this->model('UsersModel');

        // Pass page to the view
        $this->view('users/usersprofile', $model->users_profile($params));
    }
}