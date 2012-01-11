<?php

require_once "go.php";

/* Do LDAP authentication */
session_start();
if (!isset($_SESSION["AUTH"]) || $_SESSION["AUTH"] == "") {
  header("Location: login.php?r=" . $_SERVER["PHP_SELF"]);
  exit();
}

/* Do CAS authentication
$_SESSION["AUTH"] = new GoAuthCas();
*/

global $connection;

$select = $connection->prepare("SELECT code.name AS name, user.name AS user, code.url AS url, user.notify AS send FROM code LEFT JOIN user_to_code ON (code.name = user_to_code.code) LEFT JOIN user ON (user_to_code.user = user.name) ORDER BY code.name");
$select->execute();

$codes = array();
$users = array();
$results = array();

while($row = $select->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_NEXT)) {
	$codes[$row->name] = $row->url;
	
	if ($row->send == "1") {
		$users[$_SESSION["AUTH"]->getEmail($row->user)][] = $row->name;
	}
	
	$users["go@middlebury.edu"][] = $row->name;
}

foreach($codes as $name => $url) {
	$response = Go::httpquery($url, array());
	
	if((substr_compare($response, "4", 0, 1) == 0 || substr_compare($response, "5", 0, 1) == 0 ||
		strlen($response) > 3) && substr_compare($response, "401", 0, 3) != 0) {
		
		$results[$name] = $response;
	}
}

foreach($users as $email => $names) {
	sort($names);
	
	$message = ""; $count = 0;
	
	foreach($names as $name) {
		if (isset($results[$name])) {
			$message = $message . "go/" . $name . " | RESPONSE: " . $results[$name] . "\r\n";
		
			$count++;
		}
	}
	
	if ($count > 0) {
		$message = "There were " . $count . " errors today.\r\n\r\n" .
			"Below are the errors for today: \r\n\r\n" . $message;
		
		$to = $email;
		$subject = "Nightly GO Address Check";
		$headers = "From: go@middlebury.edu\r\n";
		mail($to, $subject, $message, $headers);
	}
}

?>