<?php

require_once "go.php";
require_once "code.php";

if (!isset($_GET["code"])) {
	header("Location: gotionary.php");
	exit;
}

$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath != '/')
	$basePath .= '/';

$name = str_replace(" ", "+", $_GET["code"]);
try {
	
	try {
		$code = Code::get($name, $institution);
	} catch (Exception $e) {
		// If not found, send to the gotionary.
		header("Location: ".$basePath."gotionary.php?letter=" . substr($name, 0, 1));
		exit;
	}
	
	// For codes that don't have URLs, send them to the info page.
	if (!Code::isUrlValid($code->getUrl())) {
		header("Location: ".$basePath."info.php?code=" . $name);
		exit;
	} else {
		header("Location: " . $code->getUrl());
		exit;
	}
	
} catch (Exception $e) {
	header("Location: ".$basePath."info.php?code=" . $name);
	exit;
}

?>