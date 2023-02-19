<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\Controller;

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
    * IP information
    */
    public function ip_information()
    {
        $model = $this->model('AdminpanelModel');
 
        // Pass page to the view
        $this->view('adminpanel/ip_information', $model->ip_information());
    }

    /**
    * Data from the log files
    */
    public function logfiles()
    {
        $model = $this->model('Logfiles');
 
        // Pass page to the view
        $this->view('adminpanel/logfiles', $model->index());
    }

    /**
    * File upload
    */
    public function file_upload()
    {
        $model = $this->model('FileUpload');
 
        // Pass page to the view
        $this->view('adminpanel/file_upload', $model->index());
    }

    /**
    * Finish file upload
    */
    public function finish_upload()
    {
        $model = $this->model('FileUpload');
 
        // Save file
        $model->finish_upload();
    }

    /**
    * Files that has been uploaded
    */
    public function uploaded_files()
    {
        $model = $this->model('FileUpload');
 
        // Pass page to the view
        $this->view('adminpanel/uploaded_files', $model->uploaded_files());
    }

    /**
    * Search uploaded files
    */
    public function search_uploads()
    {
        $model = $this->model('FileUpload');
 
        // Pass page to the view
        $this->view('adminpanel/search_uploads', $model->search_uploads());
    }

    /**
    * Add mail to the email queue
    */
    public function email_queue()
    {
        $model = $this->model('EmailQueue');
 
        // Pass page to the view
        $this->view('index', $model->email_queue());
    }

    /**
    * Manage email subscriptions
    */
    public function subscriptions()
    {
        $model = $this->model('Subscription');
 
        // Pass page to the view
        $this->view('index', $model->index());
    }

    /**
     * Email subscription options
     */
    public function subscription_options()
    {
        $model = $this->model('Subscription');

        $this->view('adminpanel/email_subscriptions', $model->subscription_options());
    }

    /**
    * Sitemap generator
    */
    public function sitemap()
    {
        $model = $this->model('Sitemap');
 
        // Pass page to the view
        $this->view('adminpanel/sitemap', $model->index());
    }
}