<?php
require_once "go.php";
require_once "code.php";

// Trim off and store any trailing Google Analytics "Linker" suffix for later pass-through.
// This looks like the following:
//		?_ga=2.263707972.951205599.1494365257-288906005.1494362059'
//	or
//		&_ga=2.263707972.951205599.1494365257-288906005.1494362059'
$ga_linker = NULL;
if (preg_match('/((?:\?|&)_ga=([^&]+))$/', $_GET['code'], $m)) {
	$ga_linker = $m[2];
	// Removed the linker from our code for lookup purposes.
	$_GET['code'] = preg_replace('/((\?|&)_ga=[0-9a-z\-\.]+)$/', '', $_GET['code']);
}

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
		if (empty($ga_linker)) {
			header("Location: " . $code->getUrl());
		}
		// Pass through the Google Analytics Linker code.
		else {
			$url = $code->getUrl();
			// There is already a query string...
			if (preg_match('/\?/', $url)) {
				$separator = '&';
			} else {
				$separator = '?';
			}
			header("Location: " . $url . $separator . '_ga=' . $ga_linker);
		}
		exit;
	}

} catch (Exception $e) {
	header("Location: ".$basePath."info.php?code=" . $name);
	exit;
}
