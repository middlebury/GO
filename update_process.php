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
		
		// Instantiate a code object using the submitted name/institution
		$code = new Code($_POST['code'], $_POST['institution']);
		
		// We have two submit buttons on the previous form so we need to acount for both
		// We want to do the following when "Apply" aka. "update" is pressed
		if(isset($_POST['update'])) {
			
			//update url in database
			$code->setUrl($_POST['update_url'], true);
			//update description in database
			$code->setDescription($_POST['update_description'], true);
			//update show in gotionary in database
			$code->setPublic((bool) $_POST['public'], true);
			
			// ADD ALIAS STUFF
			
			if (isset($_POST['alias_list'])) {
				foreach ($_POST['alias_list'] as $current_alias) {
					// Trim in case there is extra whitespace
					$current_alias = trim($current_alias);
					// Make a new alias and set a message
					$alias = new Alias($current_alias, $_POST['code'], $_POST['institution']);
					$_SESSION['update_message'][] = "<p class='update_message'>Alias ".$current_alias." was added to '".$code->getName()."'.</p>";
				}
			}
			
			// ADD ADMIN STUFF
			
			if (isset($_POST['admin_list'])) {
				foreach ($_POST['admin_list'] as $current_admin) {
					// Trim in case there is extra whitespace
					$current_admin = trim($current_admin);
					if ($_SESSION["AUTH"]->getId($current_admin)) {
						// Check to see if user is already an admin
						$select = $connection->prepare("SELECT user FROM user_to_code WHERE user = ? AND code = ?");
  					$select->bindValue(1, $_SESSION["AUTH"]->getId($current_admin));
  					$select->bindValue(2, $_POST['code']);
						$select->execute();
						$result = $select->fetchAll();
						// If they aren't already an admin
						if (!count($result)) {
							// Add the user to the code and set a message
							$code->addUser($_SESSION["AUTH"]->getId($current_admin));
							$_SESSION['update_message'][] = "<p class='update_message'>User ".$current_admin." was added as an admin of '".$code->getName()."'.</p>";
						}
						else {
							// Otherwise set a message saying the user is already an admin
							$_SESSION['update_message'][] = "<p class='update_message'>User ".$current_admin." is already an admin of '".$code->getName()."'.</p>";
						}
					}
				}
			}
			
			// DELETE ALIAS STUFF
			
			if (isset($_POST['alias_list_del'])) {
				foreach ($_POST['alias_list_del'] as $current_alias) {
					// Trim in case there is extra whitespace
					$current_alias = trim($current_alias);
					// Check to see if the same alias is being added. If so, don't delete it.
					if (isset($_POST['alias_list'])) {
						$dont_delete_current_alias = 0;
						foreach ($_POST['alias_list'] as $add_alias) {
							if ($add_alias == $current_alias) {
								$dont_delete_current_alias = 1;
							}
							if (!$dont_delete_current_alias) {
								$alias = new Alias($current_alias, $_POST['code'], $_POST['institution']);
								$alias->delete();	
								$_SESSION['update_message'][] = "<p class='update_message'>Alias ".$current_alias." was removed from '".$code->getName()."'.</p>";
							}
						}
					// Otherwise go ahead and delete it.
					} else {
						$alias = new Alias($current_alias, $_POST['code'], $_POST['institution']);
						$alias->delete();
						$_SESSION['update_message'][] = "<p class='update_message'>Alias ".$current_alias." was removed from '".$code->getName()."'.</p>";
					}
				}
			}
			
			// DELETE ADMIN STUFF
			
			if (isset($_POST['admin_list_del'])) {
				foreach ($_POST['admin_list_del'] as $current_admin) {
					// Trim in case there is extra whitespace
					$current_admin = trim($current_admin);
					// Check to see if the same admin is being added. If so, don't delete it.
					if (isset($_POST['admin_list'])) {
						$dont_delete_current_admin = 0;
						foreach ($_POST['admin_list'] as $add_admin) {
							if ($add_admin == $current_admin) {
								$dont_delete_current_admin = 1;
							}
							if (!$dont_delete_current_admin) {
								$code->delUser($_SESSION["AUTH"]->getId($current_admin));
								$_SESSION['update_message'][] = "<p class='update_message'>User ".$current_admin." was removed as an admin of '".$code->getName()."'.</p>";
							}
						}
					// Otherwise go ahead and delete it.
					} else {
						$code->delUser($_SESSION["AUTH"]->getId($current_admin));
						$_SESSION['update_message'][] = "<p class='update_message'>User ".$current_admin." was removed as an admin of '".$code->getName()."'.</p>";
					}
				}
			}
			
		}
		elseif(isset($_POST['delete'])) {
			print "Delete was pressed";
			$_SESSION['update_message'][] = "<p class='update_message'>The code " . $code->getName() . " was deleted.</p>";
		}

	} //end if (isSuperAdmin($_SESSION['AUTH']->getId()) || $is_admin) {

} //end if (isset($_SESSION['AUTH'])) {

// Redirect to originating location
header("location: " . $_POST['url']);