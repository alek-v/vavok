<?php

use App\Classes\Core;

class Upload extends Core {
    public function upload($directory = '', $localization = '')
    {
        if (isset($localization) && !empty($localization)) $this->localization = $localization;

        if (isset($_POST['width']) && !empty($_POST['width'])) {
            $width = $this->check($_POST['width']);
        }
        if (isset($_POST['rename']) && !empty($_POST['rename'])) {
            $rename = $this->check($_POST['rename']);
        } 
        if (isset($_POST['lowercase']) && !empty($_POST['lowercase'])) {
            $lowercase = $this->check($_POST['lowercase']);
        } else {
            $lowercase = '';
        }

        // Receiving variables
        $upload_Name = $_FILES['upload']['name'];
        $upload_Size = $_FILES['upload']['size'];
        $upload_Temp = $_FILES['upload']['tmp_name'];
        $upload_Mime_Type = $_FILES['upload']['type'];
        $fileInfo = pathinfo($upload_Name);
        $upload_Ext = strtolower($fileInfo["extension"]);

        function RecursiveMkdir($path) {
            if (!file_exists($path)) {
                RecursiveMkdir(dirname($path));
                mkdir($path, 0777);
            } 
        }

        // Validation
        if ($upload_Size == 0) {
            die("<p align='center'><font face='Arial' size='3' color='#FF0000'>Please enter a valid upload</font></p>");
        }

        if ($upload_Size > 10000000) { // 10000000 bytes = 10MB
            // delete file if it is too big
            unlink($upload_Temp);
            
            return array('error' => $this->localization->string('bigfile'));
        }
        
        /*
        if ($upload_Mime_Type != "image/gif" AND $upload_Mime_Type != "image/png" AND $upload_Mime_Type != "image/jpeg" AND $upload_Mime_Type != "text/" AND $upload_Mime_Type != "text/calendar" AND $upload_Mime_Type != "text/css" AND $upload_Mime_Type != "text/directory" AND $upload_Mime_Type != "text/enriched" AND $upload_Mime_Type != "text/html" AND $upload_Mime_Type != "text/parityfec" AND $upload_Mime_Type != "text/plain") {
            unlink($upload_Temp);
            die("<p align='center'><font face='Arial' size='3' color='#FF0000'>This file extension is not allowed</font></p>");
        } 
        */

        if (!empty($rename)) {
            $upload_Name = $rename . '.' . $upload_Ext;
        }

        $upload_Name = str_replace(' ', '-', $upload_Name);
        $upload_Name = str_replace('--', '-', $upload_Name);

        if ($lowercase == 'lower') {
            $upload_Name = mb_strtolower($upload_Name, 'UTF-8');
        }

        $first_dir = date('y');
        if (!is_dir("../fls/" . $first_dir)) {
            @mkdir(PUBLICDIR . "fls/" . $first_dir, 0777);
            @touch(PUBLICDIR . "fls/" . $first_dir . "/index.php");
        }

        $second_dir = date('n');
        if (!is_dir(PUBLICDIR . "fls/" . $first_dir . "/" . $second_dir)) {
            @mkdir(PUBLICDIR . "fls/" . $first_dir . "/" . $second_dir, 0777);
            @touch(PUBLICDIR . "fls/" . $first_dir . "/" . $second_dir . "/index.php");
            @copy(PUBLICDIR . "fls/.htaccess", "../fls/" . $first_dir . "/" . $second_dir . "/.htaccess");
        }

        $uploadFile = PUBLICDIR . "fls/" . $first_dir . "/" . $second_dir . "/" . $upload_Name;

        if (!empty($directory)) { $uploadFile = $directory . $upload_Name; }

        if (file_exists($uploadFile)) {
            return array('error' => $this->localization->string('fileexists'));
        } else {
            if (!is_dir(dirname($uploadFile))) {
                RecursiveMkdir(dirname($uploadFile));
            } else {
                chmod(dirname($uploadFile), 0777);
            } 

            if (!empty($width) && $width > 0) {
                include APPDIR . 'classes/SimpleImage.php';

                $image = new SimpleImage();
                $image->load($upload_Temp);
                $image->resizeToWidth($width);
                $image->save($uploadFile);
            } else {
                move_uploaded_file($upload_Temp , $uploadFile);
            }

            // Insert data to database if we are uploading to default upload directory
            if (empty($directory)) {
                chmod($uploadFile, 0644);

                $upload_URL = "/fls/" . $first_dir . "/" . $second_dir . "/" . $upload_Name;
                $ext = substr($upload_Name, strrpos($upload_Name, '.') + 1);
                $ext = strtolower($ext);

                $values = array(
                    'name' => $upload_Name,
                    'date' => time(),
                    'ext' => $ext,
                    'fulldir' => $upload_URL
                );

                $this->db->insert('uplfiles', $values);
            } else {
                $upload_URL = $directory . $upload_Name;
            }
        }

        return array('file_address' => $this->websiteHomeAddress() . $upload_URL);
    }
}