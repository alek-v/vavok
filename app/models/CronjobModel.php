<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Database;

class CronjobModel extends BaseModel {
    /**
     * Clean expired tokens
     */
    public function clean_tokens()
    {
        $now = new DateTime();
        $new_time = $now->format('Y-m-d H:i:s');

        $this->container['db']->delete('tokens', "expiration_time < '{$new_time}'");
    }

    /**
     * Delete registrations that are not confirmed
     */
    public function clean_unconfirmed_reg()
    {
        $this->container['core']->cleanRegistrations($this->container['user']);
    }
}