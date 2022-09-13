<?php
require_once "header.php";
require_once "admin_nav.php";
?>

<div class="content">
	<div id="response"></div>

<?php
global $connection;
$user = new User($_SESSION["AUTH"]->getId());

// If an update message was set prior to a redirect
// to this page display it and clear the message.
if (isset($_SESSION['update_message'])) {
	foreach ($_SESSION['update_message'] as $message) {
		print $message;
	}
	unset($_SESSION['update_message']);
}
?>
<div id="notify">
	<h2>Notification</h2>
	<p>We check all of our GO shortcuts every week to see whether there are problems. If you want, we can send you an email letting you know if we receive any errors trying to access the sites for which you've created GO shortcuts.</p>
	<form action="process.php" method="post">
		<p>
			<input id="notify_yes" name="notify" type="radio" value="1" <?php if ($user->getNotify()) echo "checked='checked'"; ?> />
			<label for="notify_yes">Yes</label>
			<input id="notify_no" name="notify" type="radio" value="0" <?php if (!$user->getNotify()) echo "checked"; ?> />
			<label for="notify_no">No</label>
		</p>
		<p>
			<input id="notify_action" name="action" type="hidden" value="notify" />
			<!-- Pass the current URL -->
			<input type="hidden" name="url" value="<?php echo htmlentities(curPageURL()); ?>" />
			<input type="hidden" name="xsrfkey" value="<?php echo $_SESSION['xsrfkey']; ?>" />
			<input id="notify_submit" type="submit" value="Submit" />
		</p>
	</form>
</div>
<?php
require_once "footer.php";
