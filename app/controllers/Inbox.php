<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\Controller;

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

    public function send_message()
    {
        // Load page model
        $model = $this->model('InboxModel');

        // Send message
        $model->send_message();
    }

    public function receive_message()
    {
        // Load page model
        $model = $this->model('InboxModel');

        // Receive message
        $model->receive_message();
    }
}