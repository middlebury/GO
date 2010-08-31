<?php
require_once "header.php";
require_once "config.php";
print '<p>';
var_dump($_POST);
print '</p>';
print $_POST["code"];
print '<p>';
var_dump($_SESSION);
print '</p>';
//function to get IP address via client ip or x
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

//try to do this and catch the error if there is an issue
try {
	//get the statement object for this insert statement
  $insert = $connection->prepare("INSERT INTO flag (code, user, ipaddress) VALUES (?, ?, ?)");
  
  //bind the values represented by the "?" in the statement
  $insert->bindValue(1, $_POST["code"]);
  if (isset($_SESSION["AUTH"])) {
  	$insert->bindValue(2, $_SESSION["AUTH"]->getId());
  	//we want to add the current code to the session array
  	//"flagged" so we know the user has flagged this code
  	$_SESSION['flagged'][$_POST["code"]] = $_POST["code"];
  	print '<p>';
		var_dump($_SESSION);
		print '</p>';
  } else {
  	$insert->bindValue(2, '');
  }
  $insert->bindValue(3, getRealIpAddr());
  
  //finally execute the statement
  $insert->execute();

//now catch any exceptions
} catch (Exception $e) {
	throw $e;
}

		
/*$flag_connection = mysql_connect(GO_DATABASE_HOST, GO_DATABASE_USER, GO_DATABASE_PASS);
if (!$flag_connection) {
	die ('Could not conenct to DB '.mysql_error().'<br />');
} else {
	print 'Connected successfully<br />';	
}

$select_db = mysql_select_db(GO_DATABASE_NAME);
if (!$select_db) {
	die ('Could not select DB '.mysql_error().'<br />');
} else {
	print 'DB selected successfully<br />';	
}

//get IP address
$ip = $_SERVER['REMOTE_ADDR'];

if (isset($_SESSION["AUTH"])) {
	print "Session Auth set";
} else {
	print "Session auth not set";
}

$query = "INSERT INTO flag (code, user, ipaddress) VALUES ('".$_POST["code"]."','".$_SESSION["AUTH"]->getId()."','".getRealIpAddr()."')";
//$query = addslashes($query);
print $query;
$result = mysql_query($query);
if (!$result) {
	die ('Could not query DB '.mysql_error().'<br />');
} else {
	print 'DB queried successfully<br />';	
}

$closed = mysql_close($flag_connection);
if ($closed == true) {
	print "flag_connection closed successfully.";
} else {
	print "Doh!";
}*/

if ($_POST['xsrfkey'] == $_SESSION['xsrfkey']) {
	print "<p>All Good</p>";
} else if ($_POST['xsrfkey'] != $_SESSION['xsrfkey']) {
	print "<p>Not Good</p>";
} else {
	print "<p>:P</p>";
	}
?>

