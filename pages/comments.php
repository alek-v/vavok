<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   19.06.2020. 23:57:14
*/

include"../include/strtup.php";


$action = isset($_GET['action']) ? check($_GET['action']) : '';
$comment = isset($_POST['comment']) ? check($_POST['comment']) : '';
$pid = isset($_GET['pid']) ? check($_GET['pid']) : ''; // Page id - where to show comment
$ptl = isset($_GET['ptl']) ? ltrim(check($_GET['ptl']), '/') : ''; // Return page

// In case data is missing
if ($action == 'save' && $users->is_reg() && (empty($comment) || empty($pid))) { redirect_to(HOMEDIR . $ptl . '?isset=msgshort'); }

$comments = new Comments();

// Save comment
if ($action == 'save') {

	// Insert data to database
	$comments->insert($comment, $pid);

	// Saved, return to page
	redirect_to(HOMEDIR . $ptl . '?isset=savedok');

}



?>