<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\Controller;

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

    /**
     * Save comment
     */
    public function save_comment()
    {
        // Load page model
        $model = $this->model('BlogModel');
        $model->save_comment(); 
    }
}