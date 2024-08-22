<?php
require_once "config.php";
require_once "go.php";

// This page is in the list of admin pages in go.php and therefore
// requires login. It accepts the URL of the referring page which
// it then redirects back to.

//redirect on completion

if (!empty($_GET['r']) && strpos($_GET['r'], 'https://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/') === 0) {
  header("location: " . $_GET['r']);
} else {
  header("location: admin.php");
}
