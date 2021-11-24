<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class Inbox extends Controller {
    public function index()
    {
        // Load page model
        $model = $this->model('InboxModel');

        // Pass page to the view
        $this->view('inbox/index', $model->index());
    }

    public function dialog()
    {
        // Load page model
        $model = $this->model('InboxModel');

        // Pass page to the view
        $this->view('inbox/dialog', $model->dialog());
    }

    public function sendto()
    {
        // Load page model
        $model = $this->model('InboxModel');

        // Pass page to the view
        $this->view('inbox/sendto', $model->sendto());
    }
}