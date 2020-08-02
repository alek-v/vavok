<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   26.07.2020. 17:37:18
*/

require_once"../include/startup.php";

$current_page->page_title = 'Change Photo'; // update lang
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

?>
<style type="text/css">
	.photo img {
		max-width: 320px;
		max-height: 320px;
		overflow: hidden;
	}
	</style>
<?php

if (!empty($_GET['action'])) {
    $action = $vavok->check($_GET["action"]);
} else {
    $action = '';
} 

if ($users->is_reg()) {
    if (empty($action)) {
        $chk_photo = $db->get_data('vavok_about', "uid='{$users->user_id}'", 'photo');

        if (!empty($localization->string('photo'))) {
            echo '<div class="photo">';
            echo '<h4>Your photo</h4><br /><img src="../' . $localization->string('photo') . '" alt="" /><br>';
            echo '</div>';
            echo '<div class="break"></div>';
        } 

        echo '<form action="photo.php?action=photo" method="post" name="form" enctype="multipart/form-data">';
        echo '<br>Change your profile photography: <br>';
        echo '<input type="file" name="file" /><br><br>';
        echo '<input type="submit" value="' . $localization->string('upload') . '" /></form>';
        echo '<div class="break"></div>';
        echo '<div class="break"></div>';


    } 
    // choose photo
    if ($action == "photo") {
        $avat_size = $_FILES['file']['size'];
        $avat_name = $_FILES['file']['name'];
        $size = GetImageSize($_FILES['file']['tmp_name']);
        $width = $size[0];
        $height = $size[1];
        $av_file = file($_FILES['file']['tmp_name']);
        $av_string = substr($avat_name, strrpos($avat_name, '.') + 1);
        $av_string = strtolower($av_string);

        if ($avat_size < 5242880) {
            if ($width < 1024 && $height < 1024) {
                if ($av_string == "gif" || $av_string == "jpg" || $av_string == "jpeg" || $av_string == "png") {
                    if ($av_file) {
                        // remove old photo
                        if (file_exists("../used/dataphoto/" . $users->user_id . ".jpg")) {
                            unlink("../used/dataphoto/" . $users->user_id . ".jpg");
                        } elseif (file_exists("../used/dataphoto/" . $users->user_id . ".png")) {
                            unlink("../used/dataphoto/" . $users->user_id . ".png");
                        } elseif (file_exists("../used/dataphoto/" . $users->user_id . ".gif")) {
                            unlink("../used/dataphoto/" . $users->user_id . ".gif");
                        } elseif (file_exists("../used/dataphoto/" . $users->user_id . ".jpeg")) {
                            unlink("../used/dataphoto/" . $users->user_id . ".jpeg");
                        } 
                        // add new photo
                        copy($_FILES['file']['tmp_name'], "../used/dataphoto/" . $users->user_id . "." . $av_string . "");
                        $ch = $_FILES['file']['tmp_name'];
                        chmod($ch, 0777);
                        chmod("../used/dataphoto/" . $users->user_id . "." . $av_string . "", 0777);

                        $db->update('vavok_about', 'photo', "gallery/photo.php?uz=" . $users->user_id, "uid='{$users->user_id}'");
                        echo '<div class="photo">';
                        echo '<br>Photo saved!<br>';
                        echo 'Current photo: <img src="../gallery/photo.php?uz=' . $users->user_id . '" alt="" /><br>';
                        echo '</div>';
                    } else {
                        echo 'Error uploading photography<br>';
                    } 
                } else {
                    echo $localization->string('badfileext') . '<br>';
                } 
            } else {
                echo 'Photography must be under 1024 px<br>';
            } 
        } else {
            echo $localization->string('filemustb') . ' under 5 MB<br>';
        } 

        echo '<a href="photo.php">' . $localization->string('back') . '</a><br>';
    } 
    if ($action == 'remove') {
        if (file_exists("../used/dataphoto/" . $users->user_id . ".jpg")) {
            unlink("../used/dataphoto/" . $users->user_id . ".jpg");
        } elseif (file_exists("../used/dataphoto/" . $users->user_id . ".png")) {
            unlink("../used/dataphoto/" . $users->user_id . ".png");
        } elseif (file_exists("../used/dataphoto/" . $users->user_id . ".gif")) {
            unlink("../used/dataphoto/" . $users->user_id . ".gif");
        } elseif (file_exists("../used/dataphoto/" . $users->user_id . ".jpeg")) {
            unlink("../used/dataphoto/" . $users->user_id . ".jpeg");
        } 

        $db->update('vavok_about', 'photo', "", "uid='{$users->user_id}'");

        echo '<p>Your photography successfully deleted!</p><br />'; // update lang

        echo '<a href="profile.php">' . $localization->string('back') . '</a><br />';
    } 
} else {
    echo '<br>' . $localization->string('notloged') . '<br><br><br>';
} 

if (empty($action)) {
    echo '<a href="../pages/profile.php">' . $localization->string('back') . '</a><br />';
} 
echo '<a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>