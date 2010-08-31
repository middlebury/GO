<?php
//go.php handles the session and xss check for admin
//pages and pages where a session is necessary
require_once "go.php";
//functions.phh gives us access to getRealIpAddr() function
require_once "functions.php";

//check for xss attempt
if ($_POST['xsrfkey'] != $_SESSION['xsrfkey']) {
	die("Session variables do not match");
}

//try to do this and catch the error if there is an issue
try {
	//get the statement object for this insert statement
  $insert = $connection->prepare("INSERT INTO flag (code, user, ipaddress) VALUES (?, ?, ?)");
  
  //bind the values represented by the "?" in the statement
  //first bind code
  $insert->bindValue(1, $_POST["code"]);
  if (isset($_SESSION["AUTH"])) {
  	//bind the logged in user
  	$insert->bindValue(2, $_SESSION["AUTH"]->getId());
  	//we want to add the current code to the session array
  	//"flagged" so we know the user has flagged this code
  	$_SESSION['flagged'][$_POST["code"]] = $_POST["code"];
  } else {
  	//otherwise just leave the user field blank
  	$insert->bindValue(2, '');
  }
  //bind the ipaddress
  $insert->bindValue(3, getRealIpAddr());
  
  //finally execute the statement
  $insert->execute();

//now catch any exceptions
} catch (Exception $e) {
	throw $e;
}

//redirect on completion
header("location: info.php?code=".$_POST['code']);
?>



