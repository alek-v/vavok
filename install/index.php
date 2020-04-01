<?php
// (c) vavok.net

// create main website data
include 'include/main_data.php';
include '../include/classes/Config.class.php';


$action = isset($_GET['action']) ? $_GET['action'] : '';

// init class
$myconfig = new Config;

if ($action == 'proceed') {

$myconfig->update(array(47 => $_POST['language']));

header("Location: install.php"); exit;
}



// include header
include"include/header.php";
?>

<form id="form1" name="form1" action="index.php?action=proceed" method="post">
  <fieldset>
    <legend>Language</legend>
    <label for="select">Select:</label>
    <select name="language" id="language">
    <option name="english" value="english">English</option>
    </select>
    <input id="submit" name="submit" type="submit" value="Submit" />
  </fieldset>
</form>

<?php
include"include/footer.php";
?>