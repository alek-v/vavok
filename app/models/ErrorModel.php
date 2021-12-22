<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class ErrorModel extends BaseModel {
    /**
     * Log error
     * 
     * @param array $params
     */
    protected function log_error($params = [])
    {
        // Localization
        $this->localization = $this->model('Localization');
        $this->localization->load('', 'error');

        $http_referer = !empty($_SERVER['HTTP_REFERER']) ? $this->check($_SERVER['HTTP_REFERER']) : 'No referer';
        $http_referer = str_replace(':|:', '|', $http_referer);
        $request_uri = $this->check(str_replace(':|:', '|', REQUEST_URI));
        $hostname = gethostbyaddr($this->user->find_ip());
        $hostname = str_replace(':|:', '|', $hostname);

        $log = !empty($this->user->show_username()) ? $this->user->show_username() : 'Guest';

        $write_data = $request_uri . ':|:' . time() . ':|:' . $this->user->find_ip() . ':|:' . $hostname . ':|:' . $this->user->user_browser() . ':|:' . $http_referer . ':|:' . $log . ':|:';
        
        $error_number_info = '';
        $additional_error_info = '';

        // No params from url
        if (!isset($params[0])) $params[0] = '';

        if ($params[0] == 'error_401') {
            $error_number_info = $this->localization->string('err401');
            $logdat = "datalog/error401.dat";
            $write = ':|:Error 401:|:' . $write_data;
        } elseif ($params[0] == 'error_402') {
            $error_number_info =  $this->localization->string('err402');
            $logdat = "datalog/error402.dat";
            $write = ':|:Error 402:|:' . $write_data;
        } elseif ($params[0] == 'error_403') {
            $error_number_info = $this->localization->string('err403');
            $write = ':|:Error 403:|:' . $write_data;
            $logdat = "datalog/error403.dat";
        } elseif ($params[0] == 'error_404') {
            $error_number_info = $this->localization->string('err404youtrytoop') . ' ' . $_SERVER['HTTP_HOST'] . $request_uri;
            $additional_error_info = $this->localization->string('filenotfound');
            $write = ':|:Error 404:|:' . $write_data;
            $logdat = 'datalog/error404.dat';
        } elseif ($params[0] == 'error_406') {
            $error_number_info = $this->localization->string('err406descr') . ' ' . $_SERVER['HTTP_HOST'] . $request_uri . ' ' . $this->localization->string('notfonserver');
            $write = ':|:406 - Not acceptable:|:' . $write_data;
            $logdat = "datalog/error406.dat";
        } elseif ($params[0] == 'error_500') {
            $error_number_info = $this->localization->string('err500');
            $logdat = "datalog/error500.dat";
            $write = ':|:500 - Internal server error:|:' . $write_data;
        } elseif ($params[0] == 'error_502') {
            $error_number_info = $this->localization->string('err502');
            $logdat = "datalog/error502.dat";
            $write = ':|:Error 502:|:' . $write_data;
        } else {
            $logdat = "datalog/error.dat";
            $write = ':|:Unknown error:|:' . $write_data;
        }

        if (isset($write) && !empty($logdat)) {
            // Write new data to log file
            $this->writeDataFile($logdat, $write . PHP_EOL, 1);
        
            // Remove lines from file
            $this->limitFileLines(APPDIR . 'used/' . $logdat, $this->configuration('maxLogData'));
        }

        $this_page['error_number_info'] = $error_number_info;
        $this_page['additional_error_info'] = $additional_error_info;

        return $this_page;
    }

    /**
     * Error 403
     * 
     * @param array $params
     */
    public function error_403($params = [])
    {
        // Log error
        $error_info = $this->log_error($params);

        // Users data
        $this_page['user'] = $this->user_data;
        $this_page['tname'] = 'Error 403';
        $this_page['error_number_info'] = $error_info['error_number_info'];

        return $this_page;
    }

    /**
     * Error 404
     * 
     * @param array $params
     */
    public function error_404($params = [])
    {
        // Send page status code
        header("HTTP/1.0 404 Not Found");

        // Log error
        $error_info = $this->log_error($params);

        // Users data
        $this_page['user'] = $this->user_data;
        $this_page['tname'] = 'Error 404';
        $this_page['error_number_info'] = $error_info['error_number_info'];

        return $this_page;
    }

    /**
     * Error 500
     * 
     * @param array $params
     */
    public function error_500($params = [])
    {
        // Log error
        $error_info = $this->log_error($params);

        // Users data
        $this_page['user'] = $this->user_data;
        $this_page['tname'] = 'Error 500';
        $this_page['error_number_info'] = $error_info['error_number_info'];

        return $this_page;
    }
}