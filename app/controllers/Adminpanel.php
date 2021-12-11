<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class Adminpanel extends Controller {
    /**
     * Index page
     */
    public function index()
    {
        $model = $this->model('AdminpanelModel');

        // Pass page to the view
        $this->view('adminpanel/index', $model->index());
    }

    /**
     * Settings
     */
    public function settings()
    {
        $model = $this->model('AdminpanelModel');

        // Pass page to the view
        $this->view('adminpanel/settings', $model->settings());
    }

    /**
     * Adminchat
     */
    public function adminchat()
    {
        $model = $this->model('AdminchatModel');

        // Pass page to the view
        $this->view('adminpanel/adminchat', $model->index());
    }

    /**
     * Admin list
     */
    public function adminlist()
    {
        $model = $this->model('AdminpanelModel');

        // Pass page to the view
        $this->view('adminpanel/adminlist', $model->adminlist());
    }

    /**
     * Registrations that are not confirmed
     */
    public function unconfirmed_reg()
    {
        $model = $this->model('AdminpanelModel');

        // Pass page to the view
        $this->view('adminpanel/unconfirmed_reg', $model->unconfirmed_reg());
    }

    /**
     * Add ban
     */
    public function addban()
    {
        $model = $this->model('BanModel');

        // Pass page to the view
        $this->view('adminpanel/addban', $model->addban());
    }

    /**
     * List of banned users
     */
    public function banlist()
    {
        $model = $this->model('BanModel');

        // Pass page to the view
        $this->view('adminpanel/banlist', $model->banlist());
    }
    
    /**
    * Statistics
    */
   public function statistics()
   {
       $model = $this->model('AdminpanelModel');

       // Pass page to the view
       $this->view('adminpanel/statistics', $model->statistics());
   }

    /**
    * Users and profile management
    */
    public function users()
    {
        $model = $this->model('AdminpanelModel');
 
        // Pass page to the view
        $this->view('adminpanel/users', $model->users());
    }

    /**
    * IP ban
    */
    public function ipban()
    {
        $model = $this->model('AdminpanelModel');
 
        // Pass page to the view
        $this->view('adminpanel/ipban', $model->ipban());
    }

    /**
    * System check
    */
    public function systemcheck()
    {
        $model = $this->model('AdminpanelModel');
 
        // Pass page to the view
        $this->view('adminpanel/systemcheck', $model->systemcheck());
    }

    /**
    * Page manager
    */
    public function pagemanager()
    {
        $model = $this->model('PagemanagerModel');
 
        // Pass page to the view
        $this->view('adminpanel/pagemanager', $model->index());
    }

    /**
    * Page search
    */
    public function pagesearch()
    {
        $model = $this->model('AdminpanelModel');
 
        // Pass page to the view
        $this->view('adminpanel/pagesearch', $model->pagesearch());
    }

    /**
    * Blog category
    */
    public function blogcategory()
    {
        $model = $this->model('AdminpanelModel');
 
        // Pass page to the view
        $this->view('adminpanel/blogcategory', $model->blogcategory());
    }

    /**
    * Page title
    */
    public function pagetitle()
    {
        $model = $this->model('AdminpanelModel');
 
        // Pass page to the view
        $this->view('adminpanel/pagetitle', $model->pagetitle());
    }

    /**
    * IP informations
    */
    public function ip_informations()
    {
        $model = $this->model('AdminpanelModel');
 
        // Pass page to the view
        $this->view('adminpanel/ip_informations', $model->ip_informations());
    }

    /**
    * Data from log files
    */
    public function logfiles()
    {
        $model = $this->model('Logfiles');
 
        // Pass page to the view
        $this->view('adminpanel/logfiles', $model->index());
    }
}