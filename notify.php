<?php
require_once "functions.php";
require_once "go.php";
require_once "header.php";
require_once "admin_nav.php";
?>

<div class="content">
	<div id="response"></div>
	
<?php
global $connection;
$user = new User($_SESSION["AUTH"]->getId());
?>
<input type='hidden' id='xsrfkey' value='<?php echo $_SESSION['xsrfkey']; ?>'/>
<div id="notify">
	<h2>Notification</h2>
	<p>We check all of our GO shortcuts every night to see whether there are problems. If you want, we can send you an email letting you know if we receive any errors trying to access the sites for which you've created GO shortcuts.</p>
	<p>
		<input id="notify_yes" name="notify" type="radio" value="1" <?php if ($user->getNotify()) echo "checked='checked'"; ?> />
		<label for="notify_yes">Yes</label>
		<input id="notify_no" name="notify" type="radio" value="0" <?php if (!$user->getNotify()) echo "checked"; ?> />
		<label for="notify_no">No</label>
	</p>
	<p>
		<input id="notify_action" name="action" type="hidden" value="notify" />
		<input id="notify_submit" type="button" value="Submit" onclick="notify();" />
	</p>
</div>
<?php
require_once "footer.php";
?>