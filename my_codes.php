<?php
// Require go_functions so we have access to function isSuperAdmin
require_once "go_functions.php";
require_once "header.php";
require_once "admin_nav.php";
?>

<!-- Include jQuery/JS -->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script src="my_codes.js" type="text/javascript"></script>

<div class="content">
	<div id="response"></div>

<?php

//validation
if ($_POST != array()) {
	if (!$_SESSION["AUTH"]->getId($_POST['other_username'])) {
		$_SESSION['update_message'][] = "<p class='update_message_failure'>User is not a valid user. Check that the user name is correct.</p>";
		unset($_POST['other_username']);
	}
}

// Show all codes the currently logged in user may admin
$user = new User($_SESSION["AUTH"]->getId());

//current user is the user whose codes we will see
$current_user = $user;
$current_user_id = '';

//only for superadmins, the current user may be different from themselves
if (isSuperAdmin($user->getName())) {
	if(isset($_POST['other_username'])) {
		$current_user_id = trim($_POST['other_username']);
		$current_user = new User($_SESSION["AUTH"]->getId($current_user_id));
		unset($_POST['other_username']);
	} elseif (isset($_SESSION['current_user_id'])) {
		$current_user_id = $_SESSION['current_user_id'];
		$current_user = new User($_SESSION["AUTH"]->getId($current_user_id));
		unset($_SESSION['current_user_id']);
	}
}

// If an update message was set prior to a redirect
// to this page display it and clear the message.
if (isset($_SESSION['update_message'])) {
	foreach ($_SESSION['update_message'] as $message) {
		print $message;
	}
	unset($_SESSION['update_message']);
}

	print "<h2>" . Go::getUserDisplayName($current_user->getName()) . "'s Shortcuts</h2>";

// Superadmin may admin all codes so show a link to "show all" and
// submit a user whose codes they'd like to see.
if (isSuperAdmin($user->getName())) {
	print "<p>As a superadmin you have the option to <a href='all_codes.php'>view a list of all codes</a> or view/subscribe to a <a href='feed/'>feed of new codes <img src='application-icons/feed.png' alt='rss icon' /></a>.</p>
	
	<form action='my_codes.php' method='post' id='other_users_codes'>
	<p><strong>Edit Codes for User:</strong> username <input type='text' name='other_username' max='30' required='required' autocomplete='yes' /> <input type='submit' form='other_users_codes' name='show_users_codes' value=\"Show User's Codes\" /></p>
	</form>";
}	
	
	// Get the codes the current user can edit
	$codes = $current_user->getCodes();
	// If there are any, put them in a table with editing options
	if (count($codes) > 0) {
		print "<form action='process_batchadmin.php' method='post' id='bulk_admin'>
		<p><strong>Bulk Admin Add/Remove:</strong> Admin username <input type='text' name='admin_name' max='30' required='required' autocomplete='yes' /> <input type='submit' form='bulk_admin' name='bulk_admin_add' value='Add admin to checked codes' /> <input type='submit' form='bulk_admin' name='bulk_admin_remove' value='Remove admin from checked codes' /><input type='hidden' name='current_user_id' value='". $current_user_id ."'> </p>
		<table id='my_codes_table'>
		<tr>
			<th></th>
			<th>Go Shortcut</th>
			<th>Description</th>
			<th>Admins</th>
			<th>Aliases</th>
			<th>Institution</th>
			<th>Actions</th>
		</tr>
		<tr>
			<td>
				<input type='checkbox' id='check_all' />
			</td>
			<td>Check/Uncheck All</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>";
		foreach ($codes as $code) {
			$current_aliases = array();
			$aliases = $code->getAliases();
			foreach ($aliases as $thisalias) {
				$current_aliases[] = $thisalias->getName();
			}
			$current_aliases = implode(', ', $current_aliases);
			$current_users = array();
			$users = $code->getUsers();
			foreach ($users as $thisuser) {
				if ($thisuser->getName() != '') {
						$current_users[] = trim(preg_replace('#\(.+\)#','',Go::getUserDisplayName($thisuser->getName())));
				}
			}
			$current_users = implode(', ', $current_users);
			print "<tr>
				<td>
					<input type='checkbox' class='code_checkbox' name='codes[".$code->getInstitution()."][".$code->getName()."]'>
				</td>
				<td>
					<a href='" . htmlspecialchars($code->getUrl()) . "'>" . $code->getName() . "</a>
				</td>
				<td>
					" . htmlspecialchars($code->getDescription()) . "
				</td>
				<td>
					" . $current_users . "
				</td>
				<td>
					" . $current_aliases . "
				</td>
				<td>
					" . $code->getInstitution() . "
				</td>
				<td>
				
					<a class='edit_button' href='update.php?code=" . $code->getName() . "&amp;institution=" . $code->getInstitution() . "&amp;url=" . urlencode(curPageURL()) . "'><input onclick='window.location=\"update.php?code=" . $code->getName() . "&amp;institution=" . $code->getInstitution() . "&amp;url=" . urlencode(curPageURL()) . "\"' type='button' value='Edit Shortcut' /></a>

					<a class='edit_button' href='info.php?code=".$code->getName()."'><input type='button' onclick='window.location=\"info.php?code=".$code->getName()."\"' value='Info' /></a>";
					if (isSuperAdmin($_SESSION["AUTH"]->getId())) {
						print "\n\t\t\t\t<a class='edit_button' href='details.php?code=".$code->getName()."&amp;institution=".$code->getInstitution()."' onclick=\"var details=window.open(this.href, 'details', 'width=700,height=400,scrollbars=yes,resizable=yes'); details.focus(); return false;\"><input type='button' value='History' /></a>";						
					}
					print
				"</td>
			</tr>";
		}
		print "</table>";
	
	} //end if (count($codes) > 0) {
	
	print '<p>
<!-- Pass the current URL --> 
<input type="hidden" name="form_url" value="'. htmlentities(curPageURL()) .'" />
<input type="hidden" name="xsrfkey" value="'. $_SESSION['xsrfkey'] .'" />
</p>
</form>';

require_once "footer.php";
