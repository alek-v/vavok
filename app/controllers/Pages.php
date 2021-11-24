<?php

class Pages extends Controller {
    /**
     * Homepage
     * 
     * @param array $params
     * @return void
     */
    public function index($params = [])
    {
        // Load page model
        $model = $this->model('Page');

        // Pass page to the view
        $this->view('index', $model->homepage($params));
    }

    /**
     * List of users
     */
    public function userlist()
    {
        // Load page model
        $model = $this->model('Page');

        // Pass page to the view
        $this->view('pages/userlist', $model->userlist());
    }

    /**
     * Statistics
     */
    public function statistics()
    {
        // Load page model
        $model = $this->model('Page');

        // Pass page to the view
        $this->view('pages/statistics', $model->statistics());
    }

    /**
     * Users online
     */
    public function online()
    {
        // Load page model
        $model = $this->model('Page');

        // Pass page to the view
        $this->view('pages/online', $model->online());
    }

    /**
     * Dynamic pages
     * 
     * @param array $params
     */
    public function dynamic($params = [])
    {
        // Load page model
        $model = $this->model('Page');

        // Pass page to the view
        $this->view('page', $model->dynamic($params));
    }
}