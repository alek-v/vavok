<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\Controller;

class Cronjob extends Controller {
    public function __construct()
    {
        // Don't update visitors counter if current request is a cronjob
        if (!defined('DYNAMIC_REQUEST')) define('DYNAMIC_REQUEST', true);
    }

    /**
     * Index
     */
    public function index()
    {
        die('Cronjob does not exist.');
    }

    /**
     * Send email from the queue
     */
    public function email_queue_send()
    {
        $model = $this->model('EmailQueue');
        $model->send();
    }

    /**
     * Clean email queue
     * Delete emails that has been sent
     */
    public function email_queue_clean()
    {
        $model = $this->model('EmailQueue');
        $model->clean();
    }

    /**
     * Delete expired tokens
     */
    public function clean_tokens()
    {
        $model = $this->model('CronjobModel');
        $model->clean_tokens();
    }

    /**
     * Delete registrations that are not confirmed
     */
    public function clean_unconfirmed_reg()
    {
        $model = $this->model('CronjobModel');
        $model->clean_unconfirmed_reg();
    }
}