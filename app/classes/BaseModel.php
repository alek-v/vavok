<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

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
        if ($this->user->is_reg()) $this->user_data['authenticated'] = true;
        // Admin status
        if ($this->user->is_administrator()) $this->user_data['admin_status'] = 'administrator';
        if ($this->user->is_moderator()) $this->user_data['admin_status'] = 'moderator';
        // Users laguage
        $this->user_data['language'] = $this->user->get_user_language();

        // Localization
        $this->localization = $this->model('Localization');
        $this->localization->load();
    }
}