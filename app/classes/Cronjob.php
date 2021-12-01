<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class Cronjob extends Controller {
    /**
     * Send email from the queue
     */
    public function queue_send_email()
    {
        $model = $this->model('EmailQueue');
        $model->send();
    }
}