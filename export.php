<?php
require_once "Go.php";

$file = "\\\\scout\\C$\\Inetpub\\go.";
$institutions = array("middlebury.edu", "miis.edu");

global $connection;

foreach($institutions as $institution) {
	$lines = array();
	$chars = "/([\(\)\$\\\?:])/";
	$replace = "\\\\$1";

	$select = $connection->prepare("SELECT name, url FROM code WHERE institution = :institution");
	$select->bindValue(":institution", $institution);
	$select->execute();

	while ($row = $select->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_NEXT)) {
		$lines[$row->name] = "RewriteRule ^/" . str_replace("?", "\\?", $row->name) . "/? ". preg_replace($chars, $replace, $row->url) . " [I,R]\r\n";
	}

	$alias = $connection->prepare("SELECT alias.name AS name, code.url AS url, code.description AS description FROM alias JOIN code ON (alias.code = code.name) WHERE alias.institution = :institution");
	$alias->bindValue(":institution", $institution);
	$alias->execute();

	while ($row = $alias->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_NEXT)) {
		$lines[$row->name] = "RewriteRule ^/" . str_replace("?", "\\?", $row->name) . "/? ". preg_replace($chars, $replace, $row->url) . " [I,R]\r\n";
	}

	ksort($lines);

	$httpd = "";
	foreach($lines as $name => $line) {
		$httpd .= $line;
	}

	if (!$handle = fopen($file . $institution . "\\httpd.ini.diesel", 'wb')) {
		echo "Cannot open file!";
		exit;
	}

	if (!fwrite($handle, $httpd)) {
		echo "Cannot write to file!";
		exit;
	}

	fclose($handle);
}
