<?php
//go_functions.php gives us access to the isSuperAdmin and isAdmin functions
require_once "go_functions.php";
//go.php handles the session and xss check for admin
//pages and pages where a session is necessary
require_once "go.php";

//check for xss attempt
if ($_POST['xsrfkey'] != $_SESSION['xsrfkey']) {
	die("Session variables do not match");
}

// This should only be available to authenticated users 
if (isset($_SESSION['AUTH'])) {
	
	// Is logged in user an admin?
	$is_admin = isAdmin($_POST['code'], $_POST['institution']);

	// This is only available if user is a superadmin or admin
	if (isSuperAdmin($_SESSION['AUTH']->getId()) || $is_admin) {
		
		$code = new Code($_POST['code'], $_POST['institution']);

		var_dump($_POST);

		if(isset($_POST['update'])) {
			
			print "Apply was pressed";
			
			//public must be boolean
			//(bool) $public = $_POST['public'];
			
			//update url
			$code->setUrl($_POST['update_url'], true);
			//update description
			$code->setDescription($_POST['update_description'], true);
			//update show in gotionary
			$code->setPublic((bool) $_POST['public'], true);
			
			if (isset($_POST['alias_list'])) {
				foreach ($_POST['alias_list'] as $current_alias) {
					$alias = new Alias($current_alias, $_POST['code'], $_POST['institution']);	
				}
			}
			
			if (isset($_POST['admin_list'])) {
				foreach ($_POST['admin_list'] as $current_admin) {
					$code->addUser($_SESSION["AUTH"]->getId($current_admin));
				}
			}
			
			if (isset($_POST['alias_list_del'])) {
				foreach ($_POST['alias_list_del'] as $current_alias) {
					$alias = new Alias($current_alias, $_POST['code'], $_POST['institution']);
					$alias->delete();	
				}
			}
			
			if (isset($_POST['admin_list_del'])) {
				foreach ($_POST['admin_list_del'] as $current_admin) {
					$code->delUser($_SESSION["AUTH"]->getId($current_admin));
				}
			}
			
		}
		elseif(isset($_POST['delete'])) {
			print "Delete was pressed";
		}

	} //end if (isSuperAdmin($_SESSION['AUTH']->getId()) || $is_admin) {

} //end if (isset($_SESSION['AUTH'])) {