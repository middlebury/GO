<?php
//copy pasted function to get IP address via client ip or x
//forwarded first before falling back on remote addr
function getRealIpAddr() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {  //check ip from share internet
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  //to check ip is pass from proxy
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

//function to check if a user is a
//super admin of the GO application
function isSuperAdmin() {
	//this var is not passed to this function, use the global
	global $goAdmin;
	//if the current user is logged in, check it they are in the admin array
	if(isset($_SESSION["AUTH"]) && in_array($_SESSION["AUTH"]->getId(), $goAdmin)) {
		return true;
	} else {
		return false;
	}
}

//function to check if a user can view details (history, flags, user-to-code lists).
function isAuditor() {
	if (isSuperAdmin())
		return true;
	
	//if the current user is logged in, check it they are in the auditors array
	global $goAuditors;
	if(!empty($goAuditors) && !empty($_SESSION["AUTH"]) && in_array($_SESSION["AUTH"]->getId(), $goAuditors))
		return true;
	
	return false;
}

// This is copy pasted function to get URL from current page 
function curPageURL() {
	$isHTTPS = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
	$port = (isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));
	$port = ($port) ? ':'.$_SERVER["SERVER_PORT"] : '';
	$url = ($isHTTPS ? 'https://' : 'http://').$_SERVER["SERVER_NAME"].$port.$_SERVER["REQUEST_URI"];
	return $url;
}

// Check to see if current user is admin of passed code
function isAdmin($code, $institution) {
	global $connection;
	$is_admin = false;
	// Find what users are admins of this code
	$select =  $connection->prepare("
  SELECT
  	user
  FROM
  	user_to_code
  WHERE
  	code = ?
  	AND
  	institution = ?");
  $select->bindValue(1, $code);
  $select->bindValue(2, $institution);
  $select->execute();
  
  // If authenticated user is admin of code then set $is_admin
  foreach ($select->fetchAll() as $row) {
		if ($row['user'] == $_SESSION['AUTH']->getId()) {
			$is_admin = true;
		}
	}
	return $is_admin;
}
// Check passed field type against error type and return
// "failed_validation" (the class name that flags and 
// field has having failed validation) if true. This
// lets us add this to all fields to check if they are
// in error or not
function errorCase($error_type, $field_type) {
	if ($error_type == $field_type) {
		return 'failed_validation';
	} else {
		return '';
	}
}
