<?php
// Requiring go_functions.php give us acces to the curPageURL() function
require_once "go_functions.php";
require_once "go.php";

$name = "";

if (isset($_SESSION["AUTH"])) {
  try {
    $name = $_SESSION["AUTH"]->getCurrentUserName();
  } catch (Throwable $e) {
    // We may have an expired proxy-ticket kept around. If so, regenerate the session
    // and log-in again.
    if ($e->getCode() == PHPCAS_SERVICE_PT_FAILURE) {
      session_destroy();
      header('Location: '.$_SERVER['REQUEST_URI']);
      exit;
    } else {
    	throw $e;
    }
  }
}

require_once $institutions[$institution]['header'];
