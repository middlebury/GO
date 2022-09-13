<?php
//go.php handles the session and xss check for admin
//pages and pages where a session is necessary
require_once "go.php";
require_once "go_functions.php"; //for access to isSuperAdmin()

//check for xss attempt
if ($_POST['xsrfkey'] != $_SESSION['xsrfkey']) {
	die("Session variables do not match");
}

// Check for user authentication and POST values
if (!isset($_SESSION['AUTH']) || !isset($_POST['admin_name']) || !isset($_POST['codes']) || $_POST['admin_name'] == '') {
	if (isset($_POST['form_url'])){
		$_SESSION['update_message'][] = "<p class='update_message_failure'>Inappropriate values passed. Make sure you've checked at least one check box and have entered an admin username.</p>";
		die(header("location: " . $_POST['form_url']));
	} else {
		die(header("You must be authenticated and pass appropriate values to access this page."));
	}
}

// The admin to add or remove from multiple codes
$bulk_admin = trim($_POST['admin_name']);

//Check if admin is valid
if (!$_SESSION["AUTH"]->getId($bulk_admin)) {
	$_SESSION['update_message'][] = "<p class='update_message_failure'>User ".$bulk_admin." is not a valid user. Check that the user name is correct.</p>";
	die(header("location: " . $_POST['form_url']));
}

//Check if admin is valid chars
if (!Code::isValidAdmin($bulk_admin)) {
	$_SESSION['update_message'][] = "<p class='update_message_failure'>The admin you are trying to add contains invalid characters. Admins may only contain letters. Given ".$bulk_admin."</p>";
	$_SESSION['field_id_in_error'] = 'add_admin_text';
	// Redirect to originating location
	die(header("location: " . $_POST['form_url']));
}

// Instantiate user(s)
$user = new User($_SESSION["AUTH"]->getId());
$current_user = $user;

//super admins can work with a user that is not themselves
if (isSuperAdmin($user->getName())) {
	if($_POST['current_user_id'] != null) {
		$current_user = new User($_SESSION["AUTH"]->getId($_POST['current_user_id']));
		$_SESSION['current_user_id'] = $_POST['current_user_id'];
	}
}

// Get codes for all institutions
foreach ($_POST['codes'] as $inst => $shortcuts) {
	foreach ($shortcuts as $name => $value) {
		$codes[] = $current_user->getCode((string) $name, $inst);
	}
}

// Bulk adding admin behavior
if (isset($_POST['bulk_admin_add'])) {

	foreach ($codes as $code) {

		// Check to see if user is already an admin
		if ($code->isAdmin($_SESSION["AUTH"]->getId($bulk_admin))) {
			// Set a message saying the user is already an admin
			$_SESSION['update_message'][] = "<p class='update_message_failure'>User ".$bulk_admin." is already an admin of '".$code->getName()."'.</p>";
		} else {
			// Add the user to the code and set a message
			$code->addUser($_SESSION["AUTH"]->getId($bulk_admin));
			$_SESSION['update_message'][] = "<p class='update_message_success'>User ".$bulk_admin." was added as an admin of '".$code->getName()."'.</p>";
		}

	}

// Bulk removing admin behavior
} elseif (isset($_POST['bulk_admin_remove'])) {

	foreach ($codes as $code) {

		// Check to see if user is already an admin
		if ($code->isAdmin($_SESSION["AUTH"]->getId($bulk_admin))) {
			if(count($code->getUsers()) <= 1) {
				// Don't allow removal if there is only one admin
				$_SESSION['update_message'][] = "<p class='update_message_failure'>You are the only admin of code ".$code->getName()." and were not removed as admin. Please add another admin before removing ".$bulk_admin." as admin.</p>";
			} else {
				// Remove the user to the code and set a message
				$code->delUser($_SESSION["AUTH"]->getId($bulk_admin));
				$_SESSION['update_message'][] = "<p class='update_message_success'>User ".$bulk_admin." was removed as an admin of '".$code->getName()."'.</p>";
			}
		} else {
			// Set a message saying the user is not an admin
			$_SESSION['update_message'][] = "<p class='update_message_failure'>User ".$bulk_admin." is not an admin of '".$code->getName()."'.</p>";
		}

	}
}

die(header("location: " . $_POST['form_url']));
