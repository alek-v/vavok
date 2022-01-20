<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class Controller extends Core {
    // Include and instantiate model class
    public function model($model)
    {
        // Require model file
        require_once '../app/models/' . $model . '.php';
        // Instantiate model
        return new $model();
    }

    // Load the view
    public function view($view, $data = [])
    {
        // Users data
        $user = $data['user'];
        // Unser array with users data
        unset($data['user']);

        // Localization
        $localization = $this->model('Localization');
        $localization->load($user['language'], $view);

        // Instantiate page parsing class
        $page = $this->model('ParsePage');
        // Load the file from the view
        $page->load_page($view, $data);

        // Header
        $header = $this->model('ParsePage');
        $header->load('includes/header');
        $page->set('header', $page->merge(array($header)));

        // Footer
        $footer = $this->model('ParsePage');
        $footer->load('includes/footer');
        $page->set('footer', $page->merge(array($footer)));

        // Authentications
        $auth = $this->model('ParsePage');
        // Authenticated user
        if ($user['authenticated']) $auth->load('includes/authenticated');
        // Not authenticated user
        if (!$user['authenticated']) $auth->load('includes/not_authenticated');

        // Administrators
        if ($user['admin_status'] == 'administrator' || $user['admin_status'] == 'moderator') {
            $admins = $this->model('ParsePage');
            $admins->load('includes/admin_link');

            $page->set('authentication', $page->merge(array($auth, $admins)));
        } else {
            $page->set('authentication', $page->merge(array($auth)));
        }

        // Show page
        echo $page->show($localization->getStrings());
    }
}