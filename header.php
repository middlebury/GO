<?php
// Requiring go_functions.php give us acces to the curPageURL() function
require_once "go_functions.php";
require_once "go.php";

$name = "";

if (isset($_SESSION["AUTH"]) && $_SESSION["AUTH"]->isAuthenticated()) {
  $name = $_SESSION["AUTH"]->getCurrentUserName();
}

require_once $institutions[$institution]['header'];
