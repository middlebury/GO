<?php

require_once "go.php";
require_once "phpqrcode.php";

try {
	$name = str_replace(" ", "+", $_GET["code"]);
	$institution = $_GET["institution"];
	$code = Code::get($name, $institution);
	
	QRcode::png('http://go.'.$institution.'/' . $code->getName());

} catch (Exception $e) {
	error_log($e->getMessage(), 3);
	print "<div class='error'>Error. Please contact ".GO_HELP_HTML."</div>";
}