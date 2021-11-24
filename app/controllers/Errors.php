<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class Errors extends Controller {
    /**
     * Error 403
     * 
     * @param array $params
     */
    public function error_403($params = [])
    {
        $model = $this->model('ErrorModel');

        // Pass page to the view
        $this->view('error/error403', $model->error_403($params));
    }

    /**
     * Error 404
     * 
     * @param array $params
     */
    public function error_404($params = [])
    {
        $model = $this->model('ErrorModel');

        // Pass page to the view
        $this->view('error/error404', $model->error_404($params));
    }

    /**
     * Error 500
     * 
     * @param array $params
     */
    public function error_500($params = [])
    {
        $model = $this->model('ErrorModel');

        // Pass page to the view
        $this->view('error/error500', $model->error_500($params));
    }
}