<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

namespace App\Classes;
use App\Classes\Localizaion;
use Pimple\Container;

abstract class BaseModel {
    protected object $user;
    protected object $localization;
    protected array  $user_data = [
        'authenticated' => false,
        'admin_status' => 'user',
        'language' => 'english'
        ];
    protected object $db;

    public function __construct(protected Container $container)
    {
        // User object
        $this->user = $container['user'];
        // Database connection
        $this->db = $this->container['db'];

        // Check if user is authenticated
        // This time we check data from database, because of this we pass parameter true
        // Next time when you use method userAuthenticated don't use parameter true and data will be used from session, no new database request
        if ($this->user->userAuthenticated(true)) $this->user_data['authenticated'] = true;

        // Admin status
        if ($this->user->administrator()) $this->user_data['admin_status'] = 'administrator';
        if ($this->user->moderator()) $this->user_data['admin_status'] = 'moderator';

        // Users laguage
        $this->user_data['language'] = $this->user->getUserLanguage();

        // Localization
        $this->localization = new Localization;
        $this->localization->load();
    }
}