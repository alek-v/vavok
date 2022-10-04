<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\Controller;

class Install extends Controller {
    /**
     * Index page
     */
    public function index()
    {
        $model = $this->model('InstallModel');

        // Pass page to the view
        $this->view('pages/installer', $model->index());
    }

    /**
     * Administrator registration
     */
    public function register()
    {
        $model = $this->model('InstallModel');

        // Pass page to the view
        $this->view('pages/installer_register_admin', $model->register());
    }

    /**
     * Complete administrator registration
     */
    public function register_admin()
    {
        $model = $this->model('InstallModel');

        // Pass page to the view
        $this->view('pages/installer', $model->register_admin());
    }
}