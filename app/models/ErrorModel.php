<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Traits\Files;

class ErrorModel extends BaseModel {
    use Files;

    /**
     * Log error
     * 
     * @param array $params
     * @return array
     */
    protected function log_error(array $params = []): array
    {
        // Load additional localization data
        $this->localization->loadAdditional('error');

        $http_referer = !empty($_SERVER['HTTP_REFERER']) ? $this->check($_SERVER['HTTP_REFERER']) : 'No referer';
        $http_referer = str_replace(':|:', '|', $http_referer);
        $request_uri = $this->check(str_replace(':|:', '|', REQUEST_URI));
        $hostname = gethostbyaddr($this->user->findIpAddress());
        $hostname = str_replace(':|:', '|', $hostname);

        $log = !empty($this->user->showUsername()) ? $this->user->showUsername() : 'Guest';

        $write_data = $request_uri . ':|:' . time() . ':|:' . $this->user->findIpAddress() . ':|:' . $hostname . ':|:' . $this->user->userBrowser() . ':|:' . $http_referer . ':|:' . $log . ':|:';
        
        $error_number_info = '';
        $additional_error_info = '';

        // No params from url
        if (!isset($params[0])) $params[0] = '';

        if ($params[0] == 'error_401') {
            $error_number_info = $this->localization->string('error_401');
            $logdat = "datalog/error401.dat";
            $write = ':|:Error 401:|:' . $write_data;
        } elseif ($params[0] == 'error_402') {
            $error_number_info =  $this->localization->string('error_402');
            $logdat = "datalog/error402.dat";
            $write = ':|:Error 402:|:' . $write_data;
        } elseif ($params[0] == 'error_403') {
            $error_number_info = $this->localization->string('error_403');
            $write = ':|:Error 403:|:' . $write_data;
            $logdat = "datalog/error403.dat";
        } elseif ($params[0] == 'error_404') {
            $error_number_info = $this->localization->string('error_404youtrytoop') . ' ' . $_SERVER['HTTP_HOST'] . $request_uri;
            $additional_error_info = $this->localization->string('filenotfound');
            $write = ':|:Error 404:|:' . $write_data;
            $logdat = 'datalog/error404.dat';
        } elseif ($params[0] == 'error_406') {
            $error_number_info = $this->localization->string('error_406descr') . ' ' . $_SERVER['HTTP_HOST'] . $request_uri . ' ' . $this->localization->string('notfonserver');
            $write = ':|:406 - Not acceptable:|:' . $write_data;
            $logdat = "datalog/error406.dat";
        } elseif ($params[0] == 'error_500') {
            $error_number_info = $this->localization->string('error_500');
            $logdat = "datalog/error500.dat";
            $write = ':|:500 - Internal server error:|:' . $write_data;
        } elseif ($params[0] == 'error_502') {
            $error_number_info = $this->localization->string('error_502');
            $logdat = "datalog/error502.dat";
            $write = ':|:Error 502:|:' . $write_data;
        } else {
            $logdat = "datalog/error.dat";
            $write = ':|:Unknown error:|:' . $write_data;
        }

        if (isset($write) && !empty($logdat)) {
            // Write new data to the log file
            $this->writeDataFile($logdat, $write . PHP_EOL, 1);
        
            // Limit number of records to save
            $this->limitFileLines($logdat, $this->configuration->getValue('limit_log_entries'));
        }

        $this->page_data['error_number_info'] = $error_number_info;
        $this->page_data['additional_error_info'] = $additional_error_info;

        return $this->page_data;
    }

    /**
     * Error 403
     * 
     * @param array $params
     * @return array
     */
    public function error_403(array $params = []): array
    {
        // Log error
        $error_info = $this->log_error($params);

        $this->page_data['page_title'] = 'Error 403';
        $this->page_data['error_number_info'] = $error_info['error_number_info'];

        return $this->page_data;
    }

    /**
     * Error 404
     * 
     * @param array $params
     * @return array
     */
    public function error_404(array $params = []): array
    {
        // Send page status code
        header("HTTP/1.1 404 Not Found");

        // Log error
        $error_info = $this->log_error($params);

        $this->page_data['page_title'] = 'Error 404';
        $this->page_data['error_number_info'] = $error_info['error_number_info'];

        return $this->page_data;
    }

    /**
     * Error 500
     * 
     * @param array $params
     * @return array
     */
    public function error_500(array $params = []): array
    {
        // Log error
        $error_info = $this->log_error($params);

        $this->page_data['page_title'] = 'Error 500';
        $this->page_data['error_number_info'] = $error_info['error_number_info'];

        return $this->page_data;
    }
}