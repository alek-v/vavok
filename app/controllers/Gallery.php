<?php

use App\Classes\Controller;

class Gallery {
    /**
     * Default page
     */
    public function index()
    {
        die('There is nothing to show here');
    }

    /**
     * Profile photography
     */
    public function photo($params)
    {
        $uz = $params[0];

        if (file_exists(STORAGEDIR . "dataphoto/" . $uz . ".jpg")) {
            $filename = STORAGEDIR . "dataphoto/" . $uz . ".jpg";
        } elseif (file_exists(STORAGEDIR . "dataphoto/" . $uz . ".png")) {
            $filename = STORAGEDIR . "dataphoto/" . $uz . ".png";
        } elseif (file_exists(STORAGEDIR . "dataphoto/" . $uz . ".gif")) {
            $filename = STORAGEDIR . "dataphoto/" . $uz . ".gif";
        } elseif (file_exists(STORAGEDIR . "dataphoto/" . $uz . ".jpeg")) {
            $filename = STORAGEDIR . "dataphoto/" . $uz . ".jpeg";
        }

        $ext = substr($filename, strrpos($filename, '.') + 1);
        $filename = file_get_contents($filename);

        header('Content-Disposition: inline; filename="' . $uz . '"');
        header("Content-type: image/" . $ext . "");
        echo $filename;
    }
}