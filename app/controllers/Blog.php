<?php

class Blog extends Controller {
    /**
     * Blog
     * 
     * @param array $params
     * @return void
     */
    public function index($params = [])
    {
        // Load page model
        $model = $this->model('BlogModel');

        // Pass page to the view
        $this->view('blog/main', $model->index($params));
    }
}