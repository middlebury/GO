<?php
//go_functions.php gives us access to the isSuperAdmin function 
require_once "go_functions.php";
//go.php handles the session and xss check for admin
//pages and pages where a session is necessary
require_once "go.php";

//check for xss attempt
if ($_POST['xsrfkey'] != $_SESSION['xsrfkey']) {
	die("Session variables do not match");
}

// This script should only run for superadmins
if (!isSuperAdmin()) {
		die("You do not have permission to view this page");
	}

try {
	//get the statement object for this update statement
	//set who completed the flag and when it was completed
	$update = $connection->prepare("UPDATE flag SET completed = ?, completed_on = NOW() WHERE code = ? AND institution = ? AND completed = '0'");
	$update->bindValue(1, $_SESSION["AUTH"]->getName());
  $update->bindValue(2, $_POST['code']);
  $update->bindValue(3, $_POST['institution']);
	$update->execute();
	
	//log completion
	Go::log("Flag as inappropriate flag was completed", $_POST['code']);
//now catch any exceptions
} catch (Exception $e) {
	throw $e;
} //end catch (Exception $e) {

//redirect on completion
header("location: flag_admin.php?code=".$_POST['code']);
