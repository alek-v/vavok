<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\Controller;

class Contact extends Controller {
    /**
     * Index page
     */
    public function index()
    {
        $model = $this->model('ContactModel');

        // Pass page to the view
        $this->view('contact/index', $model->index());
    }

    /**
     * Send email
     */
    public function send()
    {
        $model = $this->model('ContactModel');

        // Pass page to the view
        $this->view('contact/send', $model->send());
    }
}