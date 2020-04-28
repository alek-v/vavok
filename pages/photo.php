<?php 
// (c) vavok.net
require_once"../include/strtup.php";

$mediaLikeButton = 'off'; // dont show like buttons

$my_title = 'Change Photo'; // update lang
include_once"../themes/$config_themes/index.php";

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
    $action = check($_GET["action"]);
} else {
    $action = '';
} 

if ($users->is_reg()) {
    if (empty($action)) {
        $chk_photo = $db->select('vavok_about', "uid='" . $user_id . "'", '', 'photo');

        if (!empty($chk_photo['photo'])) {
            echo '<div class="photo">';
            echo '<h4>Your photo</h4><br /><img src="../' . $chk_photo['photo'] . '" alt="" /><br>';
            echo '</div>';
            echo '<div class="break"></div>';
        } 

        echo '<form action="photo.php?action=photo" method="post" name="form" enctype="multipart/form-data">';
        echo '<br>Change your profile photography: <br>';
        echo '<input type="file" name="file" /><br><br>';
        echo '<input type="submit" value="' . $lang_page['upload'] . '" /></form>';
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
                        if (file_exists("../used/dataphoto/" . $user_id . ".jpg")) {
                            unlink("../used/dataphoto/" . $user_id . ".jpg");
                        } elseif (file_exists("../used/dataphoto/" . $user_id . ".png")) {
                            unlink("../used/dataphoto/" . $user_id . ".png");
                        } elseif (file_exists("../used/dataphoto/" . $user_id . ".gif")) {
                            unlink("../used/dataphoto/" . $user_id . ".gif");
                        } elseif (file_exists("../used/dataphoto/" . $user_id . ".jpeg")) {
                            unlink("../used/dataphoto/" . $user_id . ".jpeg");
                        } 
                        // add new photo
                        copy($_FILES['file']['tmp_name'], "../used/dataphoto/" . $user_id . "." . $av_string . "");
                        $ch = $_FILES['file']['tmp_name'];
                        chmod($ch, 0777);
                        chmod("../used/dataphoto/" . $user_id . "." . $av_string . "", 0777);

                        $db->update('vavok_about', 'photo', "gallery/photo.php?uz=" . $user_id, "uid='" . $user_id . "'");
                        echo '<div class="photo">';
                        echo '<br>Photo saved!<br>';
                        echo 'Current photo: <img src="../gallery/photo.php?uz=' . $user_id . '" alt="" /><br>';
                        echo '</div>';
                    } else {
                        echo 'Error uploading photography<br>';
                    } 
                } else {
                    echo $lang_page['badfileext'] . '<br>';
                } 
            } else {
                echo 'Photography must be under 1024 px<br>';
            } 
        } else {
            echo $lang_page['filemustb'] . ' under 5 MB<br>';
        } 

        echo '<a href="photo.php">' . $lang_home['back'] . '</a><br>';
    } 
    if ($action == 'remove') {
        if (file_exists("../used/dataphoto/" . $user_id . ".jpg")) {
            unlink("../used/dataphoto/" . $user_id . ".jpg");
        } elseif (file_exists("../used/dataphoto/" . $user_id . ".png")) {
            unlink("../used/dataphoto/" . $user_id . ".png");
        } elseif (file_exists("../used/dataphoto/" . $user_id . ".gif")) {
            unlink("../used/dataphoto/" . $user_id . ".gif");
        } elseif (file_exists("../used/dataphoto/" . $user_id . ".jpeg")) {
            unlink("../used/dataphoto/" . $user_id . ".jpeg");
        } 

        $db->update('vavok_about', 'photo', "", "uid='" . $user_id . "'");

        echo '<p>Your photography successfully deleted!</p><br />'; // update lang

        echo '<a href="profil.php">' . $lang_home['back'] . '</a><br />';
    } 
} else {
    echo '<br>' . $lang_home['notloged'] . '<br><br><br>';
} 

if (empty($action)) {
    echo '<a href="../pages/profil.php">' . $lang_home['back'] . '</a><br />';
} 
echo '<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a>';

include_once"../themes/$config_themes/foot.php";

?>