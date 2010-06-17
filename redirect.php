<?php

require_once "go.php";
require_once "code.php";

if (!isset($_GET["code"]) || !isset($_GET["institution"])) {
	header("Location: gotionary.php");
	exit;
}

global $connection;

$name = str_replace(" ", "+", $_GET["code"]);
$institution = $_GET["institution"];

if (substr($name, strlen($name) - 1, 1) == "/") {
  $name = substr($name, 0, strlen($name) - 1);
}

$alias = $connection->prepare("SELECT code FROM alias WHERE name = :name AND institution = :institution");
$alias->bindValue(":name", $name);
$alias->bindValue(":institution", $institution);
$alias->execute();

if ($alias->rowCount() > 0) {
	$row = $alias->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_NEXT);
	$name = $row->code;
}

$code = $connection->prepare("SELECT url FROM code WHERE name = :name AND institution = :institution");
$code->bindValue(":name", $name);
$code->bindValue(":institution", $institution);
$code->execute();

if ($code->rowCount() == 0) {
	header("Location: gotionary.php?institution=" . $institution . "&letter=" . substr($name, 0, 1));
} else {
	$row = $code->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_NEXT);
	$url = $row->url;
	header("Location: " . $url);
}
?>