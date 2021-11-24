<?php

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

        if (file_exists(APPDIR . "used/dataphoto/" . $uz . ".jpg")) {
            $filename = APPDIR . "used/dataphoto/" . $uz . ".jpg";
        } elseif (file_exists(APPDIR . "used/dataphoto/" . $uz . ".png")) {
            $filename = APPDIR . "used/dataphoto/" . $uz . ".png";
        } elseif (file_exists(APPDIR . "used/dataphoto/" . $uz . ".gif")) {
            $filename = APPDIR . "used/dataphoto/" . $uz . ".gif";
        } elseif (file_exists(APPDIR . "used/dataphoto/" . $uz . ".jpeg")) {
            $filename = APPDIR . "used/dataphoto/" . $uz . ".jpeg";
        }

        $ext = substr($filename, strrpos($filename, '.') + 1);
        $filename = file_get_contents($filename);

        header('Content-Disposition: inline; filename="' . $uz . '"');
        header("Content-type: image/" . $ext . "");
        echo $filename;
    }
}