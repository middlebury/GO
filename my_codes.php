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

// Superadmin may admin all codes so show all
if (isSuperAdmin($user->getName())) {
	print "<p><a href='all_codes.php'>View all codes</a></p>";
}

// If an update message was set prior to a redirect
// to this page display it and clear the message.
if (isset($_SESSION['update_message'])) {
	print $_SESSION['update_message'];
	unset($_SESSION['update_message']);
}

	$codes = $user->getCodes();

	if (count($codes) > 0) {
		print "<ul>";
		foreach ($codes as $name => $code) {
			print "<li><a href='update2.php?code=" . $code->getName() . "&amp;institution=" . $code->getInstitution() . "&amp;url=" . urlencode(curPageURL()) . "'>" . $code->getName() . "</a></li>";
		}
		print "</ul>";
	} //end if (count($codes) > 0) {

?>


<?php
require_once "footer.php";
?>