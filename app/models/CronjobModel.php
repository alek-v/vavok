<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\Controller;
use App\Classes\Database;

class CronjobModel extends Controller {
    protected object $db;
    private object $user;

    public function __construct()
    {
        $this->db = Database::instance();
    }

    /**
     * Clean expired tokens
     */
    public function clean_tokens()
    {
        $now = new DateTime();
        $new_time = $now->format('Y-m-d H:i:s');

        $this->db->delete('tokens', "expiration_time < '{$new_time}'");
    }

    /**
     * Delete registrations that are not confirmed
     */
    public function clean_unconfirmed_reg()
    {
        $this->cleanRegistrations($this->model('User'));
    }
}