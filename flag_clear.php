<?php
//go.php handles the session and xss check for admin
//pages and pages where a session is necessary
require_once "go.php";

//check for xss attempt
if ($_POST['xsrfkey'] != $_SESSION['xsrfkey']) {
	die("Session variables do not match");
}

try {
	//set array to hold results
	$result = array();
	//get the statement object for this select statement
	$delete = $connection->prepare("DELETE FROM flag WHERE code = ?");
  $delete->bindValue(1, $_POST['code']);
	$delete->execute();
	Go::log("Flag as inappropriate flag was cleared", $_POST['code']);
//now catch any exceptions
} catch (Exception $e) {
	throw $e;
} //end catch (Exception $e) {

//redirect on completion
header("location: flag_admin.php?code=".$_POST['code']);
