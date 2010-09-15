<?php
require_once "config.php";
require_once "go.php";

//$current_page = basename($_SERVER['PHP_SELF']);

/*if (AUTH_METHOD == 'ldap') {
		if (!isset($_SESSION["AUTH"]) && $current_page != "login.php") {
  		header("Location: login.php?r=" . $_SERVER["PHP_SELF"]);
  		exit();
		}
	} else if (AUTH_METHOD == 'cas') {
		
		$_SESSION["AUTH"] = new GoAuthCas();
		
	} else {
		throw new Exception('Unknown Auth Method');
	}*/

//redirect on completion
//header("location: https://login.middlebury.edu/cas/login?service=".$_GET['url']);
header("location: ".$_GET['url']);