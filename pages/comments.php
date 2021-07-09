<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

$ptl = isset($_GET['ptl']) ? ltrim($vavok->check($_GET['ptl']), '/') : ''; // Return page

// In case data is missing
if ($vavok->post_and_get('action') == 'save' && $vavok->go('users')->is_reg() && (empty($vavok->post_and_get('comment')) || empty($vavok->post_and_get('pid')))) { $vavok->redirect_to(HOMEDIR . $ptl . '?isset=msgshort'); }

$comments = new Comments();

// Save comment
if ($vavok->post_and_get('action') == 'save') {
	// Insert data to database
	$comments->insert($vavok->post_and_get('comment'), $vavok->post_and_get('pid'));

	// Saved, return to page
	$vavok->redirect_to(HOMEDIR . $ptl . '?isset=savedok');
}

?>