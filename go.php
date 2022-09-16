<?php

require_once(dirname(__FILE__).'/vendor/autoload.php');
require_once "config.php";
require_once "user.php";
require_once "code.php";
require_once "alias.php";

// Define admin pages and non-admin pages that need session
$admin_pages = array(
	"admin.php",
	"create.php",
	"update.php",
	"notify.php",
	"functions.php",
	"flag_admin.php",
	"logs.php",
	"login2.php",
	"my_codes.php",
	"user_codes.php",
);
$session_pages = array(
	"info.php",
	"flag.php",
	"flag_clear.php",
	"details.php",
	"gotionary.php",
	"gobacktionary.php",
	"login.php",
	"logout.php",
	"go_functions.php",
	"process.php",
	"all_codes.php",
	"process_batchadmin.php",
);
$session_pages = array_merge($session_pages, $admin_pages);
$current_page = basename($_SERVER['PHP_SELF']);

// Initialize session on all but the redirect
if (in_array($current_page, $session_pages)) {
	session_name('GOSID');
	session_start();

	//set up a x-site forgery key
	if (!isset($_SESSION['xsrfkey'])) {
		$_SESSION['xsrfkey'] = uniqid('', true);
	}

	//also set up an array to hold what codes
	//the authenticated user has flagged this session
	if (!isset($_SESSION['flagged'])) {
		$_SESSION['flagged'] = array();
	}
}

// Force authentication on admin pages
if (in_array($current_page, $admin_pages)) {
	if (AUTH_METHOD == 'ldap') {
		if (!isset($_SESSION["AUTH"]) && $current_page != "login.php") {
  		header("Location: login.php?r=" . $_SERVER["PHP_SELF"]);
  		exit();
		}
	} else if (AUTH_METHOD == 'cas') {
		try {
			$_SESSION["AUTH"] = new GoAuthCas();
		}
		catch (AuthorizationFailedException $e) {
			header("Location: authfail.php");
			exit();
		}

	} else {
		throw new Exception('Unknown Auth Method');
	}
}

// Initialize database
try {
	global $connection;
	$connection = new PDO(
  		"mysql:dbname=" . GO_DATABASE_NAME . ";host=" . GO_DATABASE_HOST . ";",
  		GO_DATABASE_USER, GO_DATABASE_PASS, array (PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
} catch (Exception $e) {
	throw new Exception('Could not connect to the database.');
}

// Match the institution by URL.
global $institutions;
foreach ($institutions as $inst => $opts) {
	if (strpos('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $opts['base_uri']) !== FALSE) {
		$institution = $inst;
		break;
	}
}
// Set the first institution as the default if we haven't matched by URL.
if (!isset($institution)) {
	reset($institutions);
	$institution = key($institutions);
}

/**
 * Answer a URL equivalent to the current one, but for another institution.
 *
 * @param string $institution
 * @return string
 * @since 6/18/10
 */
function equivalentUrl ($institution) {
	global $institutions;
	if (!isset($institutions[$institution]))
		throw new Exception ("$institution was not found in the configured list.");

	$url = $institutions[$institution]['base_uri'];
	$url .= basename($_SERVER['SCRIPT_NAME']);
	if (strlen($_SERVER['QUERY_STRING']))
		$url .= '?'.$_SERVER['QUERY_STRING'];
	return $url;
}
