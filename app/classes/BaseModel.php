<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

namespace App\Classes;

class BaseModel extends Controller {
    protected object $db;
    protected object $user;
    protected object $localization;
	protected array  $user_data = [
		'authenticated' => false,
		'admin_status' => 'user',
		'language' => 'english'
        ];

    public function __construct()
    {
        // Instantiate database class
        $this->db = new Database;

        // Instantiate user class
        $this->user = $this->model('User');

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
        $this->localization = $this->model('Localization');
        $this->localization->load();
    }
}