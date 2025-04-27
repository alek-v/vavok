<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

namespace App\Classes;
use Pimple\Container;

abstract class Controller {
    protected Container $container;

    public function __construct()
    {
        // Instantiate dependency injection container
        $this->container = new Container();
        $this->container['db'] = fn() => Database::instance();
        $this->container['config'] = fn($c) => new Config($c);
        $this->container['user'] = fn($c) => new User($c);
        $this->container['parse_page'] = $this->container->factory(fn($c) => new ParsePage($c));
        $this->container['localization'] = new Localization();
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
        // Check if localization has been changed
        if ($this->container['user']->getUserLanguage() != $this->container['localization']->currentLocalization()) {
            // Load new localization data if localization has been changed
            $this->container['localization'] = new Localization();
        }

        // Instantiate page parsing class
        $page = $this->container['parse_page'];
        // Load the file from the view
        $page->loadPage($view, $data);

        // Header
        $header = $this->container['parse_page'];
        $header->load('includes/header');
        // Set header for current page
        $page->set('header', $page->merge(array($header)));

        // Footer
        $footer = $this->container['parse_page'];
        $footer->load('includes/footer');
        // Set footer for the page
        $page->set('footer', $page->merge(array($footer)));

        // Authentications
        $auth = $this->container['parse_page'];

        // Load the file for an authenticated user, or default to loading for an unauthenticated user
        if ($this->container['user']->user_data['authenticated']) {
            $auth->load('includes/authenticated');
        } else {
            $auth->load('includes/not_authenticated');
        }

        // Administrators
        if ($this->container['user']->user_data['admin_status'] == 'administrator' || $this->container['user']->user_data['admin_status'] == 'moderator') {
            // Add link to the administration panel
            $admins = $this->container['parse_page'];
            $admins->load('includes/admin_link');

            // Set authentication data for the page
            $page->set('authentication', $page->merge(array($auth, $admins)));
        } else {
            // Set authentication data for the page
            $page->set('authentication', $page->merge(array($auth)));
        }

        // Pass localization data to method and show the page
        echo $page->show($this->container['localization']->getStrings());
    }

    /**
     * Get page name from the params
     * 
     * @param array $params
     * @return string
     */
    protected function handlePage($params = [])
    {
        $params[0] ??= 'index';
        return $params[0];
    }
}