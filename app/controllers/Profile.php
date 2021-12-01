<?php

class Profile extends Controller {
    /**
     * Profile page
     */
    public function index()
    {
        $model = $this->model('ProfileModel');

        // Pass page to the view
        $this->view('profile/index', $model->index());
    }

    /**
     * Update profile
     */
    public function save()
    {
        $model = $this->model('ProfileModel');

        // Save data
        $model->save();
    }

    /**
     * Delete profile
     */
    public function delete()
    {
        $model = $this->model('ProfileModel');

        // Pass page to the view
        $this->view('profile/delete', $model->delete());
    }

    /**
     * New password
     */
    public function newpass()
    {
        $model = $this->model('ProfileModel');

        // Pass page to the view
        $this->view('profile/newpassword', $model->newpass());
    }

    /**
     * Profile photo
     */
    public function photo()
    {
        $model = $this->model('ProfileModel');

        // Pass page to the view
        $this->view('profile/photo', $model->photo());
    }

    /**
     * Save photography
     */
    public function savephoto()
    {
        $model = $this->model('ProfileModel');

        // Pass page to the view
        $this->view('notifications', $model->savephoto());
    }

    /**
     * Remove profile photo
     */
    public function removephoto()
    {
        $model = $this->model('ProfileModel');

        // Pass page to the view
        $this->view('notifications', $model->removephoto());
    }

    /**
     * Confirm email address
     */
    public function confirm_email()
    {
        $model = $this->model('ProfileModel');

        // Pass page to the view
        $this->view('notifications', $model->confirm_email());
    }
}