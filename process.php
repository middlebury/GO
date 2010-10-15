<?php
//go_functions.php gives us access to the isSuperAdmin and isAdmin functions
require_once "go_functions.php";
//go.php handles the session and xss check for admin
//pages and pages where a session is necessary
require_once "go.php";

// Debugging code
//var_dump($_POST);
//die();

// add the results of $_POST to $_SESSION. We'll use
// this to repopulate values in the form if it fails
// validation
$_SESSION['form_values'] = $_POST;

//check for xss attempt
if ($_POST['xsrfkey'] != $_SESSION['xsrfkey']) {
	die("Session variables do not match");
}

// This should only be available to authenticated users 
if (isset($_SESSION['AUTH'])) {
	
	// Is logged in user an admin?
	//$is_admin = isAdmin($_POST['code'], $_POST['institution']);

	// This is only available to authenticated users
	if (isset($_SESSION['AUTH'])) {
	//if (isSuperAdmin($_SESSION['AUTH']->getId()) || $is_admin) {
		
		// We have two submit buttons on the edit form (the one on the create for is still
		// named "update" in order to trigger the same behavior as the button on edit).
		// We want to do the following when "Apply" aka. "update" is pressed
		// if delete was pressed then delete would be set and update would not.
		// We'll do something different in that case
		if(isset($_POST['update'])) {
		
		// Check our input
		if (!Code::isValidCode($_POST['code'])) {
			// We add these to an array so that we can print out
			// more than one message at a time
			$_SESSION['update_message'][] = "<p class='update_message_failure'>The shortcut you are trying to make contains invalid characters. Shortcuts may only contain letters, numbers, and the following punctuation; _, +, ?. Given ".$_POST['code']."</p>";
			// This tells us the ID of the field that is in error
			// if this validation fails. Used to change the class
			// of the failed field when redirected back to the
			// original form
			$_SESSION['field_id_in_error'] = 'code';
			// Redirect to originating location
			die(header("location: " . $_POST['form_url']));
		}
		if (!Code::isValidUrl($_POST['update_url'])) {
			$_SESSION['update_message'][] = "<p class='update_message_failure'>The URL you are trying to set (".$_POST['update_url'].") is not valid. Please enter a properly formed URL.</p>";
			$_SESSION['field_id_in_error'] = 'update_url';
			// Redirect to originating location
			die(header("location: " . $_POST['form_url']));
		}
		if (!Code::isValidDescription($_POST['update_description'])) {
			$_SESSION['update_message'][] = "<p class='update_message_failure'>The description you are trying to set contains invalid characters. The characters allowed are letters, numbers, and common puntcuation. Please make adjustments and try again.</p>";
			$_SESSION['field_id_in_error'] = 'update_description';
			// Redirect to originating location
			die(header("location: " . $_POST['form_url']));
		}
		if (isset($_POST['alias_list'])) {
			foreach ($_POST['alias_list'] as $current_alias) {
				if (!Code::isValidCode($current_alias)) {
					$_SESSION['update_message'][] = "<p class='update_message_failure'>The alias you are trying to add contains invalid characters. Shortcuts may only contain letters, numbers, and the following punctuation; _, +, ?. Given ".$current_alias."</p>";
					$_SESSION['field_id_in_error'] = 'add_alias_text';
					// Redirect to originating location
					die(header("location: " . $_POST['form_url']));
				}
			}
		}
		if (isset($_POST['admin_list'])) {
			foreach ($_POST['admin_list'] as $current_admin) {
				if (!Code::isValidAdmin($current_admin)) {
					$_SESSION['update_message'][] = "<p class='update_message_failure'>The admin you are trying to add contains invalid characters. Admins may only contain letters. Given ".$current_admin."</p>";
					$_SESSION['field_id_in_error'] = 'add_admin_text';
					// Redirect to originating location
					die(header("location: " . $_POST['form_url']));
				}
			}
		}
		
		// Set messages for the create process (as opposed to the edit
		// process. We're using this script for both).
		// If the code is new then say it was created, otherwise it's
		// being edited and we don't need to say that.
		if (!Code::exists($_POST['code'], $_POST['institution'])) {
			$_SESSION['update_message'][] = "<p class='update_message_success'>The shortcut ".$_POST['code']." was created.</p>";
		} else {
		// If it does exist, we need to know if it's being edited or created
		// Check the "udate" value (the value of the submit button). If it's
		// 'Create Shortcut' then complain that it already exists (if we're editing
		// then it should already exist and we don't need a message. We also
		// should not run the rest of the script, we don't want the values being
		// updated via the create screen
			if ($_POST['update'] == 'Create Shortcut') {
				$_SESSION['update_message'][] = "<p class='update_message_failure'>The shortcut ".$_POST['code']." already exists. The shortcut was not created. Would you like to <a href='my_codes.php'>edit your codes</a>?</p>";
				// Redirect to originating location
				die(header("location: " . $_POST['form_url']));
			}
		}
		
		// Instantiate a code object using the submitted name/institution
		$code = new Code($_POST['code'], $_POST['institution']);
			
			//update url in database
			if ($code->getUrl() != $_POST['update_url']) {
				$code->setUrl($_POST['update_url'], true);
				$_SESSION['update_message'][] = "<p class='update_message_success'>The url was set to '".$_POST['update_url']."' for shortcut ".$_POST['code'].".</p>";
			}
			//update description in database
			if ($code->getDescription() != $_POST['update_description']) {
				$code->setDescription($_POST['update_description'], true);
				$_SESSION['update_message'][] = "<p class='update_message_success'>The description was set to '".$_POST['update_description']."' for shortcut ".$_POST['code'].".</p>";
			}
			//update show in gotionary in database
			if (isSuperAdmin($_SESSION['AUTH']->getId())) {
				if ($code->getPublic() != $_POST['public']) {
					$code->setPublic((bool) $_POST['public'], true);
					if ($_POST['public']) {
						$_SESSION['update_message'][] = "<p class='update_message_success'>The publicity was set to 'true' for shortcut ".$_POST['code'].".</p>";
					} else {
						$_SESSION['update_message'][] = "<p class='update_message_success'>The publicity was set to 'false' for shortcut ".$_POST['code'].".</p>";
					}
				}
			}
			
			// ADD ALIAS STUFF
			
			if (isset($_POST['alias_list'])) {
				foreach ($_POST['alias_list'] as $current_alias) {
					// Trim in case there is extra whitespace
					$current_alias = trim($current_alias);
					// This is where we do a quick check to see if the
					// alias already exists
					$select = $connection->prepare("
					  (SELECT
  						name
					  FROM
  						alias
  					WHERE
  						institution = ?
  					AND
  						name = ?)
  					UNION
  					(SELECT
  						name
  					FROM
  						code
  					WHERE
  						institution = ?
  					AND
  						name = ?)
  					");
  				$select->execute(array($_POST['institution'], $current_alias, $_POST['institution'], $current_alias));
  				// If there are results then don't make the alias and set a message.
					if(count($select->fetchAll())) {
						$_SESSION['update_message'][] = "<p class='update_message_failure'>Alias ".$current_alias." already exists as an alias or shortcut name. Was not created as an alias of '".$code->getName()."'.</p>";
					} else {
						// Otherwise make a new alias and set a message
						$alias = new Alias($current_alias, $_POST['code'], $_POST['institution']);
						$_SESSION['update_message'][] = "<p class='update_message_success'>Alias ".$current_alias." was added to '".$code->getName()."'.</p>";
					}
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
							$_SESSION['update_message'][] = "<p class='update_message_success'>User ".$current_admin." was added as an admin of '".$code->getName()."'.</p>";
						}
						else {
							// Otherwise set a message saying the user is already an admin
							$_SESSION['update_message'][] = "<p class='update_message_failure'>User ".$current_admin." is already an admin of '".$code->getName()."'.</p>";
						}
					}
				}
			}
			
			// DELETE ALIAS STUFF
			
			if (isset($_POST['alias_list_del'])) {
				foreach ($_POST['alias_list_del'] as $current_alias) {
					// Trim in case there is extra whitespace
					$current_alias = trim($current_alias);
					// Check to see if the same alias is being added as is being deleted. If so
					// don't delete it.
					// This might sound weird, but it's in the case that a user deletes an alias
					// and then decides they want it and adds it back on the same page load.
					// It will appear in the remove and add lists but we only want to keep it.
					if (isset($_POST['alias_list'])) {
						$dont_delete_current_alias = 0;
						foreach ($_POST['alias_list'] as $add_alias) {
							// Keep the alias
							if ($add_alias == $current_alias) {
								$dont_delete_current_alias = 1;
							}
							// Don't keep the alias
							if (!$dont_delete_current_alias) {
								$alias = new Alias($current_alias, $_POST['code'], $_POST['institution']);
								$alias->delete();	
								$_SESSION['update_message'][] = "<p class='update_message_success'>Alias ".$current_alias." was removed from '".$code->getName()."'.</p>";
							}
						}
					// Otherwise none are being added so just go ahead and delete all
					// the aliases in the "delete list".
					} else {
						$alias = new Alias($current_alias, $_POST['code'], $_POST['institution']);
						$alias->delete();
						$_SESSION['update_message'][] = "<p class='update_message_success'>Alias ".$current_alias." was removed from '".$code->getName()."'.</p>";
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
								$_SESSION['update_message'][] = "<p class='update_message_success'>User ".$current_admin." was removed as an admin of '".$code->getName()."'.</p>";
							}
						}
					// Otherwise none are being added so just go ahead and delete all
					// the admins in the "delete list".
					} else {
						$code->delUser($_SESSION["AUTH"]->getId($current_admin));
						$_SESSION['update_message'][] = "<p class='update_message_success'>User ".$current_admin." was removed as an admin of '".$code->getName()."'.</p>";
					}
				}
			}
			
		}
		// If delete was pressed just delete the code and set a message.
		elseif(isset($_POST['delete'])) {
			
			// Instantiate a code object using the submitted name/institution
			$code = new Code($_POST['code'], $_POST['institution']);
			
			$code->delete();
			
			$_SESSION['update_message'][] = "<p class='update_message_success'>The shortcut " . $code->getName() . " was deleted.</p>";
		}
		// If revert changes was pressed
		elseif(isset($_POST['revert'])) {
			$_SESSION['update_message'][] = "<p class='update_message_success'>Changes on this form have been reverted to default.</p>";
			unset($_SESSION['form_values']);
			die(header("location: " . $_POST['form_url']));
		}

	} //end if (isSuperAdmin($_SESSION['AUTH']->getId()) || $is_admin) {

} //end if (isset($_SESSION['AUTH'])) {

unset($_SESSION['form_values']);

// Finally redirect to originating location
header("location: " . $_POST['url']);