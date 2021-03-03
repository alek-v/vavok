<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   03.03.2021. 20:36:29
 */

require_once '../include/startup.php';

$action = isset($_GET['action']) ? $vavok->check($_GET['action']) : '';
$comment = isset($_POST['comment']) ? $vavok->check($_POST['comment']) : '';
$pid = isset($_GET['pid']) ? $vavok->check($_GET['pid']) : ''; // Page id - where to show comment
$ptl = isset($_GET['ptl']) ? ltrim($vavok->check($_GET['ptl']), '/') : ''; // Return page

// In case data is missing
if ($action == 'save' && $vavok->go('users')->is_reg() && (empty($comment) || empty($pid))) { $vavok->redirect_to(HOMEDIR . $ptl . '?isset=msgshort'); }

$comments = new Comments();

// Save comment
if ($action == 'save') {
	// Insert data to database
	$comments->insert($comment, $pid);

	// Saved, return to page
	$vavok->redirect_to(HOMEDIR . $ptl . '?isset=savedok');
}

?>