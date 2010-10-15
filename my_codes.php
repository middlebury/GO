<?php
// Require go_functions so we have access to function isSuperAdmin
require_once "go_functions.php";
require_once "header.php";
require_once "admin_nav.php";
?>

<div class="content">
	<div id="response"></div>

<?php

// Show all codes the currently logged in user may admin

$user = new User($_SESSION["AUTH"]->getId());

// If an update message was set prior to a redirect
// to this page display it and clear the message.
if (isset($_SESSION['update_message'])) {
	foreach ($_SESSION['update_message'] as $message) {
		print $message;
	}
	unset($_SESSION['update_message']);
}

print "<h2>" . $_SESSION["AUTH"]->getName() . "'s Shortcuts</h2>";

// Superadmin may admin all codes so show a link to "show all"
if (isSuperAdmin($user->getName())) {
	print "<p>As a superadmin you have the option to <a href='all_codes.php'>view a list of all codes</a>.</p>";
}
	
	// Get the codes the current user can edit
	$codes = $user->getCodes();
	// If there are any, put them in a table with editing options
	if (count($codes) > 0) {
		print "<table id='my_codes_table'>
		<tr>
			<th>Go Shortcut</th>
			<th>Description</th>
			<th>Aliases</th>
			<th>Institution</th>
			<th>Actions</th>
		</tr>";
		foreach ($codes as $code) {
			$current_aliases = array();
			$aliases = $code->getAliases();
			foreach ($aliases as $thisalias) {
				$current_aliases[] = $thisalias->getName();
			}
			$current_aliases = implode(', ', $current_aliases);
			print "<tr>
				<td>
					<a href='" . htmlspecialchars($code->getUrl()) . "'>" . $code->getName() . "</a>
				</td>
				<td>
					" . htmlspecialchars($code->getDescription()) . "
				</td>
				<td>
					" . $current_aliases . "
				</td>
				<td>
					" . $code->getInstitution() . "
				</td>
				<td>
				
					<a class='edit_button' href='update.php?code=" . $code->getName() . "&amp;institution=" . $code->getInstitution() . "&amp;url=" . urlencode(curPageURL()) . "'><input type='button' value='Edit Shortcut' /></a>

					<a class='edit_button' href='info.php?code=".$code->getName()."'><input type='button' value='Info' /></a>";
					if (isSuperAdmin($_SESSION["AUTH"]->getId())) {
						print "\n\t\t\t\t<a class='edit_button' href='details.php?code=".$code->getName()."&amp;institution=".$code->getInstitution()."' onclick=\"var details=window.open(this.href, 'details', 'width=700,height=400,scrollbars=yes,resizable=yes'); details.focus(); return false;\"><input type='button' value='History' /></a>";						
					}
					print
				"</td>
			</tr>";
		}
		print "</table>";
	} //end if (count($codes) > 0) {

require_once "footer.php";
