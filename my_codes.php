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
// What codes may the current user admin?

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

// Superadmin may admin all codes so show all
if (isSuperAdmin($user->getName())) {
	print "<p>As a superadmin you have the ability to <a href='all_codes.php'>view a list of all codes</a>.</p>";
}

	$codes = $user->getCodes();
	if (count($codes) > 0) {
		print "<table id='my_codes_table'>
		<tr>
			<th>Go Shortcut</th>
			<th>Description</th>
			<th>Aliases</th>
			<th>Institution</th>
			<th>Edit</th>
			<th>Info</th>
		</tr>";
		foreach ($codes as $code) {
			$current_aliases = array();
			$aliases = $code->getAliases();
			foreach ($aliases as $thisalias) {
				$current_aliases[] = $thisalias->getName();
			}
			//var_dump(get_class_methods($code));
			$current_aliases = implode(', ', $current_aliases);
			print "<tr>
				<td>
					<a href='" . $code->getUrl() . "'>" . $code->getName() . "</a>
				</td>
				<td>
					" . $code->getDescription() . "
				</td>
				<td>
					" . $current_aliases . "
				</td>
				<td>
					" . $code->getInstitution() . "
				</td>
				<td class='no_border'>
					<a class='edit_button' href='update2.php?code=" . $code->getName() . "&amp;institution=" . $code->getInstitution() . "&amp;url=" . urlencode(curPageURL()) . "'><input type='button' value='Edit Shortcut' /></a>
				</td>
				<td class='no_border'>
					<a class='edit_button' href='flag_details.php?code=".$code->getName()."&amp;institution=".$code->getInstitution()."' onclick=\"var details=window.open(this.href, 'details', 'width=700,height=400,scrollbars=yes,resizable=yes'); details.focus(); return false;\"><input type='button' value='Info' />
					</a>
				</td>
			</tr>";
		}
		print "</table>";
	} //end if (count($codes) > 0) {

require_once "footer.php";
?>