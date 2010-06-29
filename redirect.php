<?php

require_once "go.php";
require_once "code.php";

if (!isset($_GET["code"])) {
	header("Location: gotionary.php");
	exit;
}

$name = str_replace(" ", "+", $_GET["code"]);

try {
	
	try {
		$code = Code::get($name, $institution);
	} catch (Exception $e) {
		// If not found, send to the gotionary.
		header("Location: gotionary.php?letter=" . substr($name, 0, 1));
		exit;
	}
	
	// For codes that don't have URLs, send them to the info page.
	if (!Code::isUrlValid($code->getUrl())) {
		header("Location: info.php?code=" . $name);
		exit;
	} else {
		header("Location: " . $code->getUrl());
		exit;
	}
	
} catch (Exception $e) {
	header("Location: info.php?code=" . $name);
	exit;
}

?>