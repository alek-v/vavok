<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\Controller;

class Search extends Controller {
    /**
     * Index page
     */
    public function index($params = [])
    {
        $model = $this->model('SearchModel');

        // Pass page to the view
        $this->view('search', $model->index($params));
    }
}