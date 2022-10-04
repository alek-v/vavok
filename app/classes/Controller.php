<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

namespace App\Classes;
use App\Classes\ParsePage;
use App\Classes\Localization;
use Pimple\Container;

class Controller {
    protected object $container;

    public function __construct()
    {
        // Instantiate dependency injection container
        $container = new Container();

        // Globaly used methods
        $container['db'] = fn() => Database::instance();
        $container['core'] = fn($c) => new Core($c);
        $container['user'] = fn($c) => new User($c);
        $container['parse_page'] = $container->factory(fn($c) => new ParsePage($c));

        $this->container = $container;
    }

    /**
     * Include and instantiate model class
     * 
     * @param string $model
     * @return object
     */
    public function model(string $model): object
    {
        // Require model file
        require_once '../app/models/' . $model . '.php';

        // Instantiate model
        return new $model($this->container);
    }

    /**
     * Load the view and show the page content
     * 
     * @param string $view
     * @param array $data
     * @return void
     */
    public function view(string $view, array $data = []): void
    {
        // Users data
        $user = $data['user'];
        // Unser array with users data
        unset($data['user']);

        // Localization
        $localization = new Localization;
        // Load localization data depending of current $view (page)
        $localization->load($user['language'], $view);

        // Instantiate page parsing class
        $page = $this->container['parse_page'];
        // Load the file from the view
        $page->loadPage($view, $data);

        // Header
        $header =$this->container['parse_page'];
        $header->load('includes/header');
        // Set header for current page
        $page->set('header', $page->merge(array($header)));

        // Footer
        $footer =$this->container['parse_page'];
        $footer->load('includes/footer');
        // Set footer for current page
        $page->set('footer', $page->merge(array($footer)));

        // Authentications
        $auth =$this->container['parse_page'];

        // Load file for authenticated user
        if ($user['authenticated']) $auth->load('includes/authenticated');
        // Load file for user that is not authenticated
        if (!$user['authenticated']) $auth->load('includes/not_authenticated');

        // Administrators
        if ($user['admin_status'] == 'administrator' || $user['admin_status'] == 'moderator') {
            // Add link to admin panel
            $admins = $this->container['parse_page'];
            $admins->load('includes/admin_link');

            // Set authentication data for current page
            $page->set('authentication', $page->merge(array($auth, $admins)));
        } else {
            // Set authentication data for current page
            $page->set('authentication', $page->merge(array($auth)));
        }

        // Pass localization data to method and show the page
        echo $page->show($localization->getStrings());
    }
}