<?php

require_once "config.php";
require_once "go.php";

// Unset all of the session variables.
$_SESSION = array();
// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

$dest = $institutions[$institution]['base_uri'];

if (AUTH_METHOD == 'cas') {
	GoAuthCas::configurePhpCas();
	phpCAS::logoutWithRedirectServiceAndUrl($dest, $dest);
}

//redirect on completion
header("location: ".$dest);
exit;
